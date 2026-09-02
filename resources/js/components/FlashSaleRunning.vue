<template>
    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-full w-full">
        <!-- Header -->
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <h2 class="text-slate-900 text-xl font-extrabold tracking-tight">{{ $t('Flash Sale') }}</h2>
            
            <div class="flex items-center gap-1 text-slate-900 text-xs font-bold bg-slate-100 px-2.5 py-1 rounded-full">
                <span>⏰</span>
                <span>{{ endDay > 0 ? `${endDay}d ` : '' }}{{ endHour }}:{{ endMinute }}:{{ endSecond }}</span>
            </div>
        </div>

        <!-- Product Carousel / Display -->
        <div v-if="flashSale?.products && flashSale.products.length > 0" class="flex flex-col gap-4 mt-4">
            <swiper 
                :navigation="true" 
                :slides-per-view="1" 
                :modules="[Navigation]" 
                class="flashSaleSwiper w-full"
                :loop="true"
            >
                <swiper-slide v-for="product in flashSale.products" :key="product.id">
                    <div class="flex flex-col gap-3">
                        <!-- Image inside gray box (Reference Style) -->
                        <div class="w-full aspect-square rounded-2xl bg-slate-100/90 flex items-center justify-center p-6 relative overflow-hidden group">
                            <img :src="product.thumbnail" :alt="product.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                            
                            <div v-if="product.discount_percentage > 0" class="px-2 py-1 bg-rose-600 text-white text-[11px] font-extrabold rounded-md absolute top-3 left-3 shadow-xs">
                                {{ product.discount_percentage }}% {{ $t('OFF') }}
                            </div>
                        </div>

                        <!-- Product Title & Details -->
                        <div>
                            <h3 class="text-slate-900 font-extrabold text-base leading-snug truncate" :title="product.name">
                                {{ product.name }}
                            </h3>
                            <p class="text-xs text-slate-400 line-clamp-1 mt-1 leading-relaxed">
                                {{ product.short_description || product.description || 'Exclusive flash offer item available for a limited time.' }}
                            </p>

                            <!-- Price -->
                            <div class="flex items-center gap-2 mt-2">
                                <div class="text-slate-900 font-extrabold text-lg">
                                    {{ masterStore.showCurrency(product.discount_price > 0 ? product.discount_price : product.price) }}
                                </div>
                                <div v-if="product.discount_price > 0" class="text-slate-400 text-xs line-through">
                                    {{ masterStore.showCurrency(product.price) }}
                                </div>
                            </div>
                        </div>

                        <!-- Stock Progress Bar (Chawkbazar Reference Style) -->
                        <div class="mt-2 pt-2 border-t border-slate-100">
                            <div class="flex justify-between items-center text-xs font-semibold text-slate-700 mb-1.5">
                                <span>{{ $t('Sold') }} : <strong class="text-slate-900 font-bold">{{ product.total_sold || 180 }}</strong></span>
                                <span>{{ $t('Available') }} : <strong class="text-slate-900 font-bold">{{ product.quantity || 140 }}</strong></span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-slate-900 rounded-full transition-all duration-500"
                                    :style="{ width: `${Math.min(100, Math.max(15, ((product.total_sold || 180) / ((product.total_sold || 180) + (product.quantity || 140))) * 100))}%` }">
                                </div>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation } from 'swiper/modules';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import ProductCardHorizontal from './ProductCardHorizontal.vue';
import { useMaster } from '../stores/MasterStore';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';

const masterStore = useMaster();

const props = defineProps({
    flashSale: Object
});

const endDay = ref('');
const endHour = ref('');
const endMinute = ref('');
const endSecond = ref('');
let countdownInterval = null;

const startCountdown = () => {
    if (!props.flashSale?.end_date) return;
    const endDate = new Date(props.flashSale.end_date).getTime();

    countdownInterval = setInterval(() => {
        const now = new Date().getTime();
        const timeLeft = endDate - now;

        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            endDay.value = '00';
            endHour.value = '00';
            endMinute.value = '00';
            endSecond.value = '00';
        } else {
            endDay.value = String(Math.floor(timeLeft / (1000 * 60 * 60 * 24))).padStart(2, '0');
            endHour.value = String(Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            endMinute.value = String(Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            endSecond.value = String(Math.floor((timeLeft % (1000 * 60)) / 1000)).padStart(2, '0');
        }
    }, 1000);
};

onMounted(startCountdown);

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
});
</script>

