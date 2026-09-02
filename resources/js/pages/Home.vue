<template>
    <main class="w-full min-h-screen bg-white">
        <h1 class="sr-only">APOLO Online Store - Premium Fashion, Innerwear, Kurtis & Apparel</h1>
        
        <!-- Hero Section with Left Category Sidebar + Right Banner Slider -->
        <section aria-label="Hero Section">
            <HeroBanner :banners="banner" :ads="ads" :isLoading="isLoading" />
        </section>
        
        <!-- Featured Categories Grid Section (Reference Image 1) -->
        <section aria-label="Featured Categories">
            <Categories :categories="categories" :isLoading="isLoginCategory" />
        </section>

        <!-- Top Products & Flash Sale Section (Reference Image 2) -->
        <section aria-label="Top Products and Flash Sale" class="main-container my-12">
            <div class="grid grid-cols-12 gap-6 items-stretch">
                <div :class="runningFlashSale ? 'col-span-12 lg:col-span-8' : 'col-span-12'">
                    <PopularProducts :products="popularProducts" :isLoading="isLoading" />
                </div>
                
                <div v-if="runningFlashSale" class="col-span-12 lg:col-span-4 flex">
                    <FlashSaleRunning :flashSale="runningFlashSale" />
                </div>
            </div>
        </section>

        <!-- Upcoming Flash Sale if available -->
        <section v-if="incomingFlashSale" aria-label="Upcoming Flash Sale">
            <FlashSaleIncoming :flashSale="incomingFlashSale" />
        </section>
        
        <!-- Just For You Grid Section -->
        <section aria-label="Just For You">
            <JustForYou :justForYou="justForYou" :isLoading="isLoading" />
        </section>

        <!-- Recently Viewed Products History -->
        <section aria-label="Recently Viewed Products">
            <RecentlyViews :products="recentlyViewProducts" :isLoading="isLoginRecentlyView" />
        </section>
    </main>
</template>

<script setup>
import { onMounted, ref } from "vue";
import AboutSupport from "../components/AboutSupport.vue";
import Categories from "../components/Categories.vue";
import FlashSaleIncoming from "../components/FlashSaleIncoming.vue";
import FlashSaleRunning from "../components/FlashSaleRunning.vue";
import HeroBanner from "../components/HeroBanner.vue";
import JustForYou from "../components/JustForYou.vue";
import PopularProducts from "../components/PopularProducts.vue";
import RecentlyViews from "../components/RecentlyViews.vue";
import TopRatedShops from "../components/TopRatedShops.vue";
import { useBasketStore } from "../stores/BasketStore";
import { useMaster } from "../stores/MasterStore";

import axios from "axios";
import { useAuth } from "../stores/AuthStore";

const master = useMaster();
const basketStore = useBasketStore();

const authStore = useAuth();
const isLoading = ref(true);
const isLoginCategory = ref(true);
const isLoginRecentlyView = ref(false);

onMounted(() => {
    getData();
    master.fetchData();
    basketStore.fetchCart();
    fetchViewProducts();
    master.basketCanvas = false;
    authStore.loginModal = false;
    authStore.registerModal = false;
    authStore.showAddressModal = false;
    authStore.showChangeAddressModal = false;
});

const banner = ref([]);
const categories = ref([]);
const incomingFlashSale = ref(null);
const runningFlashSale = ref(null);
const popularProducts = ref([]);
const topRatedShops = ref([]);
const justForYou = ref([]);
const recentlyViewProducts = ref([]);
const ads = ref([]);

const getData = () => {
    isLoading.value = true;
    axios.get("/home?page=1&per_page=12", {
        headers: {
            Authorization: authStore.token,
        },
    }).then((response) => {
        ads.value = response.data.data.ads;
        banner.value = response.data.data.banners;
        categories.value = response.data.data.categories;
        justForYou.value = response.data.data.just_for_you;
        popularProducts.value = response.data.data.popular_products;
        topRatedShops.value = response.data.data.shops.slice(0, 4);
        incomingFlashSale.value = response.data.data.incoming_flash_sale;
        runningFlashSale.value = response.data.data.running_flash_sale;
        isLoading.value = false;
    }).catch((error) => {
        isLoading.value = false;
    });

    // fetch categories
    isLoginCategory.value = true;
    axios.get("/categories").then((response) => {
        master.categories = response.data.data.categories;
        setTimeout(() => {
            isLoginCategory.value = false;
        }, 500);
    }).catch(() => {
        isLoginCategory.value = false;
    });
};

const fetchViewProducts = () => {
    if (authStore.token) {
        isLoginRecentlyView.value = true;
        axios.get("/recently-views", {
            headers: {
                Authorization: authStore.token,
            },
        }).then((response) => {
            recentlyViewProducts.value = response.data.data.products;
            isLoginRecentlyView.value = false;
        }).catch((error) => {
            isLoginRecentlyView.value = false;
            if (error.response.status === 401) {
                authStore.token = null;
                authStore.user = null;
                authStore.addresses = [];
            }
        });
    }
};
</script>

<style scoped></style>
