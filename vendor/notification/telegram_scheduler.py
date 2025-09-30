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
    base_url: str = os.getenv("HAJJ_BASE_URL", "https://menfins.site/hajj")
    api_endpoint: str = os.getenv("HAJJ_API_ENDPOINT", "/api/schedule_notifications")
    timeout: int = int(os.getenv("HTTP_TIMEOUT", "30"))

# =========================
# Logging (aman untuk service)
# =========================
logger = logging.getLogger(APP_NAME)
logger.setLevel(logging.INFO)

# Pastikan folder log ada (kalau LOG_FILE pakai path)
try:
    _abs_log_path = os.path.abspath(LOG_FILE)
    _log_dir = os.path.dirname(_abs_log_path)
    if _log_dir and not os.path.exists(_log_dir):
        os.makedirs(_log_dir, exist_ok=True)
except Exception:
    pass

# Rotating file handler (UTF-8)
try:
    file_handler = RotatingFileHandler(LOG_FILE, maxBytes=LOG_MAX_BYTES, backupCount=LOG_BACKUPS, encoding="utf-8")
    file_handler.setFormatter(logging.Formatter("%(asctime)s - %(levelname)s - %(message)s"))
    logger.addHandler(file_handler)
except Exception as e:
    # Fallback ke basicConfig jika ada masalah handler
    logging.basicConfig(level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s")
    logger.warning(f"Gagal inisialisasi RotatingFileHandler: {e}")

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
            # kirim body sebagai JSON (lebih aman)
            resp = self.session.post(url, json=payload, timeout=15)
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
        jam_formatted = schedule_data.get('jam_formatted', '')  # Format AM/PM dari API
        total_peserta = schedule_data.get('total_count', 0)
        tanpa_barcode = schedule_data.get('no_barcode_count', 0)
        dengan_barcode = schedule_data.get('with_barcode_count', 0)

        # --- Pilih tampilan jam sistem (prioritas: jam_formatted -> jam HH:MM)
        if jam_formatted:
            jam_display = jam_formatted
        else:
            try:
                jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
            except Exception:
                jam_display = jam  # fallback apa adanya

        # --- Hitung Jam Mekkah = jam_display + 5 jam (robust parsing)
        # gunakan tanggal sebagai anchor agar rollover hari terdeteksi
        try:
            base_date = datetime.strptime(tanggal, '%Y-%m-%d').date()
        except Exception:
            base_date = datetime.now().date()

        parsed_dt: Optional[datetime] = None
        # Coba parse jam_display dulu (AM/PM atau 24-jam)
        for fmt in ('%I:%M %p', '%H:%M', '%H:%M:%S'):
            try:
                t = datetime.strptime(jam_display, fmt).time()
                parsed_dt = datetime.combine(base_date, t)
                break
            except Exception:
                pass

        # Jika gagal, coba parse dari 'jam' mentah
        if parsed_dt is None:
            for fmt in ('%H:%M:%S', '%H:%M'):
                try:
                    t = datetime.strptime(jam, fmt).time()
                    parsed_dt = datetime.combine(base_date, t)
                    break
                except Exception:
                    pass

        # Default tampilan Jam Mekkah (jika parsing gagal)
        jam_mekkah_display = jam_display
        if parsed_dt is not None:
            mekkah_dt = parsed_dt + timedelta(hours=5)
            # Ikuti gaya format jam_display (AM/PM vs 24-jam)
            if 'AM' in jam_display.upper() or 'PM' in jam_display.upper():
                jam_mekkah_display = mekkah_dt.strftime('%I:%M %p')
            else:
                jam_mekkah_display = mekkah_dt.strftime('%H:%M')

            # Tambahkan penanda hari jika rollover
            day_diff = (mekkah_dt.date() - base_date).days
            if day_diff > 0:
                jam_mekkah_display += ' (+1 hari)'
            elif day_diff < 0:
                jam_mekkah_display += ' (-1 hari)'

        # --- Tanggal tampil
        try:
            tanggal_display = datetime.strptime(tanggal, '%Y-%m-%d').strftime('%d %B %Y')
        except Exception:
            tanggal_display = tanggal

        message = (
            f"🔔 <b>ALERT JADWAL • {alert_label}</b>\n"
            f"📅 <b>Tanggal:</b> {tanggal_display}\n"
            f"🕐 <b>Jam Sistem:</b> {jam_display}\n"
            f"🕐 <b>Jam Mekkah:</b> {jam_mekkah_display}\n\n"
            f"📊 <b>STATISTIK PESERTA</b>\n"
            f"👥 Total: <b>{total_peserta}</b>\n"
            f"✅ Dengan Barcode: <b>{dengan_barcode}</b>\n"
            f"❌ Tanpa Barcode: <b>{tanpa_barcode}</b>\n"
        )
        return message

    def send_schedule_alert(self, schedule_data: Dict[str, Any], alert_label: str) -> bool:
        return self.send_message(self.build_alert_message(schedule_data, alert_label))

    def send_overdue_report(self, overdue_schedules: List[Dict[str, Any]]) -> bool:
        if not overdue_schedules:
            return True
        now = self.get_current_time()
        total_overdue = len(overdue_schedules)
        total_no_barcode = sum(s.get('no_barcode_count', 0) for s in overdue_schedules)

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
            jam_formatted = s.get('jam_formatted', '')  # Format AM/PM dari API
            total_peserta = s.get('total_count', 0)
            tanpa_barcode = s.get('no_barcode_count', 0)
            overdue_minutes = s.get('overdue_minutes', 0)

            # Gunakan jam_formatted jika tersedia, fallback ke jam biasa
            if jam_formatted:
                jam_display = jam_formatted
            else:
                try:
                    jam_display = datetime.strptime(jam, '%H:%M:%S').strftime('%H:%M')
                except Exception:
                    jam_display = jam

            try:
                tanggal_display = datetime.strptime(tanggal, '%Y-%m-%d').strftime('%d/%m/%Y')
            except Exception:
                tanggal_display = tanggal
            lines.append(f"{i}. 📅 {tanggal_display} 🕐 {jam_display} | 👥 {total_peserta} | ❌ {tanpa_barcode} | ⏰ {overdue_minutes} menit")

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
        params = {
            "tanggal": target.strftime("%Y-%m-%d"),
            "jam": target.strftime("%H:%M:%S"),
            "hours_ahead": hours_ahead,
        }
        try:
            url = f"{self.config.base_url}{self.config.api_endpoint}"
            data = self._fetch(url, params)
            if data.get("success"):
                schedules = data.get("data", [])
                # Log format jam yang diterima untuk debugging
                if schedules:
                    sample_schedule = schedules[0]
                    jam_formatted = sample_schedule.get('jam_formatted', 'N/A')
                    jam_original = sample_schedule.get('jam', 'N/A')
                    logger.info(
                        f"API OK (ahead={hours_ahead}) tz={data.get('timezone','?')} now={data.get('current_time','?')} "
                        f"sample_jam={jam_original} formatted={jam_formatted}"
                    )
                else:
                    logger.info(
                        f"API OK (ahead={hours_ahead}) tz={data.get('timezone','?')} now={data.get('current_time','?')} - no schedules"
                    )
                return schedules
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API: {e}")
        return []

    def get_overdue_schedules(self) -> List[Dict[str, Any]]:
        try:
            url = f"{self.config.base_url}/api/overdue_schedules"
            data = self._fetch(url)
            if data.get("success"):
                overdue_schedules = data.get("data", [])
                # Log format jam yang diterima untuk debugging
                if overdue_schedules:
                    sample_schedule = overdue_schedules[0]
                    jam_formatted = sample_schedule.get('jam_formatted', 'N/A')
                    jam_original = sample_schedule.get('jam', 'N/A')
                    logger.info(
                        f"Overdue API OK tz={data.get('timezone','?')} now={data.get('current_time','?')} "
                        f"sample_jam={jam_original} formatted={jam_formatted}"
                    )
                else:
                    logger.info(
                        f"Overdue API OK tz={data.get('timezone','?')} now={data.get('current_time','?')} - no overdue schedules"
                    )
                return overdue_schedules
            logger.error(f"API error: {data.get('message', 'Unknown error')}")
        except Exception as e:
            logger.error(f"Error saat mengakses API overdue: {e}")
        return []

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

        candidates: List[Dict[str, Any]] = []
        for h in (2, 1, 0.5, 10/60):
            try:
                data = self.hajj_client.get_schedule_data(hours_ahead=h)
                candidates.extend(data)
            except Exception as e:
                logger.error(f"Error mengambil data horizon {h}: {e}")

        unique: Dict[Tuple[str, str], Dict[str, Any]] = {}
        for item in candidates:
            tgl = item.get("tanggal", "")
            jam = item.get("jam", "")
            if tgl and jam:
                unique[(tgl, jam)] = item

        for (tgl, jam), sched in unique.items():
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

            no_barcode = sched.get("no_barcode_count", 0) > 0

            def in_window(target_min: float) -> bool:
                return (target_min - 1) <= mins < (target_min + 1)

            if no_barcode and (in_window(120) or (mins < 120 and not flags.sent_2h)):
                if not flags.sent_2h and self.telegram_notifier.send_schedule_alert(sched, "2 jam"):
                    flags.sent_2h = True

            if no_barcode and (in_window(60) or (mins < 60 and not flags.sent_1h and flags.sent_2h)):
                if not flags.sent_1h and self.telegram_notifier.send_schedule_alert(sched, "1 jam"):
                    flags.sent_1h = True

            if no_barcode and (in_window(30) or (mins < 30 and not flags.sent_30m and flags.sent_1h)):
                if not flags.sent_30m and self.telegram_notifier.send_schedule_alert(sched, "30 menit"):
                    flags.sent_30m = True

            if no_barcode and (in_window(10) or (mins < 10 and not flags.sent_10m and flags.sent_30m)):
                if not flags.sent_10m and self.telegram_notifier.send_schedule_alert(sched, "10 menit"):
                    flags.sent_10m = True
                    flags.reminder_active = True

            if no_barcode and flags.reminder_active and mins <= 10:
                current_minute = int(now.strftime("%Y%m%d%H%M"))
                if flags.last_reminder_minute != current_minute:
                    self.telegram_notifier.send_schedule_alert(sched, "pengingat")
                    flags.last_reminder_minute = current_minute

            if (not no_barcode) and flags.reminder_active:
                logger.info(f"✅ Semua peserta {tgl} {jam} sudah upload barcode. Reminder dihentikan.")
                flags.reminder_active = False

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
                "📊 <b>RINGKASAN HARIAN DASHBOARD</b> 📊\n\n"
                f"📅 <b>Tanggal:</b> {now.strftime('%d %B %Y')}\n"
                f"🕐 <b>Waktu Sistem:</b> {now.strftime('%H:%M')}\n\n"
                "✅ <b>Sistem notifikasi berjalan normal</b>\n"
                "🔔 <b>Alert aktif:</b> 2 jam, 1 jam, 30 menit, 10 menit sebelum jadwal\n"
                "⏰ <b>Reminder:</b> Tiap 1 menit setelah T-10 sampai jam H\n"
                "📋 <b>Laporan terlewat:</b> Setiap jam"
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
            "🤖 <b>NOTIFICATION BOT AKTIF</b>\n\n"
            f"📅 <b>Waktu Start:</b> {now.strftime('%d %B %Y %H:%M:%S')}\n"
            "✅ <b>Status:</b> Bot terhubung\n"
            "🔔 <b>Notifikasi:</b> Siap mengirim alert & reminder"
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
# Entrypoint dengan graceful shutdown
# =========================
def main():
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
