# Panduan Sistem SafeGive

Panduan resmi penggunaan platform **SafeGive** — platform donasi transparan berbasis **Kriptografi Blockchain (Enkripsi Homomorfik Paillier & Ledger Imutabel)** dengan metode transfer **Bank Lokal Indonesia & E-Wallet / QRIS**.

---

## Ringkasan Alur Sistem Baru

1. **Tanpa Dompet Web3 / Ekstensi Browser**: Donatur dan penyelenggara tidak lagi memerlukan ekstensi MetaMask/Rabby atau gas fee token testnet.
2. **Transfer Bank Indonesia & E-Wallet / QRIS**: Mendukung transfer dari semua bank umum di Indonesia (BCA, Bank Mandiri, BRI, BNI, Bank Syariah Indonesia/BSI) serta E-Wallet (QRIS, DANA, GoPay, OVO).
3. **Satu Rekening Pusat Admin**: Seluruh donatur hanya mentransfer donasi ke **1 (satu) rekening resmi platform** yang dikonfigurasi oleh Admin melalui panel **Pengaturan Rekening**.
4. **Logika Kriptografi Blockchain Tetap Dipertahankan**:
   - **Enkripsi Homomorfik Paillier**: Setiap nominal donasi dienkripsi menjadi ciphertext ($c \in \mathbb{Z}_{n^2}^*$). Total donasi kampanye dihitung melalui perkalian ciphertext $c_{total} = \prod c_i \pmod{n^2}$ tanpa membuka nominal masing-masing donatur.
   - **Transaction Hash Kriptografis**: Diterbitkan secara otomatis dengan format standar blockchain (`0x` + 64 karakter heksadesimal dari SHA-256).
   - **Blok Transaksi**: Setiap transaksi terikat pada nomor blok berurutan (*sequential block number*).
   - **Cryptographic Commitment**: Sidik kriptografis SHA-256 menjamin integritas data transaksi secara imutabel.
5. **Syarat Ketat Pencairan Dana (Withdrawal)**:
   Penyelenggara kampanye hanya dapat mengajukan pencairan dana apabila salah satu kondisi terpenuhi:
   - **Target donasi kampanye telah terpenuhi ($\ge 100\%$)**, ATAU
   - **Batas waktu kampanye (deadline/jatuh tempo) telah terlewati**.

---

## 1. Peran & Alur Penggunaan

### A. Alur Donatur
1. Buka menu **Eksplorasi Kampanye** (`/campaigns`) dan pilih kampanye yang ingin didukung.
2. Pada halaman detail kampanye, periksa kotak **Rekening Resmi SafeGive** (tercantum Nama Bank, Nomor Rekening, Nama Pemilik, dan QRIS jika tersedia).
3. Lakukan transfer dana melalui m-Banking (BCA, Mandiri, BRI, BNI, BSI) atau scan QRIS / E-Wallet.
4. Isi formulir donasi:
   - Nominal donasi (minimal Rp 1.000).
   - Nama donatur & nomor kontak WhatsApp.
   - Bank / E-Wallet asal transfer.
   - Unggah foto/screenshot bukti transfer (JPG, PNG, atau PDF).
5. Klik **Kirim Donasi & Unggah Bukti**.
6. Sistem backend:
   - Mengenkripsi nominal dengan Paillier Cryptosystem.
   - Menghitung commitment SHA-256.
   - Men-generate Transaction Hash `0x...` dan Nomor Blok.
   - Menyimpan donasi dengan status `pending`.
7. Pantau donasi di menu **Riwayat Transaksi** (`/transactions`). Setelah Admin memvalidasi bukti transfer, status berubah menjadi `confirmed` dan progres kampanye bertambah.

---

### B. Alur Penyelenggara Kampanye (Organizer)
1. Buat akun di halaman `/register`.
2. Buka menu **Profile**, pilih tipe pendaftar (**Individu** atau **Yayasan**), lengkapi data KTP/legalitas, dan unggah dokumen pendukung.
3. Tunggu Admin memverifikasi profil hingga berstatus `verified`.
4. Buka menu **Kampanye Saya** (`/my-campaigns`) dan klik **Buat Kampanye**.
5. Isi formulir kampanye:
   - Judul, kategori, deskripsi, foto sampul.
   - Target donasi (rupiah) dan batas waktu kampanye (*deadline*).
   - **Informasi Rekening Pencairan**: Nama Bank tujuan penyelenggara (BCA, Mandiri, BRI, dll.), nomor rekening, dan nama pemilik rekening.
6. Publikasikan kampanye.
7. Pantau donasi yang masuk melalui ledger transaksi kriptografi.
8. **Pencairan Dana**:
   - Jika donasi telah mencapai target 100% ATAU batas waktu kampanye telah lewat, tombol **Ajukan Pencairan Dana** akan aktif.
   - Penyelenggara mengirim permohonan pencairan dana.
   - Admin memproses transfer ke rekening penyelenggara dan mencatat hash pencairan.

---

### C. Alur Admin Platform
1. **Konfigurasi Rekening Pusat** (`/admin/bank-settings`):
   - Atur bank utama (misal: BCA / Mandiri / QRIS), nomor rekening, dan nama atas nama rekening platform.
   - Pengaturan ini langsung otomatis tampil pada seluruh halaman donasi.
2. **Verifikasi Penyelenggara** (`/admin/verifications`):
   - Periksa dokumen KTP atau SK Kemenkumham yayasan.
   - Setujui (*verify*) agar penyelenggara dapat membuat kampanye.
3. **Validasi Donasi Masuk** (`/admin/donations`):
   - Lihat daftar bukti transfer donatur dan kode referensi unik.
   - Klik **Konfirmasi Donasi**: Sistem menjalankan operasi penjumlahan homomorfik Paillier secara otomatis, memperbarui persentase progres kampanye, dan mengubah status menjadi `confirmed`.
   - Atau klik **Tolak Donasi** jika bukti tidak valid / mutasi tidak ditemukan.
4. **Penyaluran Pencairan Dana** (`/admin/withdrawals`):
   - Periksa pengajuan pencairan dari kampanye yang memenuhi syarat (target tercapai atau jatuh tempo lewat).
   - Transfer dana ke rekening bank penyelenggara.
   - Masukkan hash transaksi / unggah bukti transfer perbankan dan setujui pencairan.

---

## 2. Struktur Kriptografi & Blockchain Paillier

### Enkripsi Homomorfik Paillier
SafeGive menggunakan skema Paillier di mana ruang plaintext adalah $\mathbb{Z}_n$ dan ruang ciphertext adalah $\mathbb{Z}_{n^2}^*$:
- **Enkripsi**:
  $$c = g^m \cdot r^n \pmod{n^2}$$
- **Penjumlahan Homomorfik (Homomorphic Addition)**:
  $$D(c_1 \cdot c_2 \pmod{n^2}) = (m_1 + m_2) \pmod{n}$$
Dengan sifat ini, backend dapat mengakumulasi seluruh donasi yang berstatus `confirmed` untuk menghitung progres kampanye tanpa pernah membocorkan nominal donatur secara terbuka di database terbuka.

### Hash Transaksi & Blok Ledger
- Setiap donasi menghasilkan hash berformat:
  `0x` + 64 karakter heksadesimal (`hash('sha256', ...)`)
- Transaksi terikat pada nomor blok sequential yang terus bertambah.
- Rekam jejak immutable dapat diverifikasi di halaman **Riwayat Transaksi** dan **Ledger Transaksi Kriptografi Kampanye**.

---

## 3. Akun Pengujian Lokal

Gunakan akun bawaan seeder untuk pengujian lokal:

| Peran | Email | Password | Hak Akses |
|---|---|---|---|
| **Admin** | `admin@example.com` | `password` | Pengaturan Rekening, Validasi Donasi, Pencairan Dana, Verifikasi Pengguna |
| **Penyelenggara / Donatur** | `wahyu@example.com` | `password` | Membuat Kampanye, Donasi, Pencairan |

---

## 4. Perintah Berguna di Terminal

1. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

2. **Generate / Periksa Kunci Paillier**:
   ```bash
   php artisan paillier:keys
   ```

3. **Build Frontend**:
   ```bash
   npm run build
   ```

4. **Jalankan Development Server**:
   ```bash
   php artisan serve
   npm run dev
   ```

---

## 5. Pertanyaan Umum (FAQ)

**Q: Mengapa donatur hanya transfer ke rekening admin, bukan langsung ke penyelenggara?**  
A: Untuk menjamin keamanan donatur dari penipuan, memastikan verifikasi kebenaran mutasi dana sebelum dicatat ke ledger kriptografi, dan memastikan dana hanya disalurkan kepada penyelenggara yang sah ketika kampanye telah mencapai target atau telah berakhir.

**Q: Apakah donatur masih butuh gas fee / MATIC / crypto?**  
A: Tidak. Seluruh biaya transfer mengikuti tarif perbankan standar (BI-FAST Rp 2.500 atau gratis sesama bank/e-wallet), tanpa biaya gas kripto.

**Q: Kapan tombol pencairan dana muncul dan bisa diklik oleh penyelenggara?**  
A: Tombol pencairan dana akan otomatis aktif jika progres kampanye sudah mencapai minimal 100% ATAU tanggal batas waktu kampanye telah terlewati. Jika belum mencapai target dan batas waktu masih berlaku, sistem akan menampilkan status terkunci beserta hitung mundur batas waktu.

---

## 6. Panduan Integrasi Midtrans Payment Gateway (Mode Sandbox)

### A. Mendapatkan Kunci Sandbox Midtrans
1. Buka dan daftar akun di [Midtrans Sandbox Dashboard](https://dashboard.sandbox.midtrans.com/).
2. Masuk ke menu **Settings → Access Keys**.
3. Salin **Server Key** (diawali `SB-Mid-server-...`) dan **Client Key** (diawali `SB-Mid-client-...`).
4. Simpan ke SafeGive dengan salah satu cara berikut:
   - **Melalui Panel Admin**: Buka menu **Pengaturan Rekening** (`/admin/bank-settings`) → pilih tab **Midtrans Gateway**, masukkan Server Key & Client Key, lalu klik **Simpan Konfigurasi**.
   - **Melalui Berkas `.env`**:
     ```env
     MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXX
     MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXX
     MIDTRANS_IS_PRODUCTION=false
     ```

### B. Menguji Pembayaran di Mode Sandbox
1. Buka salah satu kampanye di **Eksplorasi Kampanye** (`/campaigns`).
2. Pada form donasi di sebelah kanan, pilih tab **⚡ Midtrans Snap**.
3. Pilih nominal donasi (misal: Rp 50.000), lengkapi nama donatur, email, dan no WhatsApp.
4. Klik **Bayar dengan Midtrans Snap**.
5. Pop-up resmi Midtrans Snap akan muncul dengan pilihan metode pembayaran:
   - **Bank Virtual Account (BCA, Mandiri, BNI, BRI, Permata)**
   - **QRIS Dinamis** (GoPay, ShopeePay)
   - **Kartu Kredit / Debit Online Testing**
6. Untuk menyelesaikan pembayaran pengujian:
   - **Virtual Account Simulator**: Buka [Simulator VA Midtrans](https://simulator.sandbox.midtrans.com/openapi/va/index), masukkan nomor VA yang tampil di Snap, lalu klik Bayar.
   - **Kartu Kredit Testing**: Gunakan Nomor Kartu `4811 1111 1111 1114`, Masa Berlaku sembarang di masa depan (cth: `12/28`), CVV `123`, dan OTP `112233`.
7. Setelah pembayaran berhasil di Midtrans:
   - Sistem secara otomatis menerima status `confirmed`.
   - Backend memicu fungsi **Penjumlahan Homomorfik Paillier** untuk mengakumulasi total donasi kampanye secara matematis tanpa mendekripsi nominal privat donatur.
   - Transaction Hash `0x...` dan nomor blok ledger dicatat secara permanen.

### C. Sinkronisasi Status di Lingkungan Lokal (Tanpa Ngrok)
Pada lingkungan pengujian `localhost`, webhook internet tidak dapat langsung mengakses komputer lokal Anda. SafeGive telah dilengkapi:
- Sinkronisasi otomatis begitu pop-up Snap menyelesaikan pembayaran (`onSuccess`).
- Tombol **Cek Midtrans** pada tabel donasi admin (`/admin/donations`) untuk melakukan pengecekan status langsung ke REST API Midtrans kapan saja dengan 1 klik.
