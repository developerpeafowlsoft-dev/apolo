<template>
    <div class="main-container mt-8 sm:mt-12">
        <div v-if="!isLoading" class="p-6 md:p-8 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl shadow-xl border border-slate-700/50 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 text-white">
            <!-- 1. PAN India Fast Home Delivery -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-primary/40 transition-all duration-300 group">
                <div class="p-3 bg-primary/20 text-primary rounded-xl group-hover:bg-primary group-hover:text-white transition-colors duration-300 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-bold text-white group-hover:text-primary transition-colors">
                        {{ $t('PAN India Home Delivery') }}
                    </div>
                    <div class="text-slate-300 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ $t('Fast & reliable delivery to any pincode across India') }}
                    </div>
                </div>
            </div>

            <!-- 2. APOLO Unjha Showroom Store -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-primary/40 transition-all duration-300 group">
                <div class="p-3 bg-primary/20 text-primary rounded-xl group-hover:bg-primary group-hover:text-white transition-colors duration-300 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0-4h.01M9 7h.01M15 7h.01M15 11h.01M9 15h.01M15 15h.01"/>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-bold text-white group-hover:text-primary transition-colors">
                        {{ $t('APOLO Unjha Showroom') }}
                    </div>
                    <div class="text-slate-300 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ $t('Exclusive Kurtis, Denim, Innerwear & Apparel collections') }}
                    </div>
                </div>
            </div>

            <!-- 3. All Age Categories -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-primary/40 transition-all duration-300 group">
                <div class="p-3 bg-primary/20 text-primary rounded-xl group-hover:bg-primary group-hover:text-white transition-colors duration-300 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-bold text-white group-hover:text-primary transition-colors">
                        {{ $t('All Age Fashion Ranges') }}
                    </div>
                    <div class="text-slate-300 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ $t("Complete Mens & Ladies wear for every age group") }}
                    </div>
                </div>
            </div>

            <!-- 4. 100% Quality & Easy Support -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-primary/40 transition-all duration-300 group">
                <div class="p-3 bg-primary/20 text-primary rounded-xl group-hover:bg-primary group-hover:text-white transition-colors duration-300 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-bold text-white group-hover:text-primary transition-colors">
                        {{ $t('100% Authentic Quality') }}
                    </div>
                    <div class="text-slate-300 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ $t('Curated fabrics, premium comfort & hassle-free returns') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- loader -->
        <div v-else class="p-4 bg-slate-900 rounded-2xl grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <SkeletonLoader v-for="i in 4" :key="i" class="w-full h-[90px] rounded-xl" />
        </div>
    </div>
</template>

<script setup>
import SkeletonLoader from './SkeletonLoader.vue';
const props = defineProps({
    isLoading: {
        type: Boolean,
        default: true
    }
});
</script>

<style scoped></style>
