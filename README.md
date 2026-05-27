# 📘 Helpdesk UPA TIK — Universitas Lampung

Sistem manajemen tiket helpdesk berbasis web untuk Unit Penunjang Akademik Teknologi Informasi dan Komunikasi (UPA TIK) Universitas Lampung. Dibangun di atas **Laravel 12**, sistem ini mengelola pengaduan dan permintaan layanan TIK dari civitas akademika maupun tamu eksternal, dilengkapi dengan notifikasi multi-kanal, laporan analitik, dan integrasi SSO institusional.

---

## 📑 Daftar Isi

- [Tentang Sistem](#-tentang-sistem)
- [Fitur Utama](#-fitur-utama)
- [Arsitektur & Stack Teknologi](#-arsitektur--stack-teknologi)
- [Prasyarat](#-prasyarat)
- [Instalasi & Setup](#-instalasi--setup)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Migrasi & Seeding Database](#-migrasi--seeding-database)
- [Menjalankan Aplikasi (Development)](#-menjalankan-aplikasi-development)
- [Role & Hak Akses](#-role--hak-akses)
- [Alur Kerja Tiket](#-alur-kerja-tiket)
- [Persiapan Production](#-persiapan-production)
- [Integrasi SSO](#-integrasi-sso)
- [Notifikasi (Email & WhatsApp)](#-notifikasi-email--whatsapp)
- [Laporan & Ekspor](#-laporan--ekspor)
- [Struktur Direktori Penting](#-struktur-direktori-penting)
- [Troubleshooting](#-troubleshooting)

---

## 🎯 Tentang Sistem

Helpdesk UPA TIK adalah platform terpadu untuk:

- **Pelapor Internal** (mahasiswa, dosen, tendik/karyawan): melaporkan masalah dan permintaan layanan TIK setelah login via SSO institusi.
- **Pelapor Tamu/Eksternal**: membuat laporan tanpa akun dengan verifikasi identitas (foto KTP/KTM + selfie) dan validasi reCAPTCHA.
- **Staf Admin & Superuser**: menerima, mengelola, dan menyelesaikan tiket disertai komunikasi dua arah.

Sistem mendukung notifikasi real-time melalui database, e-mail, dan WhatsApp (Twilio), serta menghasilkan laporan analitik termasuk Customer Satisfaction Index (CSI) dan kinerja staf.

---

## ✨ Fitur Utama

### Manajemen Tiket

- Pembuatan tiket oleh **user internal** (login SSO) dan **tamu eksternal** (tanpa akun)
- Status tiket: `Waiting` → `Progress` → `Done` / `Reject`
- Prioritas tiket: `Low`, `Medium`, `High`
- Pencarian tiket via kode tiket untuk tamu & user
- Filter tiket berdasarkan status, prioritas, layanan, petugas, dan rentang tanggal
- Urutan cerdas: tiket aktif + prioritas tinggi + terlama tampil paling atas
- Upload lampiran (gambar & dokumen) via Trix editor (max 2 MB per file)
- Cetak **Surat Tugas** penugasan petugas dalam format PDF

### Komentar & Komunikasi

- Komentar dua arah antara pelapor dan staf di dalam tiket
- Upload file inline di komentar (jpg, jpeg, png, pdf, doc, docx, zip)
- Komentar tamu dilindungi reCAPTCHA

### Sistem Notifikasi Multi-Kanal

- **Database** (bell notifikasi real-time di UI)
- **Email** (via SMTP/Mailer)
- **WhatsApp** (via Twilio Sandbox API)
- Notifikasi dikirim pada: tiket baru, balasan komentar, penugasan, perubahan status, tiket selesai/ditolak

### Dashboard Berbasis Role

- **Superuser**: statistik global, tiket terbaru, distribusi per layanan
- **Admin**: tiket belum ditangani, tugas aktif milik sendiri, antrian prioritas
- **User**: ringkasan tiket aktif & selesai milik sendiri

### Laporan & Analitik

- Filter periode: harian, mingguan, bulanan, tahunan, atau rentang kustom
- Statistik global (total, waiting, progress, done, reject, completion rate)
- Tren harian, mingguan, bulanan (grafik Chart.js)
- Distribusi status dan distribusi entitas pelapor (mahasiswa, dosen, dll.)
- Rekap per layanan beserta breakdown status dan entitas
- **Customer Satisfaction Index (CSI)** berbasis weighted score survei
- **Peringkat kinerja staf** dengan bonus dedikasi (tiket off-hours & akhir pekan)
- Histogram durasi resolusi tiket
- **Ekspor Excel** laporan lengkap

### Survei Kepuasan

- Survei muncul setelah tiket berstatus Done/Reject
- Pertanyaan dinamis berbasis `SurveyQuestion` yang aktif
- Perhitungan CSI: Satisfaction Score × Importance Score / Total Importance × 100
- Middleware `EnsureSurveyCompleted` memastikan user mengisi survei sebelum lanjut

### Master Data

- **Layanan** (`services`): nama, status aktif, visibilitas ke tamu/user
- **Divisi** (`divisions`): unit fungsi staf
- **Departemen** (`departments`): unit kerja pelapor

### Manajemen Pengguna

- **Manajemen Staf** (Superuser only): tambah/edit/hapus admin & superuser, assign divisi
- **Manajemen User SSO**: listing, tambah, reset password, nonaktifkan user di sistem SSO eksternal (via API Node.js)
- **Konfigurasi**: nama, NIP, dan jabatan Kepala UPA untuk keperluan cetak surat

### Profil Pengguna

- Upload & hapus foto avatar
- Update nomor telepon
- Pilih divisi (staf) atau departemen (user)

### Keamanan

- Autentikasi SSO via API eksternal (Node.js/Express), token disimpan di session
- Middleware `role:` untuk pembatasan akses per kelompok role
- Validasi reCAPTCHA v2 untuk form tamu
- Sinkronisasi data profil dari SSO saat login (tanpa menimpa data yang sudah ada)
- Pembatasan domain email: alamat `@*.unila.ac.id` tidak bisa digunakan pelapor tamu
- Pencegahan akses lintas user (user A tidak bisa lihat tiket user B)

---

## 🛠 Arsitektur & Stack Teknologi

| Komponen          | Teknologi                                     |
| ----------------- | --------------------------------------------- |
| Backend Framework | Laravel 12 (PHP 8.4+)                         |
| Database          | PostgreSQL                                    |
| Frontend          | Blade, Tailwind CSS v4, Alpine.js v3          |
| Build Tool        | Vite 7                                        |
| Rich Text Editor  | Trix 2                                        |
| Chart             | Chart.js 4                                    |
| Font              | Inter Variable (`@fontsource-variable/inter`) |
| Ikon              | Material Design Icons                         |
| PDF Generate      | barryvdh/laravel-dompdf                       |
| Excel Export      | maatwebsite/excel 3.1                         |
| HTML Sanitizer    | mews/purifier                                 |
| Notifikasi WA     | Twilio WhatsApp API                           |
| SSO               | API Node.js/Express (internal)                |

---

## ✅ Prasyarat

Pastikan server/mesin development memiliki:

- **PHP** >= 8.4 dengan ekstensi: `pdo`, `pdo_pgsql` (atau `pdo_mysql`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`
- **Composer** >= 2
- **Node.js** >= 18 dan **npm** >= 9
- **PostgreSQL** >= 14
- **Git**
- Akses ke **API SSO** institusi (Node.js/Express, berjalan di `http://localhost:3000`)
- Akun **Twilio** (untuk notifikasi WhatsApp, opsional)
- Kunci **Google reCAPTCHA v2** (untuk form tamu)

---

## 🚀 Instalasi & Setup

### 1. Clone Repositori

```bash
git clone https://github.com/kiraware/helpdesk-upa-tik-unila
cd helpdesk-upa-tik
```

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Install Dependensi Node.js

```bash
npm install
```

### 4. Salin File Environment

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

Edit file `.env` dan sesuaikan blok database:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=helpdesk_upa_tik_unila
DB_USERNAME=postgres
DB_PASSWORD=password_anda
```

### 7. Buat Database

```bash
# Di PostgreSQL
psql -U postgres -c "CREATE DATABASE helpdesk_upa_tik_unila;"
```

### 8. Jalankan Migrasi

```bash
php artisan migrate
```

### 9. (Opsional) Jalankan Seeder

```bash
php artisan db:seed
```

### 10. Build Asset Frontend

```bash
# Development
npm run dev

# Production
npm run build
```

### 11. Buat Symlink Storage

```bash
php artisan storage:link
```

---

## ⚙️ Konfigurasi Environment

Berikut penjelasan lengkap setiap variabel di `.env`:

### Aplikasi

```env
APP_NAME="Helpdesk UPA TIK UNILA"      # Nama aplikasi (tampil di email, notif, dll.)
APP_ENV=local                          # Nilai: local | staging | production
APP_KEY=                               # Diisi otomatis oleh php artisan key:generate
APP_DEBUG=false                        # Set false di production
APP_URL=http://localhost               # URL publik aplikasi (wajib benar di production)
APP_LOCALE=id                          # Locale default
```

### Database

```env
DB_CONNECTION=pgsql           # pgsql (PostgreSQL)
DB_HOST=127.0.0.1
DB_PORT=5432                  # 5432 untuk PostgreSQL
DB_DATABASE=helpdesk_upa_tik_unila
DB_USERNAME=postgres
DB_PASSWORD=
```

### Email (Mail)

```env
MAIL_MAILER=smtp              # log (dev) | smtp (production)
MAIL_HOST=smtp.mailtrap.io    # Host SMTP
MAIL_PORT=587                 # Port SMTP (587 untuk TLS, 465 untuk SSL)
MAIL_USERNAME=                # Username SMTP
MAIL_PASSWORD=                # Password SMTP
MAIL_ENCRYPTION=tls           # tls | ssl | null
MAIL_FROM_ADDRESS="noreply@helpdesktik.unila.ac.id"
MAIL_FROM_NAME="${APP_NAME}"
```

Untuk development, gunakan `MAIL_MAILER=log` — email akan ditulis ke `storage/logs/laravel.log` tanpa dikirim.

### WhatsApp (Twilio)

```env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx   # Account SID dari Twilio Console
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_WHATSAPP_FROM="+14155238886"             # Nomor sandbox Twilio (atau nomor production)
```

> Untuk production, pastikan Twilio WhatsApp number sudah diaktifkan dan template pesan sudah disetujui.

### Google reCAPTCHA v2

```env
RECAPTCHA_SITE_KEY=           # Site Key dari Google reCAPTCHA Console
RECAPTCHA_SECRET_KEY=         # Secret Key dari Google reCAPTCHA Console
```

Digunakan pada form pembuatan tiket tamu dan komentar tamu.

---

## 🗄 Migrasi & Seeding Database

### Menjalankan Migrasi

```bash
# Jalankan semua migrasi baru
php artisan migrate

# Reset dan jalankan ulang semua migrasi (HAPUS SEMUA DATA)
php artisan migrate:fresh

# Migrasi dengan seeder sekaligus
php artisan migrate:fresh --seed
```

### Tabel Utama yang Dibuat

| Tabel                   | Keterangan                                 |
| ----------------------- | ------------------------------------------ |
| `users`                 | Pengguna sistem (user, admin, superuser)   |
| `tickets`               | Data tiket helpdesk                        |
| `ticket_comments`       | Komentar / balasan pada tiket              |
| `ticket_attachments`    | Lampiran pada deskripsi tiket              |
| `comment_attachments`   | Lampiran pada komentar                     |
| `guest_ticket_details`  | Data identitas pelapor tamu                |
| `services`              | Master data layanan                        |
| `departments`           | Master data unit kerja (untuk user)        |
| `divisions`             | Master data unit fungsi (untuk staf)       |
| `ticket_surveys`        | Header survei kepuasan                     |
| `ticket_survey_answers` | Detail jawaban survei per pertanyaan       |
| `survey_questions`      | Pertanyaan survei (dinamis)                |
| `configurations`        | Konfigurasi global (nama kepala UPA, dll.) |
| `notifications`         | Notifikasi database Laravel                |
| `sessions`              | Sesi pengguna                              |
| `jobs`                  | Antrian pekerjaan (queue)                  |
| `cache`                 | Cache database                             |

### Seeder

Seeder disarankan untuk mengisi:

- Data `configurations` awal (nama kepala UPA, dll)
- Data `departments` awal (unit kerja)
- Data `divisions` awal (unit fungsi)
- Data `services` awal (layanan TIK)
- Data `survey_questions` awal (pertanyaan survei kepuasan)
- Akun superuser pertama

```bash
php artisan db:seed
```

---

## 🖥 Menjalankan Aplikasi (Development)

Gunakan perintah berikut untuk menjalankan semua proses sekaligus:

```bash
composer run dev
```

Perintah ini menjalankan secara paralel:

- `php artisan serve` — server PHP
- `php artisan queue:listen --tries=1` — worker antrian untuk notifikasi
- `npm run dev` — Vite development server dengan HMR

Atau jalankan secara terpisah di terminal berbeda:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

Pastikan juga **API SSO** (Node.js/Express) sudah berjalan di `http://localhost:3000`.

Akses aplikasi di: `http://localhost:8000`

---

## 👥 Role & Hak Akses

Sistem memiliki tiga role pengguna (`UserRole` enum):

### `user` (Pengguna Internal)

- Login via SSO (mahasiswa, dosen, tendik, karyawan)
- Membuat tiket baru untuk layanan yang `show_to_user = true`
- Melihat & mengomentari tiket milik sendiri saja
- Mengisi survei kepuasan setelah tiket selesai
- Mengelola profil (avatar, nomor telepon, departemen)

### `admin` (Staf Helpdesk)

- Semua akses `user`
- Melihat **semua** tiket (user internal maupun tamu)
- Mengambil/menerima tiket (`assign-me`)
- Mengubah petugas, layanan, dan prioritas tiket
- Menutup tiket (Done/Reject)
- Melihat antrian `waiting` dan tiket milik sendiri (`assigned`)
- Akses laporan & ekspor Excel
- Manajemen master data: layanan, divisi, departemen
- Manajemen User SSO (listing, tambah, reset password, nonaktifkan)
- Cetak Surat Tugas PDF

### `superuser` (Administrator Sistem)

- Semua akses `admin`
- Manajemen staf (tambah/edit/hapus admin & superuser)
- Konfigurasi data pejabat penandatangan surat

### Tamu (Guest / Tidak Login)

- Membuat tiket via form publik (dengan reCAPTCHA & upload KTP/selfie) untuk layanan `show_to_guest = true`
- Melacak tiket via kode tiket
- Mengomentari tiket milik sendiri (via kode tiket)
- Mengisi survei kepuasan

---

## 🔄 Alur Kerja Tiket

```
[Pelapor membuat tiket]
        │
        ▼
   Status: WAITING
   ─────────────────────────────────────────────
   Admin/Superuser menerima notifikasi
        │
        ├─ Admin komentar atau klik "Ambil Tiket"
        │         │
        │         ▼
        │    Otomatis: assigned_to = admin,
        │              assigned_at = now(),
        │              status = PROGRESS
        │
        ▼
   Status: PROGRESS
   ─────────────────────────────────────────────
   Komunikasi dua arah via komentar
   Notifikasi dikirim ke pelapor & petugas
        │
        ├─ Admin klik "Selesaikan"
        │         │
        │         ▼
        │    Status: DONE  ──→ Survei kepuasan terbuka
        │
        └─ Admin klik "Tolak"
                  │
                  ▼
             Status: REJECT ──→ Survei kepuasan terbuka
```

---

## 🏭 Persiapan Production

### 1. Konfigurasi Environment Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://helpdesktik.unila.ac.id

DB_CONNECTION=pgsql
# ... isi kredensial database production

MAIL_MAILER=smtp
# ... isi kredensial SMTP resmi

TWILIO_SID=...
TWILIO_AUTH_TOKEN=...
TWILIO_WHATSAPP_FROM="+628xxxxxxxxxx"  # Nomor WA production terverifikasi

RECAPTCHA_SITE_KEY=...
RECAPTCHA_SECRET_KEY=...
```

### 2. Install Dependensi (tanpa dev packages)

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Build Asset Frontend

```bash
npm run build
```

### 4. Jalankan Migrasi

```bash
php artisan migrate --force
```

### 5. Optimasi Laravel

```bash
php artisan optimize
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Atur Permission Direktori

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Konfigurasi Web Server

**Nginx** — contoh konfigurasi:

```nginx
server {
    listen 80;
    server_name helpdesktik.unila.ac.id;
    root /var/www/helpdesk-upa-tik/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan HTTPS dengan **Let's Encrypt** / **Certbot**:

```bash
certbot --nginx -d helpdesktik.unila.ac.id
```

### 9. Queue Worker (Production)

Gunakan **Supervisor** untuk menjaga queue worker tetap berjalan:

Buat file `/etc/supervisor/conf.d/helpdesk-worker.conf`:

```ini
[program:helpdesk-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/helpdesk-upa-tik/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopaswhim=false
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/helpdesk-upa-tik/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start helpdesk-worker:*
```

### 10. Task Scheduler (Opsional)

Jika ada scheduled task, tambahkan cron entry:

```bash
crontab -e
# Tambahkan:
* * * * * cd /var/www/helpdesk-upa-tik && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Deployment Checklist

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production` di `.env`
- [ ] Database migration sudah dijalankan (`--force`)
- [ ] `php artisan optimize` dijalankan
- [ ] `php artisan storage:link` dijalankan
- [ ] Permission `storage/` dan `bootstrap/cache/` sudah benar
- [ ] Queue worker berjalan via Supervisor
- [ ] HTTPS aktif
- [ ] API SSO berjalan dan bisa diakses dari server
- [ ] Twilio WhatsApp number terverifikasi (jika digunakan)
- [ ] reCAPTCHA domain terdaftar di Google Console

---

## 🔐 Integrasi SSO

Aplikasi menggunakan autentikasi SSO eksternal via API Node.js/Express yang berjalan di `http://localhost:3000`.

### Endpoint API SSO yang Digunakan

| Method | URL               | Fungsi                                                |
| ------ | ----------------- | ----------------------------------------------------- |
| `POST` | `/login`          | Autentikasi user, mengembalikan data user & JWT token |
| `GET`  | `/users`          | Listing user SSO (butuh Bearer token)                 |
| `POST` | `/users`          | Tambah user SSO baru                                  |
| `POST` | `/reset-password` | Reset password user                                   |
| `POST` | `/inactive-user`  | Nonaktifkan user                                      |

### Alur Login SSO

1. User submit form login (`username` + `password`)
2. Aplikasi POST ke `http://localhost:3000/login`
3. Jika berhasil: data user disinkronisasi ke tabel `users` lokal
4. JWT token SSO disimpan di session (`sso_token`)
5. User di-login via `Auth::login()`

### Sinkronisasi Data User

Setiap login, data berikut diperbarui dari SSO (hanya jika tidak kosong):

- `identity_number` (NIM/NIP)
- `name`
- `email`
- `phone`
- `entity` (Mahasiswa, Dosen, Karyawan, Tamu, dll.)

Data yang ada di database **tidak akan ditimpa** oleh nilai kosong dari SSO (menggunakan operator Elvis `?:`).

### Mapping Entity dari SSO

| Nilai SSO (raw)           | `UserEntity` |
| ------------------------- | ------------ |
| `super user`, `superuser` | `SUPER_USER` |
| `mahasiswa`               | `MAHASISWA`  |
| `dosen`                   | `DOSEN`      |
| `karyawan`, `staff`       | `KARYAWAN`   |
| `tamu`, `guest`           | `TAMU`       |
| Lainnya / kosong          | `LAINNYA`    |

---

## 📬 Notifikasi (Email & WhatsApp)

### Kanal yang Tersedia

| Kanal    | Class / Driver                 | Keterangan                                   |
| -------- | ------------------------------ | -------------------------------------------- |
| Database | Laravel built-in               | Notifikasi bell di UI, real-time via polling |
| Email    | Laravel Mail                   | Dikirim via SMTP yang dikonfigurasi          |
| WhatsApp | `App\Channels\WhatsAppChannel` | Menggunakan Twilio API                       |

### Peristiwa yang Memicu Notifikasi

| Peristiwa                       | Penerima          | Kanal                       |
| ------------------------------- | ----------------- | --------------------------- |
| Tiket baru (user internal)      | Admin & Superuser | DB + Email + WA             |
| Tiket baru (tamu)               | Admin & Superuser | DB + Email + WA             |
| Tiket baru (konfirmasi ke tamu) | Tamu (email/WA)   | Email + WA (jika ada no HP) |
| Admin komentar                  | Pelapor           | DB + Email + WA             |
| User/tamu komentar balik        | Petugas assigned  | DB + Email + WA             |
| Tiket selesai / ditolak         | Pelapor           | DB + Email + WA             |

### Konfigurasi Twilio untuk WhatsApp

1. Buat akun di [twilio.com](https://www.twilio.com)
2. Aktifkan **WhatsApp Sandbox** (development) atau daftarkan nomor production
3. Isi `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, dan `TWILIO_WHATSAPP_FROM` di `.env`
4. Penerima WhatsApp sandbox harus opt-in terlebih dahulu dengan mengirim pesan ke nomor sandbox

---

## 📊 Laporan & Ekspor

Diakses di `/reports` (hanya Admin & Superuser).

### Filter Periode

- **Harian**: hari ini
- **Mingguan**: minggu berjalan (Senin–Minggu)
- **Bulanan**: bulan berjalan
- **Tahunan**: tahun berjalan
- **Kustom**: pilih rentang tanggal bebas

### Data yang Tersedia

- Statistik global (total, per status, completion rate)
- Tren harian, mingguan, bulanan (line/bar chart)
- Distribusi status & entitas pelapor (doughnut/pie chart)
- Rekap per layanan (tabel + chart)
- CSI global (persentase + predikat: Sangat Puas / Puas / Cukup Puas / Kurang Puas / Tidak Puas)
- Peringkat kinerja staf (tiket diselesaikan, CSI personal, waktu resolusi rata-rata, bonus dedikasi off-hours)
- Histogram durasi resolusi tiket

### Ekspor Excel

Klik tombol **Export Excel** di halaman laporan. File akan diunduh dengan nama format:
`Laporan_Helpdesk_DD-MMM-YYYY_sd_DD-MMM-YYYY.xlsx`

---

## 📁 Struktur Direktori Penting

```
app/
├── Channels/
│   └── WhatsAppChannel.php      # Custom notif channel via Twilio
├── Enums/
│   ├── IdentityType.php         # Jenis identitas tamu
│   ├── TicketPriority.php       # Low, Medium, High
│   ├── TicketStatus.php         # Waiting, Progress, Done, Reject
│   ├── UserEntity.php           # Mahasiswa, Dosen, Karyawan, dst.
│   └── UserRole.php             # User, Admin, Superuser
├── Exports/
│   └── TicketReportExport.php   # Excel export class
├── Helpers/
│   └── OffHoursHelper.php       # Kalkulasi bonus dedikasi staf
├── Http/
│   ├── Controllers/             # Semua controller (lihat daftar di atas)
│   └── Middleware/
│       └── EnsureSurveyCompleted.php
├── Models/                      # Eloquent models
├── Notifications/
│   └── SystemNotification.php   # Kelas notifikasi terpadu
└── Rules/
    └── ValidRecaptcha.php       # Custom rule validasi reCAPTCHA

resources/
├── views/
│   ├── auth/                    # Halaman login
│   ├── dashboard/               # Dashboard per role (superuser, admin, user)
│   ├── tickets/                 # CRUD tiket & cetak PDF surat tugas
│   ├── guest-tickets/           # Form tamu, tracking, detail
│   ├── reports/                 # Halaman laporan
│   ├── users/                   # Manajemen staf
│   ├── sso-users/               # Manajemen user SSO
│   ├── services/                # Master data layanan
│   ├── departments/             # Master data unit kerja
│   ├── divisions/               # Master data unit fungsi
│   ├── configurations/          # Konfigurasi sistem
│   ├── notifications/           # Halaman notifikasi
│   └── profile/                 # Halaman profil

storage/
└── app/public/
    ├── avatars/                 # Foto profil user
    ├── ticket-attachments/      # Lampiran deskripsi tiket
    ├── comment-attachments/     # Lampiran komentar
    ├── guest-identities/        # Foto KTP/identitas tamu
    └── guest-selfies/           # Foto selfie tamu

routes/
└── web.php                      # Semua route aplikasi
```

---

## 🔧 Troubleshooting

### Notifikasi tidak terkirim

Pastikan queue worker berjalan:

```bash
php artisan queue:listen --tries=1
```

Cek antrian yang gagal:

```bash
php artisan queue:failed
php artisan queue:retry all
```

### Login SSO gagal

- Pastikan API SSO berjalan di `http://localhost:3000`
- Cek log: `storage/logs/laravel.log`
- Pastikan endpoint `/login` di API SSO mengembalikan format `{ user: {...}, token: "..." }`

### File upload tidak muncul

Pastikan symlink storage sudah ada:

```bash
php artisan storage:link
```

Cek permission direktori `storage/app/public/`.

### Cache/config lama setelah deployment

```bash
php artisan optimize:clear
php artisan optimize
```

### Sesi logout sendiri / sering expired

Periksa `SESSION_LIFETIME` di `.env` dan pastikan tabel `sessions` ada di database:

```bash
php artisan migrate
```

---

## 📄 Lisensi

Proyek ini dikembangkan untuk kebutuhan internal UPA TIK Universitas Lampung.

---
