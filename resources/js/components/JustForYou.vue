<template>
    <div class="main-container my-14">

        <div v-if="!isLoading" class="flex justify-between items-center gap-4 mb-6">
            <h2 class="text-slate-900 text-2xl md:text-3xl font-extrabold tracking-tight">
                {{ $t('Just For You') }}
            </h2>

            <router-link to="/products" class="text-slate-600 hover:text-slate-900 text-sm font-semibold flex items-center gap-1 transition-colors">
                <span>{{ $t('View All') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </router-link>
        </div>
        <!-- loading -->
        <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-10 rounded-lg mb-6" />

        <!-- 5-Column Grid of Best Seller Portrait Product Cards (Chawkbazar Style) -->
        <div v-if="!isLoading && products"
            class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 items-stretch">
            <div v-for="product in products" :key="product.id" class="w-full">
                <ProductCard :product="product" />
            </div>
        </div>

        <!-- loading skeleton -->
         <div v-if="isLoading" class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 items-stretch">
             <div v-for="i in 5" :key="i">
                 <SkeletonLoader class="w-full h-[360px] rounded-2xl" />
            </div>
        </div>

        <!-- Load More Products Button -->
        <div v-if="!isLoading" class="mt-10 w-full flex justify-center">
            <button v-if="hasMoreProducts && !loadMore"
                class="px-8 py-3.5 rounded-2xl bg-slate-900 hover:bg-black text-white text-sm font-bold transition-all duration-300 shadow-md hover:shadow-lg flex items-center gap-2 hover:scale-105"
                @click="loadMoreProducts()">
                <span>{{ $t('Load More Products') }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <button v-if="loadMore"
                class="px-8 py-3.5 rounded-2xl bg-slate-100 text-slate-400 text-sm font-bold flex items-center justify-center cursor-not-allowed"
                disabled>
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                {{ $t('Loading products') }}...
            </button>
        </div>

    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import ProductCard from './ProductCard.vue';
import { useAuth } from '../stores/AuthStore';
import SkeletonLoader from './SkeletonLoader.vue';

const authStore = useAuth();

const props = defineProps({
    justForYou: Object,
    isLoading: Boolean
});

const currentPage = ref(2);
const hasMoreProducts = ref(false);
const totalPages = ref(1);
const loadMore = ref(false);

const products = ref([]);
watch(() => props.justForYou, () => {
    products.value = props.justForYou?.products;
    totalPages.value = Math.ceil(props.justForYou?.total / 12);
    if (totalPages.value > 1) {
        hasMoreProducts.value = true;
    }
});

const loadMoreProducts = () => {
    loadMore.value = true
    axios.get('/home?page=' + currentPage.value + '&per_page=12',{
        headers: {
            Authorization: authStore.token
        }
    }).then((response) => {
        products.value = products.value.concat(response.data.data.just_for_you.products);
        currentPage.value++;
        if (currentPage.value >= totalPages.value) {
            hasMoreProducts.value = false;
        }
        loadMore.value = false
    }).catch((error) => {
        loadMore.value = false
        console.log(error);
    })
}
</script>

<style scoped></style>
