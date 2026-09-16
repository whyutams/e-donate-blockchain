<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    IconArrowLeft,
    IconArrowRight,
    IconBuildingBank,
    IconCalendar,
    IconCheck,
    IconCircleCheck,
    IconClock,
    IconCopy,
    IconHeart,
    IconHeartHandshake,
    IconLock,
    IconShieldCheck,
    IconUsers,
    IconVideo,
    IconBrandInstagram,
    IconBrandFacebook,
    IconBrandTiktok,
    IconExternalLink,
    IconFileCheck,
} from '@tabler/icons-vue';

interface Donation {
    id: number;
    is_anonymous?: boolean;
    donor_name: string;
    encrypted_donor_name?: string | null;
    donor_name_commitment?: string | null;
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
    slug: string;
    description: string;
    category: string;
    image_url: string | null;
    target_amount: number;
    collected_amount: number;
    remaining_amount: number;
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
    video_url: string | null;
    organizer: { id?: number; name: string };
    donations: Donation[];
}

const props = defineProps<{ campaign: Campaign }>();
const page = usePage<{ auth: { user: { name: string; email: string } | null } }>();
const user = computed(() => page.props.auth?.user);

const copiedCampaignLink = ref(false);
const copyCampaignLink = () => {
    const identifier = props.campaign.slug || props.campaign.id;
    const url = `${window.location.origin}/campaigns/${identifier}`;
    navigator.clipboard.writeText(url);
    copiedCampaignLink.value = true;
    setTimeout(() => {
        copiedCampaignLink.value = false;
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

const copiedVideoUrl = ref(false);
const copyVideoUrl = () => {
    if (props.campaign.video_url) {
        navigator.clipboard.writeText(props.campaign.video_url);
        copiedVideoUrl.value = true;
        setTimeout(() => {
            copiedVideoUrl.value = false;
        }, 2000);
    }
};

const formatRupiah = (value: number) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const formatDate = (value: string) => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
}).format(new Date(value));

const formatDateTime = (value: string) => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date(value));

const shortHash = (hash: string) => `${hash.slice(0, 8)}...${hash.slice(-6)}`;

const getPlatformInfo = (url: string | null | undefined) => {
    if (!url) return null;
    const u = url.toLowerCase().trim();
    if (u.includes('instagram.com') || u.includes('instagr.am')) {
        return {
            name: 'Instagram',
            type: 'instagram',
            badgeClass: 'bg-gradient-to-r from-pink-500/10 via-purple-500/10 to-amber-500/10 text-pink-700 border-pink-200',
            badgeIconClass: 'text-pink-600',
            buttonClass: 'bg-gradient-to-r from-pink-600 via-purple-600 to-amber-600 hover:opacity-90 text-white shadow-pink-600/20',
            label: 'Instagram Video / Reel',
        };
    }
    if (u.includes('tiktok.com')) {
        return {
            name: 'TikTok',
            type: 'tiktok',
            badgeClass: 'bg-slate-900/10 text-slate-900 border-slate-300',
            badgeIconClass: 'text-slate-900',
            buttonClass: 'bg-slate-900 hover:bg-black text-white shadow-slate-900/20',
            label: 'TikTok Video',
        };
    }
    if (u.includes('facebook.com') || u.includes('fb.watch') || u.includes('fb.com')) {
        return {
            name: 'Facebook',
            type: 'facebook',
            badgeClass: 'bg-blue-50 text-blue-700 border-blue-200',
            badgeIconClass: 'text-blue-600',
            buttonClass: 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-600/20',
            label: 'Facebook Video / Reel',
        };
    }
    return null;
};
</script>

<template>
    <div class="min-h-screen bg-[#f3f6ef] text-slate-900 font-sans selection:bg-emerald-200 selection:text-emerald-900">
        <Head :title="`${campaign.title} - SafeGive`" />

        <!-- NAVBAR -->
        <header class="sticky top-0 z-40 border-b border-[#dce6d8] bg-white/95 backdrop-blur-md">
            <div class="mx-auto flex h-18 max-w-6xl items-center justify-between px-4 sm:px-8">
                <!-- Brand Logo & Navlink -->
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

                    <!-- Navlink to Home/Kampanye -->
                    <nav class="flex items-center">
                        <Link
                            href="/#kampanye"
                            class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:bg-[#edf4e9] hover:text-emerald-700"
                        >
                            Kampanye
                        </Link>
                    </nav>
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

        <!-- MAIN CONTAINER -->
        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-8 sm:py-10">
            <!-- Breadcrumbs / Back Link -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <Link
                    href="/#kampanye"
                    class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-slate-600 transition hover:text-emerald-700"
                >
                    <IconArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Beranda Kampanye</span>
                </Link>

                <button
                    type="button"
                    @click="copyCampaignLink"
                    class="inline-flex items-center gap-2 rounded-xl border border-[#dce6d8] bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-xs transition hover:border-emerald-300 hover:bg-[#edf4e9] hover:text-emerald-800 active:scale-95"
                >
                    <IconCheck v-if="copiedCampaignLink" class="h-4 w-4 text-emerald-600" />
                    <IconCopy v-else class="h-4 w-4 text-slate-500" />
                    <span>{{ copiedCampaignLink ? 'Link Tersalin ke Clipboard!' : 'Salin Link Kampanye' }}</span>
                </button>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- LEFT CONTENT AREA (8 Cols) -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Campaign Hero Header Card -->
                    <div class="overflow-hidden rounded-2xl border border-[#dce6d8] bg-white shadow-sm">
                        <!-- Cover Image -->
                        <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#edf4e9]">
                            <img
                                v-if="campaign.image_url"
                                :src="campaign.image_url"
                                :alt="campaign.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full items-center justify-center text-emerald-700">
                                <IconHeart class="h-16 w-16" stroke-width="1.5" />
                            </div>

                            <!-- Category & Status Badge Overlay -->
                            <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                                <span class="rounded-xl bg-white/95 backdrop-blur-xs px-3 py-1 text-xs font-black uppercase tracking-wide text-emerald-800 shadow-sm">
                                    {{ campaign.category }}
                                </span>
                            </div>
                            <div class="absolute right-4 top-4">
                                <span
                                    class="rounded-xl px-3 py-1 text-xs font-black uppercase tracking-wide shadow-sm"
                                    :class="campaign.status === 'goal_reached' ? 'bg-orange-500 text-white' : (campaign.status === 'withdrawn' ? 'bg-blue-600 text-white' : 'bg-emerald-700 text-white')"
                                >
                                    {{ campaign.status === 'goal_reached' ? 'Target Tercapai' : (campaign.status === 'withdrawn' ? 'Dana Dicairkan' : 'Aktif') }}
                                </span>
                            </div>
                        </div>

                        <!-- Header Info -->
                        <div class="p-6 sm:p-8">
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl lg:text-4xl leading-snug">
                                {{ campaign.title }}
                            </h1>

                            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs sm:text-sm text-slate-600 pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                                        {{ (campaign.organizer?.name || 'SG').slice(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1 font-bold text-slate-900">
                                            <span>{{ campaign.organizer?.name || 'Penyelenggara Terverifikasi' }}</span>
                                            <IconCircleCheck class="h-4 w-4 text-emerald-600" />
                                        </div>
                                        <span class="text-[11px] text-slate-400">Penyelenggara Program</span>
                                    </div>
                                </div>

                                <div class="hidden sm:block h-6 w-px bg-slate-200"></div>

                                <div class="flex items-center gap-1.5 text-slate-500">
                                    <IconClock class="h-4 w-4 text-slate-400" />
                                    <span>Dimulai: {{ formatDate(campaign.starts_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Story & Details Section -->
                    <div class="rounded-2xl border border-[#dce6d8] bg-white p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-5 w-1.5 rounded-full bg-emerald-600"></div>
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900">Cerita Penggalangan Dana</h2>
                        </div>

                        <div class="prose max-w-none text-sm sm:text-base leading-relaxed text-slate-700 whitespace-pre-line">
                            {{ campaign.description }}
                        </div>
                    </div>

                    <!-- Social Video Panel (If Provided) -->
                    <div
                        v-if="campaign.video_url && getPlatformInfo(campaign.video_url)"
                        class="rounded-2xl border border-[#dce6d8] bg-white p-6 sm:p-8 shadow-sm"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-50 text-purple-700">
                                    <IconVideo class="h-4 w-4" />
                                </div>
                                <h2 class="text-lg font-bold text-slate-900">Dokumentasi Video Penyaluran</h2>
                            </div>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold"
                                :class="getPlatformInfo(campaign.video_url)?.badgeClass"
                            >
                                <IconBrandInstagram v-if="getPlatformInfo(campaign.video_url)?.type === 'instagram'" class="h-3.5 w-3.5" />
                                <IconBrandTiktok v-else-if="getPlatformInfo(campaign.video_url)?.type === 'tiktok'" class="h-3.5 w-3.5" />
                                <IconBrandFacebook v-else-if="getPlatformInfo(campaign.video_url)?.type === 'facebook'" class="h-3.5 w-3.5" />
                                <span>{{ getPlatformInfo(campaign.video_url)?.label }}</span>
                            </span>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                                Penyelenggara telah mempublikasikan video transparansi bukti penyaluran dana kampanye ini di <strong>{{ getPlatformInfo(campaign.video_url)?.name }}</strong>.
                            </p>

                            <div class="flex flex-wrap items-center gap-3">
                                <a
                                    :href="campaign.video_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold shadow-sm transition active:scale-95"
                                    :class="getPlatformInfo(campaign.video_url)?.buttonClass || 'bg-emerald-700 text-white hover:bg-emerald-800'"
                                >
                                    <span>Tonton Video Dokumentasi</span>
                                    <IconExternalLink class="h-4 w-4" />
                                </a>

                                <button
                                    type="button"
                                    @click="copyVideoUrl"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-[#dce6d8] bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs transition hover:bg-[#edf4e9]"
                                >
                                    <IconCheck v-if="copiedVideoUrl" class="h-4 w-4 text-emerald-600" />
                                    <IconCopy v-else class="h-4 w-4 text-slate-500" />
                                    <span>{{ copiedVideoUrl ? 'Tersalin!' : 'Salin Link Video' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Donor History / Blockchain Transparency Table -->
                    <div class="rounded-2xl border border-[#dce6d8] bg-white p-6 sm:p-8 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-2">
                                <div class="h-5 w-1.5 rounded-full bg-emerald-600"></div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900">
                                    Daftar Donatur & Riwayat Blockchain
                                </h2>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200/60">
                                {{ campaign.donations.length }} Donasi Tercatat
                            </span>
                        </div>

                        <!-- Donations List -->
                        <div v-if="campaign.donations.length > 0" class="space-y-3">
                            <div
                                v-for="donation in campaign.donations"
                                :key="donation.id"
                                class="rounded-xl border border-[#e6eee3] bg-[#fafdff] p-4 transition hover:border-emerald-300"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <template v-if="donation.is_anonymous">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-emerald-900 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-lg" :title="donation.encrypted_donor_name || donation.donor_name">
                                                        <IconLock class="h-3.5 w-3.5 text-emerald-700 shrink-0" />
                                                        <span class="font-mono">{{ shortHash(donation.encrypted_donor_name || donation.donor_name) }}</span>
                                                    </span>
                                                    <span class="rounded bg-emerald-100/70 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800">
                                                        Terenkripsi
                                                    </span>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <span class="font-bold text-slate-900 text-sm">
                                                    {{ donation.donor_name }}
                                                </span>
                                            </template>
                                        </div>

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                            <span>Metode: <strong class="text-slate-700">{{ donation.payment_method }}</strong></span>
                                            <span>•</span>
                                            <span>{{ formatDateTime(donation.created_at) }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <span
                                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold"
                                            :class="donation.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                        >
                                            <IconCircleCheck v-if="donation.status === 'confirmed'" class="h-3.5 w-3.5" />
                                            <IconClock v-else class="h-3.5 w-3.5" />
                                            <span>{{ donation.status === 'confirmed' ? 'Terkonfirmasi' : 'Menunggu' }}</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Blockchain Record -->
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white border border-slate-100 p-2.5 text-[11px]">
                                    <div class="flex items-center gap-2 font-mono text-slate-600">
                                        <span class="text-slate-400">Tx:</span>
                                        <span class="font-bold text-emerald-800">{{ shortHash(donation.transaction_hash) }}</span>
                                        <button
                                            type="button"
                                            @click="copyHash(donation.transaction_hash)"
                                            class="text-slate-400 hover:text-emerald-700 transition"
                                            title="Salin Tx Hash"
                                        >
                                            <IconCheck v-if="copiedHash === donation.transaction_hash" class="h-3.5 w-3.5 text-emerald-600" />
                                            <IconCopy v-else class="h-3.5 w-3.5" />
                                        </button>
                                    </div>

                                    <div v-if="donation.block_number" class="text-slate-500 font-medium">
                                        Blok #{{ donation.block_number }}
                                    </div>
                                </div>

                                <!-- Paillier Commitment if anonymous -->
                                <div v-if="donation.donor_name_commitment" class="mt-1.5 px-2 text-[10px] text-slate-400 font-mono">
                                    Komitmen SHA-256: {{ shortHash(donation.donor_name_commitment) }}
                                </div>
                            </div>
                        </div>

                        <!-- Empty Donations -->
                        <div v-else class="rounded-xl border border-dashed border-[#dce6d8] bg-[#f8faf6] p-8 text-center">
                            <IconHeartHandshake class="mx-auto h-10 w-10 text-emerald-600/60 mb-2" />
                            <p class="text-sm font-bold text-slate-700">Belum Ada Donasi Tercatat</p>
                            <p class="mt-1 text-xs text-slate-500">Jadilah yang pertama menyalurkan kebaikan untuk kampanye ini.</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDEBAR (4 Cols - Sticky Card) -->
                <div class="space-y-6 lg:col-span-4">
                    <!-- Progress & Target Card -->
                    <div class="sticky top-24 space-y-6">
                        <div class="rounded-2xl border border-[#dce6d8] bg-white p-6 shadow-sm">
                            <div class="space-y-4">
                                <div>
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dana Terkumpul</span>
                                    <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-0.5">
                                        {{ formatRupiah(campaign.collected_amount) }}
                                    </div>
                                    <div class="mt-1 flex items-center justify-between text-xs text-slate-600 font-medium">
                                        <span>dari target <strong>{{ formatRupiah(campaign.target_amount) }}</strong></span>
                                        <span class="font-bold text-emerald-700">{{ campaign.progress_percentage.toFixed(1) }}%</span>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-emerald-600 transition-all duration-500"
                                        :style="{ width: `${Math.min(campaign.progress_percentage, 100)}%` }"
                                    ></div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
                                    <div class="rounded-xl bg-[#f8faf6] p-3 border border-[#e6eee3]">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1">
                                            <IconUsers class="h-3.5 w-3.5 text-emerald-600" />
                                            <span>Donatur</span>
                                        </div>
                                        <div class="text-base font-bold text-slate-900">
                                            {{ campaign.donors_count }} orang
                                        </div>
                                    </div>

                                    <div class="rounded-xl bg-[#f8faf6] p-3 border border-[#e6eee3]">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1">
                                            <IconCalendar class="h-3.5 w-3.5 text-slate-400" />
                                            <span>Batas Waktu</span>
                                        </div>
                                        <div class="text-xs font-bold text-slate-900">
                                            {{ formatDate(campaign.ends_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CTA Box for Guests (Donasi Sekarang) -->
                            <div class="mt-6 rounded-xl border border-emerald-200/80 bg-gradient-to-b from-emerald-50/70 to-emerald-50/20 p-5">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-xs">
                                        <IconHeartHandshake class="h-4 w-4" />
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900">Ingin Berdonasi?</h3>
                                </div>

                                <p class="text-xs leading-relaxed text-slate-600 mb-4">
                                    Masuk ke akun Anda atau daftarkan akun baru untuk menyalurkan donasi dengan keamanan enkripsi Paillier dan verifikasi blockchain.
                                </p>

                                <div class="space-y-2">
                                    <Link
                                        href="/login"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 py-3 text-xs sm:text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 active:scale-95"
                                    >
                                        <span>Masuk untuk Berdonasi</span>
                                        <IconArrowRight class="h-4 w-4" />
                                    </Link>

                                    <Link
                                        href="/register"
                                        class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-emerald-300 bg-white py-2.5 text-xs font-bold text-emerald-800 shadow-xs transition hover:bg-emerald-50 active:scale-95"
                                    >
                                        <span>Daftar Akun Baru</span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Blockchain Transparency Box -->
                        <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm text-xs text-slate-600 space-y-3">
                            <div class="flex items-center gap-2 font-bold text-slate-900">
                                <IconShieldCheck class="h-4 w-4 text-emerald-600" />
                                <span>Keamanan & Transparansi</span>
                            </div>
                            <p class="leading-relaxed">
                                Setiap transaksi dicatat secara permanen di buku besar terdistribusi dan nominal donatur diproses dengan enkripsi homomorfik Paillier demi privasi mutlak.
                            </p>
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                                <IconFileCheck class="h-4 w-4 text-emerald-600" />
                                <span>Penyelenggara Terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-[#dce6d8] bg-white py-8 mt-12">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 text-center sm:flex-row sm:px-8 sm:text-left">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xs">
                        <IconShieldCheck class="h-4 w-4" stroke-width="2.2" />
                    </div>
                    <span class="text-sm font-bold text-slate-900">Safe<span class="text-emerald-700">Give</span></span>
                </div>
                <p class="text-xs text-slate-500">
                    &copy; {{ new Date().getFullYear() }} SafeGive. Platform Donasi Transparan Berbasis Blockchain.
                </p>
            </div>
        </footer>
    </div>
</template>
