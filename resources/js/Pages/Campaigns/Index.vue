<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { IconArrowRight, IconCalendar, IconHeart, IconShieldCheck, IconCopy, IconCheck } from '@tabler/icons-vue';

interface Campaign {
    id: number;
    title: string;
    slug: string;
    description: string;
    category: string;
    image_url: string | null;
    target_amount: number;
    encrypted_collected_amount: string | null;
    progress_percentage: number;
    donors_count: number;
    ends_at: string | null;
    status: 'active' | 'goal_reached';
}

defineProps<{
    campaigns: {
        data: Campaign[];
        current_page: number;
        last_page: number;
    };
}>();

const copiedCampaignId = ref<number | null>(null);
const copyCampaignLink = (campaign: Campaign, e?: Event) => {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const identifier = campaign.slug || campaign.id;
    const url = `${window.location.origin}/campaigns/${identifier}`;
    navigator.clipboard.writeText(url);
    copiedCampaignId.value = campaign.id;
    setTimeout(() => {
        if (copiedCampaignId.value === campaign.id) {
            copiedCampaignId.value = null;
        }
    }, 2000);
};

const formatRupiah = (value: number): string => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const formatDate = (value: string | null): string => value
    ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value))
    : '-';
</script>

<template>
    <AppLayout>
        <Head title="Eksplorasi Kampanye - SafeGive Blockchain" />

        <div class="space-y-8">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Donasi transparan</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Eksplorasi Kampanye</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">
                        Progres donasi dihitung dari ciphertext Paillier. Identitas donatur tetap privat, sementara bukti transaksinya dapat diverifikasi di blockchain.
                    </p>
                </div>
                <Link href="/dashboard" class="inline-flex items-center gap-2 rounded-xl border border-[#dce6d8] bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-[#edf4e9] hover:text-emerald-800">
                    Dashboard
                    <IconArrowRight class="h-4 w-4" />
                </Link>
            </header>

            <section v-if="campaigns.data.length" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="campaign in campaigns.data" :key="campaign.id" class="flex flex-col overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                    <div class="aspect-[16/9] bg-[#edf4e9]">
                        <img v-if="campaign.image_url" :src="campaign.image_url" :alt="campaign.title" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center text-emerald-700">
                            <IconHeart class="h-10 w-10" stroke-width="1.5" />
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center justify-between gap-3 text-xs font-bold uppercase tracking-wide">
                            <span class="text-emerald-700">{{ campaign.category }}</span>
                            <span :class="campaign.status === 'goal_reached' ? 'text-orange-600' : 'text-slate-500'">
                                {{ campaign.status === 'goal_reached' ? 'Target tercapai' : 'Aktif' }}
                            </span>
                        </div>
                        <h2 class="mt-3 line-clamp-2 text-lg font-extrabold leading-snug text-slate-900">{{ campaign.title }}</h2>
                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600">{{ campaign.description }}</p>

                        <div class="mt-5 flex items-center justify-between text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5"><IconCalendar class="h-4 w-4" />{{ formatDate(campaign.ends_at) }}</span>
                            <span>{{ campaign.donors_count }} donatur</span>
                        </div>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-600" :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }" aria-label="Progres terenkripsi" />
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="font-bold text-emerald-700">{{ campaign.progress_percentage.toFixed(2) }}% terverifikasi</span>
                            <span class="font-bold text-slate-800">Target {{ formatRupiah(campaign.target_amount) }}</span>
                        </div>
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <IconShieldCheck class="h-4 w-4 text-emerald-600" />
                            <span>Akumulasi tersimpan sebagai ciphertext</span>
                        </div>
                        
                        <div class="mt-5 grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-[#dce6d8] bg-white px-3 py-2.5 text-xs font-bold text-slate-700 shadow-xs transition hover:border-emerald-300 hover:bg-[#edf4e9] hover:text-emerald-800 active:scale-95"
                                @click="copyCampaignLink(campaign, $event)"
                            >
                                <IconCheck v-if="copiedCampaignId === campaign.id" class="h-3.5 w-3.5 text-emerald-600" />
                                <IconCopy v-else class="h-3.5 w-3.5 text-slate-500" />
                                <span>{{ copiedCampaignId === campaign.id ? 'Tersalin!' : 'Salin Link' }}</span>
                            </button>

                            <Link :href="`/campaigns/${campaign.slug || campaign.id}`" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-800 text-center">
                                Detail & Donasi
                            </Link>
                        </div>
                    </div>
                </article>
            </section>

            <div v-else class="rounded-2xl border border-dashed border-[#cbd8c6] bg-white p-12 text-center text-sm text-slate-500">
                Belum ada kampanye aktif.
            </div>
        </div>
    </AppLayout>
</template>
