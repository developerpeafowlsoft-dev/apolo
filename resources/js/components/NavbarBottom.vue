<template>
    <div class="main-container flex items-center justify-between md:gap-4 lg:gap-6 border-b border-slate-200 relative bg-white py-2 shadow-2xs">

        <!-- Categories dropdown button -->
        <div class="shrink-0">
            <Popover v-slot="{ open }">
                <div>
                    <PopoverButton class="h-10 px-4 flex items-center gap-2.5 outline-none rounded-lg transition-colors font-semibold text-xs lg:text-sm text-white shadow-xs"
                        :class="open ? 'bg-primary-700' : 'bg-primary hover:bg-primary-600'">
                        <div class="flex items-center justify-center">
                            <MenuIcon :colorClass="'text-white'" />
                        </div>
                        <div class="hidden xs:block truncate tracking-wide" :class="master.langDirection === 'rtl' ? 'text-right' : 'text-left'">
                            {{ $t('Categories') }}
                        </div>
                        <ChevronDownIcon class="w-4 h-4 transition-transform duration-200 text-white" :class="open ? 'rotate-180' : ''" />
                    </PopoverButton>
                </div>

                <transition enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1">

                    <PopoverPanel class="absolute pb-6 left-0 right-0 z-30 mt-2 flex main-container">
                        <PopoverButton as="div" class="w-full p-5 sm:p-6 bg-white shadow-xl border border-slate-200 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 xl:grid-cols-8 gap-3.5 sm:gap-4 rounded-2xl">
                            <div v-for="category in heroCategories" :key="category.id" class="w-full">
                                <MenuCategory :category="category" @update:click="hiddenPopover" />
                            </div>
                        </PopoverButton>
                    </PopoverPanel>
                </transition>
            </Popover>
        </div>

        <!-- Main menu -->
        <div class="hidden md:inline-flex justify-start items-center gap-5 lg:gap-7 xl:gap-9 grow overflow-x-auto py-1">
            <template v-for="(menu, index) in formattedMenus" :key="menu.id">
                <div v-if="menu.url !== '/shops' && menu.name?.toLowerCase() !== 'shops'" class="flex items-center">
                    <router-link v-if="!menu.is_external" :to="menu.url" :target="menu.target"
                        class="text-xs lg:text-sm font-medium text-slate-700 hover:text-primary transition-colors py-1.5 whitespace-nowrap"
                        active-class="text-primary font-bold border-b-2 border-primary">
                        {{ $t(menu.name) }}
                    </router-link>
                    <a v-else :href="menu.url" :target="menu.target"
                        class="text-xs lg:text-sm font-medium text-slate-700 hover:text-primary transition-colors py-1.5 whitespace-nowrap">
                        {{ $t(menu.name) }}
                    </a>
                </div>
            </template>
        </div>

        <!-- Download app button -->
        <div v-if="master.showDownloadApp" class="inline-block shrink-0">
            <Menu as="div" class="relative text-left" v-slot="{ open }">
                <div>
                    <MenuButton class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs lg:text-sm font-medium transition-colors border"
                        :class="open ? 'bg-slate-100 text-primary border-slate-300' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border-slate-200 bg-white'">
                        <DevicePhoneMobileIcon class="w-4 h-4 text-primary" />
                        <div class="leading-normal">{{ $t('Download App') }}</div>
                        <ChevronDownIcon class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180 text-primary' : 'text-slate-400'" />
                    </MenuButton>
                </div>

                <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                        class="absolute right-0 z-10 mt-1 min-w-[170px] origin-top-right p-2.5 bg-white rounded-xl shadow-lg border border-slate-200 focus:outline-none">
                        <div class="flex-col flex gap-2">
                            <MenuItem v-slot="{ active }">
                            <button :class="active ? 'opacity-90' : ''" class="transition-opacity" @click="playStore">
                                <img :src="'/assets/icons/playStore.png'" alt="Play Store" class="h-9 object-contain rounded-md">
                            </button>
                            </MenuItem>

                            <MenuItem v-slot="{ active }">
                            <button :class="active ? 'opacity-90' : ''" class="transition-opacity" @click="appStore">
                                <img :src="'/assets/icons/appleStore.png'" alt="App Store" class="h-9 object-contain rounded-md">
                            </button>
                            </MenuItem>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { DevicePhoneMobileIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'
import MenuCategory from './MenuCategory.vue';
import MenuIcon from '../icons/Menu.vue';

import { useMaster } from "../stores/MasterStore";
const master = useMaster();

const formattedMenus = computed(() => {
    const list = [...(master.menus || [])];
    const hasNewArrivals = list.some(m => m.url === '/new-arrivals' || m.name?.toLowerCase() === 'new arrivals');
    if (!hasNewArrivals) {
        const prodIndex = list.findIndex(m => m.url === '/products' || m.name?.toLowerCase() === 'products');
        const newArrivalItem = {
            id: 'new-arrivals-nav-item',
            name: 'New Arrivals',
            url: '/new-arrivals',
            is_external: false,
            target: '_self'
        };
        if (prodIndex !== -1) {
            list.splice(prodIndex + 1, 0, newArrivalItem);
        } else {
            list.splice(1, 0, newArrivalItem);
        }
    }
    return list;
});

const heroCategories = computed(() => {
    return (master.categories || []).filter(c => Boolean(c.show_in_hero));
});

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

const hiddenPopover = () => {
   open = false
}

</script>

<style scoped>
.router-link-active {
    @apply border-b-2 border-primary text-primary
}
</style>
