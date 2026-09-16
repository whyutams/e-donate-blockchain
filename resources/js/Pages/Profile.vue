<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { IconCheck, IconFileUpload, IconLock, IconShieldCheck } from '@tabler/icons-vue';

interface User {
    name: string;
    email: string;
}

interface VerificationProfile {
    entity_type: 'individual' | 'foundation';
    legal_name: string;
    status: 'pending' | 'verified' | 'rejected';
    review_notes: string | null;
    verified_at: string | null;
}

const page = usePage<{ auth: { user: User | null }; verificationProfile: VerificationProfile | null }>();
const user = computed(() => page.props.auth.user);
const initials = computed(() => user.value?.name.split(' ').map((name) => name[0]).slice(0, 2).join('').toUpperCase() || 'SG');
const verificationProfile = computed(() => page.props.verificationProfile);
const submitted = ref(false);

const form = useForm<{
    entity_type: 'individual' | 'foundation';
    legal_name: string;
    individual_nik: string;
    individual_address: string;
    individual_call_center: string;
    individual_selfie: File | null;
    individual_ktp: File | null;
    foundation_responsible_name: string;
    foundation_npwp: string;
    foundation_call_center: string;
    foundation_legal_document: File | null;
    encrypted_identity_data: string;
}>({
    entity_type: verificationProfile.value?.entity_type || 'individual',
    legal_name: verificationProfile.value?.legal_name || '',
    individual_nik: '',
    individual_address: '',
    individual_call_center: '',
    individual_selfie: null,
    individual_ktp: null,
    foundation_responsible_name: '',
    foundation_npwp: '',
    foundation_call_center: '',
    foundation_legal_document: null,
    encrypted_identity_data: '',
});

const statusLabel = computed(() => ({
    pending: 'Sedang ditinjau',
    verified: 'Terverifikasi',
    rejected: 'Perlu diperbaiki',
}[verificationProfile.value?.status || 'pending']));

const submitVerification = () => {
    form.post('/verification-profile', {
        forceFormData: true,
        onSuccess: () => {
            submitted.value = true;
            form.reset('individual_selfie', 'individual_ktp', 'foundation_legal_document');
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Profile dan Verifikasi" />
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Akun saya</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Profile</h1>
            <p class="mt-2 text-slate-500">Kelola akun dan status verifikasi penyelenggara.</p>

            <section class="mt-8 rounded-3xl border border-[#dce6d8] bg-white p-6 shadow-[0_18px_55px_rgba(66,87,58,0.08)] sm:p-8">
                <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-[#fff0e9] text-lg font-bold text-[#c65d3d]">{{ initials }}</span>
                    <div><h2 class="text-lg font-bold text-slate-900">{{ user?.name || 'Pengguna' }}</h2><p class="mt-1 text-sm text-slate-500">{{ user?.email || 'Akun aktif' }}</p></div>
                </div>
                <dl class="mt-6 space-y-5"><div><dt class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nama lengkap</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ user?.name || '-' }}</dd></div><div><dt class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Email</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ user?.email || '-' }}</dd></div></dl>
            </section>

            <section class="mt-6 rounded-3xl border border-[#dce6d8] bg-white p-6 shadow-[0_18px_55px_rgba(66,87,58,0.08)] sm:p-8">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <IconShieldCheck class="h-5 w-5 text-emerald-700" />
                            <h2 class="text-lg font-bold text-slate-900">Verifikasi penyelenggara</h2>
                        </div>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">Verifikasi diperlukan sebelum membuat kampanye sebagai individu atau yayasan.</p>
                    </div>
                    <span v-if="verificationProfile" class="shrink-0 rounded-full px-3 py-1 text-xs font-bold" :class="{
                        'bg-amber-50 text-amber-700': verificationProfile.status === 'pending',
                        'bg-emerald-50 text-emerald-700': verificationProfile.status === 'verified',
                        'bg-red-50 text-red-700': verificationProfile.status === 'rejected',
                    }">{{ statusLabel }}</span>
                </div>

                <div v-if="verificationProfile?.status === 'verified'" class="mt-6 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800">
                    <IconCheck class="mt-0.5 h-5 w-5 shrink-0" />
                    <div><p class="font-bold">Profil sudah terverifikasi.</p><p class="mt-1">Anda dapat membuat kampanye dan mengelola pencairan sesuai aturan smart contract.</p></div>
                </div>
                <div v-else-if="verificationProfile?.status === 'pending'" class="mt-6 flex items-start gap-3 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800">
                    <IconLock class="mt-0.5 h-5 w-5 shrink-0" /><p>Dokumen sudah dikirim dan sedang ditinjau administrator.</p>
                </div>

                <form v-else class="mt-6 space-y-5" @submit.prevent="submitVerification">
                    <div v-if="verificationProfile?.review_notes" class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">{{ verificationProfile.review_notes }}</div>
                    <div>
                        <label class="text-sm font-bold text-slate-800">Jenis pendaftar</label>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            <label v-for="type in [{ value: 'individual', label: 'Individu' }, { value: 'foundation', label: 'Yayasan' }]" :key="type.value" class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-semibold transition" :class="form.entity_type === type.value ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : 'border-slate-200 text-slate-600 hover:border-emerald-300'">
                                <input v-model="form.entity_type" type="radio" :value="type.value" class="accent-emerald-600" />
                                {{ type.label }}
                            </label>
                        </div>
                        <p v-if="form.errors.entity_type" class="mt-1 text-xs text-red-600">{{ form.errors.entity_type }}</p>
                    </div>
                    <div>
                        <label for="legal_name" class="text-sm font-bold text-slate-800">Nama legal</label>
                        <input id="legal_name" v-model="form.legal_name" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" :placeholder="form.entity_type === 'individual' ? 'Nama lengkap sesuai KTP' : 'Nama resmi yayasan'" />
                        <p v-if="form.errors.legal_name" class="mt-1 text-xs text-red-600">{{ form.errors.legal_name }}</p>
                    </div>
                    <template v-if="form.entity_type === 'individual'">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block"><span class="text-sm font-bold text-slate-800">NIK</span><input v-model="form.individual_nik" inputmode="numeric" maxlength="16" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="16 digit NIK" /><span v-if="form.errors.individual_nik" class="mt-1 block text-xs text-red-600">{{ form.errors.individual_nik }}</span></label>
                            <label class="block"><span class="text-sm font-bold text-slate-800">Call center / WhatsApp</span><input v-model="form.individual_call_center" type="tel" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="08xxxxxxxxxx" /><span v-if="form.errors.individual_call_center" class="mt-1 block text-xs text-red-600">{{ form.errors.individual_call_center }}</span></label>
                        </div>
                        <label class="block"><span class="text-sm font-bold text-slate-800">Alamat</span><textarea v-model="form.individual_address" rows="3" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Alamat sesuai identitas" /><span v-if="form.errors.individual_address" class="mt-1 block text-xs text-red-600">{{ form.errors.individual_address }}</span></label>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block"><span class="text-sm font-bold text-slate-800">Foto selfie</span><input type="file" accept=".jpg,.jpeg,.png" class="mt-2 block w-full rounded-xl border border-slate-200 p-2 text-xs" @change="form.individual_selfie = ($event.target as HTMLInputElement).files?.[0] || null" /><span v-if="form.errors.individual_selfie" class="mt-1 block text-xs text-red-600">{{ form.errors.individual_selfie }}</span></label>
                            <label class="block"><span class="text-sm font-bold text-slate-800">Foto / scan KTP</span><input type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-2 block w-full rounded-xl border border-slate-200 p-2 text-xs" @change="form.individual_ktp = ($event.target as HTMLInputElement).files?.[0] || null" /><span v-if="form.errors.individual_ktp" class="mt-1 block text-xs text-red-600">{{ form.errors.individual_ktp }}</span></label>
                        </div>
                    </template>
                    <template v-else>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block"><span class="text-sm font-bold text-slate-800">Nama penanggung jawab</span><input v-model="form.foundation_responsible_name" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Nama pengurus" /><span v-if="form.errors.foundation_responsible_name" class="mt-1 block text-xs text-red-600">{{ form.errors.foundation_responsible_name }}</span></label>
                            <label class="block"><span class="text-sm font-bold text-slate-800">NPWP yayasan</span><input v-model="form.foundation_npwp" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Nomor NPWP" /><span v-if="form.errors.foundation_npwp" class="mt-1 block text-xs text-red-600">{{ form.errors.foundation_npwp }}</span></label>
                        </div>
                        <label class="block"><span class="text-sm font-bold text-slate-800">Call center / WhatsApp yayasan</span><input v-model="form.foundation_call_center" type="tel" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Nomor layanan yayasan" /><span v-if="form.errors.foundation_call_center" class="mt-1 block text-xs text-red-600">{{ form.errors.foundation_call_center }}</span></label>
                        <label class="block"><span class="text-sm font-bold text-slate-800">Dokumen legalitas yayasan</span><input type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-2 block w-full rounded-xl border border-slate-200 p-2 text-xs" @change="form.foundation_legal_document = ($event.target as HTMLInputElement).files?.[0] || null" /><span v-if="form.errors.foundation_legal_document" class="mt-1 block text-xs text-red-600">{{ form.errors.foundation_legal_document }}</span></label>
                    </template>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ form.processing ? 'Mengirim...' : 'Kirim pengajuan verifikasi' }}
                    </button>
                    <p v-if="submitted" class="text-sm font-semibold text-emerald-700">Pengajuan berhasil dikirim.</p>
                </form>
            </section>
        </div>
    </AppLayout>
</template>