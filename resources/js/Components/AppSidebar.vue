<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import UserProfileMenu from './UserProfileMenu.vue';
import { adminNavigation } from '@/navigation/admin';
import { userNavigation } from '@/navigation/user';
import {
    IconLayoutDashboard,
    IconHeartHandshake,
    IconActivity,
    IconAdjustments,
    IconUsers,
    IconChevronLeft,
    IconX,
    IconShieldCheck,
    IconWallet,
    IconReceipt,
} from '@tabler/icons-vue';
import type { Component } from 'vue';

const props = defineProps<{ open: boolean; collapsed: boolean }>();
const emit = defineEmits<{ close: []; toggle: [] }>();

const page = usePage<{ auth: { is_admin: boolean; is_user: boolean } }>();

const navigation = computed(() => {
    if (page.props.auth?.is_admin === true) {
        return adminNavigation;
    }

    return userNavigation;
});

const getIcon = (iconName: string): Component => {
    switch (iconName) {
        case 'grid':
        case 'dashboard':
            return IconLayoutDashboard;
        case 'heart':
        case 'donation':
            return IconHeartHandshake;
        case 'activity':
            return IconActivity;
        case 'manage':
        case 'settings':
            return IconAdjustments;
        case 'users':
            return IconUsers;
        case 'wallet':
            return IconWallet;
        case 'receipt':
        case 'ledger':
            return IconReceipt;
        default:
            return IconLayoutDashboard;
    }
};

const isActive = (href: string) => computed(() => page.url.startsWith(href)).value;

watch(() => page.url, () => emit('close'));
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-30 bg-slate-900/20 backdrop-blur-sm lg:hidden" @click="emit('close')"></div>

    <aside
        class="fixed inset-y-0 left-0 z-40 flex h-screen w-[min(82vw,256px)] -translate-x-full flex-col overflow-visible border-r border-[#dce6d8] bg-white px-4 py-5 shadow-xl transition-[width,transform] duration-200 lg:z-40 lg:w-64 lg:translate-x-0 lg:shadow-none"
        :class="[{ 'translate-x-0': open }, { 'lg:w-[76px]': props.collapsed }]"
    >
        <div class="relative flex shrink-0 items-center justify-between px-2">
            <Link href="/dashboard" class="flex min-w-0 items-center gap-3" :class="props.collapsed ? 'lg:mx-auto' : ''" @click="emit('close')">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/20">
                    <IconShieldCheck class="h-5 w-5" stroke-width="2.2" />
                </div>
                <div :class="props.collapsed ? 'lg:hidden' : ''" class="flex flex-col">
                    <span class="text-base font-bold tracking-tight text-slate-900">Safe<span class="text-emerald-700">Give</span></span>
                    <span class="text-[10px] font-medium tracking-wide text-slate-400 uppercase">Blockchain Donate</span>
                </div>
            </Link>
            <button v-if="!props.collapsed" type="button" aria-label="Tutup menu" class="rounded-lg p-2 text-slate-400 transition hover:bg-[#f3f6ef] hover:text-slate-700 lg:hidden" @click="emit('close')">
                <IconX class="h-4 w-4" stroke-width="2" />
            </button>
            <button
                type="button"
                :aria-label="props.collapsed ? 'Buka sidebar' : 'Kecilkan sidebar'"
                :title="props.collapsed ? 'Buka sidebar' : 'Kecilkan sidebar'"
                class="hidden rounded-lg p-2 text-slate-400 transition hover:bg-[#f3f6ef] hover:text-emerald-700 lg:block"
                :class="props.collapsed ? 'absolute -right-3 top-1 bg-white shadow-md border border-[#dce6d8]' : ''"
                @click="emit('toggle')"
            >
                <IconChevronLeft class="h-4 w-4 transition-transform duration-200" :class="props.collapsed ? 'rotate-180' : ''" stroke-width="2" />
            </button>
        </div>

        <div class="mt-8 min-h-0 flex-1 overflow-y-auto px-1 [scrollbar-width:thin] [scrollbar-color:#dce6d8_transparent]">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400" :class="props.collapsed ? 'lg:hidden' : ''">Menu Utama</p>
            <nav class="mt-3 space-y-1.5 pb-4">
                <template v-for="item in navigation" :key="item.label">
                    <Link
                        :href="item.href"
                        class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                        :class="[
                            isActive(item.href)
                                ? 'bg-[#edf4e9] text-emerald-800 shadow-sm'
                                : 'text-slate-600 hover:bg-[#f8faf6] hover:text-emerald-700',
                            props.collapsed
                                ? 'lg:justify-center lg:px-0'
                                : ''
                        ]"
                        @click="emit('close')"
                    >
                        <component
                            :is="getIcon(item.icon)"
                            class="h-5 w-5 shrink-0 transition-transform duration-150 group-hover:scale-105"
                            :class="isActive(item.href) ? 'text-emerald-700' : 'text-slate-400 group-hover:text-emerald-700'"
                            stroke-width="1.9"
                        />

                        <span :class="props.collapsed ? 'lg:hidden' : ''">
                            {{ item.label }}
                        </span>

                        <span
                            v-if="props.collapsed"
                            class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 hidden -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-lg transition lg:group-hover:block"
                        >
                            {{ item.label }}
                        </span>
                    </Link>
                </template>
            </nav>
        </div>

        <div class="shrink-0 border-t border-slate-100 pt-4">
            <UserProfileMenu :compact="props.collapsed" />
        </div>
    </aside>
</template>