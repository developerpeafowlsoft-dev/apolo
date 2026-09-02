<template>
    <div class="main-container py-10 md:py-14 bg-slate-50/80 rounded-3xl my-8 border border-slate-100">

        <!-- Header -->
        <div v-if="!isLoading" class="flex justify-between items-end gap-4 pb-2 border-b border-slate-200/60">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary inline-block"></span>
                    <span class="text-xs font-bold text-primary uppercase tracking-wider">{{ $t('Verified Vendors') }}</span>
                </div>
                <div class="text-slate-900 text-xl md:text-3xl font-extrabold tracking-tight">{{ $t('Top Rated Shops') }}</div>
            </div>

            <router-link to="/shops" class="group inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-primary hover:text-white rounded-xl text-slate-700 text-sm font-semibold transition-all duration-300 shadow-sm border border-slate-200">
                <span>{{ $t('View All') }}</span>
                <ArrowRightIcon class="w-4 h-4 text-slate-500 group-hover:text-white transition-colors" />
            </router-link>
        </div>
        <!-- loading -->
        <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-12 rounded-lg" />

        <!-- Shops -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">

            <div v-if="!isLoading" v-for="shop in shops" :key="shop.id" class="w-full">
                <ShopCardTop :shop="shop"/>
            </div>

            <!-- loading -->
            <div v-else v-for="i in 4" :key="i">
                <SkeletonLoader class="w-full h-[220px] sm:h-[260px] rounded-2xl" />
            </div>
        </div>

    </div>
</template>

<script setup>
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import ShopCardTop from './ShopCardTop.vue';
import SkeletonLoader from './SkeletonLoader.vue';

const { shops, isLoading } = defineProps(['shops', 'isLoading']);
</script>

<style scoped></style>
