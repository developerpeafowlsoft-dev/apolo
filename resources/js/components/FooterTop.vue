<template>
    <div class="bg-[#0b0f19] text-slate-300 pt-12 lg:pt-16 pb-12 border-t border-slate-800/90 relative overflow-hidden">
        <!-- Ambient Top Luminous Accent Line -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-6xl h-[1px] bg-gradient-to-r from-transparent via-primary/70 to-transparent pointer-events-none"></div>

        <div class="main-container relative z-10">
            <!-- Modern E-Commerce Trust Badges Ribbon -->
            <div class="border-b border-slate-800/80 pb-10 mb-12">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700 transition group">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition duration-300 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <div class="text-white text-xs sm:text-sm font-bold tracking-tight">{{ $t('Express Delivery') }}</div>
                            <div class="text-slate-400 text-[11px] sm:text-xs mt-0.5">{{ $t('Fast shipping PAN India') }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700 transition group">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition duration-300 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <div class="text-white text-xs sm:text-sm font-bold tracking-tight">{{ $t('100% Authentic') }}</div>
                            <div class="text-slate-400 text-[11px] sm:text-xs mt-0.5">{{ $t('Direct from verified brands') }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700 transition group">
                        <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center shrink-0 group-hover:bg-blue-500 group-hover:text-white transition duration-300 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <div>
                            <div class="text-white text-xs sm:text-sm font-bold tracking-tight">{{ $t('Easy Returns') }}</div>
                            <div class="text-slate-400 text-[11px] sm:text-xs mt-0.5">{{ $t('Hassle-free replacements') }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700 transition group">
                        <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center shrink-0 group-hover:bg-purple-500 group-hover:text-white transition duration-300 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <div class="text-white text-xs sm:text-sm font-bold tracking-tight">{{ $t('Secure Checkout') }}</div>
                            <div class="text-slate-400 text-[11px] sm:text-xs mt-0.5">{{ $t('256-bit SSL encrypted') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Columns Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12">
                <div v-for="(footer, index) in master.footers" :key="footer.id" class="flex flex-col gap-3">

                    <!-- Column Header (Accordion on Mobile) -->
                    <div v-if="footer.title"
                         class="text-white text-base sm:text-lg font-bold tracking-tight pb-3 border-b border-slate-800/90 flex items-center justify-between cursor-pointer sm:cursor-default select-none group"
                         @click="!isLargeScreen ? toggleLinks(index) : ''">
                        <span class="flex items-center gap-2">
                            <span>{{ footer.title }}</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-primary/80 opacity-70 group-hover:opacity-100 transition-opacity"></span>
                        </span>
                        <div v-if="footer.items.some(item => item.type === 'link')"
                             class="transition-transform duration-300 block sm:hidden text-slate-400"
                             :class="checkOpen(index) ? 'rotate-180 text-primary' : ''">
                            <ChevronDownIcon class="w-5 h-5" />
                        </div>
                    </div>

                    <!-- Column Items -->
                    <div v-for="item in footer.items" :key="item.id" class="space-y-1">

                        <!-- Brand Logo -->
                        <div v-if="item.type == 'logo'" class="mb-4">
                            <img :src="master.footerLogo" alt="APOLO Footer Logo" class="h-11 object-contain brightness-105" loading="lazy" />
                        </div>

                        <!-- Brand Description Text -->
                        <div v-if="item.type == 'text'" class="text-slate-400 py-1 text-xs sm:text-sm font-normal leading-relaxed max-w-[340px]">
                            {{ item.title }}
                        </div>

                        <!-- Phone Contact Card -->
                        <div v-if="item.type == 'phone'" class="pt-2">
                            <a :href="'tel:' + (master.mobile || item.title)"
                               class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-emerald-500/50 text-slate-300 hover:text-white transition-all text-xs sm:text-sm group shadow-xs">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition">
                                    <PhoneIcon class="w-3.5 h-3.5" />
                                </div>
                                <span class="font-mono font-medium">{{ master.mobile || item.title }}</span>
                            </a>
                        </div>

                        <!-- Email Contact Card -->
                        <div v-if="item.type == 'email'" class="pt-1.5">
                            <a :href="'mailto:' + (master.email || item.title)"
                               class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-primary/50 text-slate-300 hover:text-white transition-all text-xs sm:text-sm group shadow-xs">
                                <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition">
                                    <EnvelopeIcon class="w-3.5 h-3.5" />
                                </div>
                                <span class="font-medium">{{ master.email || item.title }}</span>
                            </a>
                        </div>

                        <!-- Social Media Links -->
                        <div v-if="item.type == 'social_links'" class="flex justify-start pt-4 items-center gap-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <a v-for="socialLink in master.socialLinks" :key="socialLink.name" target="_blank"
                                    :href="socialLink.link"
                                    class="w-10 h-10 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-primary hover:bg-primary/10 flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-sm"
                                    :title="socialLink.name">
                                    <img :src="socialLink.logo" :alt="socialLink.name" class="w-4 h-4 object-contain">
                                </a>
                            </div>
                        </div>

                        <!-- App Store & QR Download -->
                        <div v-if="item.type == 'app_store'" class="pt-2">
                            <div v-if="master.footerQr" class="bg-slate-900/90 p-3.5 rounded-2xl border border-slate-800 inline-flex flex-col items-center gap-2 shadow-md mb-4">
                                <div class="bg-white p-2 rounded-xl overflow-hidden shadow-xs">
                                    <img :src="master.footerQr" alt="Scan QR Code" class="h-24 w-24 object-contain" loading="lazy" />
                                </div>
                                <span class="text-slate-300 text-[11px] font-semibold flex items-center gap-1.5 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    {{ $t('Scan to Download App') }}
                                </span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2.5">
                                <button class="border-none hover:scale-105 transition-transform duration-300 cursor-pointer shadow-xs rounded-xl overflow-hidden"
                                    @click="appStore">
                                    <img :src="'/assets/icons/appStoreFooter.png'" alt="App Store"
                                        class="h-10 rounded-xl" loading="lazy" />
                                </button>
                                <button class="border-none hover:scale-105 transition-transform duration-300 cursor-pointer shadow-xs rounded-xl overflow-hidden"
                                    @click="playStore">
                                    <img :src="'/assets/icons/playStoreFooter.png'" alt="Play Store"
                                        class="h-10 rounded-xl" loading="lazy" />
                                </button>
                            </div>
                        </div>

                        <!-- Navigation Link -->
                        <transition v-if="item.type == 'link'" name="slide" mode="out-in">
                            <div v-if="checkOpen(index) || isLargeScreen || (footer.title ? false : true)" class="w-full">
                                <router-link :to="item.url" :target="item.target"
                                             class="group flex items-center gap-2 py-1.5 text-slate-400 hover:text-white text-xs sm:text-sm font-medium transition-all duration-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-primary group-hover:scale-125 transition-all"></span>
                                    <span class="group-hover:translate-x-1 transition-transform duration-200">{{ item.title }}</span>
                                </router-link>
                            </div>
                        </transition>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { PhoneIcon, EnvelopeIcon, ChevronDownIcon } from '@heroicons/vue/24/solid';

import { useMaster } from "../stores/MasterStore";
const master = useMaster();

const [item0, item1, item2, item3] = [ref(false), ref(false), ref(false), ref(false)];

const isLargeScreen = ref(window.innerWidth >= 640);

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
});

const checkOpen = (index) => {
    const items = [item0, item1, item2, item3];
    return items[index]?.value || false;
}

const handleResize = () => {
    isLargeScreen.value = window.innerWidth >= 640;
    if (isLargeScreen.value) {
        item0.value = item1.value = item2.value = item3.value = true;
    } else {
        item0.value = item1.value = item2.value = item3.value = false;
    }
};

const toggleLinks = (index) => {
    switch (index) {
        case 0:
            item0.value = !item0.value;
            break;
        case 1:
            item1.value = !item1.value;
            break;
        case 2:
            item2.value = !item2.value;
            break;
        case 3:
            item3.value = !item3.value;
            break;
    }
}

const appStore = () => {
    if (master.appStoreLink) {
        window.open(master.appStoreLink, '_blank');
    }
}

const playStore = () => {
    if (master.playStoreLink) {
        window.open(master.playStoreLink, '_blank');
    }
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
}

.slide-enter-from,
.slide-leave-to {
    max-height: 0;
    opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
    max-height: 500px;
    opacity: 1;
}
</style>
