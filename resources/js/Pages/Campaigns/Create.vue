<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { IconArrowLeft, IconUpload } from '@tabler/icons-vue';

const form = useForm({
    title: '',
    description: '',
    category: '',
    image: null as File | null,
    target_amount: '',
    starts_at: '',
    ends_at: '',
    wallet_address: '',
});

const submit = () => form.post('/campaigns', { forceFormData: true });
</script>

<template>
    <AppLayout>
        <Head title="Buat Kampanye - SafeGive" />
        <div class="mx-auto max-w-3xl space-y-7">
            <Link href="/campaigns" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-emerald-700"><IconArrowLeft class="h-4 w-4" />Kembali ke kampanye</Link>
            <header><p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Penyelenggara terverifikasi</p><h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Buat kampanye donasi</h1><p class="mt-2 text-sm text-slate-600">Detail kampanye akan tampil publik dan setiap transaksi dicatat untuk audit blockchain.</p></header>
            <form class="space-y-5 rounded-2xl border border-[#dce6d8] bg-white p-6 shadow-sm sm:p-8" @submit.prevent="submit">
                <label class="block"><span class="text-sm font-bold text-slate-800">Judul kampanye</span><input v-model="form.title" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Bantuan biaya pendidikan" /><span v-if="form.errors.title" class="mt-1 block text-xs text-red-600">{{ form.errors.title }}</span></label>
                <div class="grid gap-5 sm:grid-cols-2"><label class="block"><span class="text-sm font-bold text-slate-800">Kategori</span><input v-model="form.category" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Pendidikan, kesehatan" /><span v-if="form.errors.category" class="mt-1 block text-xs text-red-600">{{ form.errors.category }}</span></label><label class="block"><span class="text-sm font-bold text-slate-800">Target (rupiah)</span><input v-model="form.target_amount" type="number" min="10000" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="10000000" /><span v-if="form.errors.target_amount" class="mt-1 block text-xs text-red-600">{{ form.errors.target_amount }}</span></label></div>
                <label class="block"><span class="text-sm font-bold text-slate-800">Deskripsi</span><textarea v-model="form.description" rows="6" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Jelaskan tujuan, penerima manfaat, dan rencana penggunaan dana." /><span v-if="form.errors.description" class="mt-1 block text-xs text-red-600">{{ form.errors.description }}</span></label>
                <label class="block"><span class="text-sm font-bold text-slate-800">Gambar kampanye</span><span class="mt-2 flex items-center gap-2 rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500"><IconUpload class="h-5 w-5 text-emerald-700" /><input type="file" accept=".jpg,.jpeg,.png,.webp" @change="form.image = ($event.target as HTMLInputElement).files?.[0] || null" /> </span><span v-if="form.errors.image" class="mt-1 block text-xs text-red-600">{{ form.errors.image }}</span></label>
                <div class="grid gap-5 sm:grid-cols-2"><label class="block"><span class="text-sm font-bold text-slate-800">Mulai</span><input v-model="form.starts_at" type="datetime-local" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" /><span v-if="form.errors.starts_at" class="mt-1 block text-xs text-red-600">{{ form.errors.starts_at }}</span></label><label class="block"><span class="text-sm font-bold text-slate-800">Batas waktu</span><input v-model="form.ends_at" type="datetime-local" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" /><span v-if="form.errors.ends_at" class="mt-1 block text-xs text-red-600">{{ form.errors.ends_at }}</span></label></div>
                <label class="block"><span class="text-sm font-bold text-slate-800">Alamat wallet kampanye</span><input v-model="form.wallet_address" class="mt-2 w-full rounded-xl border-slate-200 font-mono text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="0x..." /><span v-if="form.errors.wallet_address" class="mt-1 block text-xs text-red-600">{{ form.errors.wallet_address }}</span></label>
                <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-emerald-700 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 hover:bg-emerald-800 disabled:opacity-60">{{ form.processing ? 'Menyimpan...' : 'Publikasikan kampanye' }}</button>
            </form>
        </div>
    </AppLayout>
</template>
