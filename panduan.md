# Panduan SafeGive

## Jawaban singkat: isi form atau MetaMask dulu?

### Alur aplikasi saat ini

Karena tombol donasi belum terhubung langsung ke MetaMask, gunakan urutan berikut:

1. Buka detail kampanye.
2. Isi **Nominal donasi (rupiah)**, misalnya `100000`.
3. Buka MetaMask/Rabby secara terpisah.
4. Lakukan dan setujui transaksi ke smart contract kampanye.
5. Salin **transaction hash** yang diberikan MetaMask/Rabby.
6. Kembali ke halaman SafeGive.
7. Masukkan transaction hash ke field **Transaction hash dari wallet**.
8. Isi nomor block jika sudah tersedia, atau biarkan kosong.
9. Klik **Catat transaksi**.

Jadi untuk kode saat ini:

```text
Isi nominal di SafeGive
→ Transfer/setujui di MetaMask/Rabby
→ Salin transaction hash
→ Masukkan hash ke SafeGive
→ Klik Catat transaksi
```

### Alur produksi yang diharapkan

Setelah integrasi Web3 selesai, user cukup:

```text
Isi nominal
→ Klik Donasi melalui wallet
→ MetaMask/Rabby terbuka otomatis
→ Setujui transaksi
→ Hash dikirim otomatis ke SafeGive
→ Status pending
→ Listener blockchain mengubah menjadi confirmed
```

Jangan melakukan transfer sebelum memeriksa alamat smart contract tujuan. Mengirim dana langsung ke alamat wallet biasa tidak otomatis menjadi donasi yang dapat diverifikasi oleh sistem.

## 1. Alur penggunaan

1. Daftar akun melalui halaman register.
2. Buka menu **Profile**.
3. Pilih jenis pendaftar: individu atau yayasan.
4. Kirim data verifikasi dan dokumen.
5. Tunggu admin memeriksa pengajuan.
6. Setelah status menjadi `verified`, menu **Kampanye Saya** dapat digunakan untuk membuat kampanye.
7. User lain membuka **Eksplorasi Kampanye**, memilih kampanye, lalu melakukan donasi.
8. Riwayat donasi dapat dilihat pada menu **Riwayat Transaksi**.

## 2. Wallet yang digunakan

Aplikasi ini disiapkan untuk jaringan **Polygon Amoy Testnet**. Alamat wallet kampanye adalah alamat publik milik penyelenggara yang menerima dana donasi.

Format alamat wallet EVM/Polygon:

```text
0x + 40 karakter heksadesimal
Contoh format: 0x1234567890abcdef1234567890abcdef12345678
```

Yang dimasukkan pada field **Alamat wallet kampanye** adalah alamat publik penerima, bukan:

- seed phrase,
- private key,
- password wallet,
- kode OTP,
- API key.

Jangan pernah memasukkan seed phrase atau private key ke SafeGive.

## 3. Cara membuat kampanye

1. Pastikan profil sudah diverifikasi admin.
2. Buka **Kampanye Saya**.
3. Klik **Buat kampanye**.
4. Isi judul, kategori, deskripsi, gambar, target, waktu mulai, batas waktu, dan alamat wallet.
5. Pastikan waktu mulai tidak berada di masa depan jika kampanye ingin langsung menerima donasi.
6. Publikasikan kampanye.

Kampanye hanya menerima donasi saat:

- status kampanye `active`,
- waktu sekarang berada di antara `starts_at` dan `ends_at`,
- target belum dinyatakan tercapai atau kampanye belum dicairkan.

## 4. Cara transaksi donasi

Integrasi wallet nyata menggunakan pola berikut:

1. User menghubungkan wallet Polygon Amoy.
2. User memilih jumlah donasi di UI.
3. Nilai donasi dienkripsi dengan Paillier di backend.
4. Wallet mengirim transaksi ke smart contract kampanye.
5. Smart contract menghasilkan transaction hash.
6. Aplikasi mengirim ciphertext, commitment, transaction hash, dan nomor block ke endpoint donasi.
7. Donasi pertama kali berstatus `pending`.
8. Listener blockchain memeriksa receipt transaksi.
9. Setelah receipt valid, status berubah menjadi `confirmed`.
10. Agregasi ciphertext kampanye diperbarui dan persentase progres dihitung dari hasil agregasi yang diotorisasi.

Pada form saat ini, user hanya perlu mengisi:

| Field pada formulir | Isi |
|---|---|
| **Nominal donasi (rupiah)** | Nominal biasa, misalnya `100000` untuk Rp100.000. Minimal Rp1.000. |
| **Transaction hash dari wallet** | Hash transaksi 66 karakter: `0x` + 64 karakter hexadecimal, diberikan wallet setelah transaksi dikirim. |
| **Nomor block (opsional)** | Nomor block dari receipt blockchain. Boleh dikosongkan jika transaksi masih menunggu konfirmasi. |

User tidak perlu mengisi ciphertext atau commitment. Backend membuat keduanya secara otomatis menggunakan Paillier sebelum data disimpan.

### Data yang dibuat oleh backend

| Field pada formulir | Isi yang benar | Sumber nilai |
|---|---|---|
| **Ciphertext nominal** | Nilai nominal Paillier terenkripsi. User tidak melihat atau mengisinya. | Dibuat backend dari nominal rupiah sebelum disimpan. |
| **Commitment nominal** | Sidik kriptografis untuk mengikat nilai donasi. User tidak melihat atau mengisinya. | Dibuat backend secara otomatis. |
| **Transaction hash** | Hash transaksi blockchain dengan format `0x` + 64 karakter hexadecimal. | Diberikan oleh wallet setelah transaksi berhasil dikirim ke smart contract. |
| **Nomor block (opsional)** | Nomor blok tempat transaksi tercatat, misalnya `19842109`. | Dibaca dari receipt blockchain atau block explorer setelah transaksi masuk blok. Boleh dikosongkan saat masih `pending`. |

### Contoh alur pengisian

User ingin berdonasi Rp100.000:

1. User memasukkan `100000` pada field nominal.
2. User mengirim transaksi melalui wallet dan menyalin transaction hash.
3. User memasukkan transaction hash ke form.
4. User memasukkan nomor block jika sudah tersedia.
5. User klik **Catat transaksi**.
6. Backend mengenkripsi nominal dengan Paillier, membuat commitment dari ciphertext, lalu menyimpan data donasi berstatus `pending`.
7. Sistem blockchain mengonfirmasi receipt dan mengubah status menjadi `confirmed`.

Saat integrasi wallet penuh selesai, transaction hash dan nomor block juga akan diisi otomatis. Untuk sekarang, user hanya perlu mengisi nominal, menyalin hash dari wallet, dan mengisi nomor block jika sudah ada.

## 5. Siapa yang mengonfirmasi transaksi?

Admin **tidak** mengonfirmasi keberhasilan transaksi donasi. Ada dua pemeriksaan yang berbeda:

| Pemeriksaan | Pelaksana | Tujuan |
|---|---|---|
| Verifikasi identitas penyelenggara | Admin SafeGive | Memastikan individu/yayasan boleh membuat kampanye. |
| Konfirmasi transaksi donasi | Blockchain node/listener backend | Memastikan transaction hash benar-benar sukses di jaringan. |

Admin hanya menerima atau menolak profil verifikasi. Admin tidak boleh mengubah donasi menjadi sukses hanya berdasarkan screenshot atau pengakuan user.

## 6. Bagaimana `pending` menjadi `confirmed`?

Alur yang benar:

1. Wallet mengirim transaksi ke smart contract kampanye.
2. Wallet menghasilkan transaction hash.
3. SafeGive menyimpan donasi dengan status `pending`.
4. Worker/listener backend memanggil RPC Polygon Amoy menggunakan transaction hash.
5. Backend membaca transaction receipt.
6. Jika receipt belum tersedia, status tetap `pending`.
7. Jika `status` receipt sukses dan event smart contract valid, backend mengubah status menjadi `confirmed`.
8. Jika receipt gagal atau event tidak valid, backend mengubah status menjadi `failed`.
9. Setelah `confirmed`, backend memperbarui agregasi ciphertext, jumlah donatur, dan persentase kampanye.

Contoh keputusan berdasarkan receipt:

```text
receipt belum ditemukan       -> pending
receipt.status = 1            -> confirmed
receipt.status = 0            -> failed
event smart contract invalid  -> failed
```

Pada kode saat ini, endpoint pencatatan donasi baru menyimpan status `pending`. Listener/worker blockchain belum dipasang, sehingga transaksi tidak akan berubah otomatis menjadi `confirmed` sebelum integrasi RPC dan smart contract ditambahkan.

### Jika transaction hash diisi asal-asalan

Transaction hash bukan kode yang boleh dibuat sendiri. Pada produksi, backend harus memeriksa hash tersebut ke RPC Polygon dan memastikan receipt, alamat contract, event donasi, jaringan, dan status transaksi benar.

Validasi format saja belum membuktikan pembayaran. Hash palsu yang kebetulan memiliki format benar tetap harus ditolak oleh listener karena receipt tidak ditemukan. Jika receipt ditemukan tetapi transaksi gagal atau tidak menuju smart contract kampanye, status harus menjadi `failed`, bukan `confirmed`.

Form SafeGive menerima hash dengan format berikut:

```text
0x + 64 karakter hexadecimal
Contoh testing format: 0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
```

Hash seperti `0xlocal-test-donation-001` hanya boleh digunakan pada dokumentasi lama dan tidak lagi valid untuk form. Untuk testing lokal gunakan hash hexadecimal unik, misalnya:

```text
0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1
```

Contoh tersebut tetap hanya membuat status `pending`; gunakan `php artisan donation:confirm ID_DONASI` hanya untuk simulasi lokal.

Jangan membuat endpoint publik yang menerima request `confirmed` dari browser. Perubahan status harus dilakukan oleh listener backend setelah memeriksa receipt langsung ke jaringan blockchain.

## 7. Apakah harus menggunakan wallet Web3?

Ya, untuk transaksi blockchain nyata user memerlukan wallet Web3, misalnya:

- MetaMask,
- Rabby Wallet,
- atau wallet EVM lain yang mendukung Polygon.

Wallet diperlukan untuk:

- menyimpan private key user secara lokal/aman,
- menandatangani transaksi,
- membayar gas fee,
- menghasilkan transaction hash.

SafeGive tidak boleh meminta private key atau seed phrase. Integrasi frontend biasanya memakai `window.ethereum` atau library wallet seperti `wagmi`/`viem`.

Untuk Polygon Amoy, user perlu:

1. Menambahkan network Polygon Amoy ke wallet.
2. Mendapatkan token testnet untuk gas fee dari faucet resmi.
3. Menghubungkan wallet ke SafeGive.
4. Menekan tombol donasi.
5. Menyetujui transaksi pada popup wallet.

Tanpa wallet Web3 dan smart contract yang sudah ter-deploy, aplikasi hanya dapat mencatat metadata transaksi sebagai prototipe; aplikasi belum dapat membuktikan pembayaran on-chain.

### Hal yang tidak boleh dimasukkan

- Jangan membuat transaction hash sendiri.
- Jangan memasukkan alamat wallet ke field ciphertext atau commitment.
- Jangan memasukkan private key, seed phrase, password wallet, atau OTP ke formulir maupun chat.

Nominal plaintext tidak disimpan sebagai nilai donasi di database.

## 8. Key Paillier

Keypair Paillier dibuat dan disimpan di disk private aplikasi. Private key tidak boleh masuk repository, frontend, log, atau response API.

Untuk membuat keypair secara eksplisit:

```bash
php artisan paillier:keys
```

Keypair yang sudah ada tidak akan ditimpa. Rotasi key harus dilakukan dengan sengaja karena ciphertext lama hanya dapat didekripsi menggunakan key lama:

```bash
php artisan paillier:keys --force
```

Operasi penjumlahan ciphertext dilakukan dengan perkalian ciphertext modulo $n^2$. Dengan demikian backend dapat menghitung total donasi tanpa membuka nominal setiap donatur. Implementasi produksi sebaiknya memakai key minimal 2048-bit dan key management terpisah.

## 9. Status transaksi

| Status | Arti |
|---|---|
| `pending` | Data transaksi sudah dikirim tetapi receipt blockchain belum dikonfirmasi |
| `confirmed` | Transaksi sudah terkonfirmasi di blockchain |
| `failed` | Transaksi gagal atau receipt tidak valid |

Hash transaksi dapat dibuka melalui block explorer Polygon Amoy:

```text
https://amoy.polygonscan.com/tx/{transaction_hash}
```

## 10. Riwayat transaksi

Menu **Riwayat Transaksi** menampilkan transaksi yang dikirim oleh akun yang sedang login, termasuk:

- nama kampanye,
- potongan transaction hash,
- nomor block,
- waktu pengiriman,
- status transaksi,
- tautan block explorer.

Riwayat tidak menampilkan nominal plaintext agar privasi donatur tetap terjaga.

## 11. Mengapa kampanye tidak menerima donasi?

Pesan pada halaman detail kampanye akan menjelaskan penyebabnya. Penyebab umum:

- status kampanye bukan `active`,
- waktu mulai belum tercapai,
- batas waktu sudah lewat,
- jadwal kampanye belum lengkap,
- kampanye sudah mencapai target atau sudah dicairkan.

Pastikan timezone aplikasi menggunakan `Asia/Jakarta` dan waktu mulai/batas waktu kampanye benar.

## 12. Catatan integrasi blockchain

Fitur smart contract, koneksi wallet, listener receipt, dan pembaruan agregasi on-chain harus dijalankan pada service blockchain terpisah. Enkripsi nominal Paillier sudah dilakukan oleh backend SafeGive; backend tidak boleh meminta private key wallet pengguna.

## 13. Testing lengkap di komputer lokal

### A. Peralatan yang dibutuhkan

| Kebutuhan | Digunakan untuk | Cara mendapatkannya |
|---|---|---|
| Browser Chrome/Firefox/Edge | Membuka SafeGive | Instal dari situs browser resmi. |
| PHP, Composer, Node.js | Menjalankan Laravel dan frontend | Sudah menjadi kebutuhan project. |
| MySQL | Menyimpan user, kampanye, dan donasi | Jalankan MySQL dari Laragon/XAMPP. |
| MetaMask atau Rabby | Menandatangani transaksi Web3 | Instal extension wallet resmi. |
| Polygon Amoy | Jaringan testnet | Tambahkan network Polygon Amoy ke wallet. |
| Faucet Polygon Amoy | Mendapatkan token testnet untuk gas | Gunakan faucet Polygon Amoy resmi. |
| PolygonScan Amoy | Melihat transaction hash dan block | Buka `https://amoy.polygonscan.com`. |

### B. Menjalankan aplikasi

Di terminal project:

```bash
php artisan migrate
php artisan db:seed
php artisan paillier:keys
php artisan optimize:clear
```

Jalankan server:

```bash
php artisan serve
npm run dev
```

Buka `http://localhost:8000`.

Akun testing dari seeder:

```text
Admin: admin@example.com / password
User:  wahyu@example.com / password
```

### C. Menguji verifikasi penyelenggara

1. Login sebagai user.
2. Buka **Profile**.
3. Pilih **Individu** atau **Yayasan**.
4. Isi seluruh data dan unggah dokumen.
5. Login sebagai admin pada browser lain.
6. Buka `/admin/verifications`.
7. Klik **Lihat detail**.
8. Download dokumen untuk pemeriksaan.
9. Klik **Terima**.
10. Login kembali sebagai user dan buka **Kampanye Saya**.

Jika ditolak, admin harus mengisi catatan. User dapat memperbaiki data dan mengirim ulang.

### D. Menguji pembuatan kampanye

1. User yang sudah `verified` membuka `/my-campaigns`.
2. Klik **Buat kampanye**.
3. Isi contoh berikut:

```text
Judul: Bantuan Pendidikan Testnet
Kategori: Pendidikan
Target: 1000000
Mulai: waktu sekarang atau beberapa menit sebelumnya
Batas waktu: satu hari ke depan
Alamat wallet: alamat publik 0x... milik penyelenggara
```

4. Klik **Publikasikan kampanye**.
5. Kampanye tampil di `/campaigns`.
6. Buka detail kampanye dan pastikan pesan menunjukkan donasi sedang dibuka.

Jika muncul “belum menerima donasi”, periksa waktu mulai, batas waktu, status `active`, dan timezone `Asia/Jakarta`.

### E. Menguji pencatatan donasi dan Paillier

Pada mode kode saat ini, form belum mengirim transaksi ke smart contract secara otomatis. Untuk menguji penyimpanan aplikasi:

1. Buka detail kampanye.
2. Masukkan nominal biasa, misalnya `100000`.
3. Karena listener blockchain belum terpasang, gunakan hash testing unik, misalnya:

```text
0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa2
```

4. Nomor block boleh dikosongkan.
5. Klik **Catat transaksi**.
6. Pastikan loading menampilkan validasi nominal, enkripsi backend, pembuatan commitment, dan pencatatan transaksi.
7. Pastikan riwayat menampilkan transaksi dengan status `pending`.

Nominal `100000` dienkripsi backend dengan Paillier. Field ciphertext dan commitment tidak perlu diisi user.

### F. Mengubah `pending` menjadi `confirmed` untuk testing lokal

Pastikan sudah ada donasi. Jika tabel donasi masih kosong, lakukan dulu:

1. Login sebagai user.
2. Buka **Eksplorasi Kampanye**.
3. Buka detail kampanye yang sedang aktif.
4. Isi nominal, misalnya `100000`.
5. Untuk testing lokal, isi hash unik, misalnya `0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa3`.
6. Klik **Catat transaksi**.

Setelah donasi tercatat sebagai `pending`, tampilkan ID donasi dengan command:

```bash
php artisan donation:list
```

Contoh hasil:

```text
ID | Kampanye                 | Status  | Transaction hash
5  | Bantuan Pendidikan       | pending | 0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa3
```

Gunakan angka pada kolom **ID**, bukan nomor contoh yang belum tentu ada. Kemudian jalankan:

```bash
php artisan donation:confirm ID_DONASI
```

Contoh:

```bash
php artisan donation:confirm 5
```

Jika muncul `Donasi #1 tidak ditemukan`, artinya ID `1` belum ada. Jalankan `php artisan donation:list` dan gunakan ID yang tercantum.

Command ini hanya berjalan jika `.env` berisi:

```text
APP_ENV=local
```

Command akan:

1. Mengubah status donasi menjadi `confirmed`.
2. Mengisi `confirmed_at`.
3. Menggabungkan ciphertext semua donasi terkonfirmasi dengan operasi Paillier.
4. Menghitung ulang persentase kampanye.
5. Menghitung ulang jumlah donatur.
6. Mengubah kampanye menjadi `goal_reached` jika target tercapai.

Ini hanya simulasi listener blockchain untuk testing lokal. Jangan menjalankannya sebagai mekanisme konfirmasi produksi.

### G. Panduan admin di UI

Login dengan akun ber-role `admin`, lalu buka menu **Panduan**. Bagian **Testing verifikasi dan konfirmasi lokal** hanya tampil untuk admin dan berisi:

1. Cara membuka panel `/admin/verifications`.
2. Cara menerima atau menolak pengajuan verifikasi.
3. Cara mendapatkan ID donasi dari tabel `donations`.
4. Cara menjalankan `php artisan donation:confirm ID_DONASI`.
5. Cara memeriksa perubahan status dan progres agregasi Paillier.

User biasa tidak melihat command tersebut di UI. Command hanya boleh digunakan untuk testing lokal dengan `APP_ENV=local`.

### H. Testing transaksi blockchain nyata

Untuk status confirmed yang benar-benar berasal dari blockchain, flow-nya berbeda:

1. Deploy smart contract kampanye ke Polygon Amoy.
2. Simpan alamat contract dan campaign ID pada konfigurasi/database.
3. Hubungkan MetaMask/Rabby ke Polygon Amoy.
4. Pastikan wallet memiliki token Amoy untuk gas.
5. Klik tombol donasi yang memanggil smart contract.
6. Setujui transaksi pada popup wallet.
7. Salin transaction hash dari wallet.
8. Buka `https://amoy.polygonscan.com/tx/HASH_TRANSAKSI`.
9. Pastikan transaksi memiliki status **Success**.
10. Listener backend mengambil receipt menggunakan RPC Polygon Amoy.
11. Listener memverifikasi `from`, `to`, campaign ID, nominal/event, dan status receipt.
12. Listener baru boleh mengubah record menjadi `confirmed`.

Saat ini bagian deploy contract, RPC listener, dan tombol wallet otomatis belum tersedia di repository ini. Karena itu, transaction hash sembarang hanya menguji form aplikasi dan tidak membuktikan pembayaran blockchain.

### I. Cara pencairan dana oleh penyelenggara

Pencairan produksi harus dilakukan oleh smart contract, bukan dengan mengubah status database secara manual:

1. Kampanye mencapai target atau melewati batas waktu.
2. Smart contract memeriksa syarat pencairan.
3. Penyelenggara membuka detail kampanye dan klik **Cairkan dana**.
4. Wallet penyelenggara menandatangani transaksi withdrawal.
5. Smart contract mengirim dana ke wallet payout yang terdaftar.
6. Backend menyimpan transaction hash withdrawal.
7. Listener memverifikasi receipt withdrawal.
8. Status kampanye menjadi `withdrawn` setelah receipt sukses.
9. Laporan kampanye menampilkan hash pencairan.

Data yang harus disiapkan:

| Data | Sumber | Dimasukkan ke |
|---|---|---|
| Wallet payout | Wallet penyelenggara | Data kampanye/smart contract |
| Campaign ID | Smart contract | Database dan event transaksi |
| Target kampanye | Form pembuatan kampanye | Database dan smart contract |
| Batas waktu | Form pembuatan kampanye | Database dan smart contract |
| Withdrawal hash | Wallet setelah klik Cairkan | Laporan kampanye |

Smart contract withdrawal produksi dan listener RPC belum tersedia di repository. Tombol dan route pencatatan withdrawal sudah tersedia, tetapi pencairan nyata baru dapat terjadi setelah contract dideploy dan konfigurasi Web3 diisi.

Pembaruan integrasi Web3 menyediakan tombol **Donasi melalui MetaMask/Rabby** dan **Cairkan dana**. Tombol tersebut membutuhkan konfigurasi contract address dan `blockchain_campaign_id`. Tanpa dua data itu, tombol tetap menolak transaksi agar tidak mengirim dana ke alamat yang salah.

Konfigurasi `.env`:

```env
WEB3_RPC_URL=https://rpc-amoy.polygon.technology
WEB3_CHAIN_ID=80002
WEB3_CAMPAIGN_CONTRACT_ADDRESS=0xALAMAT_SMART_CONTRACT
VITE_WEB3_CHAIN_ID=80002
VITE_WEB3_CAMPAIGN_CONTRACT_ADDRESS=0xALAMAT_SMART_CONTRACT
VITE_MATIC_IDR_RATE=20000000
```

Setelah mengubah `.env`, jalankan:

```bash
php artisan optimize:clear
npm run build
```

`VITE_MATIC_IDR_RATE` adalah nilai konversi demo IDR ke MATIC untuk testnet. Untuk produksi, gunakan price oracle, bukan angka tetap.

Syarat tombol **Cairkan dana** aktif:

- user adalah pemilik kampanye,
- target tercapai atau batas waktu sudah lewat,
- status withdrawal masih `not_ready`,
- smart contract address tersedia,
- `blockchain_campaign_id` tersedia.

Saat tombol ditekan, MetaMask/Rabby memanggil fungsi `withdraw(campaignId)`. Hash withdrawal dikirim ke backend dan status menjadi `pending`. Listener RPC produksi kemudian harus memeriksa receipt; hanya receipt sukses yang boleh mengubah status menjadi `confirmed` dan kampanye menjadi `withdrawn`.

### J. Contoh isian form

#### Form kampanye

Dibuka dari **Kampanye Saya → Buat kampanye**:

| Field | Contoh isi | Diambil dari |
|---|---|---|
| Judul kampanye | `Bantuan Pendidikan Testnet` | Dibuat penyelenggara |
| Kategori | `Pendidikan` | Dipilih/ditulis penyelenggara |
| Target | `1000000` | Kebutuhan dana dalam rupiah |
| Mulai | `15 September 2026, 10:00` | Jadwal penyelenggara |
| Batas waktu | `22 September 2026, 10:00` | Jadwal penyelenggara |
| Alamat wallet | `0x1234...5678` | Tombol Copy Address dari MetaMask/Rabby |

#### Form donasi

Dibuka dari **Eksplorasi Kampanye → Lihat detail dan donasi**:

| Field | Contoh isi | Diambil dari |
|---|---|---|
| Nominal donasi | `100000` | Nominal yang ingin diberikan, dalam rupiah |
| Transaction hash | `0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa4` | Popup wallet setelah transaksi disetujui |
| Nomor block | `19842109` | PolygonScan Amoy; boleh kosong jika belum tersedia |

#### Form pencairan produksi

| Data | Contoh isi | Diambil dari |
|---|---|---|
| Campaign ID | ID dari smart contract | Detail smart contract/kampanye |
| Wallet payout | `0x1234...5678` | Alamat publik wallet penyelenggara |
| Jumlah pencairan | Saldo yang diizinkan contract | Dihitung smart contract |
| Withdrawal hash | `0xwithdraw...` | Popup wallet setelah withdrawal disetujui |

### K. Transfer dulu atau isi aplikasi dulu?

#### Transaksi blockchain nyata

Urutan yang benar:

1. Isi nominal donasi atau klik tombol donasi di aplikasi.
2. Wallet membuka popup dan menampilkan detail transaksi.
3. Periksa network, alamat contract, dan gas fee.
4. Setujui transaksi di MetaMask/Rabby.
5. Salin transaction hash dari popup wallet.
6. Catat hash di aplikasi jika belum diisi otomatis.
7. Buka hash di PolygonScan Amoy.
8. Tunggu status receipt `Success`.
9. Listener backend mengubah status dari `pending` menjadi `confirmed`.

Jadi, user **tidak perlu transfer manual sebelum membuka form**. Urutan praktisnya adalah:

```text
Isi nominal di aplikasi
	↓
Klik tombol donasi/kirim
	↓
Setujui transfer pada MetaMask/Rabby
	↓
Salin transaction hash dari wallet
	↓
Masukkan hash ke aplikasi jika belum otomatis
	↓
Kirim pencatatan transaksi
```

Nominal diisi lebih dulu karena aplikasi perlu mengetahui jumlah yang ingin dienkripsi. Transaction hash belum tersedia sebelum wallet mengirim dan menerima transaksi.

#### Testing lokal saat ini

Karena smart contract, RPC listener, dan koneksi wallet otomatis belum tersedia, gunakan urutan berikut:

1. Isi nominal `100000` pada detail kampanye.
2. Isi hash testing unik, misalnya `0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa5`.
3. Klik **Catat transaksi**.
4. Pastikan status menjadi `pending`.
5. Jalankan `php artisan donation:confirm ID_DONASI`.
6. Refresh halaman riwayat dan detail kampanye.
7. Pastikan status menjadi `confirmed` dan progres berubah.

Command tersebut hanya simulasi listener untuk environment lokal, bukan konfirmasi pembayaran blockchain.

### L. Checklist hasil testing

- [ ] User baru tidak dapat membuat kampanye sebelum verified.
- [ ] Admin dapat menerima/menolak verifikasi.
- [ ] Kampanye verified tampil di Eksplorasi Kampanye.
- [ ] Nominal donasi diisi sebagai rupiah biasa.
- [ ] Ciphertext Paillier tersimpan di database.
- [ ] Donasi awal berstatus `pending`.
- [ ] `php artisan donation:confirm ID` mengubah status menjadi `confirmed` pada lokal.
- [ ] Progres kampanye berubah setelah agregasi ciphertext.
- [ ] Riwayat transaksi menampilkan donasi user.
- [ ] Private key tidak tampil di browser atau response.
- [ ] Pencairan produksi menunggu smart contract dan listener RPC.
