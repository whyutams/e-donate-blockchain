<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import {
    IconCalendar,
    IconEye,
    IconHeart,
    IconPlus,
    IconTrash,
    IconCheck,
    IconCash,
    IconBuildingBank,
    IconX,
} from '@tabler/icons-vue';

interface Campaign {
    id: number;
    title: string;
    description: string;
    category: string;
    image_url: string | null;
    target_amount: number;
    progress_percentage: number;
    donors_count: number;
    ends_at: string | null;
    status: 'draft' | 'active' | 'goal_reached' | 'expired' | 'withdrawn';
    payout_bank_name: string | null;
    payout_account_number: string | null;
    payout_account_name: string | null;
    withdrawal_status: 'not_ready' | 'pending' | 'confirmed' | 'failed';
    withdrawal_transaction_hash: string | null;
    can_withdraw: boolean;
    withdrawal_message: string;
}

const props = defineProps<{ campaigns: Campaign[]; canCreate: boolean }>();
const page = usePage();

const formatRupiah = (value: number): string => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const formatDate = (value: string | null): string => value
    ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value))
    : '-';

const statusLabel: Record<Campaign['status'], string> = {
    draft: 'Draft',
    active: 'Aktif',
    goal_reached: 'Target tercapai',
    expired: 'Berakhir',
    withdrawn: 'Dicairkan',
};

const removeCampaign = (id: number) => {
    if (window.confirm('Hapus kampanye ini? Kampanye dengan donasi terkonfirmasi tidak dapat dihapus.')) {
        router.delete(`/campaigns/${id}`, { preserveScroll: true });
    }
};

// Withdrawal Modal State
const selectedCampaign = ref<Campaign | null>(null);
const withdrawForm = useForm({
    payout_bank_name: 'BCA',
    payout_account_number: '',
    payout_account_name: '',
    notes: '',
});

const openWithdrawModal = (campaign: Campaign) => {
    selectedCampaign.value = campaign;
    withdrawForm.payout_bank_name = campaign.payout_bank_name || 'BCA';
    withdrawForm.payout_account_number = campaign.payout_account_number || '';
    withdrawForm.payout_account_name = campaign.payout_account_name || '';
    withdrawForm.notes = '';
};

const submitWithdrawal = () => {
    if (!selectedCampaign.value) return;
    withdrawForm.post(`/campaigns/${selectedCampaign.value.id}/withdraw`, {
        preserveScroll: true,
        onSuccess: () => {
            selectedCampaign.value = null;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Kampanye Saya - SafeGive" />

        <div class="space-y-8">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Ruang penyelenggara</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Kampanye Saya</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">
                        Kelola kampanye Anda, pantau capaian donasi, dan ajukan pencairan dana ke rekening bank Anda saat target terpenuhi atau masa kampanye berakhir.
                    </p>
                </div>
                <Link
                    v-if="canCreate"
                    href="/campaigns/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800"
                >
                    <IconPlus class="h-4 w-4" />
                    Buat kampanye
                </Link>
                <Link
                    v-else
                    href="/profile"
                    class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                >
                    Lengkapi verifikasi
                </Link>
            </header>

            <!-- Flash Status Message -->
            <div
                v-if="page.props.flash?.status"
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-sm"
            >
                <IconCheck class="h-5 w-5 text-emerald-700 shrink-0" />
                <span>{{ page.props.flash.status }}</span>
            </div>

            <!-- Campaign Cards Grid -->
            <section v-if="props.campaigns.length" class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="campaign in props.campaigns"
                    :key="campaign.id"
                    class="overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm flex flex-col justify-between"
                >
                    <div>
                        <div class="aspect-[16/9] bg-[#edf4e9] relative">
                            <img
                                v-if="campaign.image_url"
                                :src="campaign.image_url"
                                :alt="campaign.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full items-center justify-center text-emerald-700">
                                <IconHeart class="h-10 w-10" stroke-width="1.5" />
                            </div>

                            <span
                                v-if="campaign.progress_percentage >= 100"
                                class="absolute top-3 right-3 rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-extrabold text-white shadow-sm"
                            >
                                Target 100%
                            </span>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center justify-between gap-3 text-xs font-bold uppercase tracking-wide">
                                <span class="text-emerald-700">{{ campaign.category }}</span>
                                <span class="text-slate-500">{{ statusLabel[campaign.status] }}</span>
                            </div>

                            <h2 class="mt-2.5 line-clamp-2 text-base font-extrabold leading-snug text-slate-900">
                                {{ campaign.title }}
                            </h2>

                            <!-- Progress Bar -->
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-emerald-600 transition-all duration-500"
                                    :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }"
                                />
                            </div>

                            <div class="mt-2.5 flex items-center justify-between text-xs font-bold">
                                <span class="text-emerald-700">{{ campaign.progress_percentage.toFixed(2) }}%</span>
                                <span class="text-slate-800">Target {{ formatRupiah(campaign.target_amount) }}</span>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <IconCalendar class="h-3.5 w-3.5" />
                                    {{ formatDate(campaign.ends_at) }}
                                </span>
                                <span>{{ campaign.donors_count }} donatur</span>
                            </div>

                            <!-- Payout Account Info -->
                            <div class="mt-4 rounded-xl bg-slate-50 p-2.5 text-xs text-slate-600 border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Rekening Payout:</span>
                                <span class="font-bold text-slate-800">{{ campaign.payout_bank_name || 'BCA' }}: </span>
                                <span class="font-mono text-slate-700 font-semibold">{{ campaign.payout_account_number || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="p-5 pt-0">
                        <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100">
                            <Link
                                :href="`/campaigns/${campaign.id}`"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                            >
                                <IconEye class="h-4 w-4" />
                                Detail
                            </Link>

                            <button
                                v-if="campaign.donors_count === 0"
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50"
                                title="Hapus kampanye"
                                @click="removeCampaign(campaign.id)"
                            >
                                <IconTrash class="h-4 w-4" />
                            </button>

                            <!-- Withdrawal Button -->
                            <button
                                v-if="campaign.withdrawal_status === 'not_ready' || !campaign.withdrawal_status"
                                type="button"
                                :disabled="!campaign.can_withdraw"
                                class="inline-flex flex-1 items-center justify-center gap-1 rounded-lg px-3 py-2 text-xs font-bold shadow-xs transition"
                                :class="campaign.can_withdraw ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                :title="campaign.withdrawal_message"
                                @click="openWithdrawModal(campaign)"
                            >
                                <IconCash class="h-4 w-4" />
                                <span>Cairkan Dana</span>
                            </button>

                            <span
                                v-else-if="campaign.withdrawal_status === 'pending'"
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 border border-amber-200"
                            >
                                Sedang Diproses Admin
                            </span>

                            <span
                                v-else-if="campaign.withdrawal_status === 'confirmed'"
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 border border-emerald-200"
                            >
                                Telah Dicairkan
                            </span>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Empty State -->
            <div
                v-else
                class="rounded-2xl border border-dashed border-[#cbd8c6] bg-white p-12 text-center"
            >
                <p class="text-sm text-slate-500">Anda belum memiliki kampanye donasi.</p>
                <Link
                    v-if="canCreate"
                    href="/campaigns/create"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800"
                >
                    <IconPlus class="h-4 w-4" />
                    Buat kampanye pertama
                </Link>
            </div>
        </div>

        <!-- Withdrawal Modal -->
        <div
            v-if="selectedCampaign !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Ajukan Pencairan Dana</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="selectedCampaign = null">
                        <IconX class="h-5 w-5" />
                    </button>
                </div>

                <div class="rounded-xl bg-emerald-50 p-3 border border-emerald-200 text-xs text-emerald-900 leading-relaxed">
                    <strong>Syarat Terpenuhi:</strong> Kampanye telah mencapai target (100%) atau telah melewati batas jatuh tempo. Admin SafeGive akan mentransfer dana ke rekening yang Anda konfirmasi di bawah ini.
                </div>

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
                            placeholder="Contoh: 1234567890"
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
                            placeholder="Sesuai buku tabungan"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Catatan Pencairan (Opsional)
                        </label>
                        <textarea
                            v-model="withdrawForm.notes"
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"
                            placeholder="Catatan tambahan untuk admin..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                            @click="selectedCampaign = null"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="withdrawForm.processing"
                            class="rounded-lg bg-emerald-700 px-5 py-2 text-xs font-bold text-white hover:bg-emerald-800 disabled:opacity-60"
                        >
                            {{ withdrawForm.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
