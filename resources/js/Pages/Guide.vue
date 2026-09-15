<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { IconArrowRight, IconCheck, IconChevronRight, IconClock, IconCoins, IconFileCheck, IconLock, IconWallet } from '@tabler/icons-vue';

type Audience = 'organizer' | 'donor';
const activeAudience = ref<Audience>('organizer');

const steps = [
    { number: '01', title: 'Daftar akun', text: 'Buat akun dengan nama, email, dan password.', source: 'Halaman /register', next: 'Masuk ke Profile untuk verifikasi.' },
    { number: '02', title: 'Verifikasi identitas', text: 'Pilih Individu atau Yayasan, lengkapi data, lalu unggah dokumen.', source: 'Menu Profile', next: 'Tunggu admin menerima pengajuan.' },
    { number: '03', title: 'Admin memeriksa', text: 'Admin mencocokkan data dan dokumen. Status dapat pending, verified, atau rejected.', source: 'Panel admin /admin/verifications', next: 'Jika verified, menu Kampanye Saya terbuka.' },
];

const organizerSteps = [
    { title: 'Buka Kampanye Saya', text: 'Pilih menu Kampanye Saya, bukan Eksplorasi Kampanye. Halaman ini hanya berisi kampanye milik Anda.', source: 'Menu Kampanye Saya atau /my-campaigns', next: 'Klik Buat kampanye.' },
    { title: 'Isi data kampanye', text: 'Masukkan judul, kategori, deskripsi, gambar, target rupiah, waktu mulai, batas waktu, dan alamat wallet publik.', source: 'Form /campaigns/create', next: 'Periksa jadwal dan alamat wallet sebelum publikasi.' },
    { title: 'Masukkan wallet penerima', text: 'Gunakan alamat publik EVM penyelenggara, seperti 0x... 40 karakter. Jangan masukkan private key atau seed phrase.', source: 'Wallet Web3 penyelenggara', next: 'Publikasikan kampanye.' },
    { title: 'Pantau donasi', text: 'Lihat persentase terenkripsi, jumlah donatur, transaction hash, block, dan status transaksi.', source: 'Detail kampanye /campaigns/{id}', next: 'Tunggu target tercapai atau batas waktu berakhir.' },
    { title: 'Ajukan pencairan', text: 'Setelah target tercapai atau batas waktu lewat, penyelenggara mengajukan pencairan melalui smart contract.', source: 'Smart contract dan wallet penyelenggara', next: 'Pastikan aturan pencairan kampanye terpenuhi.' },
];

const donorSteps = [
    { title: 'Eksplorasi kampanye', text: 'Buka daftar kampanye publik milik semua penyelenggara dan pilih kampanye yang ingin didukung.', source: 'Menu Eksplorasi Kampanye atau /campaigns', next: 'Buka detail kampanye.' },
    { title: 'Hubungkan wallet Web3', text: 'Gunakan MetaMask, Rabby, atau wallet EVM lain di jaringan Polygon Amoy.', source: 'Browser wallet dan network Polygon Amoy', next: 'Siapkan token testnet untuk gas fee.' },
    { title: 'Masukkan nominal biasa', text: 'Masukkan nominal rupiah, misalnya 100000 untuk Rp100.000. Backend mengenkripsi nominal dengan Paillier.', source: 'Form donasi di detail kampanye', next: 'Kirim dan setujui transaksi di wallet.' },
    { title: 'Simpan transaction hash', text: 'Setelah wallet menyetujui transaksi, wallet memberikan transaction hash. Nomor block boleh diisi setelah transaksi masuk blok.', source: 'Popup wallet atau block explorer', next: 'Klik Catat transaksi jika masih menggunakan mode prototipe.' },
    { title: 'Cek status dan riwayat', text: 'Transaksi awalnya pending, kemudian menjadi confirmed setelah listener memeriksa receipt blockchain.', source: 'Menu Riwayat Transaksi atau /transactions', next: 'Buka hash di PolygonScan untuk audit.' },
];

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
                    <div class="space-y-4"><article v-for="(step, index) in activeAudience === 'organizer' ? organizerSteps : donorSteps" :key="step.title" class="grid gap-4 rounded-2xl border border-slate-100 p-4 sm:grid-cols-[40px_minmax(0,1fr)_minmax(180px,0.55fr)] sm:items-start"><div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#edf4e9] text-sm font-black text-emerald-700">{{ index + 1 }}</div><div><h3 class="font-extrabold text-slate-900">{{ step.title }}</h3><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ step.text }}</p></div><div class="text-xs"><p class="font-bold uppercase tracking-wide text-slate-400">Sumber/input</p><p class="mt-1 font-semibold text-slate-700">{{ formatSource(step.source) }}</p><p class="mt-3 font-bold uppercase tracking-wide text-slate-400">Langkah berikutnya</p><p class="mt-1 font-semibold text-emerald-700">{{ step.next }}</p></div></article></div>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-3">
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><IconWallet class="h-6 w-6 text-emerald-700" /><h2 class="mt-3 font-extrabold text-slate-900">Wallet dan transaksi</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Gunakan MetaMask/Rabby di Polygon Amoy. Alamat wallet kampanye adalah alamat publik `0x...`, bukan private key.</p><Link href="/campaigns" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Lihat kampanye <IconArrowRight class="h-4 w-4" /></Link></div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><IconClock class="h-6 w-6 text-amber-600" /><h2 class="mt-3 font-extrabold text-slate-900">Arti status pending</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Pending berarti data sudah dicatat tetapi receipt blockchain belum diperiksa listener. Confirmed hanya boleh diberikan setelah receipt sukses.</p><Link href="/transactions" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Buka riwayat <IconArrowRight class="h-4 w-4" /></Link></div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm"><IconLock class="h-6 w-6 text-emerald-700" /><h2 class="mt-3 font-extrabold text-slate-900">Privasi nominal</h2><p class="mt-2 text-sm leading-relaxed text-slate-600">Nominal plaintext tidak disimpan. Backend mengenkripsi dengan Paillier dan dapat mengagregasi ciphertext tanpa membuka nominal setiap donatur.</p><Link href="/profile" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Cek profil <IconArrowRight class="h-4 w-4" /></Link></div>
            </section>

            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-relaxed text-amber-900"><strong>Catatan implementasi saat ini:</strong> verifikasi identitas, penyimpanan ciphertext Paillier, dan pencatatan transaksi sudah tersedia. Listener RPC, smart contract produksi, koneksi wallet otomatis, dan proses pencairan on-chain masih perlu dikonfigurasi sebelum digunakan dengan dana nyata.</section>
        </div>
    </AppLayout>
</template>
