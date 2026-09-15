<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { IconCheck, IconClock, IconDownload, IconFileDescription, IconX } from '@tabler/icons-vue';
import { ref } from 'vue';

interface Profile {
    id: number;
    entity_type: 'individual' | 'foundation';
    legal_name: string;
    status: 'pending' | 'verified' | 'rejected';
    review_notes: string | null;
    submitted_at: string;
    individual_nik: string | null;
    individual_address: string | null;
    individual_call_center: string | null;
    foundation_responsible_name: string | null;
    foundation_npwp: string | null;
    foundation_call_center: string | null;
    documents: { selfie: boolean; ktp: boolean; legalitas: boolean };
    user: { name: string; email: string };
}

const props = defineProps<{
    profiles: { data: Profile[]; current_page: number; last_page: number };
    activeStatus: string;
}>();

const selectedId = ref<number | null>(null);
const rejectId = ref<number | null>(null);
const rejectForm = useForm({ review_notes: '' });
const statuses = [
    { value: 'all', label: 'Semua' },
    { value: 'pending', label: 'Menunggu' },
    { value: 'verified', label: 'Diterima' },
    { value: 'rejected', label: 'Ditolak' },
];

const filter = (status: string) => router.get('/admin/verifications', status === 'all' ? {} : { status }, { preserveScroll: true, preserveState: true });
const approve = (id: number) => router.post(`/admin/verifications/${id}/approve`, {}, { preserveScroll: true });
const openReject = (id: number) => { rejectId.value = id; rejectForm.reset(); };
const reject = () => {
    if (!rejectId.value) return;
    rejectForm.post(`/admin/verifications/${rejectId.value}/reject`, { preserveScroll: true, onSuccess: () => { rejectId.value = null; } });
};
const formatDate = (date: string) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date));
const documentUrl = (profile: Profile, key: 'selfie' | 'ktp' | 'legalitas') => `/admin/verifications/${profile.id}/documents/${key}`;
</script>

<template>
    <AppLayout>
        <Head title="Verifikasi Pengguna - Admin" />
        <div class="space-y-7">
            <header>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Panel admin</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Pengajuan verifikasi</h1>
                <p class="mt-2 text-sm text-slate-600">Periksa identitas penyelenggara sebelum akses pembuatan kampanye dibuka.</p>
            </header>

            <nav class="flex flex-wrap gap-2" aria-label="Filter status verifikasi">
                <button v-for="status in statuses" :key="status.value" type="button" class="rounded-xl border px-4 py-2 text-sm font-bold transition" :class="props.activeStatus === status.value ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700'" @click="filter(status.value)">
                    {{ status.label }}
                </button>
            </nav>

            <section v-if="profiles.data.length" class="space-y-4">
                <article v-for="profile in profiles.data" :key="profile.id" class="rounded-2xl border border-[#dce6d8] bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-extrabold text-slate-900">{{ profile.legal_name }}</h2>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase" :class="{ 'bg-amber-50 text-amber-700': profile.status === 'pending', 'bg-emerald-50 text-emerald-700': profile.status === 'verified', 'bg-red-50 text-red-700': profile.status === 'rejected' }">{{ profile.status }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ profile.entity_type === 'individual' ? 'Individu' : 'Yayasan' }} · {{ profile.user.name }} · {{ profile.user.email }}</p>
                            <p class="mt-1 text-xs text-slate-400">Dikirim {{ formatDate(profile.submitted_at) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button v-if="profile.status === 'pending'" type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800" @click="approve(profile.id)"><IconCheck class="h-4 w-4" />Terima</button>
                            <button v-if="profile.status === 'pending'" type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50" @click="openReject(profile.id)"><IconX class="h-4 w-4" />Tolak</button>
                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50" @click="selectedId = selectedId === profile.id ? null : profile.id">{{ selectedId === profile.id ? 'Tutup detail' : 'Lihat detail' }}</button>
                        </div>
                    </div>

                    <div v-if="selectedId === profile.id" class="mt-5 border-t border-slate-100 pt-5">
                        <dl class="grid gap-4 text-sm sm:grid-cols-2">
                            <div v-if="profile.entity_type === 'individual'"><dt class="font-bold text-slate-400">NIK</dt><dd class="mt-1 font-semibold text-slate-800">{{ profile.individual_nik || '-' }}</dd></div>
                            <div v-if="profile.entity_type === 'individual'"><dt class="font-bold text-slate-400">Alamat</dt><dd class="mt-1 font-semibold text-slate-800">{{ profile.individual_address || '-' }}</dd></div>
                            <div v-if="profile.entity_type === 'individual'"><dt class="font-bold text-slate-400">Call center</dt><dd class="mt-1 font-semibold text-slate-800">{{ profile.individual_call_center || '-' }}</dd></div>
                            <div v-if="profile.entity_type === 'foundation'"><dt class="font-bold text-slate-400">Penanggung jawab</dt><dd class="mt-1 font-semibold text-slate-800">{{ profile.foundation_responsible_name || '-' }}</dd></div>
                            <div v-if="profile.entity_type === 'foundation'"><dt class="font-bold text-slate-400">NPWP yayasan</dt><dd class="mt-1 font-semibold text-slate-800">{{ profile.foundation_npwp || '-' }}</dd></div>
                            <div v-if="profile.entity_type === 'foundation'"><dt class="font-bold text-slate-400">Call center</dt><dd class="mt-1 font-semibold text-slate-800">{{ profile.foundation_call_center || '-' }}</dd></div>
                        </dl>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <a v-if="profile.documents.selfie" :href="documentUrl(profile, 'selfie')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"><IconDownload class="h-4 w-4" />Selfie</a>
                            <a v-if="profile.documents.ktp" :href="documentUrl(profile, 'ktp')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"><IconFileDescription class="h-4 w-4" />KTP</a>
                            <a v-if="profile.documents.legalitas" :href="documentUrl(profile, 'legalitas')" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"><IconFileDescription class="h-4 w-4" />Legalitas yayasan</a>
                        </div>
                        <p v-if="profile.review_notes" class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">Catatan: {{ profile.review_notes }}</p>
                    </div>
                </article>
            </section>
            <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500">Belum ada pengajuan pada filter ini.</div>

            <div v-if="rejectId" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/30 p-4">
                <form class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @submit.prevent="reject">
                    <h2 class="text-lg font-bold text-slate-900">Tolak pengajuan</h2>
                    <p class="mt-1 text-sm text-slate-500">Tulis alasan agar pemohon dapat memperbaiki data.</p>
                    <textarea v-model="rejectForm.review_notes" rows="4" class="mt-4 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: foto KTP belum terbaca jelas" />
                    <p v-if="rejectForm.errors.review_notes" class="mt-1 text-xs text-red-600">{{ rejectForm.errors.review_notes }}</p>
                    <div class="mt-4 flex justify-end gap-2"><button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600" @click="rejectId = null">Batal</button><button type="submit" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-bold text-white">Kirim penolakan</button></div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
