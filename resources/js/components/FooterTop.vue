<template>
    <div class="bg-slate-950 text-slate-300 pt-12 pb-12 border-t border-slate-800/80">
        <div class="main-container">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12">

                <!-- Footer Columns -->
                <div v-for="(footer, index) in master.footers" :key="footer.id" class="flex flex-col gap-3">

                    <div v-if="footer.title" class="text-white text-base sm:text-lg font-extrabold tracking-tight pb-2 border-b border-slate-800 flex items-center justify-between"
                        @click="!isLargeScreen ? toggleLinks(index) : ''">
                        <span>{{ footer.title }}</span>
                        <div v-if="footer.items.some(item => item.type === 'link')" class="transition-transform duration-300 block sm:hidden text-slate-400"
                            :class="checkOpen(index) ? 'rotate-180' : ''">
                            <ChevronDownIcon class="w-5 h-5" />
                        </div>
                    </div>

                    <div v-for="item in footer.items" :key="item.id">
                        <div v-if="item.type == 'logo'" class="mb-4">
                            <img :src="master.footerLogo" alt="APOLO Footer Logo" class="h-10 object-contain" loading="lazy" />
                        </div>

                        <div v-if="item.type == 'text'" class="text-slate-400 py-1 text-xs sm:text-sm font-normal leading-relaxed max-w-[320px]">
                            {{ item.title }}
                        </div>

                        <div v-if="item.type == 'email'" class="py-2 flex items-center gap-3 max-w-[320px] text-slate-300 text-xs sm:text-sm">
                            <EnvelopeIcon class="w-5 h-5 text-emerald-400 shrink-0" />
                            <span>{{ master.email || item.title }}</span>
                        </div>

                        <div v-if="item.type == 'phone'" class="py-2 flex items-center gap-3 max-w-[320px] text-slate-300 text-xs sm:text-sm">
                            <PhoneIcon class="w-5 h-5 text-emerald-400 shrink-0" />
                            <span>{{ master.mobile || item.title }}</span>
                        </div>

                        <div v-if="item.type == 'social_links'" class="flex justify-start pt-4 items-center gap-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <a v-for="socialLink in master.socialLinks" :key="socialLink.name" target="_blank"
                                    :href="socialLink.link" class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 hover:border-primary flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-sm" :title="socialLink.name">
                                    <img :src="socialLink.logo" :alt="socialLink.name" class="w-4 h-4 object-contain">
                                </a>
                            </div>
                        </div>

                        <div v-if="item.type == 'app_store'" class="pt-3">
                            <div v-if="master.footerQr" class="bg-white p-2 rounded-2xl w-32 shadow-lg overflow-hidden border border-slate-200 mb-3">
                                <img :src="master.footerQr" alt="Scan QR Code" class="h-28 w-full object-contain" loading="lazy" />
                                <div class="text-center text-slate-900 text-[11px] font-bold mt-1">
                                    {{ $t('Scan the QR') }}
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 py-1">
                                <button class="border-none hover:scale-105 transition-transform duration-300"
                                    @click="appStore">
                                    <img :src="'/assets/icons/appStoreFooter.png'" alt="App Store"
                                        class="h-10 rounded-xl" loading="lazy" />
                                </button>
                                <button class="border-none hover:scale-105 transition-transform duration-300"
                                    @click="playStore">
                                    <img :src="'/assets/icons/playStoreFooter.png'" alt="Play Store"
                                        class="h-10 rounded-xl" loading="lazy" />
                                </button>
                            </div>
                        </div>

                        <transition v-if="item.type == 'link'" name="slide" mode="out-in">
                            <div v-if="checkOpen(index) || isLargeScreen || (footer.title ? false : true)" class="w-full">
                                <router-link :to="item.url" :target="item.target" class="py-1.5 text-slate-400 hover:text-primary text-xs sm:text-sm font-medium transition-colors duration-200 inline-block">
                                    {{ item.title }}
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
import { PhoneIcon, EnvelopeIcon } from '@heroicons/vue/24/solid';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';

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
