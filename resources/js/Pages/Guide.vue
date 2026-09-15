<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { IconArrowRight, IconCheck, IconChevronRight, IconClock, IconCoins, IconFileCheck, IconLock, IconWallet } from '@tabler/icons-vue';

type Audience = 'organizer' | 'donor';
const activeAudience = ref<Audience>('organizer');
const page = usePage<{ auth: { is_admin: boolean } }>();
const isAdmin = computed(() => page.props.auth?.is_admin === true);

const steps = [
    { number: '01', title: 'Daftar akun', text: 'Buat akun dengan nama, email, dan password.', source: 'Halaman /register', next: 'Masuk ke Profile untuk verifikasi.' },
    { number: '02', title: 'Verifikasi identitas', text: 'Pilih Individu atau Yayasan, lengkapi data, lalu unggah dokumen.', source: 'Menu Profile', next: 'Tunggu admin menerima pengajuan.' },
    { number: '03', title: 'Admin memeriksa', text: 'Admin mencocokkan data dan dokumen. Status dapat pending, verified, atau rejected.', source: 'Panel admin /admin/verifications', next: 'Jika verified, menu Kampanye Saya terbuka.' },
];

const organizerSteps = [
    { title: 'Buka Kampanye Saya', text: 'Pilih Kampanye Saya, bukan Eksplorasi Kampanye. Halaman ini hanya berisi kampanye milik Anda.', source: 'Menu Kampanye Saya atau /my-campaigns', input: 'Tidak ada input. Pastikan status verifikasi sudah verified.', next: 'Klik Buat kampanye.' },
    { title: 'Isi data kampanye', text: 'Lengkapi judul, kategori, deskripsi, gambar, target rupiah, waktu mulai, batas waktu, dan alamat wallet publik.', source: 'Form /campaigns/create', input: 'Judul, kategori, deskripsi, gambar, target, tanggal mulai, batas waktu.', next: 'Periksa jadwal dan alamat wallet sebelum publikasi.' },
    { title: 'Masukkan wallet penerima', text: 'Gunakan alamat publik EVM penyelenggara. Alamat ini diawali 0x dan memiliki 40 karakter heksadesimal setelahnya.', source: 'MetaMask/Rabby milik penyelenggara', input: 'Alamat public wallet, bukan private key atau seed phrase.', next: 'Klik Publikasikan kampanye.' },
    { title: 'Pantau donasi', text: 'Lihat persentase terenkripsi, jumlah donatur, transaction hash, block, dan status transaksi.', source: 'Detail kampanye /campaigns/{id}', input: 'Tidak ada input. Data berasal dari transaksi dan listener blockchain.', next: 'Tunggu target tercapai atau batas waktu berakhir.' },
    { title: 'Ajukan pencairan', text: 'Setelah target tercapai atau batas waktu lewat, pencairan dilakukan melalui fungsi withdrawal smart contract.', source: 'Smart contract dan wallet penyelenggara', input: 'Wallet payout dan tanda tangan transaksi withdrawal.', next: 'Tunggu receipt withdrawal berstatus Success.' },
];

const donorSteps = [
    { title: 'Eksplorasi kampanye', text: 'Buka daftar kampanye publik milik semua penyelenggara dan pilih kampanye yang ingin didukung.', source: 'Menu Eksplorasi Kampanye atau /campaigns', input: 'Tidak ada input. Pilih kartu kampanye.', next: 'Klik Lihat detail dan donasi.' },
    { title: 'Hubungkan wallet Web3', text: 'Gunakan MetaMask, Rabby, atau wallet EVM lain di jaringan Polygon Amoy.', source: 'Extension MetaMask/Rabby', input: 'Network Polygon Amoy dan token testnet untuk gas.', next: 'Kembali ke halaman detail kampanye.' },
    { title: 'Masukkan nominal biasa', text: 'Masukkan nominal rupiah, misalnya 100000 untuk Rp100.000. Backend mengenkripsi nominal dengan Paillier.', source: 'Form donasi di detail kampanye', input: 'Field Nominal donasi (rupiah). Jangan isi ciphertext.', next: 'Kirim transaksi dan setujui popup wallet.' },
    { title: 'Masukkan hash transaksi', text: 'Setelah wallet menyetujui transaksi, salin hash transaksi. Nomor block boleh diisi setelah transaksi masuk blok.', source: 'Popup wallet dan PolygonScan Amoy', input: 'Transaction hash diawali 0x; nomor block opsional.', next: 'Klik Catat transaksi pada mode prototipe.' },
    { title: 'Cek status dan riwayat', text: 'Transaksi awalnya pending, kemudian menjadi confirmed setelah listener memeriksa receipt blockchain.', source: 'Menu Riwayat Transaksi atau /transactions', input: 'Tidak ada input. Buka hash untuk melihat receipt.', next: 'Pastikan PolygonScan menunjukkan Success.' },
];

const adminSteps = [
    { title: 'Login sebagai admin', text: 'Gunakan akun yang memiliki role admin. User biasa tidak melihat bagian testing ini di halaman panduan.', source: 'Akun admin SafeGive', input: 'Email dan password admin.', next: 'Buka menu Verifikasi Pengguna.' },
    { title: 'Terima verifikasi penyelenggara', text: 'Periksa dokumen pada daftar pengajuan, lalu klik Terima atau Tolak dengan catatan.', source: 'Panel /admin/verifications', input: 'Status dan catatan review.', next: 'Penyelenggara verified dapat membuat kampanye.' },
    { title: 'Cari ID donasi', text: 'Buat donasi testing dari akun user, lalu ambil ID record donasi dari database atau detail data aplikasi.', source: 'Tabel donations di database', input: 'Angka ID donasi, contoh 1.', next: 'Pastikan status awalnya pending.' },
    { title: 'Konfirmasi testing lokal', text: 'Jalankan command pada terminal project untuk mensimulasikan listener blockchain. Command ini tidak boleh dipakai untuk produksi.', source: 'Terminal pada root project', input: 'php artisan donation:confirm ID_DONASI', next: 'Refresh riwayat dan detail kampanye.' },
    { title: 'Periksa hasil agregasi', text: 'Command mengubah status menjadi confirmed, mengisi confirmed_at, menggabungkan ciphertext Paillier, dan menghitung ulang progres.', source: 'Detail kampanye dan Riwayat Transaksi', input: 'Tidak ada input tambahan.', next: 'Untuk produksi, ganti command dengan listener RPC receipt.' },
];

const web3AdminSteps = [
    { title: 'Siapkan MetaMask admin', text: 'Instal MetaMask atau Rabby, lalu tambahkan Polygon Amoy. Gunakan wallet khusus testing dan jangan masukkan private key ke SafeGive.', source: 'MetaMask/Rabby', input: 'Network Polygon Amoy, Chain ID 80002, token POL testnet.', next: 'Buka Remix IDE.' },
    { title: 'Buka Remix IDE', text: 'Remix digunakan untuk compile dan deploy smart contract ke Polygon Amoy.', source: 'https://remix.ethereum.org', input: 'File Solidity contract SafeGive.', next: 'Buka Solidity Compiler.' },
    { title: 'Compile contract', text: 'Pilih compiler sesuai versi Solidity contract, lalu klik Compile. Pastikan tidak ada error.', source: 'Menu Solidity Compiler di Remix', input: 'Versi compiler dan file contract.', next: 'Buka Deploy & Run Transactions.' },
    { title: 'Pilih Injected Provider', text: 'Hubungkan Remix dengan wallet browser agar deploy dilakukan ke Polygon Amoy, bukan Remix VM.', source: 'Deploy & Run Transactions', input: 'Environment: Injected Provider - MetaMask.', next: 'Pastikan Chain ID terbaca 80002.' },
    { title: 'Deploy smart contract', text: 'Klik Deploy dan setujui transaksi deploy di MetaMask. Tunggu transaksi selesai.', source: 'Remix dan popup MetaMask', input: 'Constructor contract jika diperlukan.', next: 'Salin alamat Deployed Contract.' },
    { title: 'Isi alamat contract', text: 'Salin alamat Deployed Contract dari Remix, bukan transaction hash dan bukan alamat wallet.', source: 'Remix: Deployed Contracts', input: 'Alamat 0x... contract yang sama pada dua variable Vite/backend.', next: 'Simpan .env dan rebuild frontend.' },
    { title: 'Refresh konfigurasi', text: 'Laravel membaca RPC dari backend, sedangkan Vite membaca alamat contract yang diawali VITE_.', source: 'Terminal root project', input: 'php artisan optimize:clear lalu npm run build.', next: 'Restart server dan buka aplikasi.' },
    { title: 'Uji donasi dan pencairan', text: 'Buat campaign dengan Campaign ID dari contract. Uji tombol donasi, lihat hash di PolygonScan, lalu uji Cairkan setelah target/deadline terpenuhi.', source: 'SafeGive, MetaMask, PolygonScan Amoy', input: 'Campaign ID smart contract dan wallet payout.', next: 'Pastikan receipt transaksi berstatus Success.' },
];

const formExamples = {
    organizer: [
        ['Judul kampanye', 'Bantuan Pendidikan Testnet'],
        ['Kategori', 'Pendidikan'],
        ['Target (rupiah)', '1000000'],
        ['Mulai', '15 September 2026, 10:00'],
        ['Batas waktu', '22 September 2026, 10:00'],
        ['Alamat wallet', '0x1234...5678 dari MetaMask/Rabby'],
    ],
    donor: [
        ['Nominal donasi (rupiah)', '100000'],
        ['Transaction hash', '0xaaaaaaaa...aaaaaaaa dari popup wallet'],
        ['Nomor block', '19842109 dari PolygonScan, opsional'],
    ],
    payout: [
        ['Campaign ID', 'Dibaca dari smart contract/detail kampanye'],
        ['Wallet payout', 'Alamat wallet publik penyelenggara'],
        ['Jumlah pencairan', 'Dihitung smart contract dari saldo tersedia'],
        ['Withdrawal hash', 'Dihasilkan wallet setelah menyetujui pencairan'],
    ],
};

const formatSource = (source: string) => source;
</script>

<template>
    <AppLayout>
        <Head title="Panduan SafeGive" />
        <div class="space-y-8">
            <header class="max-w-4xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Pusat bantuan</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Panduan SafeGive dari awal sampai akhir</h1>
                <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">Ikuti alur sesuai peran Anda. Penyelenggara membuat kampanye dan mengajukan pencairan; donatur memilih kampanye dan mengirim transaksi melalui wallet Web3.</p>
            </header>

            <section class="grid gap-4 md:grid-cols-3">
                <div v-for="step in steps" :key="step.number" class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><span class="text-xs font-black text-emerald-700">{{ step.number }}</span><h2 class="mt-3 text-base font-extrabold text-slate-900">{{ step.title }}</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">{{ step.text }}</p><p class="mt-4 text-xs font-bold text-slate-400">Sumber: <span class="font-medium text-slate-600">{{ step.source }}</span></p><p class="mt-1 text-xs font-bold text-slate-400">Berikutnya: <span class="font-medium text-slate-600">{{ step.next }}</span></p></div>
            </section>

            <section class="rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                <div class="flex flex-wrap gap-2 border-b border-slate-100 p-4 sm:p-5"><button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition" :class="activeAudience === 'organizer' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'" @click="activeAudience = 'organizer'"><IconFileCheck class="h-4 w-4" />Penyelenggara Kampanye</button><button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition" :class="activeAudience === 'donor' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'" @click="activeAudience = 'donor'"><IconCoins class="h-4 w-4" />Donatur</button></div>
                <div class="p-5 sm:p-8">
                    <div v-if="activeAudience === 'organizer'" class="mb-7 rounded-2xl bg-emerald-50 p-5"><h2 class="text-xl font-black text-emerald-950">Alur penyelenggara</h2><p class="mt-2 text-sm leading-relaxed text-emerald-900">Anda hanya dapat membuat kampanye setelah admin menerima verifikasi. Dana dicairkan melalui aturan smart contract, bukan keputusan manual berdasarkan screenshot.</p></div>
                    <div v-else class="mb-7 rounded-2xl bg-sky-50 p-5"><h2 class="text-xl font-black text-sky-950">Alur donatur</h2><p class="mt-2 text-sm leading-relaxed text-sky-900">Nominal yang Anda masukkan adalah nominal rupiah biasa. Backend membuat ciphertext Paillier; wallet digunakan untuk tanda tangan transaksi blockchain.</p></div>
                    <div class="space-y-4"><article v-for="(step, index) in activeAudience === 'organizer' ? organizerSteps : donorSteps" :key="step.title" class="grid gap-4 rounded-2xl border border-slate-100 p-4 sm:grid-cols-[40px_minmax(0,1fr)_minmax(220px,0.7fr)] sm:items-start"><div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#edf4e9] text-sm font-black text-emerald-700">{{ index + 1 }}</div><div><h3 class="font-extrabold text-slate-900">{{ step.title }}</h3><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ step.text }}</p></div><div class="space-y-2 text-xs"><div class="rounded-lg bg-slate-50 p-2.5"><p class="font-bold uppercase tracking-wide text-slate-400">Buka / sumber data</p><p class="mt-1 font-semibold text-slate-700">{{ formatSource(step.source) }}</p></div><div class="rounded-lg bg-emerald-50 p-2.5"><p class="font-bold uppercase tracking-wide text-emerald-700">Yang di-input</p><p class="mt-1 font-semibold text-emerald-900">{{ step.input }}</p></div><div class="rounded-lg bg-sky-50 p-2.5"><p class="font-bold uppercase tracking-wide text-sky-700">Langkah selanjutnya</p><p class="mt-1 font-semibold text-sky-900">{{ step.next }}</p></div></div></article></div>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-3">
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><IconWallet class="h-6 w-6 text-emerald-700" /><h2 class="mt-3 font-extrabold text-slate-900">Wallet dan transaksi</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Gunakan MetaMask/Rabby di Polygon Amoy. Alamat wallet kampanye adalah alamat publik `0x...`, bukan private key.</p><Link href="/campaigns" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Lihat kampanye <IconArrowRight class="h-4 w-4" /></Link></div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><IconClock class="h-6 w-6 text-amber-600" /><h2 class="mt-3 font-extrabold text-slate-900">Arti status pending</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Pending berarti data sudah dicatat tetapi receipt blockchain belum diperiksa listener. Confirmed hanya boleh diberikan setelah receipt sukses.</p><Link href="/transactions" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Buka riwayat <IconArrowRight class="h-4 w-4" /></Link></div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><IconLock class="h-6 w-6 text-emerald-700" /><h2 class="mt-3 font-extrabold text-slate-900">Privasi nominal</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Nominal plaintext tidak disimpan. Backend mengenkripsi dengan Paillier dan dapat mengagregasi ciphertext tanpa membuka nominal setiap donatur.</p><Link href="/profile" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Cek profil <IconArrowRight class="h-4 w-4" /></Link></div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-2"><IconCoins class="h-5 w-5 text-emerald-700" /><h2 class="text-lg font-extrabold text-slate-900">Contoh form kampanye</h2></div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Contoh data yang diisi penyelenggara pada menu <strong>Kampanye Saya → Buat kampanye</strong>.</p>
                    <dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">
                        <div v-for="item in formExamples.organizer" :key="item[0]" class="grid gap-1 p-3 sm:grid-cols-[150px_1fr]"><dt class="text-xs font-bold text-slate-400">{{ item[0] }}</dt><dd class="font-mono text-xs font-semibold text-slate-800">{{ item[1] }}</dd></div>
                    </dl>
                    <p class="mt-3 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-900"><strong>Wallet:</strong> ambil alamat publik dengan tombol Copy Address di MetaMask/Rabby. Jangan mengambil private key.</p>
                </div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-2"><IconWallet class="h-5 w-5 text-emerald-700" /><h2 class="text-lg font-extrabold text-slate-900">Contoh form donasi</h2></div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Contoh data yang dicatat pada detail kampanye setelah transaksi wallet.</p>
                    <dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">
                        <div v-for="item in formExamples.donor" :key="item[0]" class="grid gap-1 p-3 sm:grid-cols-[180px_1fr]"><dt class="text-xs font-bold text-slate-400">{{ item[0] }}</dt><dd class="font-mono text-xs font-semibold text-slate-800">{{ item[1] }}</dd></div>
                    </dl>
                    <div class="mt-3 rounded-xl bg-emerald-50 p-3 text-xs leading-relaxed text-emerald-900"><strong>Urutan:</strong> pilih nominal → kirim/setujui transaksi di wallet → salin hash → isi hash di aplikasi → klik Catat transaksi.</div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2"><IconArrowRight class="h-5 w-5 text-amber-600" /><h2 class="text-lg font-extrabold text-slate-900">Contoh form pencairan dana</h2></div>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">Pencairan dilakukan setelah target tercapai atau batas waktu lewat. Pada versi produksi, form ini memanggil fungsi withdrawal smart contract dan meminta tanda tangan wallet penyelenggara.</p>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="item in formExamples.payout" :key="item[0]" class="rounded-xl bg-slate-50 p-3"><dt class="text-xs font-bold text-slate-400">{{ item[0] }}</dt><dd class="mt-1 text-xs font-semibold text-slate-800">{{ item[1] }}</dd></div>
                </dl>
                <div class="mt-4 grid gap-3 text-sm md:grid-cols-3"><div class="rounded-xl border border-slate-100 p-3"><strong>1. Cek syarat</strong><p class="mt-1 text-xs leading-relaxed text-slate-600">Target tercapai atau deadline lewat, dan transaksi donasi sudah confirmed.</p></div><div class="rounded-xl border border-slate-100 p-3"><strong>2. Klik Cairkan</strong><p class="mt-1 text-xs leading-relaxed text-slate-600">Wallet penyelenggara membuka MetaMask/Rabby untuk menyetujui withdrawal.</p></div><div class="rounded-xl border border-slate-100 p-3"><strong>3. Tunggu receipt</strong><p class="mt-1 text-xs leading-relaxed text-slate-600">Status menjadi withdrawn setelah withdrawal hash sukses di PolygonScan.</p></div></div>
                <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-900"><strong>Status kode saat ini:</strong> form pencairan dan smart contract withdrawal belum tersedia di repository. Jangan melakukan transfer manual berdasarkan nominal database; pencairan nyata harus melalui smart contract.</p>
            </section>

            <section class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-extrabold text-sky-950">Transfer dulu atau isi aplikasi dulu?</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl bg-white p-4"><p class="text-sm font-bold text-sky-900">Transaksi blockchain nyata</p><ol class="mt-2 list-decimal space-y-1 pl-5 text-xs leading-relaxed text-slate-600"><li>Isi nominal rupiah di form aplikasi.</li><li>Klik tombol kirim/catat agar proses wallet dimulai.</li><li>Periksa detail transfer di popup MetaMask/Rabby.</li><li>Setujui transfer ke smart contract.</li><li>Setelah disetujui, salin transaction hash dari wallet.</li><li>Masukkan hash ke form pencatatan jika belum otomatis, lalu kirim.</li></ol><p class="mt-3 rounded-lg bg-emerald-50 p-2 text-xs font-semibold text-emerald-800">Intinya: nominal diisi lebih dulu, transfer wallet dilakukan setelah itu, hash baru diisi setelah wallet menghasilkan hash.</p></div>
                    <div class="rounded-xl bg-white p-4"><p class="text-sm font-bold text-sky-900">Testing lokal saat ini</p><ol class="mt-2 list-decimal space-y-1 pl-5 text-xs leading-relaxed text-slate-600"><li>Isi nominal dan hash testing pada form.</li><li>Klik Catat transaksi untuk membuat status pending.</li><li>Jalankan <code>php artisan donation:confirm ID</code>.</li><li>Periksa status confirmed dan progres kampanye.</li></ol></div>
                </div>
            </section>

            <section v-if="isAdmin" class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-red-700">Khusus admin</p><h2 class="mt-2 text-xl font-black text-slate-900">Testing verifikasi dan konfirmasi lokal</h2><p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">Bagian ini hanya tampil untuk akun dengan role admin. Admin memeriksa identitas; command konfirmasi di bawah hanya simulasi lokal, bukan bukti transaksi blockchain produksi.</p></div>
                    <span class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">Admin only</span>
                </div>
                <div class="mt-6 space-y-4"><article v-for="(step, index) in adminSteps" :key="step.title" class="grid gap-4 rounded-2xl border border-red-100 p-4 sm:grid-cols-[40px_minmax(0,1fr)_minmax(220px,0.7fr)] sm:items-start"><div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-sm font-black text-red-700">{{ index + 1 }}</div><div><h3 class="font-extrabold text-slate-900">{{ step.title }}</h3><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ step.text }}</p></div><div class="space-y-2 text-xs"><div class="rounded-lg bg-slate-50 p-2.5"><p class="font-bold uppercase tracking-wide text-slate-400">Buka / sumber data</p><p class="mt-1 font-semibold text-slate-700">{{ step.source }}</p></div><div class="rounded-lg bg-red-50 p-2.5"><p class="font-bold uppercase tracking-wide text-red-700">Yang di-input</p><p class="mt-1 font-mono font-semibold text-red-900">{{ step.input }}</p></div><div class="rounded-lg bg-sky-50 p-2.5"><p class="font-bold uppercase tracking-wide text-sky-700">Langkah selanjutnya</p><p class="mt-1 font-semibold text-sky-900">{{ step.next }}</p></div></div></article></div>
                <div class="mt-5 rounded-xl bg-slate-950 p-4 text-sm text-slate-100"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Command testing lokal</p><code class="mt-2 block text-emerald-300">php artisan donation:confirm ID_DONASI</code><p class="mt-3 text-xs leading-relaxed text-slate-400">Contoh: <code class="text-slate-200">php artisan donation:confirm 1</code>. Hanya berjalan saat <code class="text-slate-200">APP_ENV=local</code>.</p></div>
            </section>

            <section v-if="isAdmin" class="rounded-2xl border border-violet-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-700">Khusus admin</p><h2 class="mt-2 text-xl font-black text-slate-900">Tutorial deploy Web3 Polygon Amoy</h2><p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">Ikuti urutan ini untuk mendapatkan alamat smart contract yang akan dimasukkan ke `.env`. Contract harus memiliki fungsi `donate(campaignId)` dan `withdraw(campaignId)` sesuai integrasi SafeGive.</p></div>
                    <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700">Admin only</span>
                </div>
                <div class="mt-5 rounded-xl bg-violet-50 p-4 text-sm leading-relaxed text-violet-950"><strong>Penting:</strong> `WEB3_CAMPAIGN_CONTRACT_ADDRESS` adalah alamat smart contract hasil deploy. Bukan alamat wallet, bukan RPC URL, dan bukan transaction hash.</div>
                <div class="mt-6 space-y-4"><article v-for="(step, index) in web3AdminSteps" :key="step.title" class="grid gap-4 rounded-2xl border border-violet-100 p-4 sm:grid-cols-[40px_minmax(0,1fr)_minmax(220px,0.7fr)] sm:items-start"><div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-sm font-black text-violet-700">{{ index + 1 }}</div><div><h3 class="font-extrabold text-slate-900">{{ step.title }}</h3><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ step.text }}</p></div><div class="space-y-2 text-xs"><div class="rounded-lg bg-slate-50 p-2.5"><p class="font-bold uppercase tracking-wide text-slate-400">Buka / sumber</p><p class="mt-1 font-semibold text-slate-700">{{ step.source }}</p></div><div class="rounded-lg bg-violet-50 p-2.5"><p class="font-bold uppercase tracking-wide text-violet-700">Yang di-input</p><p class="mt-1 font-mono font-semibold text-violet-950">{{ step.input }}</p></div><div class="rounded-lg bg-sky-50 p-2.5"><p class="font-bold uppercase tracking-wide text-sky-700">Berikutnya</p><p class="mt-1 font-semibold text-sky-900">{{ step.next }}</p></div></div></article></div>
                <div class="mt-6 rounded-xl bg-slate-950 p-4 text-sm text-slate-100"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Isi .env setelah deploy</p><pre class="mt-3 overflow-x-auto text-xs leading-relaxed text-emerald-300">WEB3_RPC_URL=https://polygon-amoy-bor-rpc.publicnode.com
WEB3_CHAIN_ID=80002
WEB3_CAMPAIGN_CONTRACT_ADDRESS=0xALAMAT_CONTRACT_DARI_REMIX
VITE_WEB3_CHAIN_ID=80002
VITE_WEB3_CAMPAIGN_CONTRACT_ADDRESS=0xALAMAT_CONTRACT_DARI_REMIX
VITE_MATIC_IDR_RATE=20000000</pre><p class="mt-3 text-xs text-slate-400">Setelah menyimpan: <code class="text-slate-200">php artisan optimize:clear</code>, lalu <code class="text-slate-200">npm run build</code> dan restart server.</p></div>
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-relaxed text-amber-900"><strong>Jika belum punya smart contract:</strong> tutorial ini berhenti pada langkah persiapan contract. Jangan mengisi alamat contoh. Contract SafeGive harus dibuat/deploy terlebih dahulu agar donasi dan pencairan benar-benar memindahkan token di blockchain.</div>
            </section>

            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-relaxed text-amber-900"><strong>Catatan implementasi saat ini:</strong> verifikasi identitas, penyimpanan ciphertext Paillier, dan pencatatan transaksi sudah tersedia. Listener RPC, smart contract produksi, koneksi wallet otomatis, dan proses pencairan on-chain masih perlu dikonfigurasi sebelum digunakan dengan dana nyata.</section>
        </div>
    </AppLayout>
</template>
