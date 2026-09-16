<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { IconArrowLeft, IconBuildingBank, IconUpload, IconShieldCheck } from '@tabler/icons-vue';

const form = useForm({
    title: '',
    description: '',
    category: '',
    image: null as File | null,
    target_amount: '',
    starts_at: '',
    ends_at: '',
    payout_bank_name: 'BCA',
    payout_account_number: '',
    payout_account_name: '',
});

const bankOptions = [
    'Bank Central Asia (BCA)',
    'Bank Mandiri',
    'Bank Rakyat Indonesia (BRI)',
    'Bank Negara Indonesia (BNI)',
    'Bank Syariah Indonesia (BSI)',
    'CIMB Niaga',
    'Bank Permata',
    'DANA',
    'GoPay',
    'OVO',
];

const submit = () => form.post('/campaigns', { forceFormData: true });
</script>

<template>
    <AppLayout>
        <Head title="Buat Kampanye - SafeGive" />
        <div class="mx-auto max-w-3xl space-y-7">
            <Link href="/campaigns" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-emerald-700">
                <IconArrowLeft class="h-4 w-4" />
                Kembali ke kampanye
            </Link>

            <header>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Penyelenggara terverifikasi</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Buat kampanye donasi</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Isi data program donasi di bawah. Transaksi donasi akan dienkripsi dengan Paillier blockchain ledger dan dicairkan ke rekening bank penyelenggara setelah syarat terpenuhi.
                </p>
            </header>

            <form class="space-y-6 rounded-2xl border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-8" @submit.prevent="submit">
                <!-- Data Kampanye -->
                <div>
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">Informasi Program</h3>
                    <div class="mt-4 space-y-5">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-800">Judul kampanye</span>
                            <input v-model="form.title" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Bantuan biaya pendidikan anak pelosok" />
                            <span v-if="form.errors.title" class="mt-1 block text-xs text-red-600">{{ form.errors.title }}</span>
                        </label>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-bold text-slate-800">Kategori</span>
                                <input v-model="form.category" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Pendidikan, Kesehatan, Bencana" />
                                <span v-if="form.errors.category" class="mt-1 block text-xs text-red-600">{{ form.errors.category }}</span>
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-800">Target Donasi (Rupiah)</span>
                                <input v-model="form.target_amount" type="number" min="10000" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="10000000" />
                                <span v-if="form.errors.target_amount" class="mt-1 block text-xs text-red-600">{{ form.errors.target_amount }}</span>
                            </label>
                        </div>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-800">Deskripsi</span>
                            <textarea v-model="form.description" rows="5" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Jelaskan tujuan, penerima manfaat, dan rencana penggunaan dana secara transparan." />
                            <span v-if="form.errors.description" class="mt-1 block text-xs text-red-600">{{ form.errors.description }}</span>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-800">Gambar kampanye</span>
                            <span class="mt-2 flex items-center gap-2 rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                                <IconUpload class="h-5 w-5 text-emerald-700" />
                                <input type="file" accept=".jpg,.jpeg,.png,.webp" @change="form.image = ($event.target as HTMLInputElement).files?.[0] || null" />
                            </span>
                            <span v-if="form.errors.image" class="mt-1 block text-xs text-red-600">{{ form.errors.image }}</span>
                        </label>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-bold text-slate-800">Waktu Mulai</span>
                                <input v-model="form.starts_at" type="datetime-local" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <span v-if="form.errors.starts_at" class="mt-1 block text-xs text-red-600">{{ form.errors.starts_at }}</span>
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-800">Batas Waktu (Jatuh Tempo)</span>
                                <input v-model="form.ends_at" type="datetime-local" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <span v-if="form.errors.ends_at" class="mt-1 block text-xs text-red-600">{{ form.errors.ends_at }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Rekening Tujuan Pencairan Dana Penyelenggara -->
                <div class="pt-2">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                        <IconBuildingBank class="h-5 w-5 text-emerald-700" />
                        <h3 class="text-base font-bold text-slate-900">Rekening Tujuan Pencairan Dana (Penyelenggara)</h3>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        Dana yang terkumpul akan dicairkan ke rekening ini saat target terpenuhi atau masa kampanye telah berakhir.
                    </p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nama Bank / E-Wallet
                            </label>
                            <select
                                v-model="form.payout_bank_name"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            >
                                <option v-for="bank in bankOptions" :key="bank" :value="bank">
                                    {{ bank }}
                                </option>
                            </select>
                            <span v-if="form.errors.payout_bank_name" class="mt-1 block text-xs text-red-600">{{ form.errors.payout_bank_name }}</span>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Nomor Rekening / No. E-Wallet</span>
                                <input
                                    v-model="form.payout_account_number"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border-slate-200 font-mono text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Contoh: 1234567890"
                                />
                                <span v-if="form.errors.payout_account_number" class="mt-1 block text-xs text-red-600">{{ form.errors.payout_account_number }}</span>
                            </label>

                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Nama Pemilik Rekening</span>
                                <input
                                    v-model="form.payout_account_name"
                                    type="text"
                                    class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Sesuai buku tabungan / identitas"
                                />
                                <span v-if="form.errors.payout_account_name" class="mt-1 block text-xs text-red-600">{{ form.errors.payout_account_name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-emerald-50/80 p-4 border border-emerald-200/80 text-xs text-emerald-900 flex items-start gap-2.5">
                    <IconShieldCheck class="h-5 w-5 text-emerald-700 shrink-0 mt-0.5" />
                    <div>
                        <p class="font-bold">Keamanan & Transparansi Kriptografis</p>
                        <p class="mt-0.5 text-emerald-800">
                            Donasi ditransfer ke rekening resmi SafeGive yang dikelola admin, dan secara otomatis dicatat ke ledger terenkripsi Paillier homomorfik. Penyelenggara dapat mencairkan dana saat target tercapai atau batas jatuh tempo terlewati.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-emerald-700 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 hover:bg-emerald-800 disabled:opacity-60"
                >
                    {{ form.processing ? 'Menyimpan Kampanye...' : 'Publikasikan Kampanye' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
