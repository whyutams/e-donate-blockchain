<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { IconArrowRight, IconCheck, IconClock, IconExternalLink, IconLock, IconX } from '@tabler/icons-vue';

interface Transaction {
    id: number;
    campaign_id: number;
    campaign: string;
    transaction_hash: string;
    block_number: number | null;
    status: 'pending' | 'confirmed' | 'failed';
    created_at: string;
    confirmed_at: string | null;
}

defineProps<{ transactions: { data: Transaction[]; current_page: number; last_page: number } }>();

const shortHash = (hash: string) => `${hash.slice(0, 12)}...${hash.slice(-10)}`;
const formatDate = (value: string) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
const statusLabel = { pending: 'Menunggu konfirmasi', confirmed: 'Terkonfirmasi', failed: 'Gagal' };
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Transaksi - SafeGive" />
        <div class="space-y-7">
            <header>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Audit pribadi</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Riwayat Transaksi</h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Daftar donasi yang dikirim dari akun Anda. Nominal tidak ditampilkan sebagai plaintext; hash transaksi tetap dapat diverifikasi.</p>
            </header>

            <section v-if="transactions.data.length" class="overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                <div v-for="transaction in transactions.data" :key="transaction.id" class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 last:border-0">
                    <div class="min-w-0"><Link :href="`/campaigns/${transaction.campaign_id}`" class="block truncate text-sm font-bold text-slate-900 hover:text-emerald-700">{{ transaction.campaign }}</Link><p class="mt-1 font-mono text-xs text-slate-500">{{ shortHash(transaction.transaction_hash) }}</p><p class="mt-1 text-xs text-slate-400">Dikirim {{ formatDate(transaction.created_at) }} · {{ transaction.block_number ? `Block #${transaction.block_number}` : 'Belum masuk block' }}</p></div>
                    <div class="flex items-center gap-3"><span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold" :class="transaction.status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : transaction.status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'"><IconCheck v-if="transaction.status === 'confirmed'" class="h-3.5 w-3.5" /><IconX v-else-if="transaction.status === 'failed'" class="h-3.5 w-3.5" /><IconClock v-else class="h-3.5 w-3.5" />{{ statusLabel[transaction.status] }}</span><a :href="`https://amoy.polygonscan.com/tx/${transaction.transaction_hash}`" target="_blank" rel="noreferrer" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50" title="Buka transaksi di block explorer"><IconExternalLink class="h-4 w-4" /></a></div>
                </div>
            </section>
            <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center"><IconLock class="mx-auto h-8 w-8 text-slate-300" /><p class="mt-3 text-sm text-slate-500">Belum ada riwayat donasi dari akun ini.</p><Link href="/campaigns" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Jelajahi kampanye <IconArrowRight class="h-4 w-4" /></Link></div>
        </div>
    </AppLayout>
</template>
