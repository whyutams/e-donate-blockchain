<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/login');
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />
        <div class="mb-9">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Selamat datang kembali</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Masuk ke akunmu</h1>
            <p class="mt-3 text-slate-500">Lanjutkan perjalanan donasi yang berdampak.</p>
        </div>
        <form class="space-y-5" @submit.prevent="submit">
            <label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700">Email</span><input v-model="form.email" type="email" autocomplete="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="nama@email.com" /><span v-if="form.errors.email" class="mt-1 block text-sm text-red-600">{{ form.errors.email }}</span></label>
            <label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700">Password</span><input v-model="form.password" type="password" autocomplete="current-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="Masukkan password" /><span v-if="form.errors.password" class="mt-1 block text-sm text-red-600">{{ form.errors.password }}</span></label>
            <label class="flex items-center gap-3 text-sm text-slate-500"><input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" /> Ingat saya di perangkat ini</label>
            <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-emerald-600 px-5 py-3.5 font-semibold text-white shadow-lg shadow-emerald-600/15 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">{{ form.processing ? 'Memproses...' : 'Masuk' }}</button>
        </form>
        <p class="mt-8 text-center text-sm text-slate-500">Belum punya akun? <Link href="/register" class="font-semibold text-emerald-600 hover:text-emerald-700">Daftar sekarang</Link></p>
    </GuestLayout>
</template>