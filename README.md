# SafeGive (E-Donate Blockchain)

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg?style=flat-square)](tests)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20.svg?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![Vue Version](https://img.shields.io/badge/Vue.js-3.5-4FC08D.svg?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org/)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-3.x-9553E9.svg?style=flat-square&logo=inertia&logoColor=white)](https://inertiajs.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE)

> **Platform Donasi Kemanusiaan Transparan & Aman Berbasis Kriptografi Homomorfik Paillier, Ledger Transaksi Imutabel, dan Multi-Channel Payment Gateway.**

---

## Daftar Isi
- [1. Deskripsi Singkat & Solusi Masalah](#1-deskripsi-singkat--solusi-masalah)
- [2. Fitur Utama](#2-fitur-utama)
- [3. Tech Stack](#3-tech-stack)
- [4. Arsitektur & Struktur Direktori](#4-arsitektur--struktur-direktori)
- [5. Cara Memulai (Getting Started)](#5-cara-memulai-getting-started)
  - [Prasyarat (Prerequisites)](#prasyarat-prerequisites)
  - [Langkah Instalasi](#langkah-instalasi)
  - [Konfigurasi Environment (.env)](#konfigurasi-environment-env)
  - [Menjalankan Aplikasi](#menjalankan-aplikasi)
  - [Akun Bawaan (Default Seeders)](#akun-bawaan-default-seeders)
- [6. Panduan Penggunaan (Usage)](#6-panduan-penggunaan-usage)
  - [Alur Donatur](#alur-donatur)
  - [Alur Penyelenggara (Organizer)](#alur-penyelenggara-organizer)
  - [Alur Administrator](#alur-administrator)
  - [Perintah Artisan Khusus](#perintah-artisan-khusus)
- [7. Pengujian (Testing)](#7-pengujian-testing)
- [8. Panduan Kontribusi (Contributing)](#8-panduan-kontribusi-contributing)
- [9. Lisensi (License)](#9-lisensi-license)

---

## 1. Deskripsi Singkat & Solusi Masalah

### Masalah yang Dihadapi
Platform penggalangan dana konvensional kerap menghadapi dua dilema besar:
1. **Kurangnya Transparansi & Akuntabilitas**: Publik kesulitan memverifikasi apakah nominal yang dilaporkan benar-benar sesuai dengan mutasi dana masuk tanpa rekayasa database internal.
2. **Kekhawatiran Privasi Donatur**: Banyak donatur ingin menyumbang secara rahasia (anonim) tanpa nama mereka terekspos ke publik, namun di sisi lain sistem audit membutuhkan bukti otentikasi bahwa donasi tersebut nyata dan bukan donasi fiktif.
3. **Risiko Penipuan Penyelenggara**: Dana sering kali dicairkan sebelum target atau batas waktu yang ditentukan tercapai tanpa syarat transparansi penggunaan pasca-pencairan.

### Solusi SafeGive
**SafeGive** menggabungkan kemudahan transaksi perbankan lokal Indonesia dengan fondasi **Kriptografi Blockchain Modern**:
- **Enkripsi Homomorfik Paillier (1024-bit)**: Memungkinkan server menjumlahkan seluruh donasi masuk langsung di atas data terenkripsi (*ciphertext*) tanpa pernah mendekripsi nominal privat masing-masing donatur.
- **Ledger Kriptografis Imutabel**: Setiap donasi diterbitkan dengan format *Transaction Hash* standar blockchain (`0x...` SHA-256) dan nomor blok sekuensial yang tidak dapat dimanipulasi (*tamper-proof*).
- **Anonimitas Asimetris**: Identitas donatur yang memilih sembunyikan nama dienkripsi ke dalam ciphertext, sedangkan kolom nama publik dikosongkan (`null`). Publik hanya melihat potongan hash verifikasi.
- **Pencairan Bersyarat Ketat (Smart Milestone)**: Penyelenggara hanya dapat mengajukan penarikan dana jika target telah tercapai ($\ge 100\%$) atau masa kampanye telah berakhir. Pasca-pencairan, penyelenggara wajib mengunggah video dokumentasi penyaluran dana (YouTube/TikTok/Facebook).

---

## 2. Fitur Utama

- **Enkripsi Homomorfik Paillier**:
  - Penjumlahan terenkripsi: $E(m_1) \times E(m_2) \pmod{n^2} = E(m_1 + m_2)$.
  - Sidik kriptografis (*Cryptographic Commitment*) berbasis SHA-256 untuk mendeteksi manipulasi database.
- **Sistem Pembayaran Terintegrasi (Dual Method)**:
  - **Midtrans Payment Gateway**: Integrasi Snap API (Virtual Account BCA/Mandiri/BRI/BNI/Permata, QRIS Dinamis GoPay/ShopeePay, dan Kartu Kredit).
  - **Transfer Bank Manual & Upload Bukti**: Transfer ke satu rekening platform terpusat dengan upload struk/bukti bayar.
- **Verifikasi KYC Penyelenggara (Individu & Yayasan)**:
  - Pendaftaran legalitas akun penyelenggara dengan enkripsi data sensitif (NIK, NPWP, Alamat tersimpan dengan enkripsi simetris database).
  - Unggah foto KTP, selfie identitas, dan dokumen legalitas yayasan yang diverifikasi ketat oleh Admin.
- **Smart Campaign Management**:
  - URL kampanye ramah SEO dengan slug otomatis dan *fallback* ID.
  - Validasi jadwal aktif: kampanye hanya menerima donasi di antara waktu mulai (`starts_at`) dan batas waktu (`ends_at`).
  - Progress bar dinamis berdasarkan kalkulasi dekripsi total agregasi Paillier.
- **Panel Audit Ledger Publik**:
  - Laporan ledger real-time yang memuat nomor blok, hash transaksi, metode bayar, dan status validasi.
- **Panel Kontrol Admin Komprehensif**:
  - Verifikasi KYC pendaftar (Setujui / Tolak dengan catatan revisi).
  - Pengaturan rekening resmi platform & kredensial Midtrans (Sandbox/Production).
  - Validasi dan penolakan donasi manual.
  - Persetujuan pencairan dana (*payout approval*) beserta pencatatan hash mutasi bank.

---

## 3. Tech Stack

### Backend
- **Framework**: [Laravel 11 / 12](https://laravel.com/)
- **Bahasa**: [PHP 8.3 / 8.4](https://www.php.net/)
- **Monolith SPA Bridge**: [Inertia.js Laravel](https://inertiajs.com/)
- **Kriptografi Presisi Tinggi**: [phpseclib 3.0](https://phpseclib.com/) (Operasi `BigInteger` modular & pembangkitan bilangan prima Paillier)
- **Payment Gateway SDK**: [Midtrans PHP SDK](https://github.com/Midtrans/midtrans-php)

### Frontend
- **Framework**: [Vue 3](https://vuejs.org/) (Composition API, `<script setup lang="ts">`)
- **Bahasa**: [TypeScript](https://www.typescriptlang.org/)
- **Styling**: [Tailwind CSS 3.4](https://tailwindcss.com/)
- **Build Tool**: [Vite 7](https://vitejs.dev/)
- **Ikon UI**: [Tabler Icons Vue](https://tabler.io/icons)
- **Web3 Library**: [Viem](https://viem.sh/) (Kesiapan interaksi Smart Contract EVM)

### Database & Smart Contract
- **Database**: MySQL / MariaDB (Kompatibel dengan SQLite untuk pengujian cepat)
- **Smart Contract**: [Solidity 0.8.20](https://soliditylang.org/) (`contracts/SafeGiveCampaign.sol` untuk integrasi Polygon Amoy Testnet)

---

## 4. Arsitektur & Struktur Direktori

Aplikasi ini menggunakan pendekatan arsitektur **Inertia Monorepo (Single Repository SPA)**. Routing, otentikasi, validasi, dan komputasi kriptografis dikendalikan oleh backend Laravel, sementara antarmuka pengguna dirender secara reaktif menggunakan Vue 3.

```plaintext
e-donate-blockchain/
├── app/
│   ├── Actions/                  # Single Action classes (mis. LogoutAction)
│   ├── Http/
│   │   ├── Controllers/          # Controller HTTP (Admin, Campaign, Donation, Midtrans, dll.)
│   │   ├── Middleware/           # Middleware (EnsureUserIsAdmin, HandleInertiaRequests)
│   │   └── Requests/             # Form Requests & Validasi input
│   ├── Models/                   # Eloquent Model (Campaign, Donation, User, AdminBankSetting, dll.)
│   ├── Providers/                # Service Providers Laravel
│   └── Services/                 # Business logic inti:
│       ├── PaillierService.php        # Implementasi kriptografi Paillier 1024-bit
│       ├── DonationAmountEncryptor.php# Wrapper enkripsi donasi
│       └── MidtransService.php        # Integrasi Snap API, webhook signature, & status check
├── bootstrap/                    # Bootstrap kernel & konfigurasi rate limiter
├── config/                       # File konfigurasi (app, database, services, session, dll.)
├── contracts/                    # Smart contract Solidity (SafeGiveCampaign.sol)
├── database/
│   ├── factories/                # Model factories untuk testing
│   ├── migrations/               # Skema migrasi database
│   └── seeders/                  # Seeder peran (RoleSeeder) dan akun default (DatabaseSeeder)
├── public/                       # Entry point aplikasi web & build assets
├── resources/
│   ├── css/                      # File CSS utama & styling Tailwind
│   └── js/                       # Kode sumber Vue 3 & TypeScript
│       ├── Components/           # Komponen UI reusable (Modal, Sidebar, Navbar, dll.)
│       ├── Layouts/              # Layout template (AppLayout, GuestLayout)
│       └── Pages/                # Halaman view Inertia:
│           ├── Admin/            # Portal administrasi (Bank, Donasi, Verifikasi, Pencairan)
│           ├── Auth/             # Autentikasi (Login, Register, Reset Password)
│           ├── Campaigns/        # Katalog kampanye, Detail publik/auth, Buat kampanye
│           ├── Transactions/     # Riwayat transaksi donatur
│           ├── Dashboard.vue     # Dashboard pengguna
│           ├── Guide.vue         # Panduan penggunaan sistem
│           ├── Landing.vue       # Landing page publik
│           └── Profile.vue       # Profil pengguna & formulir KYC
├── routes/
│   ├── app.php                   # Rute fitur utama aplikasi (terlindungi auth & admin)
│   ├── auth.php                  # Rute autentikasi
│   ├── console.php               # Perintah kustom CLI Artisan
│   └── web.php                   # Entry point web & webhook Midtrans
├── storage/                      # Direktori penyimpanan privat, log, & upload publik
│   └── app/private/paillier/     # Lokasi penyimpanan kunci asimetris keys.json Paillier
└── tests/
    ├── Feature/                  # Feature test (DonationEncryptionTest, CampaignVideoUrlTest, dll.)
    └── Unit/                     # Unit test kriptografi (PaillierServiceTest)
```

---

## 5. Cara Memulai (Getting Started)

### Prasyarat (Prerequisites)
Pastikan lingkungan pengembangan Anda telah memenuhi kebutuhan berikut:
- **PHP**: Versi `^8.3` atau `^8.4` dengan ekstensi aktif:
  - `ext-bcmath` atau `ext-gmp` (Wajib untuk komputasi bilangan besar Paillier)
  - `ext-openssl`, `ext-pdo`, `ext-pdo_mysql`, `ext-mbstring`, `ext-curl`, `ext-fileinfo`
- **Composer**: Versi `2.x`
- **Node.js**: Versi `>= 18.x` dan **npm** `>= 9.x`
- **Database**: MySQL / MariaDB (atau Laragon / XAMPP)
- **Git**

---

### Langkah Instalasi

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/whyutams/e-donate-blockchain.git
   cd e-donate-blockchain
   ```

2. **Instal Dependensi Backend (PHP)**:
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend (Node.js)**:
   ```bash
   npm install
   ```

4. **Konfigurasi Berkas Lingkungan (`.env`)**:
   Salin template `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```

5. **Generate Kunci Aplikasi Laravel**:
   ```bash
   php artisan key:generate
   ```

6. **Konfigurasi Database**:
   Buka file `.env` dan sesuaikan koneksi database lokal Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=e_donate
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Pastikan database `e_donate` telah dibuat di MySQL).*

7. **Jalankan Migrasi Database & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

8. **Buat Tautan Simbolik Storage**:
   Perintah ini diperlukan agar bukti transfer dan dokumen yang diunggah dapat diakses oleh aplikasi:
   ```bash
   php artisan storage:link
   ```

9. **Inisialisasi Pasangan Kunci Kriptografi Paillier**:
   Hasilkan pasangan kunci asimetris 1024-bit (`keys.json`) di penyimpanan privat:
   ```bash
   php artisan paillier:keys
   ```

---

### Konfigurasi Environment (.env)

Berikut parameter penting yang dapat disesuaikan pada file `.env`:

```env
APP_NAME="SafeGive"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Konfigurasi Midtrans Payment Gateway (Mode Sandbox)
MIDTRANS_MERCHANT_ID=your_merchant_id
MIDTRANS_SERVER_KEY=SB-Mid-server-your-sandbox-server-key
MIDTRANS_CLIENT_KEY=SB-Mid-client-your-sandbox-client-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
VITE_MIDTRANS_CLIENT_KEY="${MIDTRANS_CLIENT_KEY}"

# Konfigurasi Web3 / Jaringan Polygon Amoy (Opsional)
WEB3_RPC_URL=https://polygon-amoy-bor-rpc.publicnode.com
WEB3_CHAIN_ID=80002
WEB3_CAMPAIGN_CONTRACT_ADDRESS=
```

> **Catatan Midtrans**: Anda dapat memperoleh Server Key dan Client Key secara gratis melalui [Midtrans Dashboard Sandbox](https://dashboard.sandbox.midtrans.com/) pada menu **Settings > Access Keys**.

---

### Menjalankan Aplikasi

Jalankan server backend Laravel dan server kompilasi Vite secara bersamaan:

**Terminal 1 (Backend Server)**:
```bash
php artisan serve
```
*Aplikasi berjalan pada: [http://127.0.0.1:8000](http://127.0.0.1:8000)*

**Terminal 2 (Frontend Vite Dev Server)**:
```bash
npm run dev
```

*Alternatif untuk menjalankan secara paralel dalam satu terminal:*
```bash
composer run dev
```

---

### Akun Bawaan (Default Seeders)

Setelah menjalankan `php artisan migrate --seed`, akun berikut siap digunakan:

| Peran (Role) | Email | Password | Hak Akses |
|---|---|---|---|
| **Administrator** | `admin@example.com` | `password` | Akses penuh menu `/admin/*` (Validasi Donasi, Verifikasi KYC, Pencairan Dana, Pengaturan Rekening & Midtrans). |
| **Penyelenggara / Donatur** | `wahyu@example.com` | `password` | Melakukan donasi, mengajukan profil KYC, membuat kampanye, dan mengajukan pencairan. |

---

## 6. Panduan Penggunaan (Usage)

### Alur Donatur
1. Akses halaman katalog kampanye (`/campaigns`) dan pilih program donasi.
2. Di halaman detail kampanye, pilih metode pembayaran:
   - **Midtrans Snap**: Pilih nominal, klik bayar, lalu selesaikan via simulator QRIS atau Virtual Account bank.
   - **Transfer Bank Manual**: Transfer ke rekening resmi SafeGive yang tertera, lalu unggah bukti transfer.
3. Centang opsi **Sembunyikan Nama Saya** jika ingin berdonasi secara anonim. Sistem akan mengenkripsi nama dengan Paillier cryptosystem.
4. Lacak status dan bukti hash `0x...` melalui panel **Ledger Donasi Kriptografis** atau halaman **Riwayat Transaksi** (`/transactions`).

### Alur Penyelenggara (Organizer)
1. Daftarkan akun baru di `/register`.
2. Masuk ke menu **Profile** dan isi formulir KYC:
   - **Individu**: Masukkan NIK, nomor kontak, unggah foto KTP dan foto selfie memegang KTP.
   - **Yayasan**: Masukkan nama yayasan, NPWP, nama penanggung jawab, dan unggah SK Kemenkumham / akta pendirian.
3. Setelah akun berstatus `verified` oleh admin, buat kampanye di menu **Kampanye Saya > Buat Kampanye**.
4. Isi target nominal, tenggat waktu, deskripsi, foto sampul, dan nomor rekening penampungan pencairan milik penyelenggara.
5. Tombol **Ajukan Pencairan Dana** akan aktif otomatis jika target donasi telah tercapai ($\ge 100\%$) atau batas waktu kampanye telah berakhir.
6. Setelah dana dicairkan oleh admin, unggah URL video penyaluran dana (YouTube, TikTok, atau Facebook) pada kampanye untuk transparansi ke publik.

### Alur Administrator
1. Login menggunakan akun `admin@example.com`.
2. **Pengaturan Rekening Platform** (`/admin/bank-settings`): Atur nomor rekening pusat tujuan transfer donasi manual dan pasang konfigurasi Midtrans Gateway.
3. **Verifikasi KYC Penyelenggara** (`/admin/verifications`): Tinjau dokumen identitas penyelenggara, lalu klik *Setujui* atau *Tolak* beserta catatan perbaikan.
4. **Validasi Donasi Masuk** (`/admin/donations`):
   - Periksa kesesuaian struk transfer donatur.
   - Klik **Konfirmasi Donasi**: Sistem otomatis memicu fungsi agregasi homomorfik Paillier dan menambah progres kampanye.
   - Untuk donasi Midtrans, admin dapat mengklik tombol **Cek Midtrans** untuk sinkronisasi status seketika via REST API.
5. **Pencairan Dana** (`/admin/withdrawals`): Tinjau pengajuan pencairan kampanye yang sah, lakukan transfer ke rekening penyelenggara, dan konfirmasi dengan menginput hash/bukti transfer bank.

---

### Perintah Artisan Khusus

Platform menyediakan perintah Artisan kustom untuk mempermudah pemeliharaan dan pengujian lokal:

```bash
# 1. Menghasilkan pasangan kunci Paillier 1024-bit baru di storage/app/private/paillier/keys.json
php artisan paillier:keys

# Gunakan opsi --force untuk menimpa kunci yang sudah ada
php artisan paillier:keys --force

# 2. Menampilkan daftar donasi beserta status dan hash transaksi
php artisan donation:list

# 3. Mengonfirmasi donasi secara manual untuk pengujian lokal (memicu kalkulasi homomorfik)
php artisan donation:confirm {donation_id}
```

---

## 7. Pengujian (Testing)

Proyek ini dilengkapi dengan unit test dan feature test otomatis menggunakan PHPUnit yang menguji keabsahan algoritma Paillier, alur donasi terenkripsi, batas akses otentikasi, validasi URL video penyaluran, serta rendering landing page.

Jalankan seluruh pengujian dengan perintah:

```bash
php artisan test
```

### Lingkup Pengujian:
- `Tests\Unit\PaillierServiceTest`:
  - Enkripsi dan dekripsi integer homomorfik.
  - Enkripsi dan dekripsi string nama donatur anonim.
  - Pembangkitan cryptographic commitment berbasis SHA-256.
- `Tests\Feature\DonationEncryptionTest`:
  - Enkripsi identitas donatur saat memilih opsi `is_anonymous`.
  - Konsistensi penyimpanan teks polos saat opsi anonim tidak dipilih.
  - Proteksi otentikasi donasi (guest dialihkan ke halaman login).
  - Alur enkripsi nama pada transaksi Midtrans Snap.
- `Tests\Feature\CampaignVideoUrlTest`:
  - Validasi pembaruan URL video dokumentasi hanya saat kampanye berstatus `withdrawn`.
  - Validasi domain video yang didukung (YouTube, TikTok, Facebook).
  - Pembatasan hak akses selain pemilik kampanye.
- `Tests\Feature\LandingPageTest`:
  - Rendering landing page dan kalkulasi statistik global.
  - Resolusi rute kampanye menggunakan slug unik.

---

## 8. Lisensi (License)

Proyek **SafeGive (E-Donate Blockchain)** didistribusikan di bawah lisensi terbuka [MIT License](LICENSE). Anda bebas menggunakan, memodifikasi, dan mendistribusikan kode sumber ini untuk keperluan akademik maupun komersial dengan tetap menyertakan atribusi hak cipta asli.

---

<p align="center">
  Dikembangkan oleh NEO KERNEL.
</p>
