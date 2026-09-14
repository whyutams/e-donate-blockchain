<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { IconUser, IconLogout, IconChevronRight, IconAlertTriangle } from '@tabler/icons-vue';

defineProps<{ compact?: boolean }>();

interface User {
    name: string;
    email: string;
}

const page = usePage<{ auth: { user: User | null } }>();
const menuOpen = ref(false);
const logoutModalOpen = ref(false);
const user = computed(() => page.props.auth.user);
const initials = computed(() => user.value?.name.split(' ').map((name) => name[0]).slice(0, 2).join('').toUpperCase() || 'SG');

const closeLogoutModal = () => {
    logoutModalOpen.value = false;
};

const openLogoutModal = () => {
    menuOpen.value = false;
    logoutModalOpen.value = true;
};

const logout = () => router.post('/logout', {}, { onFinish: closeLogoutModal });
</script>

<template>
    <div class="relative">
        <button
            type="button"
            :title="compact ? (user?.name || 'Profil pengguna') : undefined"
            class="flex w-full items-center gap-3 rounded-2xl border border-[#f4ddd3] bg-[#fff0e9] p-2.5 text-left transition hover:border-[#e9b9a8] hover:bg-[#ffe9e0]"
            :class="compact ? 'lg:justify-center lg:border-0 lg:bg-transparent lg:p-0' : ''"
            @click="menuOpen = !menuOpen"
        >
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-[#c65d3d] shadow-sm">{{ initials }}</span>
            <span class="min-w-0 flex-1" :class="compact ? 'lg:hidden' : ''">
                <span class="block truncate text-sm font-bold text-slate-800">{{ user?.name || 'Pengguna' }}</span>
                <span class="mt-0.5 block truncate text-xs text-slate-500">{{ user?.email || 'Akun aktif' }}</span>
            </span>
            <IconChevronRight
                class="h-4 w-4 shrink-0 text-[#c65d3d] transition-transform duration-200"
                :class="[compact ? 'lg:hidden' : '', { 'rotate-90': menuOpen }]"
                stroke-width="2"
            />
        </button>

        <div
            v-if="menuOpen"
            class="absolute bottom-[calc(100%+12px)] left-0 right-0 z-50 rounded-2xl border border-[#dce6d8] bg-white p-2 shadow-[0_14px_35px_rgba(66,87,58,0.16)]"
            :class="compact ? 'lg:bottom-0 lg:left-[calc(100%+12px)] lg:right-auto lg:w-56' : ''"
        >
            <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Akun saya</p>
            <Link
                href="/profile"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-[#edf4e9] hover:text-emerald-800"
                @click="menuOpen = false"
            >
                <IconUser class="h-4 w-4 text-slate-500" stroke-width="1.8" />
                Profile
            </Link>
            <button
                type="button"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-[#c65d3d] transition hover:bg-[#fff0e9]"
                @click="openLogoutModal"
            >
                <IconLogout class="h-4 w-4 text-[#c65d3d]" stroke-width="1.8" />
                Logout
            </button>
        </div>

        <div v-if="logoutModalOpen" class="absolute bottom-[calc(100%+12px)] left-0 right-0 z-50 rounded-2xl border border-[#f0d7cc] bg-white p-4 shadow-[0_14px_35px_rgba(66,87,58,0.16)]">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#fff0e9] text-[#c65d3d]">
                    <IconAlertTriangle class="h-4 w-4" stroke-width="1.8" />
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900">Keluar dari akun?</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Kamu akan kembali ke halaman login.</p>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="button" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50" @click="closeLogoutModal">Batal</button>
                <button type="button" class="flex-1 rounded-lg bg-[#c65d3d] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#ad4e32]" @click="logout">Keluar</button>
            </div>
        </div>
    </div>
</template>