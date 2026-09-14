<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });
const submit = () => form.post('/register');
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />
        <div class="mb-8">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Mulai berbagi</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Buat akun SafeGive</h1>
            <p class="mt-3 text-slate-500">Satu akun untuk mengelola dukungan yang berarti.</p>
        </div>
        <form class="space-y-4" @submit.prevent="submit">
            <label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</span><input v-model="form.name" type="text" autocomplete="name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="Nama kamu" /><span v-if="form.errors.name" class="mt-1 block text-sm text-red-600">{{ form.errors.name }}</span></label>
            <label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700">Email</span><input v-model="form.email" type="email" autocomplete="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="nama@email.com" /><span v-if="form.errors.email" class="mt-1 block text-sm text-red-600">{{ form.errors.email }}</span></label>
            <div class="grid gap-4 sm:grid-cols-2"><label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700">Password</span><input v-model="form.password" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="Min. 8 karakter" /><span v-if="form.errors.password" class="mt-1 block text-sm text-red-600">{{ form.errors.password }}</span></label><label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700">Ulangi password</span><input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3.5 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="Ulangi password" /></label></div>
            <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-emerald-600 px-5 py-3.5 font-semibold text-white shadow-lg shadow-emerald-600/15 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">{{ form.processing ? 'Membuat akun...' : 'Buat akun' }}</button>
        </form>
        <p class="mt-8 text-center text-sm text-slate-500">Sudah punya akun? <Link href="/login" class="font-semibold text-emerald-600 hover:text-emerald-700">Masuk di sini</Link></p>
    </GuestLayout>
</template>