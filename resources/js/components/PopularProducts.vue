<template>
    <section aria-label="Top Products" class="w-full">
        <!-- Header -->
        <div v-if="!isLoading" class="flex justify-between items-center gap-4 mb-6">
            <h2 class="text-slate-900 text-2xl md:text-3xl font-extrabold tracking-tight">{{ $t('Top Products') }}</h2>

            <router-link to="/most-popular" class="text-slate-600 hover:text-slate-900 text-sm font-semibold flex items-center gap-1.5 transition-colors group/link" aria-label="See All Products">
                <span>{{ $t('See All Product') }}</span>
                <svg class="w-4 h-4 group-hover/link:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </router-link>
        </div>
        <!-- loading -->
        <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-10 rounded-lg mb-6" />

        <!-- 2x2 Grid of Horizontal Product Cards (Chawkbazar Style) -->
        <div v-if="!isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
            <div v-for="product in products?.slice(0, 4)" :key="product.id" class="w-full">
                <ProductCardHorizontal :product="product"/>
            </div>
        </div>

        <!-- loading skeleton -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="i in 4" :key="i">
                <SkeletonLoader class="w-full h-[160px] rounded-2xl" />
            </div>
        </div>
    </section>
</template>

<script setup>
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import ProductCardHorizontal from './ProductCardHorizontal.vue';
import SkeletonLoader from './SkeletonLoader.vue';

const props = defineProps({
    products: Array,
    isLoading: Boolean
});
</script>

<style scoped></style>
