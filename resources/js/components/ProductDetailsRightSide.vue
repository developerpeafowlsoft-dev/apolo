<template>
    <div class="flex flex-col md:flex-row xl:flex-col items-center gap-5 w-full">

        <!-- Shipping Charge and Estimated delivery time -->
        <div class="w-full grow md:flex lg:gap-6 xl:block p-5 bg-gradient-to-b from-slate-50 to-white rounded-2xl border border-slate-200/80 shadow-xs space-y-4">

            <!-- Delivery charge -->
            <div class="flex grow flex-col items-start xl:flex-row xl:items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl justify-center items-center flex shrink-0">
                    <img :src="'/assets/icons/money.svg'" alt="" class="w-5 h-5">
                </div>
                <div>
                    <div class="text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        {{ $t('Delivery charge') }}
                    </div>
                    <div class="mt-0.5 flex items-center gap-2">
                        <span v-if="!product.shop?.delivery_charge || product.shop?.delivery_charge == 0" 
                              class="text-emerald-700 text-base font-extrabold font-mono">
                            {{ $t('Free') }}
                        </span>
                        <span v-else class="text-slate-900 text-base font-extrabold font-mono">
                            {{ masterStore.showCurrency(product.shop?.delivery_charge) }}
                        </span>
                        <span v-if="!product.shop?.delivery_charge || product.shop?.delivery_charge == 0"
                              class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">
                            {{ $t('FREE DELIVERY') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estimated delivery time -->
            <div class="flex grow flex-col items-start xl:flex-row xl:items-center gap-3 pt-3 border-t border-slate-100">
                <div class="w-10 h-10 bg-slate-100 rounded-xl justify-center items-center flex shrink-0">
                    <img :src="'/assets/icons/clock.svg'" alt="" class="w-5 h-5">
                </div>
                <div>
                    <div class="text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        {{ $t('Estimated delivery') }}
                    </div>
                    <div class="mt-0.5 text-slate-900 text-base font-extrabold font-mono">
                        {{ product.shop?.estimated_delivery_time || '2-4' }} {{ $t('Days') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Shop info -->
        <div v-if="masterStore.multiVendor"
            class="bg-white hover:border-primary/50 transition-all w-full grow rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="flex justify-between items-center gap-3 p-4">
                <router-link :to="`/shops/` + product.shop?.id" class="flex items-center gap-3 overflow-hidden group">
                    <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-slate-100 shadow-2xs">
                        <img :src="product.shop?.logo" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    </div>

                    <div class="overflow-hidden">
                        <div class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            {{ $t('Sold by') }}
                        </div>
                        <div class="mt-0.5 text-slate-900 text-sm font-bold truncate group-hover:text-primary transition-colors">
                            {{ product.shop?.name }}
                        </div>
                    </div>
                </router-link>

                <div class="flex items-center gap-1 bg-amber-50 border border-amber-200/70 px-2 py-1 rounded-lg shrink-0">
                    <StarIcon class="w-4 h-4 text-amber-500" />
                    <span class="text-amber-900 text-xs font-bold">
                        {{ product.shop?.rating?.toFixed(1) || '5.0' }}
                    </span>
                </div>
            </div>
            <div class="border-t border-slate-100 bg-slate-50/50 flex items-center px-4 py-2.5"
                :class="authStore.token ? 'justify-between' : 'justify-center'">
                <router-link :to="`/shops/` + product.shop?.id" class="text-primary hover:text-primary-800 text-xs font-bold flex items-center gap-1 transition">
                    <span>{{ $t('Visit Store') }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </router-link>
                <div v-if="authStore.token"
                    class="w-7 h-7 flex justify-center items-center bg-primary/10 hover:bg-primary/20 text-primary rounded-lg cursor-pointer transition"
                    @click="showChat(product.shop?.id, authStore?.user?.id, product.id)"
                    :title="$t('Chat with store')">
                    <img src="/public/assets/icons/shop-chat/chat.svg" alt="chat" class="w-4 h-4">
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <RightChatSidebar :show="showSidebar" @close="showSidebar = false" :shop="product?.shop" />
            </div>
        </div>

    </div>

    <!-- Popular Products -->
    <div class="mt-8">
        <div class="text-slate-800 text-base font-medium leading-normal">
            {{ $t('Popular Products From Them') }}
        </div>

        <div class="flex gap-4 md:grid md:grid-cols-2 lg:grid-cols-3 xl:block xl:space-y-4 mt-4 overflow-x-auto">
            <div v-for="product in popularProducts" :key="product.id" class="w-[320px]  md:w-full shrink-0">
                <ProductCardHorizontal :product="product" />
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import ProductCardHorizontal from './ProductCardHorizontal.vue';
import RightChatSidebar from './RightChatSidebar.vue';

import { useMaster } from "../stores/MasterStore";
import { useAuth } from '../stores/AuthStore';
import axios from 'axios';

const authStore = useAuth();
const masterStore = useMaster();
const showSidebar = ref(false);

const props = defineProps({
    product: Object,
    popularProducts: Array
});

const showChat = async (sid, uid, pid) => {
    const response = await axios.post('/store-message', {
        shop_id: sid,
        user_id: uid,
        product_id: pid,
        type: 'user',
    }, {
        headers: {
            Authorization: authStore.token,
        }
    });

    showSidebar.value = true;
}

</script>
