# Panduan SafeGive

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
| **Transaction hash dari wallet** | Hash transaksi yang diberikan wallet setelah transaksi dikirim, biasanya diawali `0x`. |
| **Nomor block (opsional)** | Nomor block dari receipt blockchain. Boleh dikosongkan jika transaksi masih menunggu konfirmasi. |

User tidak perlu mengisi ciphertext atau commitment. Backend membuat keduanya secara otomatis menggunakan Paillier sebelum data disimpan.

### Data yang dibuat oleh backend

| Field pada formulir | Isi yang benar | Sumber nilai |
|---|---|---|
| **Ciphertext nominal** | Nilai nominal Paillier terenkripsi. User tidak melihat atau mengisinya. | Dibuat backend dari nominal rupiah sebelum disimpan. |
| **Commitment nominal** | Sidik kriptografis untuk mengikat nilai donasi. User tidak melihat atau mengisinya. | Dibuat backend secara otomatis. |
| **Transaction hash** | Hash transaksi blockchain dengan format umumnya diawali `0x`, misalnya `0xabc123...`. | Diberikan oleh wallet setelah transaksi berhasil dikirim ke smart contract. |
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
