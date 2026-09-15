<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { IconCalendar, IconEye, IconHeart, IconPlus, IconTrash } from '@tabler/icons-vue';

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
}

const props = defineProps<{ campaigns: Campaign[]; canCreate: boolean }>();

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
</script>

<template>
    <AppLayout>
        <Head title="Kampanye Saya - SafeGive Blockchain" />
        <div class="space-y-8">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Ruang penyelenggara</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Kampanye Saya</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Kelola kampanye yang Anda buat, pantau progres donasi terenkripsi, dan lihat laporan transaksi on-chain.</p>
                </div>
                <Link v-if="canCreate" href="/campaigns/create" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800"><IconPlus class="h-4 w-4" />Buat kampanye</Link>
                <Link v-else href="/profile" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-800 transition hover:bg-amber-100">Lengkapi verifikasi</Link>
            </header>

            <section v-if="props.campaigns.length" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="campaign in props.campaigns" :key="campaign.id" class="overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                    <div class="aspect-[16/9] bg-[#edf4e9]">
                        <img v-if="campaign.image_url" :src="campaign.image_url" :alt="campaign.title" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center text-emerald-700"><IconHeart class="h-10 w-10" stroke-width="1.5" /></div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-3 text-xs font-bold uppercase tracking-wide"><span class="text-emerald-700">{{ campaign.category }}</span><span class="text-slate-500">{{ statusLabel[campaign.status] }}</span></div>
                        <h2 class="mt-3 line-clamp-2 text-lg font-extrabold leading-snug text-slate-900">{{ campaign.title }}</h2>
                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }" /></div>
                        <div class="mt-3 flex items-center justify-between text-sm"><span class="font-bold text-emerald-700">{{ campaign.progress_percentage.toFixed(2) }}%</span><span class="font-bold text-slate-800">{{ formatRupiah(campaign.target_amount) }}</span></div>
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500"><span class="inline-flex items-center gap-1.5"><IconCalendar class="h-4 w-4" />{{ formatDate(campaign.ends_at) }}</span><span>{{ campaign.donors_count }} donatur</span></div>
                        <div class="mt-5 flex gap-2"><Link :href="`/campaigns/${campaign.id}`" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"><IconEye class="h-4 w-4" />Detail</Link><button v-if="campaign.donors_count === 0" type="button" class="inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50" title="Hapus kampanye" @click="removeCampaign(campaign.id)"><IconTrash class="h-4 w-4" /></button></div>
                    </div>
                </article>
            </section>
            <div v-else class="rounded-2xl border border-dashed border-[#cbd8c6] bg-white p-12 text-center"><p class="text-sm text-slate-500">Anda belum memiliki kampanye.</p><Link v-if="canCreate" href="/campaigns/create" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800"><IconPlus class="h-4 w-4" />Buat kampanye pertama</Link></div>
        </div>
    </AppLayout>
</template>
