<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import {
    IconArrowLeft,
    IconBuildingBank,
    IconCalendar,
    IconCheck,
    IconClock,
    IconCopy,
    IconHeartHandshake,
    IconLock,
    IconQrcode,
    IconReceipt,
    IconShieldCheck,
    IconUpload,
    IconX,
    IconAlertCircle,
    IconCash,
} from '@tabler/icons-vue';

interface Donation {
    id: number;
    donor_name: string;
    payment_method: string;
    reference_code: string | null;
    payment_proof_url: string | null;
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
    payout_bank_name: string | null;
    payout_account_number: string | null;
    payout_account_name: string | null;
    withdrawal_status: 'not_ready' | 'pending' | 'confirmed' | 'failed';
    withdrawal_transaction_hash: string | null;
    can_withdraw: boolean;
    withdrawal_message: string;
    organizer: { id?: number; name: string };
    donations: Donation[];
}

interface AdminBank {
    bank_name: string;
    bank_code: string;
    account_number: string;
    account_name: string;
    qris_image_path: string | null;
    instructions: string | null;
}

const props = defineProps<{ campaign: Campaign }>();
const page = usePage<{
    admin_bank: AdminBank;
    auth: { user: { name: string; email: string } | null; is_admin: boolean };
    flash: { status?: string };
}>();

const adminBank = computed(() => page.props.admin_bank || {
    bank_name: 'Bank Central Asia (BCA)',
    bank_code: 'BCA',
    account_number: '8830192841',
    account_name: 'Yayasan SafeGive Kebaikan Indonesia',
    qris_image_path: null,
    instructions: 'Mohon transfer sesuai nominal yang dipilih. Cantumkan kode referensi donasi pada berita transfer.',
});

// Donation Form
const form = useForm({
    amount: '100000',
    payment_method: 'BCA',
    donor_name: page.props.auth.user?.name || '',
    is_anonymous: false,
    donor_email: page.props.auth.user?.email || '',
    donor_phone: '',
    donor_note: '',
    payment_proof: null as File | null,
});

const presetAmounts = [25000, 50000, 100000, 250000, 500000, 1000000];
const setAmount = (val: number) => {
    form.amount = val.toString();
};

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

const copiedBank = ref(false);
const copyAdminAccount = () => {
    navigator.clipboard.writeText(adminBank.value.account_number);
    copiedBank.value = true;
    setTimeout(() => {
        copiedBank.value = false;
    }, 2000);
};

const copiedHash = ref<string | null>(null);
const copyHash = (hash: string) => {
    navigator.clipboard.writeText(hash);
    copiedHash.value = hash;
    setTimeout(() => {
        if (copiedHash.value === hash) copiedHash.value = null;
    }, 2000);
};

const formatRupiah = (value: number) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const formatDate = (value: string) => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
}).format(new Date(value));

const shortHash = (hash: string) => `${hash.slice(0, 8)}...${hash.slice(-6)}`;

const submitDonation = () => {
    form.post(`/campaigns/${props.campaign.id}/donations`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.donor_note = '';
            form.payment_proof = null;
        },
    });
};

declare global {
    interface Window {
        snap?: {
            pay: (token: string, options?: any) => void;
        };
    }
}

const paymentType = ref<'midtrans' | 'manual'>('midtrans');
const isMidtransProcessing = ref(false);
const midtransError = ref<string | null>(null);
const midtransSuccess = ref<string | null>(null);

const payWithMidtrans = async () => {
    midtransError.value = null;
    midtransSuccess.value = null;
    isMidtransProcessing.value = true;

    try {
        const getCookie = (name: string) => {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return decodeURIComponent(parts.pop()!.split(';').shift()!);
            return '';
        };

        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || getCookie('XSRF-TOKEN');

        const response = await fetch(`/campaigns/${props.campaign.id}/donations/snap`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                amount: parseInt(form.amount, 10),
                donor_name: form.donor_name,
                donor_email: form.donor_email,
                donor_phone: form.donor_phone,
                donor_note: form.donor_note,
            }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Gagal memproses sesi pembayaran Midtrans.');
        }

        if (window.snap && typeof window.snap.pay === 'function') {
            window.snap.pay(data.snap_token, {
                onSuccess: async (result: any) => {
                    await fetch(`/donations/${data.donation_id}/sync-midtrans`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-XSRF-TOKEN': csrfToken,
                        },
                    });
                    midtransSuccess.value = 'Pembayaran donasi via Midtrans berhasil! Data telah dicatat ke ledger kriptografi.';
                    window.location.reload();
                },
                onPending: (result: any) => {
                    midtransSuccess.value = `Menunggu pembayaran diselesaikan. Kode referensi: ${data.reference_code}`;
                },
                onError: (result: any) => {
                    midtransError.value = 'Pembayaran dibatalkan atau gagal.';
                },
                onClose: () => {
                    // user closed popup
                },
            });
        } else if (data.snap_redirect_url) {
            window.location.href = data.snap_redirect_url;
        } else {
            throw new Error('SDK Midtrans Snap belum siap. Silakan muat ulang halaman.');
        }
    } catch (err: any) {
        midtransError.value = err.message || 'Terjadi kesalahan saat memanggil Midtrans.';
    } finally {
        isMidtransProcessing.value = false;
    }
};

// Withdrawal Modal for Organizer
const isWithdrawModalOpen = ref(false);
const withdrawForm = useForm({
    payout_bank_name: props.campaign.payout_bank_name || 'BCA',
    payout_account_number: props.campaign.payout_account_number || '',
    payout_account_name: props.campaign.payout_account_name || '',
    notes: '',
});

const submitWithdrawal = () => {
    withdrawForm.post(`/campaigns/${props.campaign.id}/withdraw`, {
        preserveScroll: true,
        onSuccess: () => {
            isWithdrawModalOpen.value = false;
        },
    });
};

const isOrganizer = computed(() => {
    return page.props.auth.user?.name === props.campaign.organizer.name;
});
</script>

<template>
    <AppLayout>
        <Head :title="`${campaign.title} - SafeGive`" />

        <div class="space-y-7">
            <Link href="/campaigns" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-emerald-700">
                <IconArrowLeft class="h-4 w-4" />
                Kembali ke daftar kampanye
            </Link>

            <!-- Flash Message -->
            <div
                v-if="page.props.flash?.status"
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-sm"
            >
                <IconCheck class="h-5 w-5 text-emerald-700 shrink-0" />
                <span>{{ page.props.flash.status }}</span>
            </div>

            <!-- Hero Section -->
            <section class="overflow-hidden rounded-3xl border border-[#dce6d8] bg-white shadow-sm">
                <div class="grid lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                    <div class="min-h-[280px] bg-[#edf4e9]">
                        <img
                            v-if="campaign.image_url"
                            :src="campaign.image_url"
                            :alt="campaign.title"
                            class="h-full min-h-[280px] w-full object-cover"
                        />
                        <div v-else class="flex h-full min-h-[280px] items-center justify-center text-emerald-700">
                            <IconHeartHandshake class="h-16 w-16 opacity-40" />
                        </div>
                    </div>
                    <div class="p-6 sm:p-8 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800">
                                    {{ campaign.category }}
                                </span>
                                <span
                                    v-if="campaign.progress_percentage >= 100"
                                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800"
                                >
                                    Target Tercapai
                                </span>
                            </div>

                            <h1 class="mt-3 text-2xl font-black leading-tight text-slate-900 sm:text-3xl">
                                {{ campaign.title }}
                            </h1>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                                {{ campaign.description }}
                            </p>
                            <p class="mt-4 text-xs font-semibold text-slate-500">
                                Penyelenggara: <strong class="text-slate-800">{{ campaign.organizer.name }}</strong>
                            </p>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-emerald-600 transition-all duration-500"
                                    :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }"
                                />
                            </div>
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="font-extrabold text-emerald-700">
                                    {{ campaign.progress_percentage.toFixed(2) }}% Tercapai
                                </span>
                                <span class="font-bold text-slate-700">
                                    Target {{ formatRupiah(campaign.target_amount) }}
                                </span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <IconCalendar class="h-4 w-4 text-slate-400" />
                                    Jatuh Tempo: {{ formatDate(campaign.ends_at) }}
                                </span>
                                <span>{{ campaign.donors_count }} Donatur Terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Organizer Withdrawal Section (Visible to Organizer or Admin) -->
            <section
                v-if="isOrganizer || page.props.auth.is_admin"
                class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50/70 via-white to-[#f4f9f2] p-6 shadow-sm sm:p-8"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-emerald-100 pb-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                            <IconCash class="h-6 w-6" stroke-width="2" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 tracking-tight">
                                Panel Pencairan Dana (Penyelenggara)
                            </h2>
                            <p class="text-xs text-slate-600">
                                Penyelenggara dapat mencairkan dana saat target telah terpenuhi (100%) atau masa kampanye telah melewati jatuh tempo.
                            </p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div>
                        <span
                            v-if="campaign.withdrawal_status === 'confirmed'"
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3.5 py-1.5 text-xs font-bold text-emerald-900 border border-emerald-300"
                        >
                            <IconShieldCheck class="h-4 w-4 text-emerald-700" />
                            Dana Telah Dicairkan
                        </span>
                        <span
                            v-else-if="campaign.withdrawal_status === 'pending'"
                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3.5 py-1.5 text-xs font-bold text-amber-900 border border-amber-300"
                        >
                            <IconClock class="h-4 w-4 text-amber-700" />
                            Pencairan Sedang Diproses Admin
                        </span>
                        <span
                            v-else-if="campaign.can_withdraw"
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3.5 py-1.5 text-xs font-bold text-emerald-900 border border-emerald-300"
                        >
                            <IconCheck class="h-4 w-4 text-emerald-700" />
                            Memenuhi Syarat Pencairan
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3.5 py-1.5 text-xs font-bold text-slate-700 border border-slate-200"
                        >
                            Belum Memenuhi Syarat
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 items-center">
                    <div class="space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rekening Tujuan Payout</span>
                        <p class="font-bold text-slate-900">{{ campaign.payout_bank_name || 'Bank Penyelenggara' }}</p>
                        <p class="font-mono text-sm font-semibold text-slate-700">{{ campaign.payout_account_number || '-' }}</p>
                        <p class="text-xs text-slate-500">a.n. {{ campaign.payout_account_name || '-' }}</p>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Syarat Pencairan</span>
                        <p class="text-xs leading-relaxed text-slate-700">
                            {{ campaign.withdrawal_message }}
                        </p>
                    </div>

                    <div class="flex sm:justify-end">
                        <button
                            v-if="campaign.withdrawal_status === 'not_ready' || !campaign.withdrawal_status"
                            type="button"
                            :disabled="!campaign.can_withdraw"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-xs font-bold shadow-md transition"
                            :class="campaign.can_withdraw ? 'bg-emerald-700 text-white hover:bg-emerald-800' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            @click="isWithdrawModalOpen = true"
                        >
                            <IconReceipt class="h-4 w-4" />
                            <span>Ajukan Pencairan Dana</span>
                        </button>
                        <div v-else-if="campaign.withdrawal_status === 'pending'" class="text-xs text-amber-800 font-semibold">
                            Menunggu verifikasi transfer dari Admin SafeGive.
                        </div>
                        <div v-else class="text-xs text-emerald-800 font-bold">
                            Pencairan dana selesai.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Split: Donation Form & Blockchain Ledger -->
            <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_minmax(340px,0.85fr)]">
                <!-- Blockchain Cryptographic Ledger Report (Left) -->
                <section class="rounded-3xl border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <IconLock class="h-5 w-5 text-emerald-700" />
                                <h2 class="text-lg font-black text-slate-900 tracking-tight">
                                    Ledger Donasi Kriptografis
                                </h2>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Setiap transaksi dienkripsi dengan Paillier cryptosystem dan menghasilkan hash blockchain unik.
                            </p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                            Immutable Ledger
                        </span>
                    </div>

                    <div v-if="campaign.donations.length" class="mt-4 divide-y divide-slate-100">
                        <div
                            v-for="donation in campaign.donations"
                            :key="donation.id"
                            class="py-4 flex flex-wrap items-center justify-between gap-3 hover:bg-[#fafcfa] rounded-xl px-2 transition"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-xs">{{ donation.donor_name }}</span>
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-600">
                                        {{ donation.payment_method }}
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                        {{ shortHash(donation.transaction_hash) }}
                                    </span>
                                    <button
                                        type="button"
                                        class="rounded p-1 text-slate-400 hover:text-slate-700"
                                        @click="copyHash(donation.transaction_hash)"
                                        :title="copiedHash === donation.transaction_hash ? 'Tersalin' : 'Salin Hash'"
                                    >
                                        <IconCheck v-if="copiedHash === donation.transaction_hash" class="h-3.5 w-3.5 text-emerald-600" />
                                        <IconCopy v-else class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-400">
                                    Block #{{ donation.block_number || 'Pending' }} • {{ formatDate(donation.created_at) }}
                                </p>
                            </div>

                            <div class="text-right">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-bold inline-flex items-center gap-1"
                                    :class="donation.status === 'confirmed' ? 'bg-emerald-50 text-emerald-800' : donation.status === 'failed' ? 'bg-red-50 text-red-800' : 'bg-amber-50 text-amber-800'"
                                >
                                    <IconCheck v-if="donation.status === 'confirmed'" class="h-3 w-3 text-emerald-700" />
                                    <IconClock v-else class="h-3 w-3 text-amber-700" />
                                    {{ donation.status === 'confirmed' ? 'Terkonfirmasi' : donation.status === 'failed' ? 'Ditolak' : 'Menunggu' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center text-xs text-slate-400">
                        Belum ada donasi yang tercatat untuk kampanye ini. Jadilah donatur pertama!
                    </div>
                </section>

                <!-- Donation Form (Right) -->
                <section class="rounded-3xl border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <IconHeartHandshake class="h-5 w-5 text-emerald-700" />
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Salurkan Donasi</h2>
                    </div>

                    <div v-if="!campaign.donation_open" class="mt-5 rounded-2xl bg-amber-50 p-4 text-xs font-semibold leading-relaxed text-amber-800 border border-amber-200">
                        {{ campaign.donation_message }}
                    </div>

                    <div v-else class="mt-5 space-y-5">
                        <!-- Payment Type Tabs -->
                        <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-2xl">
                            <button
                                type="button"
                                class="py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                                :class="paymentType === 'midtrans' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                @click="paymentType = 'midtrans'"
                            >
                                <span>⚡ Midtrans Snap</span>
                                <span class="rounded bg-emerald-100 text-emerald-800 text-[9px] px-1.5 py-0.5 font-black uppercase">Sandbox</span>
                            </button>
                            <button
                                type="button"
                                class="py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                                :class="paymentType === 'manual' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                @click="paymentType = 'manual'"
                            >
                                <IconBuildingBank class="h-3.5 w-3.5" />
                                <span>Transfer Rekening</span>
                            </button>
                        </div>

                        <!-- MIDTRANS SNAP FORM -->
                        <div v-if="paymentType === 'midtrans'" class="space-y-4">
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-3.5 text-xs text-emerald-900">
                                <div class="flex items-center gap-2 font-bold text-emerald-950">
                                    <IconShieldCheck class="h-4 w-4 text-emerald-700 shrink-0" />
                                    <span>Pembayaran Instan & Otomatis Terverifikasi</span>
                                </div>
                                <p class="mt-1 text-[11px] leading-relaxed text-emerald-800">
                                    Didukung QRIS Dinamis, Virtual Account Bank (BCA, Mandiri, BRI, BNI, Permata), E-Wallet, dan Kartu Debit/Kredit melalui Midtrans Sandbox Gateway.
                                </p>
                            </div>

                            <!-- Alert Errors/Success -->
                            <div v-if="midtransError" class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-800 font-semibold flex items-center gap-2">
                                <IconAlertCircle class="h-4 w-4 text-red-600 shrink-0" />
                                <span>{{ midtransError }}</span>
                            </div>
                            <div v-if="midtransSuccess" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-800 font-semibold flex items-center gap-2">
                                <IconCheck class="h-4 w-4 text-emerald-600 shrink-0" />
                                <span>{{ midtransSuccess }}</span>
                            </div>

                            <!-- Nominal Presets -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Nominal Donasi (Rupiah)
                                </label>
                                <div class="grid grid-cols-3 gap-1.5 mb-2">
                                    <button
                                        v-for="amt in presetAmounts"
                                        :key="amt"
                                        type="button"
                                        class="rounded-xl border py-2 text-xs font-bold transition text-center"
                                        :class="form.amount === amt.toString() ? 'border-emerald-600 bg-emerald-50 text-emerald-800 font-black' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                        @click="setAmount(amt)"
                                    >
                                        {{ formatRupiah(amt) }}
                                    </button>
                                </div>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    min="1000"
                                    step="1000"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-bold text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                    placeholder="Nominal lainnya..."
                                />
                            </div>

                            <!-- Donor Identity -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Nama Donatur
                                </label>
                                <input
                                    v-model="form.donor_name"
                                    :disabled="form.is_anonymous"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500 disabled:bg-slate-100"
                                    placeholder="Nama lengkap Anda..."
                                />
                                <div class="mt-1.5 flex items-center gap-2">
                                    <input
                                        id="anon-midtrans"
                                        type="checkbox"
                                        v-model="form.is_anonymous"
                                        class="rounded text-emerald-600 focus:ring-emerald-500"
                                        @change="form.donor_name = form.is_anonymous ? 'Hamba Allah' : (page.props.auth.user?.name || '')"
                                    />
                                    <label for="anon-midtrans" class="text-xs text-slate-600">Sembunyikan nama (Hamba Allah)</label>
                                </div>
                            </div>

                            <!-- Contact details for Midtrans -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Email (Kirim Resi)
                                    </label>
                                    <input
                                        v-model="form.donor_email"
                                        type="email"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500"
                                        placeholder="email@example.com"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        No. WhatsApp / HP
                                    </label>
                                    <input
                                        v-model="form.donor_phone"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500"
                                        placeholder="081234567890"
                                    />
                                </div>
                            </div>

                            <!-- Donor Note -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Doa / Pesan Kebaikan (Opsional)
                                </label>
                                <textarea
                                    v-model="form.donor_note"
                                    rows="2"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500"
                                    placeholder="Tuliskan doa atau harapan untuk penerima manfaat..."
                                ></textarea>
                            </div>

                            <!-- Pay Button Midtrans -->
                            <button
                                type="button"
                                :disabled="isMidtransProcessing"
                                class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-4 py-3.5 text-sm font-black text-white shadow-lg shadow-emerald-700/20 hover:from-emerald-700 hover:to-teal-800 disabled:opacity-60 flex items-center justify-center gap-2 transition active:scale-[0.99]"
                                @click="payWithMidtrans"
                            >
                                <IconLock class="h-4 w-4" />
                                <span>{{ isMidtransProcessing ? 'Menyiapkan Midtrans...' : `Bayar ${formatRupiah(parseInt(form.amount || '0', 10))} dengan Midtrans` }}</span>
                            </button>

                            <p class="text-center text-[10px] text-slate-400">
                                Transaksi diamankan secara kriptografis & diproses melalui Midtrans Sandbox Payment Gateway.
                            </p>
                        </div>

                        <!-- MANUAL BANK TRANSFER FORM -->
                        <div v-else class="space-y-4">
                            <!-- Central Admin Bank Account Card -->
                            <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/50 p-4 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-emerald-900 uppercase tracking-wider text-[11px]">
                                        Rekening Resmi SafeGive
                                    </span>
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 font-bold text-[10px] text-emerald-800">
                                        {{ adminBank.bank_code }}
                                    </span>
                                </div>

                                <p class="mt-1 text-slate-600 font-medium">{{ adminBank.bank_name }}</p>

                                <div class="mt-2 flex items-center justify-between rounded-xl bg-white p-2.5 border border-emerald-200/60 shadow-xs">
                                    <span class="font-mono text-base font-black text-slate-900 tracking-wider">
                                        {{ adminBank.account_number }}
                                    </span>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg bg-[#edf4e9] px-2.5 py-1 text-xs font-bold text-emerald-800 hover:bg-emerald-200"
                                        @click="copyAdminAccount"
                                    >
                                        <IconCheck v-if="copiedBank" class="h-3.5 w-3.5 text-emerald-600" />
                                        <IconCopy v-else class="h-3.5 w-3.5" />
                                        <span>{{ copiedBank ? 'Tersalin' : 'Salin' }}</span>
                                    </button>
                                </div>

                                <p class="mt-1 text-slate-700 font-semibold">a.n. {{ adminBank.account_name }}</p>

                                <div v-if="adminBank.qris_image_path" class="mt-3 text-center border-t border-emerald-100 pt-3">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase flex items-center justify-center gap-1 mb-1.5">
                                        <IconQrcode class="h-3.5 w-3.5 text-emerald-700" />
                                        <span>Scan QRIS</span>
                                    </p>
                                    <img
                                        :src="`/storage/${adminBank.qris_image_path}`"
                                        alt="QRIS"
                                        class="mx-auto h-32 w-32 rounded-lg border border-slate-200 bg-white p-1 object-contain"
                                    />
                                </div>
                            </div>

                            <!-- Donation Input Form -->
                            <form class="space-y-4" @submit.prevent="submitDonation">
                                <!-- Nominal Presets -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Nominal Donasi (Rupiah)
                                    </label>
                                    <div class="grid grid-cols-3 gap-1.5 mb-2">
                                        <button
                                            v-for="amt in presetAmounts"
                                            :key="amt"
                                            type="button"
                                            class="rounded-xl border py-2 text-xs font-bold transition text-center"
                                            :class="form.amount === amt.toString() ? 'border-emerald-600 bg-emerald-50 text-emerald-800 font-black' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                            @click="setAmount(amt)"
                                        >
                                            {{ formatRupiah(amt) }}
                                        </button>
                                    </div>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        min="1000"
                                        step="1000"
                                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-bold text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                        placeholder="Nominal lainnya..."
                                    />
                                    <span v-if="form.errors.amount" class="mt-1 block text-xs text-red-600">{{ form.errors.amount }}</span>
                                </div>

                                <!-- Payment Channel -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Metode Transfer Donatur
                                    </label>
                                    <select
                                        v-model="form.payment_method"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                    >
                                        <option v-for="m in paymentMethods" :key="m" :value="m">
                                            {{ m }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Donor Identity -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Nama Donatur
                                    </label>
                                    <input
                                        v-model="form.donor_name"
                                        :disabled="form.is_anonymous"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500 disabled:bg-slate-100"
                                        placeholder="Nama lengkap Anda..."
                                    />
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <input
                                            id="anon"
                                            type="checkbox"
                                            v-model="form.is_anonymous"
                                            class="rounded text-emerald-600 focus:ring-emerald-500"
                                            @change="form.donor_name = form.is_anonymous ? 'Hamba Allah' : (page.props.auth.user?.name || '')"
                                        />
                                        <label for="anon" class="text-xs text-slate-600">Sembunyikan nama (Donasi sebagai Hamba Allah)</label>
                                    </div>
                                </div>

                                <!-- Donor Note -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Doa / Pesan Kebaikan (Opsional)
                                    </label>
                                    <textarea
                                        v-model="form.donor_note"
                                        rows="2"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-emerald-500"
                                        placeholder="Tuliskan harapan atau doa..."
                                    ></textarea>
                                </div>

                                <!-- Upload Bukti Transfer -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Unggah Bukti Transfer (Opsional)
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-slate-300 px-3 py-2 text-xs text-slate-600 hover:bg-slate-50">
                                            <IconUpload class="h-4 w-4 text-emerald-700" />
                                            <span>Pilih Foto/Struk</span>
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="form.payment_proof = ($event.target as HTMLInputElement).files?.[0] || null"
                                            />
                                        </label>
                                        <span v-if="form.payment_proof" class="text-xs text-emerald-700 font-semibold truncate max-w-[180px]">
                                            {{ form.payment_proof.name }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 hover:bg-emerald-800 disabled:opacity-60"
                                >
                                    {{ form.processing ? 'Mengenkripsi & Menyimpan...' : 'Kirim Konfirmasi Donasi Manual' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Withdrawal Modal for Organizer -->
        <div
            v-if="isWithdrawModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Ajukan Pencairan Dana</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="isWithdrawModalOpen = false">
                        <IconX class="h-5 w-5" />
                    </button>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Dana donasi yang telah terverifikasi akan ditransfer oleh Admin SafeGive ke rekening di bawah ini:
                </p>

                <form class="space-y-4" @submit.prevent="submitWithdrawal">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Bank / E-Wallet Tujuan
                        </label>
                        <input
                            v-model="withdrawForm.payout_bank_name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold"
                            placeholder="Contoh: BCA / Mandiri / DANA"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nomor Rekening / No. HP
                        </label>
                        <input
                            v-model="withdrawForm.payout_account_number"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono font-bold"
                            placeholder="Contoh: 8830192841"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nama Pemilik Rekening
                        </label>
                        <input
                            v-model="withdrawForm.payout_account_name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold"
                            placeholder="Sesuai buku rekening"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Catatan Pengajuan (Opsional)
                        </label>
                        <textarea
                            v-model="withdrawForm.notes"
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"
                            placeholder="Tuliskan rencana penggunaan atau rincian pencairan..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                            @click="isWithdrawModalOpen = false"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="withdrawForm.processing"
                            class="rounded-lg bg-emerald-700 px-5 py-2 text-xs font-bold text-white hover:bg-emerald-800 disabled:opacity-60"
                        >
                            {{ withdrawForm.processing ? 'Mengajukan...' : 'Konfirmasi Pengajuan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
