<template>
    <div v-if="!isLoading" class="main-container mt-6 grid grid-cols-12 gap-6 items-stretch" :dir="master.langDirection || 'ltr'">
        <!-- Left Side Vertical Category List (Chawkbazar Demo Card Style - Image 1) -->
        <aside class="hidden lg:flex lg:col-span-3 h-full flex-col justify-between">
            <h2 class="sr-only">{{ $t('Categories List') }}</h2>
            <div class="flex flex-col justify-between h-full w-full gap-2">
                <router-link 
                    v-for="category in master.categories?.slice(0, 8)" 
                    :key="category.id" 
                    :to="`/categories/${category.id}`"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl bg-slate-100/80 hover:bg-slate-200/80 transition-all duration-200 group text-slate-800"
                >
                    <div class="flex items-center gap-3.5 grow min-w-0">
                        <div class="w-12 h-12 rounded-full bg-slate-200/90 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-transform duration-300">
                            <img :src="category.thumbnail" :alt="category.name" class="w-full h-full object-cover" loading="lazy" />
                        </div>
                        <span class="text-sm xl:text-base font-bold truncate text-slate-900 group-hover:text-black transition-colors">{{ category.name }}</span>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <span class="text-xs font-bold text-slate-600 bg-slate-200/90 group-hover:bg-slate-300/90 px-2.5 py-1 rounded-md transition-colors">
                            {{ getCategoryCount(category) }}
                        </span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-900 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </router-link>
            </div>
        </aside>

        <!-- Right Side Main Promotional Slider -->
        <div class="col-span-12 lg:col-span-9 relative overflow-hidden rounded-3xl shadow-sm border border-slate-100 bg-slate-50">
            <swiper 
                :navigation="true" 
                :pagination="{ clickable: true }" 
                :slides-per-view="1" 
                :space-between="20"
                :modules="modules" 
                class="heroSwiper rounded-3xl" 
                :loop="true" 
                :autoplay="{ delay: 5000, disableOnInteraction: false }"
            >
                <swiper-slide v-for="banner in banners" :key="banner.id">
                    <div class="relative w-full aspect-[16/9] sm:aspect-[16/7] lg:aspect-[16/7.5] overflow-hidden group bg-gradient-to-r from-slate-100 to-slate-200 flex items-center">
                        <img 
                            :src="banner.thumbnail" 
                            :alt="banner.title || 'Promotional Banner'" 
                            loading="lazy" 
                            class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-102" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-slate-950/30 to-transparent p-6 sm:p-12 md:p-16 flex flex-col justify-center items-start text-white">
                            <span v-if="banner.badge" class="px-3.5 py-1 bg-rose-600 text-white text-xs font-extrabold rounded-full uppercase tracking-wider mb-3 shadow-md">
                                {{ banner.badge }}
                            </span>
                            <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold max-w-lg leading-tight tracking-tight mb-4 text-white drop-shadow-md">
                                {{ banner.title || 'We Picked Every Item With Care, You Must Try Atleast Once.' }}
                            </h2>
                            <router-link :to="banner.url || banner.link || '/products'" class="inline-flex items-center gap-3 px-7 py-3.5 bg-slate-900 hover:bg-black text-white font-bold text-sm rounded-2xl shadow-xl transition-all duration-300 hover:scale-105">
                                <span>{{ $t('Go To Collection') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </router-link>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
        </div>
    </div>

    <!-- Skeleton loader -->
    <div v-else class="main-container mt-6 grid grid-cols-12 gap-6">
        <div class="hidden lg:block lg:col-span-3">
            <SkeletonLoader class="w-full h-[400px] rounded-2xl" />
        </div>
        <div class="col-span-12 lg:col-span-9">
            <SkeletonLoader class="w-full h-[400px] rounded-3xl" />
        </div>
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, A11y, Autoplay } from 'swiper/modules';
import { useMaster } from '../stores/MasterStore';
import SkeletonLoader from './SkeletonLoader.vue';

const master = useMaster();

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const modules = [
    Navigation, Pagination, A11y, Autoplay
];

const props = defineProps({
    banners: Array,
    ads: Array,
    isLoading: {
        type: Boolean,
        default: true
    }
});

const getCategoryCount = (category) => {
    if (!category) return 0;
    if (category.total_products !== undefined && category.total_products !== null) return category.total_products;
    if (category.products_count !== undefined && category.products_count !== null) return category.products_count;
    if (category.products && Array.isArray(category.products)) return category.products.length;
    if (category.sub_categories && Array.isArray(category.sub_categories)) return category.sub_categories.length;
    return 0;
};
</script>

<style>
.heroSwiper .swiper-button-prev,
.heroSwiper .swiper-button-next {
    position: absolute;
    width: 34px;
    height: 34px;
    background-color: rgba(255, 255, 255, 0.9);
    color: #1e293b !important;
    border-radius: 12px !important;
    backdrop-filter: blur(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-top: auto;
    transition: all 0.2s ease;
}

.heroSwiper .swiper-button-prev:hover,
.heroSwiper .swiper-button-next:hover {
    background-color: #ffffff;
    transform: scale(1.05);
}

.heroSwiper .swiper-button-next {
    left: auto;
    right: 20px;
    bottom: 20px;
}

.heroSwiper .swiper-button-prev {
    left: auto;
    right: 64px;
    bottom: 20px;
}

.heroSwiper .swiper-button-prev:after,
.heroSwiper .swiper-button-next:after {
    font-size: 14px !important;
    font-weight: bold;
}

.heroSwiper .swiper-pagination-bullet-active {
    @apply bg-primary w-6 h-2 rounded-full;
}
</style>
