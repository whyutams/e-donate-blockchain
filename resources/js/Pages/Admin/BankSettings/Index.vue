<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import {
    IconBuildingBank,
    IconCheck,
    IconCopy,
    IconDeviceFloppy,
    IconQrcode,
    IconUpload,
    IconAlertCircle,
} from '@tabler/icons-vue';

interface BankSetting {
    id: number;
    bank_name: string;
    bank_code: string;
    account_number: string;
    account_name: string;
    instructions: string | null;
    qris_image_path: string | null;
    is_active: boolean;
    midtrans_is_active: boolean;
    midtrans_is_production: boolean;
    midtrans_server_key: string | null;
    midtrans_client_key: string | null;
    midtrans_merchant_id: string | null;
}

const props = defineProps<{ setting: BankSetting }>();
const page = usePage();

const activeTab = ref<'bank' | 'midtrans'>('bank');

const form = useForm({
    bank_name: props.setting.bank_name || 'Bank Central Asia (BCA)',
    bank_code: props.setting.bank_code || 'BCA',
    account_number: props.setting.account_number || '',
    account_name: props.setting.account_name || '',
    instructions: props.setting.instructions || '',
    qris_image: null as File | null,
    midtrans_is_active: props.setting.midtrans_is_active ?? true,
    midtrans_is_production: props.setting.midtrans_is_production ?? false,
    midtrans_server_key: props.setting.midtrans_server_key || '',
    midtrans_client_key: props.setting.midtrans_client_key || '',
    midtrans_merchant_id: props.setting.midtrans_merchant_id || '',
});

const qrisPreview = ref<string | null>(
    props.setting.qris_image_path ? `/storage/${props.setting.qris_image_path}` : null
);

const handleQrisUpload = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.qris_image = file;
        qrisPreview.value = URL.createObjectURL(file);
    }
};

const copied = ref(false);
const copyAccountNumber = () => {
    navigator.clipboard.writeText(form.account_number);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const submit = () => {
    form.post('/admin/bank-settings', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Konfigurasi Rekening Donasi - Admin SafeGive" />

        <div class="mx-auto max-w-5xl space-y-8">
            <!-- Header -->
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                    <IconBuildingBank class="h-4 w-4" />
                    <span>Rekening Tunggal Pusat Donasi</span>
                </div>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    Konfigurasi Rekening Admin
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Semua donatur pada seluruh kampanye akan mentransfer dana ke <strong>satu rekening resmi</strong> yang ditentukan di bawah ini.
                </p>
            </div>

            <!-- Flash Status Message -->
            <div
                v-if="page.props.flash?.status"
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-sm"
            >
                <IconCheck class="h-5 w-5 text-emerald-700" />
                <span>{{ page.props.flash.status }}</span>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Form Configuration (7 Cols) -->
                <div class="rounded-2xl border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-8 lg:col-span-7">
                    <!-- Navigation Tabs -->
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-4">
                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition"
                            :class="activeTab === 'bank' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                            @click="activeTab = 'bank'"
                        >
                            Rekening Bank Manual
                        </button>
                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition flex items-center gap-1.5"
                            :class="activeTab === 'midtrans' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                            @click="activeTab = 'midtrans'"
                        >
                            <span>Midtrans Gateway</span>
                            <span class="rounded bg-emerald-200 text-emerald-900 text-[9px] px-1.5 py-0.2 font-black uppercase">Sandbox</span>
                        </button>
                    </div>

                    <form class="mt-6 space-y-5" @submit.prevent="submit">
                        <!-- TAB 1: BANK MANUAL -->
                        <div v-if="activeTab === 'bank'" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Nama Bank / E-Wallet
                                </label>
                                <input
                                    v-model="form.bank_name"
                                    type="text"
                                    placeholder="Contoh: Bank Central Asia (BCA)"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="form.errors.bank_name" class="mt-1 text-xs text-red-600">{{ form.errors.bank_name }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                        Kode / Singkatan
                                    </label>
                                    <input
                                        v-model="form.bank_code"
                                        type="text"
                                        placeholder="Contoh: BCA / MANDIRI / QRIS"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono text-slate-900 uppercase focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                    />
                                    <p v-if="form.errors.bank_code" class="mt-1 text-xs text-red-600">{{ form.errors.bank_code }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                        Nomor Rekening / No. HP
                                    </label>
                                    <input
                                        v-model="form.account_number"
                                        type="text"
                                        placeholder="Contoh: 8830192841"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono font-bold text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                    />
                                    <p v-if="form.errors.account_number" class="mt-1 text-xs text-red-600">{{ form.errors.account_number }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Atas Nama Pemilik Rekening
                                </label>
                                <input
                                    v-model="form.account_name"
                                    type="text"
                                    placeholder="Contoh: Yayasan SafeGive Kebaikan Indonesia"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="form.errors.account_name" class="mt-1 text-xs text-red-600">{{ form.errors.account_name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Gambar QRIS (Opsional)
                                </label>
                                <div class="mt-1 flex items-center gap-3">
                                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-slate-300 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-500 hover:bg-slate-50">
                                        <IconUpload class="h-4 w-4 text-emerald-700" />
                                        <span>Pilih Gambar QRIS</span>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleQrisUpload"
                                        />
                                    </label>
                                    <span v-if="form.qris_image" class="text-xs text-emerald-700 font-semibold">
                                        File dipilih: {{ form.qris_image.name }}
                                    </span>
                                </div>
                                <p v-if="form.errors.qris_image" class="mt-1 text-xs text-red-600">{{ form.errors.qris_image }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Petunjuk Transfer untuk Donatur
                                </label>
                                <textarea
                                    v-model="form.instructions"
                                    rows="3"
                                    placeholder="Petunjuk khusus atau format berita transfer..."
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                ></textarea>
                                <p v-if="form.errors.instructions" class="mt-1 text-xs text-red-600">{{ form.errors.instructions }}</p>
                            </div>
                        </div>

                        <!-- TAB 2: MIDTRANS GATEWAY -->
                        <div v-else class="space-y-4">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs text-emerald-950">
                                <span class="font-bold block text-sm">Mode Midtrans Sandbox</span>
                                <p class="mt-1 text-slate-600 leading-relaxed">
                                    Gunakan Server Key dan Client Key Sandbox dari akun Midtrans Anda. Donatur dapat menguji pembayaran dengan simulator QRIS, Virtual Account, atau nomor kartu testing Midtrans tanpa uang riil.
                                </p>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <input
                                    id="midtrans_active"
                                    type="checkbox"
                                    v-model="form.midtrans_is_active"
                                    class="rounded text-emerald-600 focus:ring-emerald-500"
                                />
                                <label for="midtrans_active" class="text-xs font-bold text-slate-800">
                                    Aktifkan Pembayaran Otomatis Midtrans
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Mode Environment
                                </label>
                                <div class="flex gap-4 items-center mt-1">
                                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                                        <input
                                            type="radio"
                                            :value="false"
                                            v-model="form.midtrans_is_production"
                                            class="text-emerald-600 focus:ring-emerald-500"
                                        />
                                        <span>Sandbox (Uji Coba / Testing)</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                                        <input
                                            type="radio"
                                            :value="true"
                                            v-model="form.midtrans_is_production"
                                            class="text-emerald-600 focus:ring-emerald-500"
                                        />
                                        <span>Production (Live)</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Midtrans Server Key
                                </label>
                                <input
                                    v-model="form.midtrans_server_key"
                                    type="text"
                                    placeholder="SB-Mid-server-..."
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-mono text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <p class="mt-1 text-[11px] text-slate-500">
                                    Server Key dirahasiakan dan digunakan backend untuk memproses transaksi.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Midtrans Client Key
                                </label>
                                <input
                                    v-model="form.midtrans_client_key"
                                    type="text"
                                    placeholder="SB-Mid-client-..."
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-mono text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <p class="mt-1 text-[11px] text-slate-500">
                                    Client Key digunakan oleh browser untuk memuat pop-up Midtrans Snap.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Merchant ID (Opsional)
                                </label>
                                <input
                                    v-model="form.midtrans_merchant_id"
                                    type="text"
                                    placeholder="G..."
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-mono text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 disabled:opacity-60"
                            >
                                <IconDeviceFloppy class="h-4 w-4" />
                                <span>{{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Konfigurasi' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preview Card (5 Cols) -->
                <div class="space-y-6 lg:col-span-5">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Pratinjau Tampilan Donatur
                        </span>

                        <div class="mt-4 overflow-hidden rounded-2xl border border-[#dce6d8] bg-gradient-to-br from-[#f6faf4] via-white to-[#edf5ea] p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-extrabold text-emerald-800">
                                    {{ form.bank_code || 'BANK' }}
                                </span>
                                <span class="flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Rekening Terverifikasi
                                </span>
                            </div>

                            <div class="mt-4">
                                <p class="text-xs text-slate-500 font-medium">{{ form.bank_name || 'Nama Bank' }}</p>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="font-mono text-xl font-black tracking-wider text-slate-900">
                                        {{ form.account_number || '0000000000' }}
                                    </span>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50"
                                        @click="copyAccountNumber"
                                    >
                                        <IconCheck v-if="copied" class="h-3.5 w-3.5 text-emerald-600" />
                                        <IconCopy v-else class="h-3.5 w-3.5" />
                                        <span>{{ copied ? 'Tersalin' : 'Salin' }}</span>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs font-semibold text-slate-700">
                                    a.n. {{ form.account_name || 'Nama Pemilik Rekening' }}
                                </p>
                            </div>

                            <div v-if="qrisPreview" class="mt-4 pt-4 border-t border-slate-200/80 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-center gap-1">
                                    <IconQrcode class="h-4 w-4 text-emerald-700" />
                                    <span>QRIS Tersedia</span>
                                </p>
                                <img
                                    :src="qrisPreview"
                                    alt="QRIS Donasi"
                                    class="mx-auto h-40 w-40 rounded-xl border border-slate-200 bg-white p-2 object-contain shadow-xs"
                                />
                            </div>

                            <div class="mt-4 rounded-xl bg-white/80 p-3 border border-slate-200/60 text-[11px] text-slate-600 leading-relaxed">
                                <span class="font-bold text-slate-800">Petunjuk: </span>
                                {{ form.instructions || 'Transfer sesuai nominal yang dipilih. Setiap transaksi dienkripsi dengan Paillier blockchain ledger.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Security Info Box -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-xs text-slate-600 leading-relaxed space-y-2">
                        <div class="flex items-center gap-2 font-bold text-slate-900">
                            <IconAlertCircle class="h-4 w-4 text-emerald-700" />
                            <span>Bagaimana Alur Donasi Berjalan?</span>
                        </div>
                        <p>
                            1. Donatur memilih kampanye dan nominal rupiah yang ingin disalurkan.
                        </p>
                        <p>
                            2. Donatur mentransfer dana ke rekening di atas dan mengunggah bukti transfer.
                        </p>
                        <p>
                            3. Backend secara otomatis mengenkripsi nominal donasi dengan enkripsi Paillier dan menerbitkan transaction hash unik format blockchain (0x...).
                        </p>
                        <p>
                            4. Admin memverifikasi bukti transfer pada menu <strong>Data Donasi</strong> untuk mengubah status menjadi terkonfirmasi dan memperbarui progres kampanye.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
