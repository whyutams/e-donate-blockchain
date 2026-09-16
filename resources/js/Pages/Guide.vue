<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { IconArrowRight, IconBuildingBank, IconCheck, IconChevronRight, IconClock, IconCoins, IconFileCheck, IconLock, IconShieldCheck } from '@tabler/icons-vue';

type Audience = 'organizer' | 'donor' | 'admin';
const activeAudience = ref<Audience>('organizer');
const page = usePage<{ auth: { is_admin: boolean } }>();
const isAdmin = computed(() => page.props.auth?.is_admin === true);

const steps = [
    { number: '01', title: 'Daftar akun & Verifikasi', text: 'Buat akun, lengkapi profil identitas (KTP/Legalitas Yayasan), dan tunggu verifikasi admin.', source: 'Menu Profile & /register', next: 'Setelah verified, Anda dapat membuat kampanye.' },
    { number: '02', title: 'Konfigurasi Rekening Admin', text: 'Admin mengatur 1 rekening bank/e-wallet pusat (BCA/Mandiri/BRI/QRIS) untuk menerima seluruh donasi.', source: 'Panel Admin /admin/bank-settings', next: 'Semua donatur transfer ke rekening ini.' },
    { number: '03', title: 'Donasi & Pencairan Terenkripsi', text: 'Donatur transfer ke rekening pusat + unggah bukti. Penyelenggara mencairkan dana jika target tercapai / deadline lewat.', source: 'Eksplorasi Kampanye & Kampanye Saya', next: 'Ledger kriptografi mencatat transaksi secara transparan.' },
];

const organizerSteps = [
    { title: 'Buka Kampanye Saya', text: 'Pilih menu Kampanye Saya. Pastikan akun Anda telah diverifikasi oleh Admin.', source: 'Menu Kampanye Saya (/my-campaigns)', input: 'Status verifikasi akun harus verified.', next: 'Klik Buat Kampanye Baru.' },
    { title: 'Isi Detail & Rekening Pencairan', text: 'Lengkapi judul, kategori, deskripsi, gambar, target rupiah, batas waktu, dan rekening bank penyelenggara untuk pencairan dana.', source: 'Form /campaigns/create', input: 'Data kampanye + Bank tujuan (BCA, Mandiri, BRI, BNI, BSI, E-Wallet) dan No Rekening.', next: 'Publikasikan kampanye.' },
    { title: 'Pantau Donasi Terenkripsi', text: 'Lihat progres kampanye, ledger blok transaksi terenkripsi Paillier, status hash kriptografis 0x..., dan daftar donatur.', source: 'Detail Kampanye (/campaigns/{id})', input: 'Otomatis diperbarui setelah verifikasi admin.', next: 'Tunggu target donasi terpenuhi atau waktu kampanye berakhir.' },
    { title: 'Ajukan Pencairan Dana', text: 'Tombol pencairan aktif jika target telah tercapai (100%) ATAU batas waktu kampanye sudah terlewati.', source: 'Detail Kampanye atau Kampanye Saya', input: 'Pilih rekening pencairan & klik Ajukan Pencairan Dana.', next: 'Admin memproses transfer ke rekening penyelenggara.' },
];

const donorSteps = [
    { title: 'Eksplorasi Kampanye', text: 'Pilih kampanye sosial, kemanusiaan, atau pendidikan yang ingin Anda bantu.', source: 'Menu Eksplorasi Kampanye (/campaigns)', input: 'Pilih salah satu kartu kampanye aktif.', next: 'Klik Lihat Detail & Donasi.' },
    { title: 'Pilih Metode Pembayaran', text: 'Pilih Bayar Otomatis Midtrans Snap (QRIS, VA Bank, E-Wallet) atau Transfer Manual ke Rekening Admin.', source: 'Tab Pembayaran di Detail Kampanye', input: 'Nominal donasi, nama, email, dan nomor WhatsApp.', next: 'Klik Bayar Sekarang dengan Midtrans.' },
    { title: 'Selesaikan di Midtrans Snap', text: 'Pop-up Midtrans terbuka. Pilih QRIS, Bank Virtual Account (BCA, Mandiri, BRI, BNI), atau E-Wallet untuk menyelesaikan pembayaran.', source: 'Pop-up Midtrans Snap (Sandbox)', input: 'Ikuti instruksi simulator/pembayaran.', next: 'Sistem mencatat transaksi ke ledger kriptografi.' },
    { title: 'Enkripsi Paillier & Hash Blockchain', text: 'Sistem mengenkripsi nominal donasi menggunakan kriptografi homomorfik Paillier dan menerbitkan Transaction Hash (0x...) serta nomor blok.', source: 'Ledger Transaksi Kriptografi', input: 'Dibuat otomatis oleh sistem backend.', next: 'Status otomatis menjadi confirmed.' },
    { title: 'Donasi Terkonfirmasi Instan', text: 'Pembayaran Midtrans otomatis mengubah status donasi menjadi Confirmed dan memicu homomorphic aggregation pada progres kampanye.', source: 'Menu Riwayat Transaksi (/transactions)', input: 'Status: Pending → Confirmed otomatis.', next: 'Donasi selesai dan tercatat abadi di ledger.' },
];

const adminSteps = [
    { title: 'Konfigurasi Rekening & Midtrans', text: 'Atur rekening bank pusat platform dan masukkan Server Key & Client Key Midtrans Sandbox.', source: 'Menu Pengaturan Rekening (/admin/bank-settings)', input: 'Nama Bank/E-Wallet, Nomor Rekening, Midtrans Server Key & Client Key.', next: 'Midtrans Snap langsung aktif untuk donatur.' },
    { title: 'Verifikasi Identitas Penyelenggara', text: 'Periksa dokumen KTP atau legalitas yayasan penyelenggara kampanye. Setujui untuk mengizinkan pembuatan kampanye.', source: 'Menu Verifikasi Pengguna (/admin/verifications)', input: 'Review dokumen identitas: Terima / Tolak.', next: 'Penyelenggara yang lolos verifikasi dapat membuat kampanye.' },
    { title: 'Validasi Donasi & Cek Midtrans', text: 'Periksa donasi masuk. Donasi Midtrans terkonfirmasi otomatis; admin juga dapat menekan Cek Midtrans untuk sinkronisasi seketika.', source: 'Menu Validasi Donasi (/admin/donations)', input: 'Klik Cek Midtrans atau Konfirmasi Manual.', next: 'Progres kampanye dan ledger blockchain otomatis terupdate.' },
    { title: 'Penyaluran Pencairan Dana', text: 'Periksa pengajuan pencairan dana dari penyelenggara yang telah memenuhi syarat (target tercapai / deadline lewat).', source: 'Menu Pencairan Dana (/admin/withdrawals)', input: 'Transfer ke rekening penyelenggara, input hash/bukti transfer, klik Setujui & Cairkan.', next: 'Status kampanye menjadi Withdrawn (Dana Dicairkan).' },
];

const formExamples = {
    organizer: [
        ['Judul kampanye', 'Bantuan Medis Anak Yatim Piatu'],
        ['Kategori', 'Kesehatan / Medis'],
        ['Target (rupiah)', 'Rp 25.000.000'],
        ['Batas waktu', '30 Hari dari tanggal publikasi'],
        ['Bank Pencairan', 'Bank Central Asia (BCA)'],
        ['No. Rekening Penyelenggara', '8735019283 a.n Yayasan Peduli Sesama'],
    ],
    donor: [
        ['Nominal donasi', 'Rp 150.000 (Minimal Rp 1.000)'],
        ['Rekening Tujuan', 'BCA 1234567890 a.n SafeGive Peduli Indonesia'],
        ['Metode Transfer', 'Bank BCA / Mandiri / QRIS'],
        ['Bukti Transfer', 'Screenshot mutasi m-banking berhasil (JPG/PNG/PDF)'],
        ['Hasil Sistem', 'Transaction Hash: 0x8f2d... + Block #104 + Paillier Ciphertext'],
    ],
    payout: [
        ['Syarat 1', 'Progres donasi mencapai 100% dari target, ATAU'],
        ['Syarat 2', 'Batas waktu kampanye (deadline) telah berakhir'],
        ['Rekening Tujuan', 'Rekening bank penyelenggara yang telah diverifikasi'],
        ['Bukti Penyaluran', 'Admin mengunggah bukti transfer pencairan resmi'],
    ],
};
</script>

<template>
    <AppLayout>
        <Head title="Panduan SafeGive" />
        <div class="space-y-8">
            <header class="max-w-4xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Pusat Bantuan & Petunjuk Penggunaan</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Panduan Alur Transaksi & Sistem SafeGive</h1>
                <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                    SafeGive menggabungkan kemudahan transfer bank/e-wallet lokal Indonesia dengan keamanan kriptografi blockchain (Enkripsi Homomorfik Paillier & Ledger Hash Imutabel).
                </p>
            </header>

            <section class="grid gap-4 md:grid-cols-3">
                <div v-for="step in steps" :key="step.number" class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <span class="text-xs font-black text-emerald-700">{{ step.number }}</span>
                    <h2 class="mt-3 text-base font-extrabold text-slate-900">{{ step.title }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ step.text }}</p>
                    <p class="mt-4 text-xs font-bold text-slate-400">Lokasi: <span class="font-medium text-slate-600">{{ step.source }}</span></p>
                    <p class="mt-1 text-xs font-bold text-slate-400">Selanjutnya: <span class="font-medium text-slate-600">{{ step.next }}</span></p>
                </div>
            </section>

            <section class="rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                <div class="flex flex-wrap gap-2 border-b border-slate-100 p-4 sm:p-5">
                    <button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition" :class="activeAudience === 'organizer' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'" @click="activeAudience = 'organizer'">
                        <IconFileCheck class="h-4 w-4" />Penyelenggara Kampanye
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition" :class="activeAudience === 'donor' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'" @click="activeAudience = 'donor'">
                        <IconCoins class="h-4 w-4" />Donatur
                    </button>
                    <button v-if="isAdmin" type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition" :class="activeAudience === 'admin' ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'" @click="activeAudience = 'admin'">
                        <IconShieldCheck class="h-4 w-4" />Admin Platform
                    </button>
                </div>

                <div class="p-5 sm:p-8">
                    <div v-if="activeAudience === 'organizer'" class="mb-7 rounded-2xl bg-emerald-50 p-5">
                        <h2 class="text-xl font-black text-emerald-950">Alur Penyelenggara Kampanye</h2>
                        <p class="mt-2 text-sm leading-relaxed text-emerald-900">
                            Penyelenggara mengisi data kampanye beserta nomor rekening bank untuk pencairan dana. Permintaan pencairan dana dapat diajukan jika <strong>target telah tercapai (100%)</strong> atau <strong>batas waktu kampanye telah berakhir</strong>.
                        </p>
                    </div>
                    <div v-else-if="activeAudience === 'donor'" class="mb-7 rounded-2xl bg-sky-50 p-5">
                        <h2 class="text-xl font-black text-sky-950">Alur Donatur</h2>
                        <p class="mt-2 text-sm leading-relaxed text-sky-900">
                            Donasi dilakukan dengan transfer bank lokal (BCA, Mandiri, BRI, BNI, BSI) atau E-Wallet/QRIS langsung ke <strong>Satu Rekening Pusat Admin</strong>. Unggah bukti transfer, dan sistem mengamankan transaksi Anda dengan enkripsi blockchain Paillier.
                        </p>
                    </div>
                    <div v-else class="mb-7 rounded-2xl bg-amber-50 p-5">
                        <h2 class="text-xl font-black text-amber-950">Alur Pengelolaan Admin</h2>
                        <p class="mt-2 text-sm leading-relaxed text-amber-900">
                            Admin mengonfigurasi rekening pusat, memverifikasi dokumen identitas penyelenggara, memvalidasi bukti transfer donasi (memicu homomorphic aggregation), dan menyalurkan pencairan dana yang memenuhi syarat.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <article v-for="(step, index) in activeAudience === 'organizer' ? organizerSteps : activeAudience === 'donor' ? donorSteps : adminSteps" :key="step.title" class="grid gap-4 rounded-2xl border border-slate-100 p-4 sm:grid-cols-[40px_minmax(0,1fr)_minmax(220px,0.7fr)] sm:items-start">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#edf4e9] text-sm font-black text-emerald-700">{{ index + 1 }}</div>
                            <div>
                                <h3 class="font-extrabold text-slate-900">{{ step.title }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ step.text }}</p>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="rounded-lg bg-slate-50 p-2.5">
                                    <p class="font-bold uppercase tracking-wide text-slate-400">Buka / Sumber Data</p>
                                    <p class="mt-1 font-semibold text-slate-700">{{ step.source }}</p>
                                </div>
                                <div class="rounded-lg bg-emerald-50 p-2.5">
                                    <p class="font-bold uppercase tracking-wide text-emerald-700">Input / Aksi</p>
                                    <p class="mt-1 font-semibold text-emerald-900">{{ step.input }}</p>
                                </div>
                                <div class="rounded-lg bg-sky-50 p-2.5">
                                    <p class="font-bold uppercase tracking-wide text-sky-700">Langkah Berikutnya</p>
                                    <p class="mt-1 font-semibold text-sky-900">{{ step.next }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-3">
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <IconBuildingBank class="h-6 w-6 text-emerald-700" />
                    <h2 class="mt-3 font-extrabold text-slate-900">Satu Rekening Pusat Admin</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Seluruh donatur mentransfer ke satu rekening resmi yang dikonfigurasi admin (Bank BCA, Mandiri, BRI, BNI, BSI atau QRIS). Aman, terpusat, dan mudah diverifikasi.
                    </p>
                    <Link v-if="isAdmin" href="/admin/bank-settings" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">
                        Kelola Rekening Admin <IconArrowRight class="h-4 w-4" />
                    </Link>
                    <Link v-else href="/campaigns" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">
                        Lihat Kampanye Aktif <IconArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <IconLock class="h-6 w-6 text-emerald-700" />
                    <h2 class="mt-3 font-extrabold text-slate-900">Enkripsi Blockchain Paillier</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Nominal donasi dienkripsi menggunakan kriptografi homomorfik Paillier dan diikat dengan SHA-256 commitment serta hash transaksi 0x... berurutan.
                    </p>
                    <Link href="/transactions" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">
                        Buka Riwayat & Ledger <IconArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <IconClock class="h-6 w-6 text-amber-600" />
                    <h2 class="mt-3 font-extrabold text-slate-900">Aturan Pencairan Dana</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Penyelenggara hanya dapat mencairkan dana apabila target donasi telah tercapai (100%) atau batas waktu kampanye (deadline) telah terlewati.
                    </p>
                    <Link href="/my-campaigns" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">
                        Cek Kampanye Saya <IconArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-2">
                        <IconFileCheck class="h-5 w-5 text-emerald-700" />
                        <h2 class="text-lg font-extrabold text-slate-900">Contoh Form Pembuatan Kampanye</h2>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Data yang diisi penyelenggara pada menu <strong>Kampanye Saya → Buat Kampanye</strong>.</p>
                    <dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">
                        <div v-for="item in formExamples.organizer" :key="item[0]" class="grid gap-1 p-3 sm:grid-cols-[180px_1fr]">
                            <dt class="text-xs font-bold text-slate-400">{{ item[0] }}</dt>
                            <dd class="font-mono text-xs font-semibold text-slate-800">{{ item[1] }}</dd>
                        </div>
                    </dl>
                    <p class="mt-3 rounded-xl bg-emerald-50 p-3 text-xs leading-relaxed text-emerald-900">
                        <strong>Rekening Penyelenggara:</strong> Pastikan nama pemilik rekening sesuai dengan identitas penyelenggara untuk kemudahan pencairan dana.
                    </p>
                </div>

                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-2">
                        <IconCoins class="h-5 w-5 text-emerald-700" />
                        <h2 class="text-lg font-extrabold text-slate-900">Contoh Form Transaksi Donasi</h2>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Alur yang dilakukan donatur pada halaman detail kampanye.</p>
                    <dl class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">
                        <div v-for="item in formExamples.donor" :key="item[0]" class="grid gap-1 p-3 sm:grid-cols-[180px_1fr]">
                            <dt class="text-xs font-bold text-slate-400">{{ item[0] }}</dt>
                            <dd class="font-mono text-xs font-semibold text-slate-800">{{ item[1] }}</dd>
                        </div>
                    </dl>
                    <div class="mt-3 rounded-xl bg-sky-50 p-3 text-xs leading-relaxed text-sky-900">
                        <strong>Urutan:</strong> Transfer ke rekening pusat admin → Unggah screenshot bukti transfer → Sistem generate hash kriptografi → Admin verifikasi → Selesai.
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2">
                    <IconArrowRight class="h-5 w-5 text-amber-600" />
                    <h2 class="text-lg font-extrabold text-slate-900">Ketentuan & Alur Pencairan Dana Kampanye</h2>
                </div>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                    Untuk menjaga integritas dan akuntabilitas dana sosial, pencairan dana diawasi oleh sistem dan admin dengan aturan otomatis:
                </p>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="item in formExamples.payout" :key="item[0]" class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-xs font-bold text-slate-400">{{ item[0] }}</dt>
                        <dd class="mt-1 text-xs font-semibold text-slate-800">{{ item[1] }}</dd>
                    </div>
                </dl>
                <div class="mt-4 grid gap-3 text-sm md:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 p-3">
                        <strong>1. Evaluasi Kelayakan</strong>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">Sistem mengecek apakah donasi sudah mencapai target 100% ATAU tanggal batas waktu telah terlewati.</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <strong>2. Pengajuan Penyelenggara</strong>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">Penyelenggara mengajukan pencairan melalui tombol di detail kampanye dengan rekening bank yang terdaftar.</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <strong>3. Penyaluran oleh Admin</strong>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">Admin mentransfer dana ke rekening penyelenggara dan mengunggah hash/bukti pencairan.</p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

