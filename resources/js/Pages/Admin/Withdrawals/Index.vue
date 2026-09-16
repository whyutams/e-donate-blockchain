<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import {
    IconBuildingBank,
    IconCheck,
    IconClock,
    IconCopy,
    IconEye,
    IconReceipt,
    IconShieldCheck,
    IconUpload,
    IconX,
} from '@tabler/icons-vue';

interface CampaignWithdrawalItem {
    id: number;
    title: string;
    organizer_name: string;
    organizer_email: string | null;
    target_amount: number;
    progress_percentage: number;
    payout_bank_name: string | null;
    payout_account_number: string | null;
    payout_account_name: string | null;
    withdrawal_transaction_hash: string | null;
    withdrawal_status: 'pending' | 'confirmed' | 'failed';
    withdrawal_proof_url: string | null;
    withdrawal_notes: string | null;
    withdrawn_at: string | null;
    ends_at: string | null;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    campaigns: Paginated<CampaignWithdrawalItem>;
    activeStatus: string;
}>();

const page = usePage();

const formatRupiah = (val: number) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(val);

const approvingCampaignId = ref<number | null>(null);
const approvalProof = ref<File | null>(null);
const approvalNotes = ref('');
const isApproving = ref(false);

const rejectingCampaignId = ref<number | null>(null);
const rejectReason = ref('');

const selectedProofUrl = ref<string | null>(null);

const copiedHash = ref<string | null>(null);
const copyHash = (hash: string) => {
    navigator.clipboard.writeText(hash);
    copiedHash.value = hash;
    setTimeout(() => {
        if (copiedHash.value === hash) copiedHash.value = null;
    }, 2000);
};

const openApproveModal = (id: number) => {
    approvingCampaignId.value = id;
    approvalProof.value = null;
    approvalNotes.value = '';
};

const submitApprove = () => {
    if (!approvingCampaignId.value) return;
    isApproving.value = true;
    const formData = new FormData();
    if (approvalProof.value) {
        formData.append('proof', approvalProof.value);
    }
    if (approvalNotes.value) {
        formData.append('notes', approvalNotes.value);
    }

    router.post(`/admin/withdrawals/${approvingCampaignId.value}/approve`, formData, {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => {
            isApproving.value = false;
            approvingCampaignId.value = null;
        },
    });
};

const openRejectModal = (id: number) => {
    rejectingCampaignId.value = id;
    rejectReason.value = '';
};

const submitReject = () => {
    if (!rejectReason.value || !rejectingCampaignId.value) return;
    router.post(`/admin/withdrawals/${rejectingCampaignId.value}/reject`, {
        notes: rejectReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            rejectingCampaignId.value = null;
            rejectReason.value = '';
        },
    });
};

const shortHash = (hash: string | null) => hash ? `${hash.slice(0, 8)}...${hash.slice(-6)}` : '-';
</script>

<template>
    <AppLayout>
        <Head title="Pencairan Dana Kampanye - Admin SafeGive" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                        <IconReceipt class="h-4 w-4" />
                        <span>Penyaluran Dana ke Penyelenggara</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        Pencairan Dana Penyelenggara
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Penyelenggara hanya dapat mencairkan dana jika <strong>target telah terpenuhi</strong> atau <strong>masa kampanye telah melewati jatuh tempo</strong>.
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap items-center gap-1 rounded-xl bg-white p-1 border border-[#dce6d8] shadow-xs text-xs font-semibold">
                    <Link
                        href="/admin/withdrawals"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'all' ? 'bg-[#edf4e9] text-emerald-800 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Semua
                    </Link>
                    <Link
                        href="/admin/withdrawals?status=pending"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'pending' ? 'bg-amber-100 text-amber-900 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Menunggu Penyaluran
                    </Link>
                    <Link
                        href="/admin/withdrawals?status=confirmed"
                        class="rounded-lg px-3 py-2 transition"
                        :class="activeStatus === 'confirmed' ? 'bg-emerald-100 text-emerald-900 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        Telah Dicairkan
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

            <!-- Table Card -->
            <div class="overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead>
                            <tr class="border-b border-[#e9efe6] bg-[#fcfdfa] text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 pl-4">Kampanye & Penyelenggara</th>
                                <th class="py-3.5">Progres & Target</th>
                                <th class="py-3.5">Rekening Tujuan Penyelenggara</th>
                                <th class="py-3.5">Hash Kriptografis</th>
                                <th class="py-3.5 text-center">Status</th>
                                <th class="py-3.5 pr-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="c in campaigns.data"
                                :key="c.id"
                                class="transition hover:bg-[#f8faf6]"
                            >
                                <td class="py-4 pl-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ c.title }}</p>
                                    <p class="mt-0.5 text-xs text-slate-700 font-medium">
                                        Penyelenggara: {{ c.organizer_name }}
                                        <span v-if="c.organizer_email" class="text-slate-400">({{ c.organizer_email }})</span>
                                    </p>
                                    <span class="mt-1 block text-[10px] text-slate-400">Batas Waktu: {{ c.ends_at || 'Tidak ada' }}</span>
                                </td>

                                <td class="py-4">
                                    <span class="font-bold text-slate-900 text-sm">{{ c.progress_percentage }}%</span>
                                    <p class="text-[11px] text-slate-500">Target: {{ formatRupiah(c.target_amount) }}</p>
                                    <span
                                        v-if="c.progress_percentage >= 100"
                                        class="mt-1 inline-block rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800"
                                    >
                                        Target Tercapai
                                    </span>
                                    <span
                                        v-else
                                        class="mt-1 inline-block rounded bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-800"
                                    >
                                        Jatuh Tempo Lewat
                                    </span>
                                </td>

                                <td class="py-4">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 max-w-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-800 text-xs">{{ c.payout_bank_name || 'Bank Belum Diisi' }}</span>
                                            <IconBuildingBank class="h-4 w-4 text-slate-400" />
                                        </div>
                                        <p class="mt-1 font-mono font-black text-sm text-slate-900">
                                            {{ c.payout_account_number || '-' }}
                                        </p>
                                        <p class="text-[11px] font-semibold text-slate-600">
                                            a.n. {{ c.payout_account_name || '-' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="py-4">
                                    <div v-if="c.withdrawal_transaction_hash" class="flex items-center gap-1.5">
                                        <span class="font-mono text-xs text-slate-800 bg-slate-100 px-2 py-0.5 rounded">
                                            {{ shortHash(c.withdrawal_transaction_hash) }}
                                        </span>
                                        <button
                                            type="button"
                                            class="rounded p-1 text-slate-400 hover:text-slate-700"
                                            @click="copyHash(c.withdrawal_transaction_hash)"
                                        >
                                            <IconCheck v-if="copiedHash === c.withdrawal_transaction_hash" class="h-3.5 w-3.5 text-emerald-600" />
                                            <IconCopy v-else class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                    <span v-else class="text-[11px] text-slate-400">-</span>
                                </td>

                                <td class="py-4 text-center">
                                    <span
                                        v-if="c.withdrawal_status === 'confirmed'"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800"
                                    >
                                        <IconShieldCheck class="h-3.5 w-3.5" />
                                        Tercairkan
                                    </span>
                                    <span
                                        v-else-if="c.withdrawal_status === 'pending'"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800"
                                    >
                                        <IconClock class="h-3.5 w-3.5" />
                                        Menunggu Penyaluran
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-700"
                                    >
                                        {{ c.withdrawal_status }}
                                    </span>
                                    <p v-if="c.withdrawn_at" class="mt-1 text-[10px] text-slate-400">
                                        {{ c.withdrawn_at }}
                                    </p>
                                </td>

                                <td class="py-4 pr-4 text-right">
                                    <div v-if="c.withdrawal_status === 'pending'" class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-800"
                                            @click="openApproveModal(c.id)"
                                        >
                                            <IconCheck class="h-3.5 w-3.5" />
                                            <span>Setujui & Cairkan</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50"
                                            @click="openRejectModal(c.id)"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                    <div v-else-if="c.withdrawal_proof_url" class="flex justify-end">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:underline"
                                            @click="selectedProofUrl = c.withdrawal_proof_url"
                                        >
                                            <IconEye class="h-3.5 w-3.5" />
                                            <span>Bukti Transfer</span>
                                        </button>
                                    </div>
                                    <span v-else class="text-[11px] text-slate-400">Selesai</span>
                                </td>
                            </tr>

                            <tr v-if="campaigns.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    Tidak ada pengajuan pencairan dana.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="campaigns.links.length > 3" class="flex items-center justify-between border-t border-slate-100 px-4 py-3 sm:px-6">
                    <p class="text-xs text-slate-500">Total: {{ campaigns.total }} pengajuan</p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in campaigns.links"
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

        <!-- Approve Modal -->
        <div
            v-if="approvingCampaignId !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Konfirmasi Penyaluran Dana Pencairan</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="approvingCampaignId = null">
                        <IconX class="h-5 w-5" />
                    </button>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pastikan Anda telah melakukan transfer dana ke rekening penyelenggara yang tertera sebelum menyetujui.
                </p>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Unggah Bukti Transfer Bank (Opsional)
                    </label>
                    <input
                        type="file"
                        accept="image/*"
                        class="w-full rounded-xl border border-slate-200 p-2 text-xs"
                        @change="approvalProof = ($event.target as HTMLInputElement).files?.[0] || null"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Catatan Penyaluran (Opsional)
                    </label>
                    <textarea
                        v-model="approvalNotes"
                        rows="2"
                        placeholder="Contoh: Dana ditransfer via Bank BCA pada 15 Sep 2026..."
                        class="w-full rounded-xl border border-slate-200 p-3 text-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                        @click="approvingCampaignId = null"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="isApproving"
                        class="rounded-lg bg-emerald-700 px-5 py-2 text-xs font-bold text-white hover:bg-emerald-800 disabled:opacity-50"
                        @click="submitApprove"
                    >
                        {{ isApproving ? 'Menyimpan...' : 'Konfirmasi Dana Dicairkan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div
            v-if="rejectingCampaignId !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Tolak Permintaan Pencairan</h3>
                <p class="text-xs text-slate-500">Berikan catatan alasan mengapa pencairan belum dapat disetujui.</p>
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
                        @click="rejectingCampaignId = null"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="!rejectReason"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700 disabled:opacity-50"
                        @click="submitReject"
                    >
                        Tolak Pencairan
                    </button>
                </div>
            </div>
        </div>

        <!-- Proof Image Preview Modal -->
        <div
            v-if="selectedProofUrl"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
            @click="selectedProofUrl = null"
        >
            <div class="max-w-xl overflow-hidden rounded-2xl bg-white p-3 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800">Bukti Transfer Pencairan Dana</span>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="selectedProofUrl = null">
                        <IconX class="h-5 w-5" />
                    </button>
                </div>
                <div class="mt-2 max-h-[75vh] overflow-y-auto">
                    <img :src="selectedProofUrl" alt="Bukti Transfer Pencairan" class="w-full rounded-xl object-contain" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
