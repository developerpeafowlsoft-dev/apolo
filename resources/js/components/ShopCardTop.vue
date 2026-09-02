<template>
    <div class="w-full rounded-2xl overflow-hidden bg-white border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
        <router-link :to="`/shops/${props.shop?.id}`" class="block">
            <div class="h-28 w-full relative bg-slate-100 overflow-hidden">
                <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :src="props.shop?.banner" :alt="props.shop?.name || 'Shop Banner'" loading="lazy" />

                <!-- Status Badge -->
                <div class="px-2.5 py-1 rounded-full absolute top-3 right-3 text-xs font-bold shadow-md" :class="props.shop?.shop_status === 'Online' ? 'bg-emerald-500 text-white' : 'bg-slate-700/80 backdrop-blur-md text-white'">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full" :class="props.shop?.shop_status === 'Online' ? 'bg-white animate-pulse' : 'bg-slate-300'"></span>
                        <span>{{ props.shop?.shop_status }}</span>
                    </div>
                </div>
            </div>
            <div class="w-full px-5 pt-12 pb-5 relative bg-slate-900 group-hover:bg-slate-950 transition-colors duration-300">

                <!-- Shop Logo Avatar -->
                <div class="w-16 h-16 absolute bg-white rounded-2xl border-4 border-slate-900 shadow-lg -top-8 left-5 overflow-hidden flex items-center justify-center p-0.5">
                    <img :src="props.shop?.logo" :alt="props.shop?.name || 'Shop Logo'" loading="lazy"
                        class="w-full h-full object-cover rounded-xl" />
                </div>

                <!-- Shop Name -->
                <div class="text-white text-lg font-bold leading-snug truncate group-hover:text-primary transition-colors">
                    {{ props.shop?.name }}
                </div>

                <div class="flex items-center gap-3 w-full mt-3 text-xs text-slate-300">
                    <div class="font-medium text-slate-200">
                        {{ props.shop?.total_products }} + {{ $t('Items') }}
                    </div>

                    <div class="h-3 w-[1px] bg-slate-700"></div>

                    <!-- Rating -->
                    <div class="flex items-center gap-1 font-bold text-white">
                        <StarIcon class="w-4 h-4 text-amber-400" />
                        <span>{{ props.shop?.rating ? props.shop.rating.toFixed(1) : '5.0' }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-800 flex justify-between items-center text-xs font-semibold text-primary group-hover:text-white transition-colors">
                    <span>{{ $t('Visit Store') }}</span>
                    <ArrowRightIcon class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                </div>

            </div>
        </router-link>
    </div>
</template>

<script setup>
import { StarIcon, ArrowRightIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    shop: Object
});

</script>
