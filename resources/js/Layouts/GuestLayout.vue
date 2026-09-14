<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#f3f6ef] text-slate-900 lg:grid lg:grid-cols-[minmax(360px,0.82fr)_1.18fr]">
        <aside class="relative hidden overflow-hidden border-r border-[#dce6d8] bg-[#e7f0e2] p-10 lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full border-[42px] border-white/70"></div>
            <div class="absolute -bottom-32 -left-20 h-72 w-72 rounded-full bg-[#f8d8ca]/70"></div>
            <div class="relative z-10">
                <Link href="/" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-white text-xl font-black text-emerald-700 shadow-sm">S</span>
                    <span class="text-xl font-bold tracking-tight text-slate-900">Safe<span class="text-emerald-700">Give</span></span>
                </Link>
            </div>
            <div class="relative z-10 max-w-sm">
                <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl text-emerald-700 shadow-sm">✦</div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">Give with clarity</p>
                <h1 class="text-4xl font-bold leading-tight tracking-tight text-slate-900">Kebaikan yang tercatat. Dampak yang terasa.</h1>
                <p class="mt-6 leading-relaxed text-slate-600">Kelola donasi secara transparan dan bantu lebih banyak cerita baik sampai ke tujuan.</p>
            </div>
            <p class="relative z-10 text-xs font-medium text-slate-500">Platform donasi transparan berbasis blockchain.</p>
        </aside>

        <main class="flex min-h-screen flex-col px-5 py-6 sm:px-10 lg:px-20">
            <div class="flex justify-end lg:hidden">
                <Link href="/" class="text-lg font-bold tracking-tight text-slate-900">Safe<span class="text-emerald-600">Give</span></Link>
            </div>
            <div class="flex flex-1 items-center justify-center py-10">
                <div class="w-full max-w-md rounded-[28px] border border-white bg-white p-7 shadow-[0_24px_70px_rgba(66,87,58,0.10)] sm:p-10">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>