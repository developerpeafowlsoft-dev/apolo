<template>
    <div v-if="!isLoading && props?.products?.length > 0" class="main-container py-10 md:py-14 bg-slate-50/60 rounded-3xl my-8 border border-slate-100">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2.5 h-2.5 rounded-full bg-primary inline-block"></span>
            <span class="text-xs font-bold text-primary uppercase tracking-wider">{{ $t('Your History') }}</span>
        </div>
        <div class="text-slate-900 text-xl md:text-3xl font-extrabold tracking-tight pb-3 border-b border-slate-200/60">
            {{ $t('Recently Viewed') }}
        </div>

        <div class="mt-6" :dir="master.langDirection || 'ltr'">
            <swiper :navigation="true" :modules="modules" :breakpoints="breakpoints" class="recentlyViewed" :loop="false">
                <swiper-slide v-for="product in products" :key="product.id">
                    <ProductCardHorizontal :product="product" />
                </swiper-slide>
            </swiper>
        </div>
    </div>

    <!-- loading -->
    <div v-if="isLoading" class="main-container py-10 md:py-14 bg-slate-50/60 rounded-3xl my-8 border border-slate-100">
        <div class="text-slate-800 text-lg md:text-3xl font-bold leading-9">
            <SkeletonLoader class="w-[220px] h-10 rounded-xl" />
        </div>
        <div class="mt-6">
            <swiper :navigation="true" :modules="modules" :breakpoints="breakpoints" class="recentlyViewed" :loop="false">
                <swiper-slide v-for="i in 4" :key="i">
                    <SkeletonLoader class="w-full h-[130px] rounded-2xl" />
                </swiper-slide>
            </swiper>
        </div>
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, A11y } from 'swiper/modules';

import ProductCardHorizontal from './ProductCardHorizontal.vue';
import SkeletonLoader from './SkeletonLoader.vue';
import { useMaster } from '../stores/MasterStore';

const master = useMaster();
// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';

const props = defineProps({
    products: Array,
    isLoading: Boolean
});

const modules = [Navigation, A11y];
const breakpoints = {
    320: {
        slidesPerView: 1,
        spaceBetween: 20
    },
    768: {
        slidesPerView: 2,
        spaceBetween: 20
    },
    1024: {
        slidesPerView: 3,
        spaceBetween: 20
    },

    1280: {
        slidesPerView: 4,
        spaceBetween: 20
    }
};

</script>

<style>
.recentlyViewed .swiper-button-prev,
.recentlyViewed .swiper-button-next {
    @apply bg-white w-8 h-8 rounded-full shadow border border-slate-200 text-slate-600;
}

.recentlyViewed .swiper-button-prev::after,
.recentlyViewed .swiper-button-next::after {
    @apply text-lg;
}

.recentlyViewed .swiper-button-next {
    right: 4px;
}

.recentlyViewed .swiper-button-prev {
    left: 4px;
}
</style>
