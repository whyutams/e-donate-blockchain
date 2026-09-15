<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import {
    IconCheck,
    IconClock,
    IconCopy,
    IconEye,
    IconHeartHandshake,
    IconReceipt,
    IconShieldCheck,
    IconX,
} from '@tabler/icons-vue';

interface DonationItem {
    id: number;
    campaign_id: number;
    campaign_title: string;
    donor_name: string;
    donor_email: string | null;
    donor_phone: string | null;
    payment_method: string;
    reference_code: string | null;
    payment_proof_url: string | null;
    donor_note: string | null;
    admin_notes: string | null;
    transaction_hash: string;
    block_number: number;
    status: 'pending' | 'confirmed' | 'failed';
    created_at: string;
    confirmed_at: string | null;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    donations: Paginated<DonationItem>;
    activeStatus: string;
}>();

const page = usePage();

const selectedProofUrl = ref<string | null>(null);
const rejectingDonationId = ref<number | null>(null);
const rejectReason = ref('');
const isConfirming = ref<number | null>(null);
const isSyncing = ref<number | null>(null);

const syncMidtrans = (id: number) => {
    isSyncing.value = id;
    router.post(`/donations/${id}/sync-midtrans`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isSyncing.value = null;
        },
    });
};

const copiedHash = ref<string | null>(null);
const copyHash = (hash: string) => {
    navigator.clipboard.writeText(hash);
    copiedHash.value = hash;
    setTimeout(() => {
        if (copiedHash.value === hash) copiedHash.value = null;
    }, 2000);
};

const confirmDonation = (id: number) => {
    if (confirm('Konfirmasi donasi ini? Sistem akan menambahkan nominal terenkripsi ke agregasi Paillier kampanye.')) {
        isConfirming.value = id;
        router.post(`/admin/donations/${id}/confirm`, {}, {
            preserveScroll: true,
            onFinish: () => {
                isConfirming.value = null;
            },
        });
    }
};

const openRejectModal = (id: number) => {
    rejectingDonationId.value = id;
    rejectReason.value = '';
};

const submitReject = () => {
    if (!rejectReason.value) return;
    router.post(`/admin/donations/${rejectingDonationId.value}/reject`, {
        admin_notes: rejectReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            rejectingDonationId.value = null;
            rejectReason.value = '';
        },
    });
};

const shortHash = (hash: string) => `${hash.slice(0, 8)}...${hash.slice(-6)}`;
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Donasi - Admin SafeGive" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                        <IconHeartHandshake class="h-4 w-4" />
                        <span>Verifikasi Donasi Masuk</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        Data Transaksi Donasi
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Periksa transfer rekening/e-wallet donatur dan konfirmasi pencatatan ke agregasi enkripsi Paillier.
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap items-center gap-1 rounded-xl bg-white p-1 border border-[#dce6d8] shadow-xs text-xs font-semibold">
                    <Link
                        href="/admin/donations"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'all' ? 'bg-[#edf4e9] text-emerald-800 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Semua
                    </Link>
                    <Link
                        href="/admin/donations?status=pending"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'pending' ? 'bg-amber-100 text-amber-900 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Menunggu Konfirmasi
                    </Link>
                    <Link
                        href="/admin/donations?status=confirmed"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'confirmed' ? 'bg-emerald-100 text-emerald-900 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Terkonfirmasi
                    </Link>
                    <Link
                        href="/admin/donations?status=failed"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'failed' ? 'bg-red-100 text-red-900 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Ditolak
                    </Link>
                </div>
            </div>

            <!-- Flash Status Message -->
            <div
                v-if="page.props.flash?.status"
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-sm"
            >
                <IconCheck class="h-5 w-5 text-emerald-700" />
                <span>{{ page.props.flash.status }}</span>
            </div>

            <!-- Donations Table Card -->
            <div class="overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead>
                            <tr class="border-b border-[#e9efe6] bg-[#fcfdfa] text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 pl-4">Kampanye & Donatur</th>
                                <th class="py-3.5">Metode & Referensi</th>
                                <th class="py-3.5">Hash Blockchain (0x)</th>
                                <th class="py-3.5 text-center">Bukti Transfer</th>
                                <th class="py-3.5 text-center">Status</th>
                                <th class="py-3.5 pr-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="d in donations.data"
                                :key="d.id"
                                class="transition hover:bg-[#f8faf6]"
                            >
                                <td class="py-4 pl-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ d.campaign_title }}</p>
                                    <p class="mt-0.5 text-xs text-slate-700 font-medium">
                                        {{ d.donor_name }}
                                        <span v-if="d.donor_email" class="text-slate-400">({{ d.donor_email }})</span>
                                    </p>
                                    <p v-if="d.donor_note" class="mt-1 text-[11px] italic text-slate-500">
                                        "{{ d.donor_note }}"
                                    </p>
                                    <span class="mt-1 block text-[10px] text-slate-400">Waktu: {{ d.created_at }}</span>
                                </td>

                                <td class="py-4">
                                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase text-slate-800">
                                        {{ d.payment_method }}
                                    </span>
                                    <p class="mt-1 font-mono text-xs font-semibold text-slate-700">
                                        {{ d.reference_code || '-' }}
                                    </p>
                                </td>

                                <td class="py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-xs text-slate-800 bg-slate-100 px-2 py-0.5 rounded">
                                            {{ shortHash(d.transaction_hash) }}
                                        </span>
                                        <button
                                            type="button"
                                            class="rounded p-1 text-slate-400 hover:text-slate-700"
                                            :title="copiedHash === d.transaction_hash ? 'Tersalin' : 'Salin Hash'"
                                            @click="copyHash(d.transaction_hash)"
                                        >
                                            <IconCheck v-if="copiedHash === d.transaction_hash" class="h-3.5 w-3.5 text-emerald-600" />
                                            <IconCopy v-else class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-mono">Block #{{ d.block_number }}</span>
                                </td>

                                <td class="py-4 text-center">
                                    <button
                                        v-if="d.payment_proof_url"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        @click="selectedProofUrl = d.payment_proof_url"
                                    >
                                        <IconEye class="h-3.5 w-3.5 text-emerald-700" />
                                        <span>Lihat Bukti</span>
                                    </button>
                                    <span v-else class="text-[11px] italic text-slate-400">Tanpa Bukti</span>
                                </td>

                                <td class="py-4 text-center">
                                    <span
                                        v-if="d.status === 'confirmed'"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800"
                                    >
                                        <IconShieldCheck class="h-3.5 w-3.5" />
                                        Terkonfirmasi
                                    </span>
                                    <span
                                        v-else-if="d.status === 'pending'"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800"
                                    >
                                        <IconClock class="h-3.5 w-3.5" />
                                        Menunggu
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-bold text-red-800"
                                    >
                                        <IconX class="h-3.5 w-3.5" />
                                        Ditolak
                                    </span>
                                </td>

                                <td class="py-4 pr-4 text-right">
                                    <div v-if="d.status === 'pending'" class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <button
                                            v-if="d.payment_method.includes('midtrans') || d.reference_code"
                                            type="button"
                                            :disabled="isSyncing === d.id"
                                            class="inline-flex items-center gap-1 rounded-lg border border-teal-600 bg-teal-50 px-2.5 py-1.5 text-xs font-bold text-teal-800 hover:bg-teal-100 disabled:opacity-50"
                                            title="Sinkronkan status transaksi dengan server Midtrans"
                                            @click="syncMidtrans(d.id)"
                                        >
                                            <span>{{ isSyncing === d.id ? 'Cek...' : 'Cek Midtrans' }}</span>
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="isConfirming === d.id"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-800 disabled:opacity-50"
                                            @click="confirmDonation(d.id)"
                                        >
                                            <IconCheck class="h-3.5 w-3.5" />
                                            <span>{{ isConfirming === d.id ? 'Memproses...' : 'Konfirmasi' }}</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50"
                                            @click="openRejectModal(d.id)"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                    <span v-else-if="d.status === 'confirmed'" class="text-[11px] font-medium text-slate-400">
                                        Selesai
                                    </span>
                                    <span v-else class="text-[11px] font-medium text-red-400">
                                        {{ d.admin_notes || 'Ditolak' }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="donations.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    Tidak ada data donasi yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="donations.links.length > 3" class="flex items-center justify-between border-t border-slate-100 px-4 py-3 sm:px-6">
                    <p class="text-xs text-slate-500">Total: {{ donations.total }} donasi</p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in donations.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="rounded-md px-2.5 py-1 text-xs font-semibold"
                            :class="[
                                link.active ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url ? 'pointer-events-none opacity-40' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Image Modal Preview -->
        <div
            v-if="selectedProofUrl"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
            @click="selectedProofUrl = null"
        >
            <div class="max-w-xl overflow-hidden rounded-2xl bg-white p-3 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800">Bukti Transfer Donatur</span>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="selectedProofUrl = null">
                        <IconX class="h-5 w-5" />
                    </button>
                </div>
                <div class="mt-2 max-h-[75vh] overflow-y-auto">
                    <img :src="selectedProofUrl" alt="Bukti Transfer" class="w-full rounded-xl object-contain" />
                </div>
            </div>
        </div>

        <!-- Reject Reason Modal -->
        <div
            v-if="rejectingDonationId !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Tolak Donasi #{{ rejectingDonationId }}</h3>
                <p class="text-xs text-slate-500">Berikan catatan alasan penolakan donasi (misal: dana tidak masuk, bukti palsu).</p>
                <textarea
                    v-model="rejectReason"
                    rows="3"
                    placeholder="Alasan penolakan..."
                    class="w-full rounded-xl border border-slate-200 p-3 text-xs focus:border-red-500 focus:ring-1 focus:ring-red-500"
                ></textarea>
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                        @click="rejectingDonationId = null"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="!rejectReason"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 disabled:opacity-50"
                        @click="submitReject"
                    >
                        Tolak Donasi
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
