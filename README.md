# Sistem Informasi Presensi Siswa dan Dewan Guru Berbasis Web
### SMK NU WARGABINANGUN
*Menggunakan Barcode / QR Code, RFID, Validasi Geofencing GPS, dan Integrasi AI Chatbot Asisten Pintar*

---

<!-- BADGES -->
[![CI Pipeline](https://github.com/novi23112006-ship-it/websekolah/actions/workflows/ci.yml/badge.svg)](https://github.com/novi23112006-ship-it/websekolah/actions/workflows/ci.yml)
![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-Custom%20MVC-blue?style=for-the-badge)
![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Frontend](https://img.shields.io/badge/Frontend-Bootstrap%205-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![AI Provider](https://img.shields.io/badge/AI%20Chatbot-Google%20Gemini%20%26%20OpenAI-00A67E?style=for-the-badge&logo=openai&logoColor=white)
![Unit Testing](https://img.shields.io/badge/Unit%20Tests-PHPUnit%2010%20(PASS)-brightgreen?style=for-the-badge)
![Location](https://img.shields.io/badge/Study%20Case-SMK%20NU%20WARGABINANGUN-success?style=for-the-badge)

---

## 📌 DAFTAR ISI
1. [Identitas Anggota Kelompok](#-1-identitas-anggota-kelompok)
2. [Tema dan Topik Proyek](#-2-tema-dan-topik-proyek)
3. [Deskripsi Aplikasi](#-3-deskripsi-aplikasi)
4. [Fitur Utama Aplikasi](#-4-fitur-utama-aplikasi)
5. [Fitur AI (Asisten Pintar Presensi SMK)](#-5-fitur-ai-asisten-pintar-presensi-smk)
6. [Tautan Video Demonstrasi (YouTube)](#-6-tautan-video-demonstrasi-youtube)
7. [Panduan Instalasi dan Menjalankan Aplikasi](#-7-panduan-instalasi-dan-menjalankan-aplikasi)
8. [Akun Pengujian Demo (Demo Credentials)](#-8-akun-pengujian-demo-demo-credentials)
9. [Pengujian Unit (PHPUnit Testing)](#-9-pengujian-unit-phpunit-testing)
10. [Struktur Direktori Proyek](#-10-struktur-direktori-proyek)

---

## 👥 1. IDENTITAS ANGGOTA KELOMPOK

Proyek Akhir Rekayasa Perangkat Lunak (RPL) ini dikembangkan oleh kelompok mahasiswa:

| No | Nama Lengkap Mahasiswa | NIM | Peran / Tugas Utama |
|:--:|:-----------------------|:---:|:--------------------|
| 1. | **[Novi Fitriani]** | **[23112006]** | Project Leader, Fullstack Developer & AI Integration |
| 2. | **[Nama Anggota 2]** | **[NIM Anggota 2]** | Frontend UI/UX Designer & Dokumentasi Sistem |
| 3. | **[Nama Anggota 3]** | **[NIM Anggota 3]** | Database Administrator & QA / Testing |
| 4. | **[Nama Anggota 4]** *(jika ada)* | **[NIM Anggota 4]** | System Analyst & Modul Helper |

* **Nomor Kelompok** : **[Kelompok 3 / Isi Nomor Kelompok Anda]**
* **Mata Kuliah**    : Rekayasa Perangkat Lunak (RPL)
* **Program Studi**  : **[Teknik Informatika / Sistem Informasi / Rekayasa Perangkat Lunak]**
* **Fakultas**       : **[Fakultas Ilmu Komputer / Sains & Teknologi]**
* **Perguruan Tinggi**: **[Isi Nama Universitas / Perguruan Tinggi Anda]**
* **Dosen Pengampu** : **[Isi Nama Dosen Pengampu RPL Beserta Gelar]**

---

## 🎯 2. TEMA DAN TOPIK PROYEK

> **Topik:** *Pengembangan Sistem Informasi Presensi Siswa dan Dewan Guru Berbasis Web Menggunakan Barcode/QR Code, RFID, Validasi Geofencing Lokasi GPS, serta Integrasi Asisten Pintar AI di SMK NU WARGABINANGUN.*

Fokus proyek ini adalah membangun solusi presensi digital sekolah yang modern, akuntabel, dan anti-kecurangan untuk menggantikan sistem pencatatan kertas konvensional pada institusi pendidikan kejuruan.

---

## 📖 3. DESKRIPSI APLIKASI

### Latar Belakang & Permasalahan
Sebelum penerapan sistem ini, proses absensi harian dan pencatatan Kegiatan Belajar Mengajar (KBM) di **SMK NU WARGABINANGUN** menghadapi sejumlah kendala mendasar:
1. **Pencatatan Presensi Manual Memakan Waktu KBM**: Setiap jam pertama, guru harus mengabsen satu per satu siswa di 27 rombel kelas, menyita waktu pembelajaran efektif hingga 10-15 menit per pertemuan.
2. **Rawan Manipulasi & Titip Absen**: Absensi kertas rentan pemalsuan paraf, salah pencatatan kehadiran, maupun kelalaian pengisian.
3. **Keterlambatan Rekapitulasi Data**: Wali kelas dan Bagian Kesiswaan membutuhkan waktu berhari-hari pada akhir bulan atau akhir semester untuk menjumlahkan rekap kehadiran siswa.
4. **Ketiadaan Notifikasi Real-time ke Orang Tua**: Orang tua murid sering kali tidak mengetahui jika putera-puterinya tidak hadir atau membolos sekolah hingga diterbitkan surat peringatan resmi.
5. **Kurangnya Asistensi Informasi Terpadu**: Guru, siswa, dan pimpinan sering kesulitan mencari informasi aturan presensi, jadwal mengajar, dan status kehadiran tanpa harus membuka lembar arsip fisik.

### Tujuan dan Solusi
Aplikasi **Sistem Absensi SMK NU WARGABINANGUN** dibangun sebagai platform web komprehensif dengan pola arsitektur **Model-View-Controller (MVC)** yang bertujuan untuk:
- Mengotomatisasi absensi siswa dan dewan guru dengan metode modern: **Scan Barcode/QR Code** via kamera perangkat, **RFID UID**, dan input manual terenkripsi.
- Mencegah kecurangan presensi jarak jauh menggunakan teknologi **Geofencing GPS Haversine** yang membatasi radius presensi maksimal **100 meter** dari koordinat resmi sekolah.
- Menyediakan pengajuan izin dan sakit mandiri secara online dengan lampiran foto bukti surat dokter.
- Memberikan asistensi informasi 24/7 melalui **AI Chatbot Asisten Pintar** yang memahami konteks data sekolah dan jadwal pelajaran.
- Mengirimkan rekapitulasi dan notifikasi kehadiran secara otomatis kepada wali murid melalui **Telegram Bot API**.

---

## ⚡ 4. FITUR UTAMA APLIKASI

Sistem ini menerapkan **Role-Based Access Control (RBAC)** dengan 5 level hak akses pengguna yang saling terintegrasi:

### 1. Super Admin (Administrator Sistem)
- **Dashboard Monitoring Real-time**: Statistik kehadiran siswa dan guru hari ini, grafik absensi mingguan, dan log aktivitas terbaru.
- **Manajemen Data Master Pengguna**: Kelola akun (Username, Password Hashing bcrypt, Role, Status Aktif/Nonaktif) untuk 169 pengguna.
- **Manajemen Data Akademik**: Pengelolaan 30 Dewan Guru, 135 Siswa, 27 Rombel Kelas (X, XI, XII pada jurusan TKJ, TKR, dan TSM), serta 18 Mata Pelajaran.
- **Manajemen Barcode / QR Code**: Generate kode barcode unik per siswa (`STD-XXXXXX`) dan guru (`BC-GURU-XXX`), serta fitur cetak kartu absensi fisik.
- **Konfigurasi Sistem**: Pengaturan titik koordinat sekolah, radius geofencing (100 meter), batas toleransi jam masuk (07:00 WIB), integrasi Telegram Bot, dan pengaturan Provider AI.

### 2. Kepala Sekolah
- **Executive Dashboard**: Ringkasan persentase kehadiran harian dan bulanan seluruh warga sekolah.
- **Monitoring KBM Real-time**: Memantau kehadiran guru per mata pelajaran dan ruang kelas.
- **Laporan dan Analisis Kehadiran**: Rekapitulasi presensi guru dan siswa dengan filter tanggal, bulan, tahun, serta jurusan.
- **Cetak Laporan Resmi**: Cetak dokumen laporan absensi untuk arsip manajerial dan akreditasi sekolah.

### 3. Wali Kelas
- **Dashboard Khusus Rombel**: Memantau 1 rombel binaan secara mendalam (misal: Kelas XII TKJ 1).
- **Verifikasi Surat Izin / Sakit**: Meninjau, menyetujui, atau menolak pengajuan ketidakhadiran siswa disertai foto surat keterangan dokter.
- **Rekapitulasi Kehadiran Rombel**: Laporan persentase kehadiran, jumlah hadir, sakit, izin, alpa, dan terlambat untuk keperluan raport kesiswaan.

### 4. Dewan Guru
- **Presensi Mandiri Guru**: Scan barcode absensi kehadiran harian dengan pencatatan waktu otomatis (batas masuk pukul 06:45 WIB).
- **Pengajuan Izin / Cuti Guru**: Formulir online pengajuan dispensasi/izin dinas guru.
- **Jadwal Mengajar Harian & Mingguan**: Tampilan interaktif jadwal mengajar di kelas sesuai beban jam mengajar.
- **Cetak Kartu Guru**: Kartu absensi identitas guru dengan QR Code resmi.

### 5. Siswa
- **Presensi Mandiri Multi-Metode**: Scan Barcode/QR Code via kamera web, scan RFID UID terminal sekolah, atau input manual barcode.
- **Validasi Radius GPS Geofencing**: Deteksi otomatis posisi GPS pengguna dengan formula Haversine (wajib dalam radius 100 meter dari sekolah).
- **Lihat Kartu Barcode Siswa**: Modal QR Code interaktif untuk ditampilkan di terminal scanner sekolah serta tombol cetak kartu mandiri.
- **Pengajuan Izin / Sakit Online**: Pengajuan ketidakhadiran dari rumah dengan mengunggah foto surat dokter.
- **Riwayat & Rekapitulasi Presensi**: Informasi histori kehadiran, catatan terlambat, dan jadwal mata pelajaran harian.

---

## 🤖 5. FITUR AI (ASISTEN PINTAR PRESENSI SMK)

Sistem ini dilengkapi dengan **Asisten Virtual AI SMK**, sebuah chatbot cerdas interaktif yang dirancang khusus untuk lingkungan **SMK NU WARGABINANGUN**.

### A. Fungsi dan Peran AI
1. **Asistensi Informasi Presensi Personal**: Menjawab pertanyaan siswa/guru mengenai status kehadiran hari ini, sisa kuota ketidakhadiran, atau jam masuk/pulang.
2. **Konsultasi Jadwal Pembelajaran**: Memberikan jadwal KBM harian guru maupun siswa berdasarkan role yang sedang login.
3. **Penyuluhan Aturan & SOP Sekolah**: Menjelaskan batasan jam masuk (Siswa: 07:00 WIB, Guru: 06:45 WIB), toleransi keterlambatan, batas radius geofencing 100 meter, dan prosedur izin.
4. **Executive Insight untuk Pimpinan**: Memberikan ringkasan tingkat kehadiran sekolah dan kelas dengan absensi tertinggi/terendah.

### B. Teknologi & Model AI yang Digunakan
- **Provider Utama**: **Google Gemini API** (`gemini-2.5-flash` dan `gemini-3.1-flash-lite-preview`).
- **Provider Alternatif (Multi-Provider)**: **OpenAI API** (`gpt-4o-mini`) via adapter cURL.
- **Fallback Engine**: Local Rule-Based Natural Language Engine yang tetap dapat menjawab pertanyaan umum seputar SOP sekolah apabila koneksi API eksternal terputus.

### C. Arsitektur dan Alur Kerja (Sequence Diagram Flow)
Alur komunikasi fitur AI Chatbot terintegrasi dengan arsitektur MVC sistem sebagai berikut:
1. **User Request**: Pengguna mengetik pertanyaan pada floating widget chat di frontend.
2. **AJAX Submission**: JavaScript mengirim request HTTP POST ke endpoint `http://localhost/websekolah3/public/chatbot/chat`.
3. **Session & Role Authentication**: Controller `Chatbot.php` memverifikasi sesi login pengguna melalui `AuthHelper` untuk memastikan hanya pengguna terdaftar yang dapat berkonsultasi.
4. **Dynamic Context Gathering**: Sistem mengumpulkan konteks aktual pengguna (Nama, Role, Rombel/Mata Pelajaran, Riwayat Presensi Hari Ini, dan Jadwal KBM).
5. **Guardrail Filtering**: Sistem menerapkan filter keamanan pada input untuk mencegah *prompt injection* dan menolak pertanyaan di luar domain sekolah (misal: coding umum, konten berbahaya).
6. **AI Provider Query**: Sistem mengirimkan prompt terstruktur beserta *system instructions* ke endpoint Google Gemini / OpenAI via HTTP cURL TLS.
7. **Response Formatting**: AI memproses konteks dan mengembalikan jawaban dalam bahasa Indonesia santun dan terstruktur (format Markdown).
8. **Frontend Rendering**: Chatbot menampilkan balon percakapan secara dinamis ke layar pengguna.

---

## 📺 6. TAUTAN VIDEO DEMONSTRASI (YOUTUBE)

Berikut tautan video rekaman demonstrasi sistem dan perkembangan proyek:

* 🔗 **Video Progres 1 (Perancangan & Basis Data)** : `[Tempelkan Tautan YouTube Video Progres 1 di sini]`
* 🔗 **Video Progres 2 (Implementasi Fitur & Modul)** : `[Tempelkan Tautan YouTube Video Progres 2 di sini]`
* 🔗 **Video Final Demo (Pengujian Lengkap & Fitur AI)** : `[Tempelkan Tautan YouTube Video Final Demo di sini]`

---

## 💻 7. PANDUAN INSTALASI DAN MENJALANKAN APLIKASI

### Persyaratan Sistem (Prerequisites)
Pastikan lingkungan server Anda memenuhi spesifikasi berikut:
- **Web Server**: Apache 2.4+ (Disarankan menggunakan **XAMPP 8.1+** atau Laragon).
- **Bahasa Pemrograman**: **PHP 8.1 / 8.2 / 8.3** (Ekstensi wajib: `pdo_mysql`, `curl`, `json`, `mbstring`, `fileinfo`, `openssl`).
- **Database Engine**: **MySQL 5.7+ / MariaDB 10.4+**.
- **Browser**: Google Chrome, Microsoft Edge, atau Mozilla Firefox versi terbaru dengan dukungan webcam dan GPS Geolocation.

---

### Langkah-langkah Instalasi

#### 1. Clone Repositori
Buka terminal / PowerShell dan arahkan ke direktori root web server Anda (`htdocs`):
```bash
cd C:\xampp\htdocs
git clone https://github.com/novi23112006-ship-it/websekolah.git websekolah3
cd websekolah3
```

#### 2. Konfigurasi Database MySQL
1. Buka browser dan akses **phpMyAdmin**: `http://localhost/phpmyadmin/`.
2. Buat database baru bernama:
   ```sql
   CREATE DATABASE db_absensi_smk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Klik database `db_absensi_smk`, lalu pilih menu **Import**.
4. Pilih berkas SQL dari proyek:
   ```
   database/db_absensi_smk.sql
   ```
5. Klik **Import / Kirim**. Seluruh 11 tabel beserta data master 169 pengguna, 30 guru, 135 siswa, 27 rombel, dan 18 mapel akan otomatis terisi.

#### 3. Konfigurasi Environment Variable (`.env`)
Salin file `.env.example` menjadi `.env` pada direktori root:
```powershell
copy .env.example .env
```
Buka file `.env` dan masukkan API Key Anda jika ingin mengaktifkan Chatbot AI dan Notifikasi Telegram:
```ini
# Notifikasi Telegram Bot (Opsional)
TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_CHAT_ID=your_telegram_chat_id_here
TELEGRAM_NOTIFICATIONS_ENABLED=false

# Multi-Provider AI (Gemini / OpenAI)
AI_PROVIDER=gemini
AI_API_KEY=your_gemini_api_key_here
AI_MODEL=gemini-2.5-flash
AI_STATUS=active
```

#### 4. Menjalankan Aplikasi
1. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL**.
2. Buka browser dan akses URL utama aplikasi:
   ```
   http://localhost/websekolah3/public/
   ```
3. Halaman Login Sistem Absensi **SMK NU WARGABINANGUN** akan tampil dan siap digunakan.

---

## 🔑 8. AKUN PENGUJIAN DEMO (DEMO CREDENTIALS)

Untuk mempermudah pengujian seluruh fitur oleh Dosen Penguji dan Asisten Lab, sistem menyediakan 5 role akun siap pakai:

| Role Pengguna | Username | Password | Deskripsi Akun Pengujian |
|:--------------|:--------:|:--------:|:--------------------------|
| **Super Admin** | `admin` | `admin` | Administrator Utama Pengelola Sistem & Data Master |
| **Kepala Sekolah** | `kepsek` | `password` | Kepala Sekolah SMK NU WARGABINANGUN |
| **Wali Kelas** | `walikelas1` | `password` | Wali Kelas Rina Marlina, S.Pd (Kelas XII TKJ 1) |
| **Dewan Guru** | `guru1` | `password` | Guru Siti Aminah, S.Pd (Mapel Matematika) |
| **Siswa** | `260031` | `password` | Siswa Aditya Sinaga (Kelas XII TKJ 1, NIS 260031) |

*(Catatan: Username seluruh siswa dapat menggunakan NIS masing-masing, dengan password default `password`).*

---

## 🧪 9. PENGUJIAN UNIT (PHPUNIT TESTING)

Sistem telah dilengkapi dengan test suite pengujian unit berbasis **PHPUnit 10** yang menguji fungsi utilitas penting (Validasi Geofencing Jarak Haversine, Konversi Penanggalan Indonesia, Validasi Hak Akses Role, serta Guardrail Filter AI Chatbot):

Untuk menjalankan pengujian unit:
```powershell
php tests/runner.php
```

**Hasil Eksekusi Pengujian Aktual:**
```text
PHPUnit 10.5.12 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.5.12

Running LokasiHelperTest...
  . testHitungJarakMeterHaversineTepat ... [PASS]
  . testHitungJarakTitikSamaNolMeter ... [PASS]
  . testValidasiJarakDalamRadius100Meter ... [PASS]
  . testValidasiJarakDiLuarRadius100MeterDitolak ... [PASS]
Running DateHelperTest...
  . testKonversiHariIndonesia ... [PASS]
  . testKonversiBulanIndonesia ... [PASS]
Running AuthHelperTest...
  . testValidasiDaftarRoleSistem ... [PASS]
Running ChatbotHelperTest...
  . testFilterPromptCodingDitolak ... [PASS]
  . testPromptSistemAbsensiDiizinkan ... [PASS]

OK (9 tests, 18 assertions) - STATUS: 100% PASS
```

---

## 📁 10. STRUKTUR DIREKTORI PROYEK

```text
websekolah3/
├── app/
│   ├── config/             # Konfigurasi konstanta sistem
│   ├── controllers/        # Controller MVC (Admin, Siswa, Guru, Wali Kelas, Chatbot, dll)
│   ├── core/               # Core MVC Engine (App, Controller, Database, Flasher)
│   ├── helpers/            # Helper utilitas (AuthHelper, DateHelper, LokasiHelper, TelegramHelper)
│   ├── models/             # Model database (Siswa_model, Guru_model, Dashboard_model, dll)
│   └── views/              # Template tampilan blade-style Bootstrap 5
│       ├── admin/          # Antarmuka Super Admin & Kepala Sekolah
│       ├── guru/           # Antarmuka Dewan Guru
│       ├── siswa/          # Antarmuka Siswa & Scanner Barcode
│       ├── wali_kelas/     # Antarmuka Wali Kelas & Verifikasi Izin
│       ├── layouts/        # Header, Footer, Navbar, dan Floating AI Chatbot
│       └── auth/           # Halaman Login dan Error Handler (403, 404)
├── config/
│   ├── config.php          # Konfigurasi Database & Environment
│   └── lokasi_sekolah.json # Pengaturan Koordinat Lat/Lng & Radius Geofencing Sekolah
├── database/
│   └── db_absensi_smk.sql  # Berkas Dump SQL Resmi Lengkap (Schema + 169 Data Akun)
├── laporan word/           # Dokumen Laporan Proyek Akhir (DOCX, PDF, dan Diagram UML/Figma)
│   ├── Laporan_Proyek_Akhir_Rekayasa_Perangkat_Lunak_Absensi_SMK.docx
│   ├── Laporan_Proyek_Akhir_Rekayasa_Perangkat_Lunak_Absensi_SMK.pdf
│   ├── sequence_diagram_ai.png
│   ├── usecase_diagram_left_actors.png
│   └── figma_ui_design_system.png
├── public/                 # Document Root Publik
│   ├── assets/             # Aset gambar, logo sekolah SMK NU WARGABINANGUN, dan ikon
│   ├── css/                # Lembar gaya kustom CSS
│   ├── js/                 # Skrip AJAX, Scanner Kamera, Geolocation, dan Chatbot AI
│   ├── uploads/            # Direktori penyimpanan unggahan bukti surat izin & foto profil
│   ├── index.php           # Entry Point Aplikasi Web
│   └── .htaccess           # Routing URL Rewrite Apache
├── tests/                  # Test Suite PHPUnit
│   ├── Unit/               # Unit Test Helpers
│   └── runner.php          # CLI Test Runner
├── .env.example            # Template Konfigurasi Environment Aman
├── .gitignore              # Daftar Filter Pengabaian Berkas Rahasia / Dependensi
├── phpunit.xml             # Konfigurasi XML Test Suite
└── README.md               # Dokumentasi Utama Repositori Proyek
```

---

## 📄 LISENSI DAN HAK CIPTA

Proyek ini dikembangkan untuk keperluan akademik Ujian Akhir Semester (UAS) mata kuliah **Rekayasa Perangkat Lunak (RPL)**. Seluruh hak cipta source code dan perancangan sistem dilindungi dan ditujukan untuk institusi **SMK NU WARGABINANGUN**.
