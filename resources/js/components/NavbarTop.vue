<template>
    <div class="bg-slate-950 text-slate-300 border-b border-slate-800/80 text-xs">
        <div class="main-container flex justify-between items-center py-2.5">

            <div class="flex sm:items-center flex-col sm:flex-row gap-2 sm:gap-6">
                <a :href="'mailto:' + (master.email || 'apolo@gmail.com')"
                    class="text-xs text-slate-300 hover:text-white font-medium transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ master.email || 'apolo@gmail.com' }}</span>
                </a>
                <div class="w-[1px] h-3 bg-slate-800 hidden sm:block"></div>
                <div class="text-xs text-slate-300 font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ $t('Hotline') }}: {{ master.mobile }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Menu as="div" class="relative inline-block text-left">
                    <div>
                        <MenuButton
                            class="inline-flex items-center text-slate-300 hover:text-white gap-1 font-medium transition-colors">
                            <span>{{ (master.selectedCurrency?.name || 'USD')+', ' + (master.selectedCurrency?.symbol || '$') }}</span>
                            <ChevronDownIcon class="w-3.5 h-3.5 text-slate-400" aria-hidden="true" />
                        </MenuButton>
                    </div>

                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <MenuItems
                            class="absolute z-30 w-28 mt-2 origin-top-right rounded-xl bg-white text-slate-800 shadow-2xl ring-1 ring-black/5 focus:outline-none overflow-hidden" :class="master.langDirection == 'rtl' ? 'left-0' : 'right-0'">
                            <div class="py-1">
                                <MenuItem v-for="currency in master.currencies" v-slot="{ active }" :key="currency.id">
                                <button type="button" @click="setCurrentCurrency(currency)"
                                    class="w-full text-left font-medium transition-colors"
                                    :class="[active ? 'bg-primary-50 text-primary' : 'text-slate-700', 'block px-4 py-2 text-xs']">
                                    {{currency.name + ', ' + currency.symbol}}
                                </button>
                                </MenuItem>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>

                <div class="w-[1px] h-3 bg-slate-800 hidden sm:block"></div>

                <Menu as="div" class="relative inline-block text-left">
                    <div>
                        <MenuButton
                            class="inline-flex items-center text-slate-300 hover:text-white gap-1 font-medium transition-colors">
                            <span>{{ currentLanguage }}</span>
                            <ChevronDownIcon class="w-3.5 h-3.5 text-slate-400" aria-hidden="true" />
                        </MenuButton>
                    </div>

                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <MenuItems
                            class="absolute z-30 w-28 mt-2 origin-top-right rounded-xl bg-white text-slate-800 shadow-2xl ring-1 ring-black/5 focus:outline-none overflow-hidden" :class="master.langDirection == 'rtl' ? 'left-0' : 'right-0'">
                            <div class="py-1">
                                <MenuItem v-for="language in master.languages" v-slot="{ active }" :key="language.id">
                                <button type="button" @click="setCurrentLanguage(language.name); reloadPage()"
                                    class="w-full text-left font-medium transition-colors" :class="[active ? 'bg-primary-50 text-primary' : 'text-slate-700', 'block px-4 py-2 text-xs']">{{
                                        language.title }}</button>
                                </MenuItem>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'
import localization from '../localization';

import { useMaster } from "../stores/MasterStore";
import { onMounted, ref, watch } from 'vue';
const master = useMaster();

const currentLanguage = ref('English');

onMounted(() => {
    setCurrentLanguage(master.locale);
});

watch(() => master.locale, (oldValue, newValue) => {
    if (oldValue !== newValue) {
        setCurrentLanguage(master.locale);
    }
});

const setCurrentLanguage = (lang) => {
    master.locale = lang;
    localStorage.setItem('locale', lang);

    const language = master.languages.find(lang => lang.name === master.locale);
    if (language) {
        currentLanguage.value = language.title;
        master.langDirection = language.direction || 'ltr';
    }
    localization.fetchLocalizationData();
};

const setCurrentCurrency = (currency) => {
    master.selectedCurrency = currency;
};

const reloadPage = () => {
    window.location.reload();
}

</script>
