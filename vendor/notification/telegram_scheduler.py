#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Telegram Notification Scheduler – Service-safe (NSSM/Windows)
- Rotating log file (UTF-8)
- Graceful shutdown via SIGTERM/SIGINT
- Robust requests with retries
- ENV overrides for secrets & timezone
"""

import os
import sys
import time
import json
import signal
import logging
from logging.handlers import RotatingFileHandler
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from typing import List, Dict, Any, Tuple, Optional

import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

import schedule
import pytz
import threading

# =========================
# Konfigurasi & ENV
# =========================
APP_NAME = "hajj_telegram_scheduler"
LOG_FILE = os.getenv("LOG_FILE", f"{APP_NAME}.log")
LOG_MAX_BYTES = int(os.getenv("LOG_MAX_BYTES", str(5 * 1024 * 1024)))  # 5 MB
LOG_BACKUPS = int(os.getenv("LOG_BACKUPS", "5"))
APP_TZ = os.getenv("APP_TZ", "Asia/Hong_Kong")  # default GMT+8 (Hong Kong)

@dataclass
class TelegramConfig:
    bot_token: str = os.getenv("TELEGRAM_BOT_TOKEN", "8190461646:AAFS7WIct-rttUvAP6rKXvqnEYRURnGHDCQ")
    chat_id: str = os.getenv("TELEGRAM_CHAT_ID", "-1003154039523")
    api_url: str = os.getenv("TELEGRAM_API_URL", "https://api.telegram.org/bot")

@dataclass
class HajjConfig:
    base_url: str = os.getenv("HAJJ_BASE_URL", "https://menfins.site/hajj/")
    api_endpoint: str = os.getenv("HAJJ_API_ENDPOINT", "/api/schedule")
    timeout: int = int(os.getenv("HTTP_TIMEOUT", "30"))

# AFTER milestones (menit setelah jadwal)
AFTER_MILESTONES: List[Tuple[int, str]] = [
    (180, "3 jam setelah jadwal"),
    (240, "4 jam setelah jadwal"),
    (270, "4 jam 30 menit setelah jadwal"),
    (290, "4 jam 50 menit setelah jadwal"),
]

# =========================
# Logging (aman untuk service)
# =========================
logger = logging.getLogger(APP_NAME)
logger.setLevel(logging.INFO)

# Rotating file handler (UTF-8)
file_handler = RotatingFileHandler(LOG_FILE, maxBytes=LOG_MAX_BYTES, backupCount=LOG_BACKUPS, encoding="utf-8")
file_handler.setFormatter(logging.Formatter("%(asctime)s - %(levelname)s - %(message)s"))
logger.addHandler(file_handler)

# Console handler opsional (hanya jika ada TTY, agar tidak error di service)
if sys.stdout and hasattr(sys.stdout, "isatty") and sys.stdout.isatty():
    console_handler = logging.StreamHandler(stream=sys.stdout)
    console_handler.setFormatter(logging.Formatter("%(asctime)s - %(levelname)s - %(message)s"))
    logger.addHandler(console_handler)

# =========================
# Utilitas Requests Session dgn Retry
# =========================
def make_session() -> requests.Session:
    s = requests.Session()
    s.headers.update({
        "Content-Type": "application/json",
        "User-Agent": "Hajj-Notification-Scheduler/1.0"
    })
    retries = Retry(
        total=5,
        backoff_factor=0.8,
        status_forcelist=(429, 500, 502, 503, 504),
        allowed_methods=frozenset(["GET", "POST"]),
        raise_on_status=False,
    )
    adapter = HTTPAdapter(max_retries=retries, pool_connections=10, pool_maxsize=20)
    s.mount("http://", adapter)
    s.mount("https://", adapter)
    return s

# =========================
# Notifier Telegram
# =========================
class TelegramNotifier:
    def __init__(self, config: TelegramConfig, tz: str):
        self.config = config
        self.session = make_session()
        self.timezone = pytz.timezone(tz)

    def get_current_time(self) -> datetime:
        return datetime.now(self.timezone)

    def format_time(self, dt: datetime) -> str:
        return dt.strftime('%d %B %Y %H:%M')

    def send_message(self, message: str, parse_mode: str = 'HTML') -> bool:
        url = f"{self.config.api_url}{self.config.bot_token}/sendMessage"
        payload = {
            "chat_id": self.config.chat_id,
            "text": message,
            "parse_mode": parse_mode,
            "disable_web_page_preview": True,
        }
        try:
            resp = self.session.post(url, json=payload, timeout=15)  # kirim sebagai JSON
            resp.raise_for_status()
            logger.info("Pesan berhasil dikirim ke Telegram")
            return True
        except requests.exceptions.RequestException as e:
            logger.error(f"Gagal mengirim pesan ke Telegram: {e}")
            return False
        except Exception as e:
            logger.error(f"Error tidak terduga saat mengirim pesan: {e}")
            return False

    def build_alert_message(self, schedule_data: Dict[str, Any], alert_label: str) -> str:
        tanggal = schedule_data.get('tanggal', '')
        jam = schedule_data.get('jam', '')
        total_peserta = schedule_data.get('count_total', 0)
        tanpa_barcode = schedule_data.get('count_tidak_ada_barcode', 0)
        dengan_barcode = schedule_data.get('count_barcode_lengkap', 0)
        jam_sistem = schedule_data.get('jam_sistem', '')
        jam_mekkah = schedule_data.get('jam_mekkah', '')

        try:
            jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
        except Exception:
            jam_display = jam
        try:
            tanggal_display = datetime.strptime(tanggal, '%Y-%m-%d').strftime('%d %B %Y')
        except Exception:
            tanggal_display = tanggal

        message = (
            f"🔔 <b>PENGINGAT • {alert_label}</b>\n"
            f"📅 <b>Tanggal:</b> {tanggal_display}\n"
            f"🕐 <b>Jam Sistem:</b> {jam_sistem or jam_display}\n"
            f"🕐 <b>Jam Mekkah:</b> {jam_mekkah}\n\n"
            f"📊 <b>STATISTIK PESERTA</b>\n"
            f"👥 Total: <b>{total_peserta}</b>\n"
            f"✅ Dengan Barcode: <b>{dengan_barcode}</b>\n"
            f"❌ Tanpa Barcode: <b>{tanpa_barcode}</b>\n"
        )

        if tanpa_barcode > 0:
            message += "⚠️ <b>PERHATIAN:</b> Masih ada peserta yang belum upload barcode!\n"

        return message

    def send_schedule_alert(self, schedule_data: Dict[str, Any], alert_label: str) -> bool:
        return self.send_message(self.build_alert_message(schedule_data, alert_label))

    def send_overdue_report(self, overdue_schedules: List[Dict[str, Any]]) -> bool:
        if not overdue_schedules:
            return True
        now = self.get_current_time()
        total_overdue = len(overdue_schedules)
        total_no_barcode = sum(s.get('count', 0) for s in overdue_schedules)

        lines = [
            "📋 <b>LAPORAN JADWAL TERLEWAT</b>",
            f"⏰ <b>Waktu Laporan:</b> {self.format_time(now)}",
            "",
            "📊 <b>RINGKASAN</b>",
            f"📅 Total Jadwal Terlewat: <b>{total_overdue}</b>",
            f"❌ Total Peserta Tanpa Barcode: <b>{total_no_barcode}</b>",
            "",
            "📋 <b>DETAIL</b>"
        ]

        for i, s in enumerate(overdue_schedules[:10], 1):
            tanggal = s.get('tanggal', '')
            jam = s.get('jam', '')
            total_peserta = s.get('count', 0)
            overdue_hours = s.get('overdue_hours', 0)
            try:
                jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
                tanggal_display = datetime.strptime(tanggal, '%Y-%m-%d').strftime('%d/%m/%Y')
            except Exception:
                jam_display = jam
                tanggal_display = tanggal
            lines.append(f"{i}. 📅 {tanggal_display} 🕐 {jam_display} | 👥 {total_peserta} | ⏰ {overdue_hours} jam terlewat")

        if total_overdue > 10:
            lines.append(f"... dan {total_overdue - 10} jadwal lainnya")

        return self.send_message("\n".join(lines))

# =========================
# Client API
# =========================
class HajjAPIClient:
    def __init__(self, config: HajjConfig, tz: str):
        self.config = config
        self.session = make_session()
        self.timezone = pytz.timezone(tz)

    def get_current_time(self) -> datetime:
        return datetime.now(self.timezone)

    def _fetch(self, url: str, params: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        resp = self.session.get(url, params=params, timeout=self.config.timeout)
        resp.raise_for_status()
        return resp.json()

    def get_schedule_data(self, hours_ahead: float) -> List[Dict[str, Any]]:
        now = self.get_current_time()
        target = now + timedelta(hours=hours_ahead)
        params = {"tanggal": target.strftime("%Y-%m-%d")}
        try:
            url = f"{self.config.base_url}{self.config.api_endpoint}"
            data = self._fetch(url, params)
            if data.get("status") == "success":
                logger.info(f"API OK (ahead={hours_ahead}) tanggal={target.strftime('%Y-%m-%d')}")
                return data.get("data", [])
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API: {e}")
        return []

    def get_overdue_schedules(self) -> List[Dict[str, Any]]:
        try:
            url = f"{self.config.base_url}/api/overdue-schedules"
            data = self._fetch(url)
            if data.get("status") == "success":
                logger.info(f"Overdue API OK - count: {data.get('count', 0)}")
                return data.get("data", [])
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API overdue: {e}")
        return []

    def get_schedule_data_for_date(self, tanggal: str) -> List[Dict[str, Any]]:
        """Mengambil data jadwal untuk tanggal tertentu"""
        try:
            url = f"{self.config.base_url}/api/schedule"
            params = {"tanggal": tanggal}
            data = self._fetch(url, params)
            if data.get("status") == "success":
                logger.info(f"Schedule API OK - {tanggal}")
                return data.get("data", [])
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API schedule: {e}")
        return []

    def get_pending_barcode_data(self, tanggal: str, jam: str) -> Dict[str, Any]:
        """Mengambil data pending barcode untuk jadwal tertentu"""
        try:
            url = f"{self.config.base_url}/api/pending-barcode"
            params = {"tanggal": tanggal, "jam": jam}
            data = self._fetch(url, params)
            if data.get("status") == "success":
                logger.info(f"Pending barcode API OK - {tanggal} {jam}")
                # Kembalikan payload data inti agar cocok dengan build_alert_message
                return data.get("data", {}) or {}
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API pending barcode: {e}")
        return {}

# =========================
# State Anti-Duplikat & Reminder
# =========================
@dataclass
class ScheduleFlags:
    # flag lama dibiarkan (tidak dipakai lagi untuk before)
    sent_2h: bool = False
    sent_1h: bool = False
    sent_30m: bool = False
    sent_10m: bool = False
    reminder_active: bool = False
    last_reminder_minute: int = -1
    completed: bool = False
    # penanda terkirim milestone AFTER (key=menit, value=True jika sudah terkirim)
    after_sent_map: Dict[int, bool] = field(default_factory=dict)

class NotificationState:
    def __init__(self):
        self._state: Dict[Tuple[str, str], ScheduleFlags] = {}

    def get(self, tanggal: str, jam: str) -> ScheduleFlags:
        key = (tanggal, jam)
        if key not in self._state:
            self._state[key] = ScheduleFlags()
        return self._state[key]

    def complete(self, tanggal: str, jam: str):
        self.get(tanggal, jam).completed = True

    def is_completed(self, tanggal: str, jam: str) -> bool:
        return self.get(tanggal, jam).completed

# =========================
# Scheduler Utama
# =========================
class NotificationScheduler:
    def __init__(self, stop_event: threading.Event):
        self.stop_event = stop_event
        self.telegram_config = TelegramConfig()
        self.hajj_config = HajjConfig()
        self.timezone_name = APP_TZ
        self.timezone = pytz.timezone(self.timezone_name)

        self.telegram_notifier = TelegramNotifier(self.telegram_config, self.timezone_name)
        self.hajj_client = HajjAPIClient(self.hajj_config, self.timezone_name)
        self.state = NotificationState()
        self.setup_schedules()

    def get_current_time(self) -> datetime:
        return datetime.now(self.timezone)

    # Helper waktu & key
    def parse_schedule_dt(self, tanggal: str, jam: str) -> datetime:
        fmt = "%H:%M:%S" if len(jam) == 8 else "%H:%M"
        naive = datetime.strptime(f"{tanggal} {jam}", f"%Y-%m-%d {fmt}")
        return self.timezone.localize(naive)

    def minutes_to_schedule(self, schedule_dt: datetime, now: datetime) -> float:
        return (schedule_dt - now).total_seconds() / 60.0

    def setup_schedules(self):
        schedule.every().minute.do(self.check_alert_window).tag("alerts")
        schedule.every().hour.do(self.check_overdue_schedules).tag("overdue")
        # jam 08:00 waktu lokal APP_TZ
        schedule.every().day.at("08:00").do(self.send_daily_summary).tag("daily")
        logger.info(f"Jadwal notifikasi aktif (TZ={self.timezone_name})")

    # Core logic (AFTER-only)
    def check_alert_window(self):
        now = self.get_current_time()

        # Penting: cek kemarin, hari ini, besok
        # agar jadwal lewat tengah malam tetap terjangkau untuk T+290
        for days_offset in (-1, 0, 1):
            target_date = now + timedelta(days=days_offset)
            tanggal = target_date.strftime("%Y-%m-%d")

            try:
                schedule_data = self.hajj_client.get_schedule_data_for_date(tanggal)

                for sched in schedule_data:
                    tgl = sched.get("tanggal", "")
                    jam = sched.get("jam", "")
                    if not tgl or not jam:
                        continue

                    flags = self.state.get(tgl, jam)
                    if flags.completed:
                        continue

                    try:
                        sched_dt = self.parse_schedule_dt(tgl, jam)
                    except Exception:
                        logger.error(f"Format tanggal/jam tidak valid: {tgl} {jam}")
                        # tandai completed supaya tidak diproses terus-menerus
                        flags.completed = True
                        continue

                    mins = self.minutes_to_schedule(sched_dt, now)

                    # Hanya proses SETELAH jadwal (AFTER-only)
                    if mins > 0:
                        # sebelum jadwal: lewati (tidak ada alert T-)
                        continue

                    minutes_after = -mins  # menit sejak H lewat (positif)
                    # Ambil data pending barcode untuk jadwal ini
                    pending_data = self.hajj_client.get_pending_barcode_data(tgl, jam)
                    no_barcode = pending_data.get("count_tidak_ada_barcode", 0) > 0

                    def in_window_after(target: float) -> bool:
                        return (target - 1) <= minutes_after < (target + 1)

                    if no_barcode:
                        # Kirim setiap milestone AFTER hanya sekali
                        for target, label in AFTER_MILESTONES:
                            already = flags.after_sent_map.get(target, False)
                            if in_window_after(target) or (minutes_after > target and not already):
                                if self.telegram_notifier.send_schedule_alert(pending_data, label):
                                    flags.after_sent_map[target] = True

                    # Selesaikan jadwal jika semua sudah upload, atau semua milestone sudah lewat
                    last_after = AFTER_MILESTONES[-1][0] if AFTER_MILESTONES else 0
                    if (not no_barcode) or (minutes_after > last_after + 2):
                        flags.completed = True
                        logger.info(f"✅ Fase setelah-jadwal selesai untuk {tgl} {jam} (minutes_after={minutes_after:.1f}).")

            except Exception as e:
                logger.error(f"Error mengambil data jadwal untuk {tanggal}: {e}")

    def check_overdue_schedules(self):
        try:
            overdue = self.hajj_client.get_overdue_schedules()
            if overdue:
                self.telegram_notifier.send_overdue_report(overdue)
        except Exception as e:
            logger.error(f"Error dalam check_overdue_schedules: {e}")

    def send_daily_summary(self):
        try:
            now = self.get_current_time()
            message = (
                "📊 <b>RINGKASAN HARIAN HAJJ DASHBOARD</b> 📊\n\n"
                f"📅 <b>Tanggal:</b> {now.strftime('%d %B %Y')}\n"
                f"🕐 <b>Waktu:</b> {now.strftime('%H:%M')}\n"
                f"🌏 <b>Timezone:</b> {self.timezone_name}\n\n"
                "✅ <b>Sistem notifikasi berjalan normal</b>\n"
                "🔔 <b>Pengingat aktif (SETELAH jadwal):</b> 3 jam, 4 jam, 4 jam 30 menit, 4 jam 50 menit\n"
                "📋 <b>Laporan terlewat:</b> Setiap jam\n"
                f"📡 <b>API Base:</b> {self.hajj_config.base_url}"
            )
            self.telegram_notifier.send_message(message)
        except Exception as e:
            logger.error(f"Error dalam send_daily_summary: {e}")

    def run(self):
        now = self.get_current_time()
        token_mask = (self.telegram_config.bot_token[:6] + "..." + self.telegram_config.bot_token[-4:]) if self.telegram_config.bot_token else "(none)"
        logger.info("🚀 Telegram Notification Scheduler dimulai...")
        logger.info(f"📡 Monitoring: {self.hajj_config.base_url}")
        logger.info(f"🤖 Bot Token: {token_mask}")
        logger.info(f"💬 Chat ID: {self.telegram_config.chat_id}")
        logger.info(f"🌏 Timezone: {self.timezone_name}")
        logger.info(f"🕐 Start Time: {now.strftime('%d %B %Y %H:%M:%S')}")

        test_message = (
            "🤖 <b>HAJJ NOTIFICATION BOT AKTIF</b>\n\n"
            f"📅 <b>Waktu Start:</b> {now.strftime('%d %B %Y %H:%M:%S')}\n"
            f"🌏 <b>Timezone:</b> {self.timezone_name}\n"
            f"📡 <b>API Base:</b> {self.hajj_config.base_url}\n"
            "✅ <b>Status:</b> Bot terhubung\n"
            "🔔 <b>Pengingat (SETELAH jadwal):</b> 3 jam, 4 jam, 4 jam 30 menit, 4 jam 50 menit\n"
            "📋 <b>Laporan:</b> Jadwal terlewat setiap jam"
        )
        self.telegram_notifier.send_message(test_message)

        # Loop utama – cek setiap 5 detik agar akurat
        while not self.stop_event.is_set():
            try:
                schedule.run_pending()
                self.stop_event.wait(timeout=5.0)
            except Exception as e:
                logger.error(f"Error dalam main loop: {e}")
                self.stop_event.wait(timeout=5.0)

# =========================
# Test function untuk API
# =========================
def send_test_message(message: str) -> bool:
    """Function untuk mengirim test message dari API"""
    try:
        config = TelegramConfig()
        notifier = TelegramNotifier(config, APP_TZ)
        return notifier.send_message(message)
    except Exception as e:
        print(f"Error sending test message: {e}")
        return False

# =========================
# Entrypoint dengan graceful shutdown
# =========================
def main():
    # Mode test cepat: python script.py --test "pesan"
    if len(sys.argv) > 1 and sys.argv[1] == "--test":
        if len(sys.argv) > 2:
            message = sys.argv[2]
            success = send_test_message(message)
            print("SUCCESS" if success else "ERROR")
            sys.exit(0 if success else 1)
        else:
            print("ERROR: No message provided for test")
            sys.exit(1)

    stop_event = threading.Event()

    def _handle_stop(signum, frame):
        logger.info(f"🛑 Sinyal berhenti diterima: {signum}. Menutup service...")
        stop_event.set()

    # Tangani SIGTERM (NSSM stop), SIGINT (Ctrl+C), dan (opsional) SIGBREAK Windows
    for sig in (getattr(signal, "SIGTERM", None), getattr(signal, "SIGINT", None), getattr(signal, "SIGBREAK", None)):
        if sig is not None:
            try:
                signal.signal(sig, _handle_stop)
            except Exception:
                pass

    try:
        scheduler = NotificationScheduler(stop_event)
        scheduler.run()
    except Exception as e:
        logger.error(f"Error fatal: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
