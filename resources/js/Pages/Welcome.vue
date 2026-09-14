<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Props {
    laravelVersion: string;
    phpVersion: string;
    dbName: string;
    extensions: {
        bcmath: boolean;
        gmp: boolean;
    };
}

defineProps<Props>();
</script>

<template>
    <Head title="Starter Project - SafeGive" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between p-6 md:p-12 relative overflow-hidden font-['Plus_Jakarta_Sans',sans-serif]">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <header class="max-w-6xl w-full mx-auto flex items-center justify-between z-10">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <svg class="w-6 h-6 text-slate-950 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white">SafeGive</h1>
                    <p class="text-xs text-slate-400 font-mono">e-donate-blockchain</p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-2"></span>
                Starter Environment Ready
            </span>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl w-full mx-auto my-12 z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Left Hero Section -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-md bg-slate-900 border border-slate-800 text-xs text-slate-300">
                        <span>🚀 Starter Stack Initialized</span>
                    </div>

                    <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                        Transparent & Secure <br />
                        <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">
                            Blockchain Donation Engine
                        </span>
                    </h2>

                    <p class="text-slate-400 text-base leading-relaxed max-w-xl">
                        Starter project <code class="text-emerald-400 bg-slate-900 px-1.5 py-0.5 rounded font-mono text-sm">e-donate-blockchain</code> telah selesai dikonfigurasi. Seluruh stack utama, koneksi database, ekstensi PHP crypto/math, dan Rate Limiting telah aktif.
                    </p>

                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-2">Status Tampilan Aplikasi</p>
                        <div class="flex items-start space-x-3 text-sm text-slate-300">
                            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Landing page ini merupakan <strong>template awal / minimalist bawaan</strong>. Belum terdapat UI untuk fitur spesifik SafeGive.</span>
                        </div>
                    </div>
                </div>

                <!-- Right Environment Specs Card -->
                <div class="lg:col-span-5">
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-2xl backdrop-blur-md space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <h3 class="font-semibold text-white text-base">System Diagnostic</h3>
                            <span class="text-xs text-slate-500 font-mono">v1.0.0</span>
                        </div>

                        <!-- Tech Stack Badges -->
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Tech Stack</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 text-xs font-medium">Laravel {{ laravelVersion }}</span>
                                <span class="px-2.5 py-1 rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs font-medium">Inertia.js (Vue 3)</span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-medium">TypeScript</span>
                                <span class="px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-xs font-medium">Tailwind CSS v3</span>
                                <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-medium">MySQL</span>
                            </div>
                        </div>

                        <!-- PHP & Extensions -->
                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Environment & Extensions</p>
                            
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/60 text-xs">
                                <span class="text-slate-300 font-medium">PHP Version</span>
                                <span class="font-mono text-slate-200">{{ phpVersion }}</span>
                            </div>

                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/60 text-xs">
                                <span class="text-slate-300 font-medium">MySQL Database</span>
                                <span class="font-mono text-emerald-400 font-semibold">{{ dbName }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/60 text-xs">
                                    <span class="text-slate-300 font-medium">bcmath</span>
                                    <span :class="extensions.bcmath ? 'text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 font-semibold' : 'text-red-400'">
                                        {{ extensions.bcmath ? 'Active' : 'Disabled' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/60 text-xs">
                                    <span class="text-slate-300 font-medium">gmp</span>
                                    <span :class="extensions.gmp ? 'text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 font-semibold' : 'text-red-400'">
                                        {{ extensions.gmp ? 'Active' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Security Notice -->
                        <div class="p-3 rounded-lg bg-emerald-950/30 border border-emerald-500/20 text-xs text-emerald-300 flex items-center justify-between">
                            <span class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Rate Limiter Configured</span>
                            </span>
                            <span class="font-mono text-emerald-400 font-semibold">Throttle Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="max-w-6xl w-full mx-auto pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 z-10 gap-4">
            <p>&copy; {{ new Date().getFullYear() }} SafeGive - e-donate-blockchain. Starter project setup complete.</p>
            <div class="flex items-center space-x-4">
                <span>Documentation: SETUP_STARTER.md</span>
            </div>
        </footer>
    </div>
</template>
