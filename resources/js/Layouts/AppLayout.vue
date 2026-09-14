<script setup lang="ts">
import { ref } from 'vue';
import AppSidebar from '../Components/AppSidebar.vue';
import { IconMenu2, IconBell, IconShieldCheck } from '@tabler/icons-vue';

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
</script>

<template>
    <div class="min-h-screen overflow-x-hidden bg-[#f3f6ef] text-slate-900 font-sans">
        <AppSidebar :open="sidebarOpen" :collapsed="sidebarCollapsed" @close="sidebarOpen = false" @toggle="sidebarCollapsed = !sidebarCollapsed" />
        
        <div class="min-w-0 transition-[margin] duration-200" :class="sidebarCollapsed ? 'lg:ml-[76px]' : 'lg:ml-64'">
            <header class="sticky top-0 z-20 border-b border-[#dce6d8] bg-white/85 backdrop-blur-md">
                <div class="flex h-16 items-center justify-between px-4 sm:h-20 sm:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            aria-label="Buka menu navigasi"
                            class="rounded-xl border border-[#dce6d8] bg-white p-2.5 text-slate-600 shadow-sm transition hover:bg-[#edf4e9] hover:text-emerald-700 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <IconMenu2 class="h-5 w-5" stroke-width="2" />
                        </button>

                        <div class="hidden lg:block">
                            <h2 class="text-sm font-bold text-slate-800 tracking-tight">SafeGive Platform</h2>
                            <p class="text-xs text-slate-500">Transparansi Donasi Berbasis Smart Contract</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-800 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            <IconShieldCheck class="h-3.5 w-3.5 text-emerald-700" stroke-width="2.2" />
                            <span>Node Ledger Terhubung</span>
                        </div>

                        <button
                            type="button"
                            aria-label="Notifikasi"
                            title="Notifikasi"
                            class="relative rounded-xl border border-[#dce6d8] bg-white p-2.5 text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-[#edf4e9] hover:text-emerald-700"
                        >
                            <IconBell class="h-5 w-5" stroke-width="1.8" />
                            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-[#c65d3d] ring-2 ring-white"></span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-8 sm:py-8">
                <slot />
            </main>
        </div>
    </div>
</template>