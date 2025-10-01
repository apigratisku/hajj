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
from dataclasses import dataclass
from datetime import datetime, timedelta
from typing import List, Dict, Any, Tuple

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
    base_url: str = os.getenv("HAJJ_BASE_URL", "http://localhost/hajj")
    api_endpoint: str = os.getenv("HAJJ_API_ENDPOINT", "/api/schedule")
    timeout: int = int(os.getenv("HTTP_TIMEOUT", "30"))

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
            resp = self.session.post(url, data=json.dumps(payload), timeout=15)
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
            f"🔔 <b>ALERT JADWAL • {alert_label}</b>\n"
            f"📅 <b>Tanggal:</b> {tanggal_display}\n"
            f"🕐 <b>Jam Sistem:</b> {jam_sistem or jam_display}\n"
            f"🕐 <b>Jam Mekkah:</b> {jam_mekkah}\n\n"
            f"📊 <b>STATISTIK PESERTA</b>\n"
            f"👥 Total: <b>{total_peserta}</b>\n"
            f"✅ Dengan Barcode: <b>{dengan_barcode}</b>\n"
            f"❌ Tanpa Barcode: <b>{tanpa_barcode}</b>\n\n"
        )
        
        # Tambahkan detail peserta jika ada yang belum upload barcode
        if tanpa_barcode > 0:
            message += f"⚠️ <b>PERHATIAN:</b> Ada {tanpa_barcode} peserta yang belum upload barcode!\n\n"
        
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

    def _fetch(self, url: str, params: Dict[str, Any] | None = None) -> Dict[str, Any]:
        resp = self.session.get(url, params=params, timeout=self.config.timeout)
        resp.raise_for_status()
        return resp.json()

    def get_schedule_data(self, hours_ahead: float) -> List[Dict[str, Any]]:
        now = self.get_current_time()
        target = now + timedelta(hours=hours_ahead)
        params = {
            "tanggal": target.strftime("%Y-%m-%d"),
        }
        try:
            url = f"{self.config.base_url}{self.config.api_endpoint}"
            data = self._fetch(url, params)
            if data.get("status") == "success":
                logger.info(
                    f"API OK (ahead={hours_ahead}) tanggal={target.strftime('%Y-%m-%d')}"
                )
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
            params = {
                "tanggal": tanggal
            }
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
            params = {
                "tanggal": tanggal,
                "jam": jam
            }
            data = self._fetch(url, params)
            if data.get("status") == "success":
                logger.info(f"Pending barcode API OK - {tanggal} {jam}")
                return data
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API pending barcode: {e}")
        return {}

# =========================
# State Anti-Duplikat & Reminder
# =========================
@dataclass
class ScheduleFlags:
    sent_2h: bool = False
    sent_1h: bool = False
    sent_30m: bool = False
    sent_10m: bool = False
    reminder_active: bool = False
    last_reminder_minute: int = -1
    completed: bool = False

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

    # Core logic alert & reminder
    def check_alert_window(self):
        now = self.get_current_time()

        # Cek jadwal untuk hari ini dan besok
        for days_ahead in (0, 1):
            target_date = now + timedelta(days=days_ahead)
            tanggal = target_date.strftime("%Y-%m-%d")
            
            try:
                # Ambil data jadwal untuk tanggal tertentu
                schedule_data = self.hajj_client.get_schedule_data_for_date(tanggal)
                
                for sched in schedule_data:
                    tgl = sched.get("tanggal", "")
                    jam = sched.get("jam", "")
                    
                    if not tgl or not jam:
                        continue
                        
                    if self.state.is_completed(tgl, jam):
                        continue

                    try:
                        sched_dt = self.parse_schedule_dt(tgl, jam)
                    except Exception:
                        logger.error(f"Format tanggal/jam tidak valid: {tgl} {jam}")
                        continue

                    mins = self.minutes_to_schedule(sched_dt, now)
                    flags = self.state.get(tgl, jam)

                    if mins <= 0:
                        flags.completed = True
                        logger.info(f"⏱️ Jadwal {tgl} {jam} sudah lewat. Notifikasi dihentikan.")
                        continue

                    # Ambil data pending barcode untuk jadwal ini
                    pending_data = self.hajj_client.get_pending_barcode_data(tgl, jam)
                    no_barcode = pending_data.get("count_tidak_ada_barcode", 0) > 0

                    def in_window(target_min: float) -> bool:
                        return (target_min - 1) <= mins < (target_min + 1)

                    # Alert 3 jam sebelum (180 menit)
                    if no_barcode and (in_window(180) or (mins < 180 and not flags.sent_2h)):
                        if not flags.sent_2h and self.telegram_notifier.send_schedule_alert(pending_data, "3 jam"):
                            flags.sent_2h = True

                    # Alert 4 jam sebelum (240 menit)
                    if no_barcode and (in_window(240) or (mins < 240 and not flags.sent_1h and flags.sent_2h)):
                        if not flags.sent_1h and self.telegram_notifier.send_schedule_alert(pending_data, "4 jam"):
                            flags.sent_1h = True

                    # Alert 4 jam 30 menit sebelum (270 menit)
                    if no_barcode and (in_window(270) or (mins < 270 and not flags.sent_30m and flags.sent_1h)):
                        if not flags.sent_30m and self.telegram_notifier.send_schedule_alert(pending_data, "4 jam 30 menit"):
                            flags.sent_30m = True

                    # Alert 4 jam 40 menit sebelum (280 menit)
                    if no_barcode and (in_window(280) or (mins < 280 and not flags.sent_10m and flags.sent_30m)):
                        if not flags.sent_10m and self.telegram_notifier.send_schedule_alert(pending_data, "4 jam 40 menit"):
                            flags.sent_10m = True
                            flags.reminder_active = True

                    # Alert 4 jam 50 menit sebelum (290 menit)
                    if no_barcode and (in_window(290) or (mins < 290 and not flags.sent_10m and flags.sent_30m)):
                        if not flags.sent_10m and self.telegram_notifier.send_schedule_alert(pending_data, "4 jam 50 menit"):
                            flags.sent_10m = True
                            flags.reminder_active = True

                    # Reminder setiap menit setelah T-10 sampai jam H
                    if no_barcode and flags.reminder_active and mins <= 10:
                        current_minute = int(now.strftime("%Y%m%d%H%M"))
                        if flags.last_reminder_minute != current_minute:
                            self.telegram_notifier.send_schedule_alert(pending_data, "pengingat")
                            flags.last_reminder_minute = current_minute

                    if (not no_barcode) and flags.reminder_active:
                        logger.info(f"✅ Semua peserta {tgl} {jam} sudah upload barcode. Reminder dihentikan.")
                        flags.reminder_active = False
                        
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
                "🔔 <b>Alert aktif:</b> 3 jam, 4 jam, 4 jam 30 menit, 4 jam 40 menit, 4 jam 50 menit\n"
                "⏰ <b>Reminder:</b> Tiap 1 menit setelah T-10 sampai jam H\n"
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
            "🔔 <b>Alert:</b> 3 jam, 4 jam, 4 jam 30 menit, 4 jam 40 menit, 4 jam 50 menit\n"
            "⏰ <b>Reminder:</b> Setiap menit setelah T-10 sampai jam H\n"
            "📋 <b>Laporan:</b> Jadwal terlewat setiap jam"
        )
        self.telegram_notifier.send_message(test_message)

        # Loop utama – cek setiap 5 detik agar jadwal per-menit tidak miss
        while not self.stop_event.is_set():
            try:
                schedule.run_pending()
                # Tunggu 5 detik atau sampai diminta berhenti
                self.stop_event.wait(timeout=5.0)
            except Exception as e:
                logger.error(f"Error dalam main loop: {e}")
                # cooldown 5 detik supaya tidak spin
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
    # Check if this is a test call from API
    if len(sys.argv) > 1 and sys.argv[1] == "--test":
        if len(sys.argv) > 2:
            message = sys.argv[2]
            success = send_test_message(message)
            if success:
                print("SUCCESS")
                sys.exit(0)
            else:
                print("ERROR")
                sys.exit(1)
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
