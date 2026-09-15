<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import {
    IconArrowUpRight,
    IconBuildingBank,
    IconCheck,
    IconClock,
    IconCoin,
    IconFileCheck,
    IconHeartHandshake,
    IconUsers,
} from '@tabler/icons-vue';

interface Stats {
    users: number;
    campaigns: number;
    donations: number;
    confirmedDonations: number;
    pendingDonations: number;
    pendingVerifications: number;
    pendingWithdrawals: number;
}

interface RecentDonation {
    id: number;
    campaign: string;
    donor: string;
    amount: number;
    status: 'pending' | 'confirmed' | 'failed';
    createdAt: string | null;
}

interface RecentCampaign {
    id: number;
    title: string;
    organizer: string;
    status: string;
    progress: number;
    target: number;
}

const page = usePage<{
    auth: { user: { name: string } };
    stats: Stats;
    recentDonations: RecentDonation[];
    recentCampaigns: RecentCampaign[];
}>();

const stats = computed(() => page.props.stats);
const recentDonations = computed(() => page.props.recentDonations ?? []);
const recentCampaigns = computed(() => page.props.recentCampaigns ?? []);

const formatRupiah = (value: number): string => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const statusLabel = (status: string): string => ({
    pending: 'Menunggu',
    confirmed: 'Terkonfirmasi',
    failed: 'Ditolak',
    active: 'Aktif',
    goal_reached: 'Target tercapai',
    expired: 'Berakhir',
    withdrawn: 'Dicairkan',
    draft: 'Draft',
}[status] ?? status);
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Admin - SafeGive" />

        <div class="space-y-8">
            <section class="relative overflow-hidden rounded-[28px] border border-emerald-200 bg-gradient-to-br from-emerald-900 via-emerald-800 to-slate-900 p-6 text-white shadow-[0_16px_40px_rgba(40,60,35,0.14)] sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl"></div>
                <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-200">Panel Administrasi</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Selamat datang, {{ page.props.auth.user.name }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-emerald-100">Pantau donasi, kampanye, verifikasi, dan pencairan berdasarkan data terbaru dari sistem.</p>
                    </div>
                    <Link href="/admin/donations" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-emerald-900 transition hover:bg-emerald-50">
                        Kelola donasi
                        <IconArrowUpRight class="h-4 w-4" />
                    </Link>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Pengguna</span>
                        <IconUsers class="h-5 w-5 text-emerald-700" />
                    </div>
                    <p class="mt-4 text-3xl font-black text-slate-900">{{ stats.users }}</p>
                    <p class="mt-1 text-xs text-slate-500">Akun terdaftar</p>
                </div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Kampanye</span>
                        <IconHeartHandshake class="h-5 w-5 text-emerald-700" />
                    </div>
                    <p class="mt-4 text-3xl font-black text-slate-900">{{ stats.campaigns }}</p>
                    <p class="mt-1 text-xs text-slate-500">Seluruh status kampanye</p>
                </div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Donasi</span>
                        <IconCoin class="h-5 w-5 text-emerald-700" />
                    </div>
                    <p class="mt-4 text-3xl font-black text-slate-900">{{ stats.donations }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ stats.confirmedDonations }} terkonfirmasi, {{ stats.pendingDonations }} menunggu</p>
                </div>
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Perlu tindakan</span>
                        <IconClock class="h-5 w-5 text-amber-600" />
                    </div>
                    <p class="mt-4 text-3xl font-black text-slate-900">{{ stats.pendingVerifications + stats.pendingWithdrawals }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ stats.pendingVerifications }} verifikasi, {{ stats.pendingWithdrawals }} pencairan</p>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
                <section class="overflow-hidden rounded-[24px] border border-[#dce6d8] bg-white shadow-sm xl:col-span-7">
                    <div class="flex items-center justify-between border-b border-slate-100 p-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Donasi terbaru</h2>
                            <p class="mt-1 text-xs text-slate-500">Data terbaru dari catatan donasi.</p>
                        </div>
                        <Link href="/admin/donations" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">Lihat semua</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-6 py-3">Donatur</th>
                                    <th class="py-3">Kampanye</th>
                                    <th class="py-3 text-right">Nominal</th>
                                    <th class="px-6 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="donation in recentDonations" :key="donation.id" class="hover:bg-[#f8faf6]">
                                    <td class="px-6 py-3.5 font-semibold text-slate-800">{{ donation.donor }}<span class="mt-0.5 block text-[10px] font-normal text-slate-400">{{ donation.createdAt }}</span></td>
                                    <td class="max-w-[180px] truncate py-3.5 text-slate-600">{{ donation.campaign }}</td>
                                    <td class="py-3.5 text-right font-bold text-slate-800">{{ formatRupiah(donation.amount) }}</td>
                                    <td class="px-6 py-3.5 text-right"><span class="rounded-full px-2 py-1 text-[10px] font-bold" :class="donation.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : donation.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'">{{ statusLabel(donation.status) }}</span></td>
                                </tr>
                                <tr v-if="recentDonations.length === 0"><td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada data donasi.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[24px] border border-[#dce6d8] bg-white shadow-sm xl:col-span-5">
                    <div class="flex items-center justify-between border-b border-slate-100 p-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Kampanye terbaru</h2>
                            <p class="mt-1 text-xs text-slate-500">Status dan progres kampanye.</p>
                        </div>
                        <Link href="/campaigns" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">Lihat semua</Link>
                    </div>
                    <div class="space-y-4 p-6">
                        <div v-for="campaign in recentCampaigns" :key="campaign.id" class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ campaign.title }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">{{ campaign.organizer }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">{{ statusLabel(campaign.status) }}</span>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" :style="{ width: `${Math.min(100, campaign.progress)}%` }"></div></div>
                                <span class="text-xs font-bold text-emerald-700">{{ Math.round(campaign.progress) }}%</span>
                            </div>
                            <p class="mt-1 text-right text-[10px] text-slate-400">Target {{ formatRupiah(campaign.target) }}</p>
                        </div>
                        <p v-if="recentCampaigns.length === 0" class="py-4 text-center text-xs text-slate-400">Belum ada kampanye.</p>
                    </div>
                </section>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link href="/admin/verifications?status=pending" class="group rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
                    <IconFileCheck class="h-5 w-5 text-emerald-700" />
                    <p class="mt-3 text-sm font-bold text-slate-900">Verifikasi profil</p>
                    <p class="mt-1 text-xs text-slate-500">{{ stats.pendingVerifications }} pengajuan menunggu pemeriksaan.</p>
                </Link>
                <Link href="/admin/donations?status=pending" class="group rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
                    <IconCheck class="h-5 w-5 text-emerald-700" />
                    <p class="mt-3 text-sm font-bold text-slate-900">Konfirmasi donasi</p>
                    <p class="mt-1 text-xs text-slate-500">{{ stats.pendingDonations }} donasi menunggu konfirmasi admin.</p>
                </Link>
                <Link href="/admin/withdrawals?status=pending" class="group rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
                    <IconBuildingBank class="h-5 w-5 text-emerald-700" />
                    <p class="mt-3 text-sm font-bold text-slate-900">Pencairan dana</p>
                    <p class="mt-1 text-xs text-slate-500">{{ stats.pendingWithdrawals }} pengajuan menunggu persetujuan.</p>
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
