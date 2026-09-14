<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';

interface User {
    name: string;
    email: string;
}

const page = usePage<{ auth: { user: User | null } }>();
const user = computed(() => page.props.auth.user);
const initials = computed(() => user.value?.name.split(' ').map((name) => name[0]).slice(0, 2).join('').toUpperCase() || 'SG');
</script>

<template>
    <AppLayout>
        <Head title="Profile" />
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Akun saya</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Profile</h1>
            <p class="mt-2 text-slate-500">Informasi akun yang sedang digunakan.</p>

            <section class="mt-8 rounded-3xl border border-[#dce6d8] bg-white p-6 shadow-[0_18px_55px_rgba(66,87,58,0.08)] sm:p-8">
                <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-[#fff0e9] text-lg font-bold text-[#c65d3d]">{{ initials }}</span>
                    <div><h2 class="text-lg font-bold text-slate-900">{{ user?.name || 'Pengguna' }}</h2><p class="mt-1 text-sm text-slate-500">{{ user?.email || 'Akun aktif' }}</p></div>
                </div>
                <dl class="mt-6 space-y-5"><div><dt class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nama lengkap</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ user?.name || '-' }}</dd></div><div><dt class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Email</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ user?.email || '-' }}</dd></div></dl>
            </section>
        </div>
    </AppLayout>
</template>