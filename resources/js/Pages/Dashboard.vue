<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <section class="relative overflow-hidden rounded-[28px] border border-[#dce6d8] bg-white p-8 shadow-[0_18px_55px_rgba(66,87,58,0.08)] sm:p-12">
            <div class="absolute -right-12 -top-20 h-56 w-56 rounded-full bg-[#e7f0e2]"></div>
            <div class="absolute -bottom-28 right-32 h-48 w-48 rounded-full bg-[#fff0e9]"></div>
            <div class="relative z-10">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Ruang kebaikanmu</p>
                <h1 class="mt-4 max-w-2xl text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl">Setiap dukungan punya jejak yang berarti.</h1>
                <p class="mt-5 max-w-xl leading-relaxed text-slate-500">Dashboard aplikasi siap digunakan untuk mengelola donasi dan memantau dampaknya secara transparan.</p>
                <div class="mt-8 flex flex-wrap gap-3"><span class="rounded-full bg-[#edf4e9] px-4 py-2 text-sm font-semibold text-emerald-800">Transparan</span><span class="rounded-full bg-[#fff0e9] px-4 py-2 text-sm font-semibold text-[#c65d3d]">Berdampak</span></div>
            </div>
        </section>
        <section class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-[#dce6d8] bg-white p-6"><p class="text-sm text-slate-500">Donasi aktif</p><p class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
            <div class="rounded-2xl border border-[#dce6d8] bg-white p-6"><p class="text-sm text-slate-500">Total kontribusi</p><p class="mt-2 text-3xl font-bold text-slate-900">Rp 0</p></div>
            <div class="rounded-2xl border border-[#dce6d8] bg-white p-6"><p class="text-sm text-slate-500">Status akun</p><p class="mt-2 text-lg font-bold text-emerald-700">Siap berdonasi</p></div>
        </section>
    </AppLayout>
</template>