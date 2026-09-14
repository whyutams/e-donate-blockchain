<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import {
    IconShieldCheck,
    IconHeartHandshake,
    IconWallet,
    IconReceipt,
    IconUsers,
    IconTrendingUp,
    IconSearch,
    IconCopy,
    IconCheck,
    IconPlus,
    IconFlame,
    IconCoins,
    IconClock,
    IconLock,
    IconArrowUpRight,
    IconX,
    IconSparkles,
} from '@tabler/icons-vue';

interface User {
    name: string;
    email: string;
}

interface Transaction {
    id: string;
    hash: string;
    campaign: string;
    category: string;
    amount: number;
    amountEth: string;
    date: string;
    blockNumber: number;
    status: 'verified' | 'pending';
}

interface Campaign {
    id: string;
    title: string;
    category: string;
    target: number;
    collected: number;
    donorsCount: number;
    daysLeft: number;
    urgency: 'high' | 'medium';
}

const page = usePage<{ auth: { user: User | null } }>();
const user = computed(() => page.props.auth.user);

// Interactive State
const activeFilter = ref<'all' | 'verified' | 'pending'>('all');
const searchQuery = ref('');
const copiedItem = ref<string | null>(null);
const isDonateModalOpen = ref(false);

// Modal state
const selectedCampaignId = ref('camp-1');
const selectedAmount = ref<number>(100000);
const customAmount = ref<string>('');
const donorNote = ref('');
const isSubmittingTx = ref(false);
const txSuccessMessage = ref(false);

const campaigns = ref<Campaign[]>([
    {
        id: 'camp-1',
        title: 'Bantuan Medis Darurat Korban Gempa & Bencana',
        category: 'Kemanusiaan',
        target: 150000000,
        collected: 118450000,
        donorsCount: 428,
        daysLeft: 5,
        urgency: 'high',
    },
    {
        id: 'camp-2',
        title: 'Beasiswa Pendidikan & Laptop Siswa Berprestasi',
        category: 'Pendidikan',
        target: 85000000,
        collected: 62300000,
        donorsCount: 215,
        daysLeft: 12,
        urgency: 'medium',
    },
    {
        id: 'camp-3',
        title: 'Pembangunan Sumber Air Bersih Wilayah Pelosok',
        category: 'Infrastruktur',
        target: 120000000,
        collected: 98000000,
        donorsCount: 340,
        daysLeft: 8,
        urgency: 'high',
    },
]);

const transactions = ref<Transaction[]>([
    {
        id: 'tx-001',
        hash: '0x8f192b49c71a39d891b0f1a92e10a2f4',
        campaign: 'Bantuan Medis Darurat Korban Gempa & Bencana',
        category: 'Kemanusiaan',
        amount: 250000,
        amountEth: '0.0068 ETH',
        date: '14 Menit yang lalu',
        blockNumber: 19842109,
        status: 'verified',
    },
    {
        id: 'tx-002',
        hash: '0x4c278a1f81d2937e0c4b2e8174df8342',
        campaign: 'Beasiswa Pendidikan & Laptop Siswa Berprestasi',
        category: 'Pendidikan',
        amount: 500000,
        amountEth: '0.0135 ETH',
        date: '2 Jam yang lalu',
        blockNumber: 19841850,
        status: 'verified',
    },
    {
        id: 'tx-003',
        hash: '0x99a4e21b8c1945a0b73c4d119e34a781',
        campaign: 'Pembangunan Sumber Air Bersih Wilayah Pelosok',
        category: 'Infrastruktur',
        amount: 100000,
        amountEth: '0.0027 ETH',
        date: 'Kemarin, 19:42',
        blockNumber: 19839401,
        status: 'verified',
    },
    {
        id: 'tx-004',
        hash: '0x3e18a902f4cb71369d12a9e8841029c5',
        campaign: 'Bantuan Medis Darurat Korban Gempa & Bencana',
        category: 'Kemanusiaan',
        amount: 150000,
        amountEth: '0.0041 ETH',
        date: '12 Sep 2026',
        blockNumber: 19834112,
        status: 'verified',
    },
    {
        id: 'tx-005',
        hash: '0x7b54a1082c9e421a8f6d3391b0e51249',
        campaign: 'Penanaman 1.000 Pohon Mangrove Pesisir',
        category: 'Lingkungan',
        amount: 75000,
        amountEth: '0.0020 ETH',
        date: '10 Sep 2026',
        blockNumber: 19828941,
        status: 'verified',
    },
]);

// Summary Stats
const totalDonationValue = computed(() => {
    return transactions.value.reduce((sum, tx) => sum + tx.amount, 0);
});

const verifiedTransactionsCount = computed(() => {
    return transactions.value.filter((tx) => tx.status === 'verified').length;
});

// Filtered Transactions
const filteredTransactions = computed(() => {
    return transactions.value.filter((tx) => {
        const matchesFilter =
            activeFilter.value === 'all' || tx.status === activeFilter.value;
        const matchesQuery =
            tx.campaign.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            tx.hash.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            tx.category.toLowerCase().includes(searchQuery.value.toLowerCase());
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

// Submit Simulation
const submitDonation = () => {
    if (!selectedAmount.value || selectedAmount.value < 10000) return;

    isSubmittingTx.value = true;
    const targetCamp = campaigns.value.find((c) => c.id === selectedCampaignId.value) || campaigns.value[0];

    setTimeout(() => {
        const randomHex = Array.from({ length: 32 }, () =>
            Math.floor(Math.random() * 16).toString(16)
        ).join('');
        const newTxHash = `0x${randomHex}`;
        const newBlock = 19842110 + Math.floor(Math.random() * 20);

        const newTx: Transaction = {
            id: `tx-${Date.now()}`,
            hash: newTxHash,
            campaign: targetCamp.title,
            category: targetCamp.category,
            amount: selectedAmount.value,
            amountEth: `${(selectedAmount.value / 37000000).toFixed(4)} ETH`,
            date: 'Baru saja',
            blockNumber: newBlock,
            status: 'verified',
        };

        transactions.value.unshift(newTx);
        targetCamp.collected += selectedAmount.value;
        targetCamp.donorsCount += 1;

        isSubmittingTx.value = false;
        txSuccessMessage.value = true;

        setTimeout(() => {
            txSuccessMessage.value = false;
            isDonateModalOpen.value = false;
            selectedAmount.value = 100000;
            customAmount.value = '';
            donorNote.value = '';
        }, 1800);
    }, 1200);
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard - SafeGive Blockchain" />

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
                            <span>Smart Contract SafeGive v1.0 Aktif</span>
                        </div>

                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span class="rounded-md bg-white px-2.5 py-1 font-mono border border-slate-200 text-slate-700">Polygon Amoy</span>
                            <span class="rounded-md bg-white px-2.5 py-1 font-mono border border-slate-200 text-slate-700">Block #19,842,109</span>
                        </div>
                    </div>

                    <div class="mt-6 max-w-3xl">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700 sm:text-sm">Ruang Kebaikan Transparan</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-4xl lg:text-5xl leading-tight">
                            Selamat Datang, <span class="text-emerald-700">{{ user?.name || 'Sahabat Kebaikan' }}</span> 👋
                        </h1>
                        <p class="mt-3.5 text-sm sm:text-base leading-relaxed text-slate-600">
                            Setiap rupiah yang kamu salurkan tercatat permanen di <strong class="text-slate-800 font-semibold">buku besar blockchain terdesentralisasi</strong>. Transparan, aman, tanpa perantara manipulatif, dan berdampak langsung bagi yang membutuhkan.
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
                            <span>Cek Ledger On-Chain</span>
                        </a>

                        <div class="ml-auto hidden sm:flex items-center gap-2 text-xs text-slate-500">
                            <IconShieldCheck class="h-4 w-4 text-emerald-600" />
                            <span>100% On-Chain Auditability</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- KPI Summary Cards -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Card 1: Total Kontribusi -->
                <div class="group relative overflow-hidden rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Donasi Anda</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100/80 text-emerald-700">
                            <IconCoins class="h-5 w-5" stroke-width="2" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900">{{ formatRupiah(totalDonationValue) }}</p>
                        <div class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <IconTrendingUp class="h-3.5 w-3.5" />
                            <span>+16.4% dari bulan lalu</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Estimasi Kripto:</span>
                        <span class="font-mono font-bold text-slate-600">~0.0301 ETH</span>
                    </div>
                </div>

                <!-- Card 2: Transaksi On-Chain -->
                <div class="group relative overflow-hidden rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Transaksi Valid</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100/80 text-teal-700">
                            <IconShieldCheck class="h-5 w-5" stroke-width="2" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900">{{ verifiedTransactionsCount }} <span class="text-sm font-semibold text-slate-500">Hash</span></p>
                        <div class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <IconCheck class="h-3.5 w-3.5" stroke-width="2.5" />
                            <span>100% Konsensus Node Lulus</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Konsensus:</span>
                        <span class="font-mono font-bold text-slate-600">Proof-of-Stake</span>
                    </div>
                </div>

                <!-- Card 3: Kampanye Didukung -->
                <div class="group relative overflow-hidden rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kampanye Diikuti</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100/80 text-amber-700">
                            <IconHeartHandshake class="h-5 w-5" stroke-width="2" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900">3 <span class="text-sm font-semibold text-slate-500">Program</span></p>
                        <div class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                            <IconClock class="h-3.5 w-3.5" />
                            <span>2 Kampanye Mendekati Target</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Kategori Utama:</span>
                        <span class="font-semibold text-slate-600">Medis & Edukasi</span>
                    </div>
                </div>

                <!-- Card 4: Jiwa Berdampak -->
                <div class="group relative overflow-hidden rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Dampak Kebaikan</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff0e9] text-[#c65d3d]">
                            <IconUsers class="h-5 w-5" stroke-width="2" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900">1.420+ <span class="text-sm font-semibold text-slate-500">Jiwa</span></p>
                        <div class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-[#c65d3d]">
                            <IconSparkles class="h-3.5 w-3.5" />
                            <span>Penerima Manfaat Langsung</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Laporan Terakhir:</span>
                        <span class="font-semibold text-slate-600">14 Sep 2026</span>
                    </div>
                </div>
            </section>

            <!-- Main Split Layout -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Left Column: Transactions Ledger & Featured Campaigns (8 Cols) -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Blockchain Ledger Table Section -->
                    <section id="ledger-section" class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-7">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <IconReceipt class="h-5 w-5 text-emerald-700" stroke-width="2" />
                                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Ledger Transparansi Donasi</h2>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Daftar transaksi on-chain tercatat secara kronologis di jaringan blockchain.</p>
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
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="mt-4 flex items-center gap-3">
                            <div class="relative flex-1">
                                <IconSearch class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari berdasarkan program donasi, kategori, atau hash..."
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
                                        <th class="pb-3">Hash Transaksi (On-Chain)</th>
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
                                            <span class="text-[10px] text-slate-400 font-mono">Block #{{ tx.blockNumber }}</span>
                                        </td>

                                        <td class="py-3.5 text-right">
                                            <p class="font-bold text-slate-900">{{ formatRupiah(tx.amount) }}</p>
                                            <p class="font-mono text-[10px] text-slate-400">{{ tx.amountEth }}</p>
                                        </td>

                                        <td class="py-3.5 text-center">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100/70 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Terverifikasi
                                            </span>
                                        </td>

                                        <td class="py-3.5 pr-2 text-right text-slate-500 text-[11px] whitespace-nowrap">
                                            {{ tx.date }}
                                        </td>
                                    </tr>

                                    <tr v-if="filteredTransactions.length === 0">
                                        <td colspan="5" class="py-8 text-center text-slate-400">
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

                            <button
                                type="button"
                                class="hidden text-xs font-bold text-emerald-700 hover:text-emerald-800 sm:block"
                                @click="isDonateModalOpen = true"
                            >
                                Jelajah Semua &rarr;
                            </button>
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

                                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[11px] font-mono text-slate-400">Smart Contract Escrow</span>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100"
                                        @click="selectedCampaignId = camp.id; isDonateModalOpen = true"
                                    >
                                        <span>Bantu Sekarang</span>
                                        <IconArrowUpRight class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Web3 Identity Card, Integrity Pillars, & Network Status (4 Cols) -->
                <div class="space-y-6 lg:col-span-4">
                    <!-- Web3 Identity & Smart Wallet Card -->
                    <div class="relative overflow-hidden rounded-[24px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 text-white shadow-xl">
                        <!-- Glow effect -->
                        <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-emerald-500/20 blur-2xl"></div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <IconWallet class="h-4 w-4" />
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Dompet Kebaikan</span>
                            </div>
                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-400 border border-emerald-500/20">
                                On-Chain
                            </span>
                        </div>

                        <div class="mt-5">
                            <p class="text-xs text-slate-400">Alamat Smart Contract / Wallet:</p>
                            <div class="mt-1.5 flex items-center justify-between rounded-xl bg-slate-800/80 px-3 py-2 border border-slate-700/60">
                                <span class="font-mono text-xs text-slate-200">0x71C...4e89</span>
                                <button
                                    type="button"
                                    :title="copiedItem === 'wallet' ? 'Tersalin!' : 'Salin Alamat'"
                                    class="rounded p-1 text-slate-400 hover:text-white transition"
                                    @click="copyToClipboard('0x71C283F67a1290Bc19f65B9e0c524e89', 'wallet')"
                                >
                                    <IconCheck v-if="copiedItem === 'wallet'" class="h-3.5 w-3.5 text-emerald-400" />
                                    <IconCopy v-else class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-800 pt-4 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase tracking-wide">Jaringan</span>
                                <p class="mt-0.5 font-bold text-slate-200">Polygon Amoy</p>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase tracking-wide">Kecepatan Blok</span>
                                <p class="mt-0.5 font-bold text-emerald-400">~2.1 Detik</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl bg-slate-800/40 p-3 border border-slate-800 text-[11px] text-slate-300 flex items-start gap-2">
                            <IconLock class="h-4 w-4 text-emerald-400 shrink-0 mt-0.5" />
                            <span>Kriptografi asimetris menjamin transparansi tanpa risiko manipulasi riwayat donasi.</span>
                        </div>
                    </div>

                    <!-- Blockchain Integrity Pillars -->
                    <div class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <IconShieldCheck class="h-4 w-4 text-emerald-700" />
                            <span>Pilar Integritas SafeGive</span>
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">Mengapa donasi blockchain lebih aman dan transparan?</p>

                        <div class="mt-4 space-y-3.5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#edf4e9] text-emerald-800 font-bold text-xs">
                                    1
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Catatan Abadi (Immutable)</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500 leading-relaxed">Riwayat transaksi tidak dapat dihapus atau dipalsukan oleh siapapun.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#edf4e9] text-emerald-800 font-bold text-xs">
                                    2
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Milestone Smart Contract</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500 leading-relaxed">Dana hanya dicairkan sesuai bukti capaian dan verifikasi lapangan.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#edf4e9] text-emerald-800 font-bold text-xs">
                                    3
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Tanpa Potongan Tersembunyi</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500 leading-relaxed">Setiap saldo dialokasikan 100% untuk penerima manfaat.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Node Status & Audit Log -->
                    <div class="rounded-[24px] border border-[#dce6d8] bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Aktivitas Ledger Terkini</span>
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                        </div>

                        <div class="mt-4 space-y-3 font-mono text-[11px]">
                            <div class="rounded-xl bg-[#f8faf6] p-2.5 border border-[#e8f0e5]">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>Sync Node #1</span>
                                    <span class="text-emerald-700 font-semibold">12ms latency</span>
                                </div>
                                <p class="mt-1 text-slate-800 font-medium">Smart contract verifikasi blok #19842109 sukses</p>
                            </div>

                            <div class="rounded-xl bg-[#f8faf6] p-2.5 border border-[#e8f0e5]">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>Penyaluran Tahap 1</span>
                                    <span class="text-slate-400">1 jam lalu</span>
                                </div>
                                <p class="mt-1 text-slate-800 font-medium">Pencairan logistik medis Rp 45.000.000 tervalidasi</p>
                            </div>
                        </div>
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
                            <p class="text-xs text-slate-500">Tercatat di Smart Contract SafeGive</p>
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
                    <!-- Success Notification inside Modal -->
                    <div
                        v-if="txSuccessMessage"
                        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center text-emerald-900"
                    >
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-white shadow-md">
                            <IconCheck class="h-6 w-6" stroke-width="2.5" />
                        </div>
                        <h4 class="mt-2 text-sm font-bold">Donasi Berhasil Dicatat ke Blockchain!</h4>
                        <p class="mt-1 text-xs text-emerald-700">Hash transaksi telah dihasilkan dan diverifikasi dalam antrean blok.</p>
                    </div>

                    <template v-else>
                        <!-- Select Campaign -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                Pilih Program Donasi
                            </label>
                            <select
                                v-model="selectedCampaignId"
                                class="w-full rounded-xl border border-[#dce6d8] bg-[#fcfdfa] px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option v-for="c in campaigns" :key="c.id" :value="c.id">
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
                                    class="rounded-xl border py-2.5 text-xs font-bold transition text-center"
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

                            <!-- Custom Amount Input -->
                            <div class="mt-3 relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input
                                    :value="customAmount"
                                    type="text"
                                    placeholder="Atau masukkan nominal lainnya..."
                                    class="w-full rounded-xl border border-[#dce6d8] bg-[#fcfdfa] pl-10 pr-4 py-2.5 text-xs font-semibold text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    @input="handleCustomAmountInput"
                                />
                            </div>
                        </div>

                        <!-- Donor Note -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                Doa / Catatan Kebaikan (Opsional)
                            </label>
                            <textarea
                                v-model="donorNote"
                                rows="2"
                                placeholder="Tuliskan harapan atau doa terbaik untuk penerima manfaat..."
                                class="w-full rounded-xl border border-[#dce6d8] bg-[#fcfdfa] p-3 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <!-- Blockchain Gas & Fee Summary -->
                        <div class="rounded-xl bg-[#f3f6ef] p-3 text-xs text-slate-600 space-y-1.5">
                            <div class="flex justify-between">
                                <span>Estimasi Biaya Gas Jaringan:</span>
                                <span class="font-bold text-emerald-700 font-mono">Gratis (Ditanggung SafeGive)</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Verifikasi On-Chain:</span>
                                <span class="font-bold text-slate-800">Instan (~2 detik)</span>
                            </div>
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
                            Memvalidasi di Blockchain...
                        </span>
                        <span v-else class="flex items-center gap-1.5">
                            <IconHeartHandshake class="h-4 w-4" />
                            Konfirmasi Donasi ({{ formatRupiah(selectedAmount) }})
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>