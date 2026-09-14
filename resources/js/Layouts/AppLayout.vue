<script setup lang="ts">
import { ref } from 'vue';
import AppSidebar from '../Components/AppSidebar.vue';

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
</script>

<template>
    <div class="min-h-screen overflow-x-hidden bg-[#f3f6ef] text-slate-900">
        <AppSidebar :open="sidebarOpen" :collapsed="sidebarCollapsed" @close="sidebarOpen = false" @toggle="sidebarCollapsed = !sidebarCollapsed" />
        <div class="min-w-0 transition-[margin] duration-200" :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-60'">
            <header class="border-b border-[#dce6d8] bg-white/90 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:h-20 sm:px-10">
                    <button type="button" aria-label="Buka menu" class="rounded-xl border border-[#dce6d8] bg-white p-2.5 text-slate-600 lg:hidden" @click="sidebarOpen = true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div class="hidden lg:block"><p class="text-sm font-semibold text-slate-800">Ruang kebaikanmu</p><p class="mt-0.5 text-xs text-slate-500">Kelola perjalanan donasimu</p></div>
                    <div class="ml-auto flex items-center gap-3">
                        <button type="button" aria-label="Notifikasi" title="Notifikasi" class="relative rounded-xl border border-[#dce6d8] bg-white p-2.5 text-slate-500 transition hover:border-emerald-200 hover:bg-[#edf4e9] hover:text-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg>
                            <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-[#c65d3d] ring-2 ring-white"></span>
                        </button>
                        
                    </div>
                </div>
            </header>
            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-10 sm:py-10">
                <slot />
            </main>
            </div>
    </div>
</template>