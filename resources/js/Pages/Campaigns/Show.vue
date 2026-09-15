<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onBeforeUnmount } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { IconArrowLeft, IconCalendar, IconCheck, IconCopy, IconLock, IconWallet } from '@tabler/icons-vue';

interface Donation {
    id: number;
    transaction_hash: string;
    block_number: number | null;
    status: 'pending' | 'confirmed' | 'failed';
    confirmed_at: string | null;
    created_at: string;
}

interface Campaign {
    id: number;
    title: string;
    description: string;
    category: string;
    image_url: string | null;
    target_amount: number;
    progress_percentage: number;
    donors_count: number;
    starts_at: string;
    ends_at: string;
    status: string;
    donation_open: boolean;
    donation_message: string;
    wallet_address: string;
    organizer: { name: string };
    donations: Donation[];
}

const props = defineProps<{ campaign: Campaign }>();
const form = useForm({ amount: '', transaction_hash: '', block_number: '' });
const processingStep = ref('');
let processingTimer: ReturnType<typeof setInterval> | null = null;
const formatRupiah = (value: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const formatDate = (value: string) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value));
const shortHash = (hash: string) => `${hash.slice(0, 10)}...${hash.slice(-8)}`;
const processingSteps = [
    'Memvalidasi nominal donasi...',
    'Mengenkripsi nominal di backend...',
    'Membuat commitment privasi...',
    'Mencatat transaksi untuk konfirmasi blockchain...',
];
const submitDonation = () => {
    let step = 0;
    processingStep.value = processingSteps[step];
    processingTimer = setInterval(() => {
        step = Math.min(step + 1, processingSteps.length - 1);
        processingStep.value = processingSteps[step];
    }, 700);
    form.post(`/campaigns/${props.campaign.id}/donations`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onFinish: () => {
            if (processingTimer) clearInterval(processingTimer);
            processingTimer = null;
            processingStep.value = '';
        },
    });
};
onBeforeUnmount(() => { if (processingTimer) clearInterval(processingTimer); });
</script>

<template>
    <AppLayout>
        <Head :title="`${campaign.title} - SafeGive`" />
        <div class="space-y-7">
            <Link href="/campaigns" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-emerald-700"><IconArrowLeft class="h-4 w-4" />Kembali ke kampanye</Link>
            <section class="overflow-hidden rounded-3xl border border-[#dce6d8] bg-white shadow-sm">
                <div class="grid lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                    <div class="min-h-[280px] bg-[#edf4e9]">
                        <img v-if="campaign.image_url" :src="campaign.image_url" :alt="campaign.title" class="h-full min-h-[280px] w-full object-cover" />
                    </div>
                    <div class="p-6 sm:p-8">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">{{ campaign.category }}</p>
                        <h1 class="mt-3 text-3xl font-black leading-tight text-slate-900">{{ campaign.title }}</h1>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ campaign.description }}</p>
                        <p class="mt-4 text-sm font-semibold text-slate-500">Penyelenggara: <span class="text-slate-800">{{ campaign.organizer.name }}</span></p>
                        <div class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }" /></div>
                        <div class="mt-3 flex items-center justify-between text-sm"><span class="font-bold text-emerald-700">{{ campaign.progress_percentage.toFixed(2) }}% terverifikasi</span><span class="font-bold text-slate-700">Target {{ formatRupiah(campaign.target_amount) }}</span></div>
                        <div class="mt-5 flex flex-wrap gap-4 text-xs text-slate-500"><span class="inline-flex items-center gap-1.5"><IconCalendar class="h-4 w-4" />Berakhir {{ formatDate(campaign.ends_at) }}</span><span>{{ campaign.donors_count }} donatur</span></div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.8fr)]">
                <section class="rounded-2xl border border-[#dce6d8] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3"><div><h2 class="text-lg font-extrabold text-slate-900">Laporan donasi</h2><p class="mt-1 text-sm text-slate-500">Nominal tetap terenkripsi; hash dan status transaksi dapat diaudit.</p></div><IconLock class="h-5 w-5 text-emerald-700" /></div>
                    <div v-if="campaign.donations.length" class="mt-5 divide-y divide-slate-100">
                        <div v-for="donation in campaign.donations" :key="donation.id" class="flex flex-wrap items-center justify-between gap-3 py-4 text-sm"><div><p class="font-mono font-semibold text-slate-700">{{ shortHash(donation.transaction_hash) }}</p><p class="mt-1 text-xs text-slate-400">{{ donation.block_number ? `Block #${donation.block_number}` : 'Menunggu block' }}</p></div><span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="donation.status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : donation.status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'">{{ donation.status }}</span></div>
                    </div>
                    <p v-else class="mt-6 text-sm text-slate-500">Belum ada transaksi donasi.</p>
                </section>

                <section class="rounded-2xl border border-[#dce6d8] bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-2"><IconWallet class="h-5 w-5 text-emerald-700" /><h2 class="text-lg font-extrabold text-slate-900">Catat donasi on-chain</h2></div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">Masukkan nominal biasa. Sistem akan mengenkripsi dan membuat commitment secara otomatis di backend.</p>
                    <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-600"><strong>Yang perlu disiapkan:</strong> nominal donasi dan transaction hash dari wallet. Jangan memasukkan private key atau seed phrase.</div>
                    <div v-if="!campaign.donation_open" class="mt-5 rounded-xl bg-amber-50 p-4 text-sm leading-relaxed text-amber-800">{{ campaign.donation_message }}</div>
                    <form v-else class="mt-5 space-y-4" @submit.prevent="submitDonation">
                        <label class="block"><span class="text-sm font-bold text-slate-800">Nominal donasi (rupiah)</span><input v-model="form.amount" type="number" min="1000" step="1000" class="mt-2 w-full rounded-xl border-slate-200 text-sm" placeholder="Contoh: 100000" /><span v-if="form.errors.amount" class="mt-1 block text-xs text-red-600">{{ form.errors.amount }}</span></label>
                        <label class="block"><span class="text-sm font-bold text-slate-800">Transaction hash dari wallet</span><input v-model="form.transaction_hash" class="mt-2 w-full rounded-xl border-slate-200 font-mono text-sm" placeholder="0x..." /><span v-if="form.errors.transaction_hash" class="mt-1 block text-xs text-red-600">{{ form.errors.transaction_hash }}</span></label>
                        <label class="block"><span class="text-sm font-bold text-slate-800">Nomor block (opsional)</span><input v-model="form.block_number" type="number" class="mt-2 w-full rounded-xl border-slate-200 text-sm" placeholder="Diisi setelah transaksi masuk blok" /></label>
                        <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-60"><IconCheck class="h-4 w-4" />{{ form.processing ? 'Memproses donasi...' : 'Catat transaksi' }}</button>
                        <div v-if="form.processing" class="rounded-xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-800" role="status" aria-live="polite">{{ processingStep }}</div>
                        <p v-if="form.recentlySuccessful" class="text-sm font-semibold text-emerald-700">Donasi berhasil dicatat dan menunggu konfirmasi blockchain.</p>
                    </form>
                    <p class="mt-4 text-xs leading-relaxed text-slate-400">Alamat wallet kampanye: <span class="font-mono text-slate-600">{{ campaign.wallet_address }}</span></p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
