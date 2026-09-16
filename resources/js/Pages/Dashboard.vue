<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import {
    IconShieldCheck,
    IconHeartHandshake,
    IconReceipt,
    IconSearch,
    IconCopy,
    IconCheck,
    IconPlus,
    IconFlame,
    IconCoins,
    IconClock,
    IconArrowUpRight, 
    IconX,
    IconSparkles,
} from '@tabler/icons-vue';

interface User {
    name: string;
    email: string;
}

interface AdminBank {
    bank_name: string;
    bank_code: string;
    account_number: string;
    account_name: string;
    qris_image_path: string | null;
    instructions: string | null;
}

interface Transaction {
    id: string;
    hash: string;
    campaign: string;
    category: string;
    amount: number;
    paymentMethod: string;
    referenceCode: string;
    date: string;
    blockNumber: number;
    status: 'verified' | 'pending';
}

interface Campaign {
    id: string | number;
    title: string;
    category: string;
    target: number;
    collected: number;
    donorsCount: number;
    daysLeft: number;
    urgency: 'high' | 'medium';
}

const page = usePage<{
    auth: { user: User | null };
    admin_bank: AdminBank;
    campaigns: Campaign[];
    transactions: Transaction[];
}>();

const user = computed(() => page.props.auth.user);
const adminBank = computed(() => page.props.admin_bank || {
    bank_name: 'Bank Central Asia (BCA)',
    bank_code: 'BCA',
    account_number: '8830192841',
    account_name: 'Yayasan SafeGive Kebaikan Indonesia',
    qris_image_path: null,
    instructions: 'Mohon transfer sesuai nominal yang dipilih. Cantumkan kode referensi donasi pada berita transfer.',
});

// Interactive State
const activeFilter = ref<'all' | 'verified' | 'pending'>('all');
const searchQuery = ref('');
const copiedItem = ref<string | null>(null);
const isDonateModalOpen = ref(false);

// Modal state
const selectedCampaignId = ref(String(page.props.campaigns?.[0]?.id ?? ''));
const selectedAmount = ref<number>(100000);
const customAmount = ref<string>('');
const selectedMethod = ref('BCA');
const donorName = ref(user.value?.name || '');
const donorNote = ref('');
const isSubmittingTx = ref(false);
const txSuccessMessage = ref(false);

const paymentMethods = [
    'BCA',
    'Mandiri',
    'BRI',
    'BNI',
    'BSI',
    'QRIS',
    'GoPay',
    'DANA',
];

const campaigns = computed(() => page.props.campaigns ?? []);
const transactions = computed(() => page.props.transactions ?? []);

// Summary Stats
const totalDonationValue = computed(() => {
    return transactions.value.reduce((sum, tx) => sum + tx.amount, 0);
});

// Filtered Transactions
const filteredTransactions = computed(() => {
    return transactions.value.filter((tx) => {
        const matchesFilter =
            activeFilter.value === 'all' || tx.status === activeFilter.value;
        const matchesQuery =
            tx.campaign.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            tx.hash.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            tx.category.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            tx.paymentMethod.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesFilter && matchesQuery;
    });
});

// Format Rupiah
const formatRupiah = (val: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

// Truncate hash
const truncateHash = (hash: string): string => {
    if (!hash || hash.length < 14) return hash;
    return `${hash.slice(0, 8)}...${hash.slice(-6)}`;
};

// Copy to clipboard helper
const copyToClipboard = async (text: string, id: string) => {
    try {
        await navigator.clipboard.writeText(text);
        copiedItem.value = id;
        setTimeout(() => {
            if (copiedItem.value === id) {
                copiedItem.value = null;
            }
        }, 2000);
    } catch {
        copiedItem.value = id;
        setTimeout(() => {
            copiedItem.value = null;
        }, 2000);
    }
};

// Preset amounts
const presetAmounts = [50000, 100000, 250000, 500000, 1000000];

const setPresetAmount = (amt: number) => {
    selectedAmount.value = amt;
    customAmount.value = '';
};

const handleCustomAmountInput = (e: Event) => {
    const val = (e.target as HTMLInputElement).value.replace(/\D/g, '');
    customAmount.value = val;
    if (val) {
        selectedAmount.value = parseInt(val, 10);
    }
};

const submitDonation = () => {
    if (!selectedAmount.value || selectedAmount.value < 10000) return;

    router.post(`/campaigns/${selectedCampaignId.value}/donations`, {
        amount: selectedAmount.value,
        payment_method: selectedMethod.value,
        donor_name: donorName.value || undefined,
        donor_note: donorNote.value || undefined,
    }, {
        preserveScroll: true,
        onStart: () => {
            isSubmittingTx.value = true;
        },
        onSuccess: () => {
            txSuccessMessage.value = true;
            setTimeout(() => {
                txSuccessMessage.value = false;
                isDonateModalOpen.value = false;
                selectedAmount.value = 100000;
                customAmount.value = '';
                donorNote.value = '';
            }, 1800);
        },
        onFinish: () => {
            isSubmittingTx.value = false;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard - SafeGive Platform" />

        <div class="space-y-8">
            <!-- Hero Banner Section -->
            <section class="relative overflow-hidden rounded-[28px] border border-[#dce6d8] bg-gradient-to-br from-white via-[#fcfdfa] to-[#edf5ea] p-6 shadow-[0_16px_40px_rgba(40,60,35,0.06)] sm:p-10">
                <!-- Ambient circles -->
                <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 right-36 h-64 w-64 rounded-full bg-orange-100/60 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/60 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-800 shadow-sm">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <IconSparkles class="h-3.5 w-3.5 text-emerald-600" />
                            <span>Catatan Donasi Terlindungi</span>
                        </div>

                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span class="rounded-md bg-white px-2.5 py-1 font-mono border border-slate-200 text-slate-700">Bank Transfer / QRIS</span>
                            <span class="rounded-md bg-white px-2.5 py-1 font-mono border border-slate-200 text-slate-700">Hash & Enkripsi Aktif</span>
                        </div>
                    </div>

                    <div class="mt-6 max-w-3xl">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700 sm:text-sm">Platform Donasi Terpercaya</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-4xl lg:text-5xl leading-tight">
                            Selamat Datang, <span class="text-emerald-700">{{ user?.name || 'Sahabat Kebaikan' }}</span> 👋
                        </h1>
                        <p class="mt-3.5 text-sm sm:text-base leading-relaxed text-slate-600">
                            Transfer donasi dengan mudah melalui <strong class="text-slate-800 font-semibold">Bank BCA, Mandiri, BRI, BNI, atau E-Wallet / QRIS</strong> ke rekening resmi SafeGive. Setiap donasi dicatat di database dengan nominal terenkripsi dan bukti hash untuk menjaga integritas catatan.
                        </p>
                    </div>

                    <!-- Quick Action Buttons & Trust Badges -->
                    <div class="mt-8 flex flex-wrap items-center gap-3.5">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2.5 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 active:scale-[0.98]"
                            @click="isDonateModalOpen = true"
                        >
                            <IconPlus class="h-4 w-4" stroke-width="2.5" />
                            <span>Salurkan Donasi Cepat</span>
                        </button>

                        <a
                            href="#ledger-section"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#dce6d8] bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-[#edf4e9] hover:text-emerald-800"
                        >
                            <IconReceipt class="h-4 w-4 text-emerald-600" stroke-width="2" />
                            <span>Lihat Catatan Donasi</span>
                        </a>

                        <div class="ml-auto hidden sm:flex items-center gap-2 text-xs text-slate-500">
                            <IconShieldCheck class="h-4 w-4 text-emerald-600" />
                                    <span>Catatan transaksi dapat diverifikasi</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- KPI Summary Cards -->
            <section class="grid grid-cols-1 gap-4">
                <!-- Card 1: Total Kontribusi -->
                <div class="group relative overflow-hidden rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Donasi Ditampilkan</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100/80 text-emerald-700">
                            <IconCoins class="h-5 w-5" stroke-width="2" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900">{{ formatRupiah(totalDonationValue) }}</p>
                        <div class="mt-1.5 text-xs font-semibold text-slate-500">
                            Dari riwayat donasi yang ditampilkan
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Metode Pembayaran:</span>
                        <span class="font-semibold text-slate-600">Bank, QRIS & E-Wallet</span>
                    </div>
                </div>

            </section>

            <!-- Main Split Layout -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Left Column: Transactions Ledger & Featured Campaigns (8 Cols) -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Protected donation records -->
                    <section id="ledger-section" class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-7">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <IconReceipt class="h-5 w-5 text-emerald-700" stroke-width="2" />
                                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Catatan Donasi</h2>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    Daftar transaksi donasi Anda dengan hash sebagai bukti integritas catatan.
                                </p>
                            </div>

                            <!-- Filter Tabs -->
                            <div class="flex items-center gap-1 rounded-xl bg-[#f3f6ef] p-1 text-xs font-semibold">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 transition"
                                    :class="activeFilter === 'all' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                    @click="activeFilter = 'all'"
                                >
                                    Semua ({{ transactions.length }})
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 transition"
                                    :class="activeFilter === 'verified' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                    @click="activeFilter = 'verified'"
                                >
                                    Terverifikasi
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 transition"
                                    :class="activeFilter === 'pending' ? 'bg-white text-amber-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                    @click="activeFilter = 'pending'"
                                >
                                    Menunggu
                                </button>
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="mt-4 flex items-center gap-3">
                            <div class="relative flex-1">
                                <IconSearch class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari berdasarkan program donasi, metode bank, atau hash..."
                                    class="w-full rounded-xl border border-[#dce6d8] bg-[#fdfefc] pl-10 pr-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead>
                                    <tr class="border-b border-[#e9efe6] text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                        <th class="pb-3 pl-2">Program Donasi</th>
                                        <th class="pb-3">Metode & Referensi</th>
                                        <th class="pb-3">Bukti Hash</th>
                                        <th class="pb-3 text-right">Nominal</th>
                                        <th class="pb-3 text-center">Status</th>
                                        <th class="pb-3 pr-2 text-right">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="tx in filteredTransactions"
                                        :key="tx.id"
                                        class="group transition hover:bg-[#f8faf6]"
                                    >
                                        <td class="py-3.5 pl-2">
                                            <p class="font-bold text-slate-900 group-hover:text-emerald-800">{{ tx.campaign }}</p>
                                            <span class="mt-0.5 inline-block rounded-md bg-[#edf4e9] px-2 py-0.5 text-[10px] font-semibold text-emerald-800">
                                                {{ tx.category }}
                                            </span>
                                        </td>

                                        <td class="py-3.5">
                                            <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-800 uppercase">
                                                {{ tx.paymentMethod }}
                                            </span>
                                            <p class="font-mono text-[10px] text-slate-500 mt-0.5">{{ tx.referenceCode }}</p>
                                        </td>

                                        <td class="py-3.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono text-[11px] font-medium text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                                                    {{ truncateHash(tx.hash) }}
                                                </span>
                                                <button
                                                    type="button"
                                                    :title="copiedItem === tx.id ? 'Tersalin!' : 'Salin Hash Transaksi'"
                                                    class="rounded p-1 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                                                    @click="copyToClipboard(tx.hash, tx.id)"
                                                >
                                                    <IconCheck v-if="copiedItem === tx.id" class="h-3.5 w-3.5 text-emerald-600" />
                                                    <IconCopy v-else class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-mono">No. catatan #{{ tx.blockNumber }}</span>
                                        </td>

                                        <td class="py-3.5 text-right">
                                            <p class="font-bold text-slate-900">{{ formatRupiah(tx.amount) }}</p>
                                        </td>

                                        <td class="py-3.5 text-center">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                                :class="tx.status === 'verified' ? 'bg-emerald-100/70 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full" :class="tx.status === 'verified' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                                {{ tx.status === 'verified' ? 'Terverifikasi' : 'Menunggu konfirmasi' }}
                                            </span>
                                        </td>

                                        <td class="py-3.5 pr-2 text-right text-slate-500 text-[11px] whitespace-nowrap">
                                            {{ tx.date }}
                                        </td>
                                    </tr>

                                    <tr v-if="filteredTransactions.length === 0">
                                        <td colspan="6" class="py-8 text-center text-slate-400">
                                            Tidak ada riwayat donasi yang sesuai dengan pencarian Anda.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Urgent Causes / Active Campaigns -->
                    <section class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <IconFlame class="h-5 w-5 text-[#c65d3d]" />
                                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Kampanye Mendesak Pilihan</h2>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Salurkan bantuanmu secara instan ke program terverifikasi audit publik.</p>
                            </div>

                            <Link
                                href="/campaigns"
                                class="hidden text-xs font-bold text-emerald-700 hover:text-emerald-800 sm:block"
                            >
                                Jelajah Semua &rarr;
                            </Link>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div
                                v-for="camp in campaigns.slice(0, 2)"
                                :key="camp.id"
                                class="group flex flex-col justify-between rounded-2xl border border-[#dce6d8] bg-[#fcfdfa] p-5 transition hover:border-emerald-300 hover:shadow-md"
                            >
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="rounded-lg bg-[#edf4e9] px-2.5 py-1 text-[11px] font-bold text-emerald-800">
                                            {{ camp.category }}
                                        </span>
                                        <span class="flex items-center gap-1 text-[11px] font-semibold text-slate-400">
                                            <IconClock class="h-3.5 w-3.5" />
                                            Sisa {{ camp.daysLeft }} hari
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-sm font-bold text-slate-900 leading-snug group-hover:text-emerald-700">
                                        {{ camp.title }}
                                    </h3>

                                    <!-- Progress Bar -->
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between text-xs font-semibold">
                                            <span class="text-slate-500">Terkumpul: <strong class="text-slate-800">{{ formatRupiah(camp.collected) }}</strong></span>
                                            <span class="text-emerald-700 font-bold">
                                                {{ Math.round((camp.collected / camp.target) * 100) }}%
                                            </span>
                                        </div>
                                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full bg-emerald-600 transition-all duration-500"
                                                :style="{ width: `${Math.min(100, Math.round((camp.collected / camp.target) * 100))}%` }"
                                            ></div>
                                        </div>
                                        <div class="mt-1.5 flex justify-between text-[11px] text-slate-400">
                                            <span>{{ camp.donorsCount }} Dermawan</span>
                                            <span>Target: {{ formatRupiah(camp.target) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                                        title="Salin link kampanye"
                                        @click="copyToClipboard(`${$page.props.app_url || window.location.origin}/campaigns/${camp.id}`, `camp-${camp.id}`)"
                                    >
                                        <IconCheck v-if="copiedItem === `camp-${camp.id}`" class="h-3.5 w-3.5 text-emerald-600" />
                                        <IconCopy v-else class="h-3.5 w-3.5 text-slate-500" />
                                        <span>{{ copiedItem === `camp-${camp.id}` ? 'Tersalin' : 'Salin' }}</span>
                                    </button>

                                    <Link
                                        :href="`/campaigns/${camp.id}`"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100"
                                    >
                                        <span>Donasi Sekarang</span>
                                        <IconArrowUpRight class="h-3.5 w-3.5" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Record integrity -->
                <div class="space-y-6 lg:col-span-4">
                    <!-- Record integrity -->
                    <div class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <IconShieldCheck class="h-4 w-4 text-emerald-700" />
                            <span>Perlindungan Catatan</span>
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">Setiap donasi disimpan dengan perlindungan enkripsi dan hash.</p>

                        <div class="mt-4 space-y-3.5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#edf4e9] text-emerald-800 font-bold text-xs">
                                    1
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Rekening resmi</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500 leading-relaxed">Pembayaran diarahkan ke rekening resmi yang dikelola admin.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#edf4e9] text-emerald-800 font-bold text-xs">
                                    2
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Nominal terenkripsi</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500 leading-relaxed">Nominal donasi disimpan dalam bentuk terenkripsi untuk menjaga kerahasiaan.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#edf4e9] text-emerald-800 font-bold text-xs">
                                    3
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Bukti hash</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500 leading-relaxed">Hash membantu memeriksa bahwa catatan transaksi tidak berubah.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Audit Activity -->
                    <div class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Transaksi Terbaru</span>
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                        </div>

                        <div v-if="transactions.length > 0" class="mt-4 space-y-3 font-mono text-[11px]">
                            <div class="rounded-xl bg-[#f8faf6] p-2.5 border border-[#e8f0e5]">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>{{ transactions[0].date }}</span>
                                    <span class="text-emerald-700 font-semibold">{{ transactions[0].status === 'verified' ? 'Terkonfirmasi' : 'Menunggu' }}</span>
                                </div>
                                <p class="mt-1 text-slate-800 font-medium">{{ transactions[0].campaign }}</p>
                                <p class="mt-1 text-slate-500">Hash {{ truncateHash(transactions[0].hash) }}</p>
                            </div>

                            <div class="rounded-xl bg-[#f8faf6] p-2.5 border border-[#e8f0e5]">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>Metode pembayaran</span>
                                    <span class="text-slate-400">{{ transactions[0].paymentMethod }}</span>
                                </div>
                                <p class="mt-1 text-slate-800 font-medium">Referensi {{ transactions[0].referenceCode }}</p>
                            </div>
                        </div>
                        <p v-else class="mt-4 rounded-xl bg-[#f8faf6] p-4 text-xs text-slate-500">Belum ada transaksi donasi.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Donation Interactive Modal -->
        <div
            v-if="isDonateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm transition"
        >
            <div
                class="w-full max-w-lg overflow-hidden rounded-[28px] border border-[#dce6d8] bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-200"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800">
                            <IconHeartHandshake class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Salurkan Donasi Cepat</h3>
                            <p class="text-xs text-slate-500">Transfer ke Rekening Resmi SafeGive</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                        @click="isDonateModalOpen = false"
                    >
                        <IconX class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    <div
                        v-if="txSuccessMessage"
                        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center text-emerald-900"
                    >
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-white shadow-md">
                            <IconCheck class="h-6 w-6" stroke-width="2.5" />
                        </div>
                        <h4 class="mt-2 text-sm font-bold">Donasi Berhasil Dicatat!</h4>
                        <p class="mt-1 text-xs text-emerald-700">Catatan donasi tersimpan dan bukti hash telah dibuat.</p>
                    </div>

                    <template v-else>
                        <!-- Central Bank Info Mini Card -->
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-xs">
                            <div class="flex items-center justify-between font-bold text-emerald-900">
                                <span>{{ adminBank.bank_name }}</span>
                                <span class="rounded bg-emerald-200/80 px-2 py-0.5 text-[10px]">{{ adminBank.bank_code }}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between font-mono font-black text-slate-900 text-sm">
                                <span>{{ adminBank.account_number }}</span>
                                <button
                                    type="button"
                                    class="text-[11px] font-sans font-bold text-emerald-800 hover:underline"
                                    @click="copyToClipboard(adminBank.account_number, 'modal-bank')"
                                >
                                    {{ copiedItem === 'modal-bank' ? 'Tersalin!' : 'Salin Rekening' }}
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-0.5">a.n. {{ adminBank.account_name }}</p>
                        </div>

                        <!-- Select Campaign -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                Pilih Program Donasi
                            </label>
                            <select
                                v-model="selectedCampaignId"
                                class="w-full rounded-xl border border-[#dce6d8] bg-[#fcfdfa] px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option v-for="c in campaigns" :key="c.id" :value="String(c.id)">
                                    {{ c.title }} ({{ c.category }})
                                </option>
                            </select>
                        </div>

                        <!-- Nominal Selector -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                                Nominal Donasi
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    v-for="amt in presetAmounts"
                                    :key="amt"
                                    type="button"
                                    class="rounded-xl border py-2 text-xs font-bold transition text-center"
                                    :class="
                                        selectedAmount === amt && !customAmount
                                            ? 'border-emerald-600 bg-emerald-50 text-emerald-800 shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-200 hover:bg-[#f8faf6]'
                                    "
                                    @click="setPresetAmount(amt)"
                                >
                                    {{ formatRupiah(amt) }}
                                </button>
                            </div>

                            <div class="mt-2.5 relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input
                                    :value="customAmount"
                                    type="text"
                                    placeholder="Atau masukkan nominal lainnya..."
                                    class="w-full rounded-xl border border-[#dce6d8] bg-[#fcfdfa] pl-10 pr-4 py-2 text-xs font-semibold text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    @input="handleCustomAmountInput"
                                />
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                Saluran Transfer
                            </label>
                            <select
                                v-model="selectedMethod"
                                class="w-full rounded-xl border border-[#dce6d8] bg-[#fcfdfa] px-3.5 py-2 text-xs font-semibold text-slate-800 focus:border-emerald-500 focus:outline-none"
                            >
                                <option v-for="m in paymentMethods" :key="m" :value="m">
                                    {{ m }}
                                </option>
                            </select>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div v-if="!txSuccessMessage" class="flex items-center justify-end gap-2 border-t border-slate-100 bg-[#fafbfa] px-6 py-4">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-100"
                        @click="isDonateModalOpen = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="isSubmittingTx || selectedAmount < 10000"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-emerald-800 disabled:opacity-50"
                        @click="submitDonation"
                    >
                        <span v-if="isSubmittingTx" class="flex items-center gap-2">
                            <span class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            Mengenkripsi Paillier...
                        </span>
                        <span v-else class="flex items-center gap-1.5">
                            <IconHeartHandshake class="h-4 w-4" />
                            Konfirmasi Transfer ({{ formatRupiah(selectedAmount) }})
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>