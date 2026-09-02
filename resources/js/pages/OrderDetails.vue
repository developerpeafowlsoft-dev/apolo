<template>
    <main class="min-h-screen bg-slate-50/70 pb-16">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="bg-white border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8 shadow-2xs">
            <div class="max-w-7xl mx-auto flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <HomeIcon class="w-4 h-4 text-slate-400 shrink-0" />
                <router-link to="/order-history" class="hover:text-primary transition-colors font-medium">
                    {{ $t('Order History') }}
                </router-link>
                <span class="text-slate-300">/</span>
                <span class="text-slate-900 font-semibold truncate">{{ $t('Order Details') }}</span>
            </div>
        </nav>

        <!-- Main Page Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <!-- Header -->
            <header class="mb-6">
                <AuthPageHeader :title="$t('Order Details')" />
            </header>

            <!-- Order Status Header Card -->
            <section aria-label="Order Status Header" class="mb-6">
                <OrderDetailsOrderStatus :order="order" />
            </section>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Main Products Column -->
                <section class="lg:col-span-2 space-y-6">
                    <!-- Shop Information & Products Card -->
                    <article class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-7 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5 mb-5">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="order.shop?.logo"
                                    :src="order.shop.logo"
                                    :alt="order.shop.name || 'Shop Logo'"
                                    class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-2xs"
                                />
                                <div>
                                    <span class="text-2xs uppercase tracking-wider font-bold text-slate-400 block mb-0.5">
                                        {{ $t('Purchased from') }}
                                    </span>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                                        {{ order.shop?.name || 'Apolo Store' }}
                                    </h2>
                                </div>
                            </div>

                            <div v-if="order.shop?.rating" class="flex items-center gap-1.5 bg-amber-50/80 px-3 py-1.5 rounded-full border border-amber-200/60 shadow-2xs">
                                <StarIcon class="w-4 h-4 text-amber-500" />
                                <span class="text-xs sm:text-sm font-bold text-amber-900">{{ order.shop?.rating }}</span>
                            </div>
                        </div>

                        <!-- Products Header -->
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span>{{ $t('Products') }}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                    {{ order.products?.length || 0 }}
                                </span>
                            </h3>
                        </div>

                        <!-- Products Component -->
                        <OrderProducts :order="order" @refresh="fetchOrderDetails"/>
                    </article>
                </section>

                <!-- Order Summary Sidebar Column -->
                <aside class="lg:col-span-1">
                    <OrderDetailsSummery :order="order" @update:paymentSuccess="fetchOrderDetails"/>
                </aside>
            </div>
        </div>
    </main>
</template>

<script setup>
import { HomeIcon } from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from 'vue-router';
import AuthPageHeader from "../components/AuthPageHeader.vue";
import OrderDetailsOrderStatus from "../components/OrderDetailsOrderStatus.vue";
import OrderDetailsSummery from "../components/OrderDetailsSummery.vue";
import OrderProducts from "../components/OrderProducts.vue";

import { useAuth } from "../stores/AuthStore";
const authStore = useAuth();
const route = useRoute();
const router = useRouter();

const order = ref({});

watch(() => authStore.orderCancel, () => {
    if (authStore.orderCancel == true) {
        fetchOrderDetails();
    }
    authStore.orderCancel = false;
});

onMounted(() => {
    fetchOrderDetails();
    window.scrollTo(0, 0, { behavior: 'smooth' });
});

const fetchOrderDetails = async () => {
    axios.get('/order-details', {
        params: { order_id: route.params.id },
        headers: {
            Authorization: authStore.token,
        }
    }).then((response) => {
        order.value = response.data.data.order;
    }).catch((error) => {
        if (error.response?.status === 401) {
            authStore.token = null;
            authStore.user = null;
            authStore.addresses = [];
            authStore.favoriteProducts = 0;
            router.push('/');
        }
    });
};
</script>
