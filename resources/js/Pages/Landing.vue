<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    IconShieldCheck,
    IconHeartHandshake,
    IconArrowRight,
    IconCopy,
    IconCheck,
    IconCalendar,
    IconSearch,
    IconHeart,
    IconCircleCheck,
    IconUsers,
} from '@tabler/icons-vue';

interface Campaign {
    id: number;
    title: string;
    slug: string;
    description: string;
    category: string;
    image_url: string | null;
    target_amount: number;
    progress_percentage: number;
    donors_count: number;
    starts_at: string | null;
    ends_at: string | null;
    status: 'active' | 'goal_reached';
    organizer: { name: string } | null;
}

const props = defineProps<{
    campaigns: Campaign[];
    categories: string[];
}>();

const page = usePage<{
    auth: { user: { id: number; name: string; email: string } | null; is_admin: boolean };
}>();

const user = computed(() => page.props.auth?.user);

const selectedCategory = ref<string>('Semua');
const searchQuery = ref<string>('');
const copiedCampaignId = ref<number | null>(null);

const filteredCampaigns = computed(() => {
    return props.campaigns.filter((campaign) => {
        const matchesCategory =
            selectedCategory.value === 'Semua' || campaign.category === selectedCategory.value;
        const matchesSearch =
            searchQuery.value === '' ||
            campaign.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            campaign.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            campaign.category.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

const formatRupiah = (value: number): string =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

const formatDate = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value))
        : '-';

const copyCampaignLink = (campaignId: number, e?: Event) => {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const url = `${window.location.origin}/campaigns/${campaignId}`;
    navigator.clipboard.writeText(url);
    copiedCampaignId.value = campaignId;
    setTimeout(() => {
        if (copiedCampaignId.value === campaignId) {
            copiedCampaignId.value = null;
        }
    }, 2500);
};

const scrollToSection = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#f3f6ef] text-slate-900 font-sans selection:bg-emerald-200 selection:text-emerald-900">
        <Head title="SafeGive - Platform Donasi Berbasis Blockchain" />

        <!-- NAVBAR -->
        <header class="sticky top-0 z-40 border-b border-[#dce6d8] bg-white/95 backdrop-blur-md py-2">
            <div class="mx-auto flex h-18 max-w-6xl items-center justify-between px-4 sm:px-8">
                <!-- Brand Logo & Navlink (Placed next to brand) -->
                <div class="flex items-center gap-6 sm:gap-8">
                    <Link href="/" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/20">
                            <IconShieldCheck class="h-5 w-5" stroke-width="2.2" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold tracking-tight text-slate-900">Safe<span class="text-emerald-700">Give</span></span>
                            <span class="text-[10px] font-medium tracking-wide text-slate-400 uppercase">Blockchain Donate</span>
                        </div>
                    </Link>

                    <!-- Navigation Link -->
                    <!-- <nav class="flex items-center">
                        <button
                            type="button"
                            @click="scrollToSection('kampanye')"
                            class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:bg-[#edf4e9] hover:text-emerald-700"
                        >
                            Kampanye
                        </button>
                    </nav> -->
                </div>

                <!-- Auth Action Buttons -->
                <div class="flex items-center gap-3">
                    <template v-if="user">
                        <Link
                            href="/dashboard"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-xs sm:text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800"
                        >
                            <span>Dashboard</span>
                            <IconArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-xl border border-[#dce6d8] bg-white px-4 py-2 text-xs sm:text-sm font-bold text-slate-700 shadow-sm transition hover:bg-[#edf4e9] hover:text-emerald-800"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-700 px-4 py-2 text-xs sm:text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800"
                        >
                            <span>Daftar</span>
                            <IconArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- SIMPLE HERO SECTION -->
        <section class="border-b border-[#dce6d8] bg-white py-14 sm:py-20">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-8">
                <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-200/60 mb-5">
                    <IconShieldCheck class="h-4 w-4 text-emerald-700" />
                    <span>Transparansi Donasi Digital</span>
                </div>

                <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl lg:text-5xl leading-tight">
                    Platform Donasi Transparan Berbasis Blockchain
                </h1>

                <p class="mt-5 text-sm sm:text-base leading-relaxed text-slate-600 max-w-2xl mx-auto">
                    SafeGive adalah platform donasi digital berbasis teknologi blockchain yang menjamin setiap dana kebaikan tercatat secara aman, terbuka, dan dapat dipantau langsung tanpa perantara tersembunyi demi terwujudnya transparansi donasi yang terpercaya.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3.5">
                    <button
                        type="button"
                        @click="scrollToSection('kampanye')"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 active:scale-95"
                    >
                        <IconHeartHandshake class="h-4 w-4" />
                        <span>Lihat Kampanye</span>
                    </button>

                    <Link
                        href="/campaigns/create"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:border-emerald-300 hover:bg-[#edf4e9] hover:text-emerald-800 active:scale-95"
                    >
                        <span>Galang Dana</span>
                        <IconArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- KAMPANYE SECTION -->
        <section id="kampanye" class="py-12 sm:py-16 max-w-6xl mx-auto px-4 sm:px-8">
            <div class="mb-8">
                <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Kampanye Donasi
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-600">
                    Pilih program kebaikan dan salurkan donasi Anda secara langsung.
                </p>
            </div>

            <!-- SEARCH & CATEGORY FILTER -->
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Category Tabs -->
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition"
                        :class="selectedCategory === 'Semua' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white border border-[#dce6d8] text-slate-700 hover:bg-[#edf4e9]'"
                        @click="selectedCategory = 'Semua'"
                    >
                        Semua
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        type="button"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition"
                        :class="selectedCategory === cat ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white border border-[#dce6d8] text-slate-700 hover:bg-[#edf4e9]'"
                        @click="selectedCategory = cat"
                    >
                        {{ cat }}
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <IconSearch class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari kampanye..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs font-semibold text-slate-900 shadow-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    />
                </div>
            </div>

            <!-- CAMPAIGNS GRID -->
            <div v-if="filteredCampaigns.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="campaign in filteredCampaigns"
                    :key="campaign.id"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm transition hover:shadow-md"
                >
                    <!-- Cover Image Container -->
                    <div class="relative aspect-[16/10] overflow-hidden bg-[#edf4e9]">
                        <img
                            v-if="campaign.image_url"
                            :src="campaign.image_url"
                            :alt="campaign.title"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        />
                        <div v-else class="flex h-full items-center justify-center text-emerald-700">
                            <IconHeart class="h-10 w-10" stroke-width="1.5" />
                        </div>

                        <!-- Category & Status Badge -->
                        <div class="absolute left-3 top-3 flex items-center gap-2">
                            <span class="rounded-lg bg-white/95 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-emerald-800 shadow-xs">
                                {{ campaign.category }}
                            </span>
                        </div>
                        <div class="absolute right-3 top-3">
                            <span
                                class="rounded-lg px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wide shadow-xs"
                                :class="campaign.status === 'goal_reached' ? 'bg-orange-500 text-white' : 'bg-emerald-700 text-white'"
                            >
                                {{ campaign.status === 'goal_reached' ? 'Target Tercapai' : 'Aktif' }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1.5">
                            <span>Oleh</span>
                            <span class="font-bold text-slate-800">{{ campaign.organizer?.name || 'Penyelenggara Terverifikasi' }}</span>
                            <IconCircleCheck class="h-3.5 w-3.5 text-emerald-600" />
                        </div>

                        <h3 class="line-clamp-2 text-base font-black leading-snug text-slate-900 group-hover:text-emerald-700 transition">
                            {{ campaign.title }}
                        </h3>

                        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-600">
                            {{ campaign.description }}
                        </p>

                        <!-- Progress Bar & Target -->
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs font-bold mb-1">
                                <span class="text-emerald-700">{{ campaign.progress_percentage.toFixed(1) }}%</span>
                                <span class="text-slate-800">Target {{ formatRupiah(campaign.target_amount) }}</span>
                            </div>

                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-emerald-600 transition-all duration-300"
                                    :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }"
                                ></div>
                            </div>

                            <div class="mt-2.5 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1">
                                    <IconUsers class="h-3.5 w-3.5 text-emerald-600" />
                                    {{ campaign.donors_count }} Donatur
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <IconCalendar class="h-3.5 w-3.5 text-slate-400" />
                                    {{ formatDate(campaign.ends_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Actions: Salin Link & Donasi Sekarang -->
                        <div class="mt-4 grid grid-cols-2 gap-2 pt-2">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-[#dce6d8] bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-xs transition hover:border-emerald-300 hover:bg-[#edf4e9] hover:text-emerald-800 active:scale-95"
                                @click="copyCampaignLink(campaign.id, $event)"
                            >
                                <IconCheck v-if="copiedCampaignId === campaign.id" class="h-3.5 w-3.5 text-emerald-600" />
                                <IconCopy v-else class="h-3.5 w-3.5 text-slate-500" />
                                <span>{{ copiedCampaignId === campaign.id ? 'Tersalin!' : 'Salin Link' }}</span>
                            </button>

                            <Link
                                :href="`/campaigns/${campaign.id}`"
                                class="inline-flex items-center justify-center gap-1 rounded-xl bg-emerald-700 px-3 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-800 active:scale-95 text-center"
                            >
                                <span>Detail Kampanye</span>
                                <IconArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-2xl border border-dashed border-[#cbd8c6] bg-white p-10 text-center">
                <p class="text-sm font-bold text-slate-700">Tidak ada kampanye ditemukan</p>
                <p class="mt-1 text-xs text-slate-500">Coba ubah kata kunci pencarian atau pilih kategori lainnya.</p>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-[#dce6d8] bg-white py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Brand Logo (Identical to Dashboard) -->
                    <Link href="/" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/20">
                            <IconShieldCheck class="h-4.5 w-4.5" stroke-width="2.2" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold tracking-tight text-slate-900">Safe<span class="text-emerald-700">Give</span></span>
                            <span class="text-[9px] font-medium tracking-wide text-slate-400 uppercase">Blockchain Donate</span>
                        </div>
                    </Link>

                    <!-- Simple Navigation (Only Kampanye) -->
                    <div class="flex items-center gap-6 text-xs font-bold text-slate-600">
                        <button type="button" @click="scrollToSection('kampanye')" class="hover:text-emerald-700">
                            Kampanye
                        </button>
                        <template v-if="user">
                            <Link href="/dashboard" class="hover:text-emerald-700">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link href="/login" class="hover:text-emerald-700">Masuk</Link>
                        </template>
                    </div>

                    <!-- Copyright -->
                    <p class="text-xs text-slate-400">
                        © 2026 SafeGive. Seluruh hak cipta dilindungi.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
