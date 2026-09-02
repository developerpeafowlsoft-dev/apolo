<template>
    <div class="main-container my-12">

        <div v-if="!isLoading" class="flex justify-between items-center gap-4 mb-6">
            <h2 class="text-slate-900 text-2xl md:text-3xl font-extrabold tracking-tight">{{ $t('Featured Categories') }}</h2>

            <router-link to="/categories" class="text-slate-600 hover:text-slate-900 text-sm font-semibold flex items-center gap-1 transition-colors">
                <span>{{ $t('View All Categories') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </router-link>
        </div>
        <!-- loading -->
        <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-10 rounded-lg mb-6" />

        <!-- 6 Featured Categories Grid (Chawkbazar Minimal Style) -->
        <div v-if="!isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            <div v-for="category in categories?.slice(0, 6)" :key="category.id" class="w-full">
                <CategoryCard :category="category" />
            </div>
        </div>

        <!-- loading skeleton -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i">
                <SkeletonLoader class="w-full h-[180px] rounded-2xl" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation } from 'swiper/modules';
import CategoryCard from './CategoryCard.vue';
import SkeletonLoader from './SkeletonLoader.vue';
import { useMaster } from '../stores/MasterStore';

const master = useMaster();
import 'swiper/css';

const props = defineProps({
    categories: Array,
    isLoading: {
        type: Boolean,
        default: true
    }
});

const swiperInstance = ref()

function onSwiper(swiper) {
    swiperInstance.value = swiper
}

const swiperNextSlide = () => {
    swiperInstance.value.slideNext()
};
const swiperPrevSlide = () => {
    swiperInstance.value.slidePrev()
};

const breakpoints = {
    320: {
        slidesPerView: 2,
        spaceBetween: 10
    },
    768: {
        slidesPerView: 4,
        spaceBetween: 10
    },
    1024: {
        slidesPerView: 6,
        spaceBetween: 30
    },

    1280: {
        slidesPerView: 8,
        spaceBetween: 30
    }
};

</script>

<style lang="scss" scoped></style>
