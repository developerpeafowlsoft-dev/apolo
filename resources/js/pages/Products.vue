<template>
    <div class="bg-white min-h-screen">
        <div class="main-container pt-6 pb-12">
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-4">
                <router-link to="/" class="hover:text-slate-900 transition-colors">{{ $t('Home') }}</router-link>
                <span class="text-slate-400">/</span>
                <span class="text-slate-700 font-semibold">{{ $t('All Products') }}</span>
            </nav>

            <!-- Page Title, Total Items Count & Filter Button -->
            <div v-if="!isLoading" class="flex justify-between items-center mb-6 flex-wrap gap-4">
                <h1 class="text-slate-900 text-2xl md:text-3xl font-extrabold tracking-tight">
                    {{ $t('All Products') }}
                </h1>

                <div class="flex items-center gap-4">
                    <span class="text-slate-400 text-xs md:text-sm font-medium">
                        {{ totalProducts }} {{ $t('items') }}
                    </span>

                    <!-- Filter Button -->
                    <button
                        class="px-4 py-2.5 rounded-xl flex items-center gap-2 text-xs sm:text-sm font-semibold whitespace-nowrap outline-none transition duration-200 shrink-0 cursor-pointer"
                        :class="hasFilter ? 'bg-primary-200 text-primary' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700'"
                        @click="showFilterCanvas = true"
                    >
                        <FunnelIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                        <span>{{ $t('Filter') }}</span>
                    </button>
                </div>
            </div>
            <div v-else class="flex justify-between items-center mb-6">
                <SkeletonLoader class="w-48 h-8 rounded-lg" />
                <div class="flex items-center gap-4">
                    <SkeletonLoader class="w-16 h-5 rounded-lg" />
                    <SkeletonLoader class="w-24 h-10 rounded-xl" />
                </div>
            </div>

            <!-- Search Query Tag if active -->
            <div v-if="!isLoading && master.search" class="mb-6">
                <span class="text-sm font-medium text-slate-600">
                    {{ $t('Search results for') }}: <strong class="text-slate-900">“{{ master.search }}”</strong>
                </span>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6 items-start">
                <div v-if="!isLoading" v-for="product in products" :key="product.id" class="w-full">
                    <ProductCard :product="product" />
                </div>

                <!-- Loading Skeleton -->
                <div v-else v-for="i in 12" :key="i">
                    <SkeletonLoader class="w-full h-[220px] sm:h-[330px] rounded-lg" />
                </div>
            </div>

            <div v-if="products.length == 0 && !isLoading" class="flex justify-center items-center w-full mt-12">
                <div class="text-slate-500 text-base font-normal">
                    {{ $t('No products found') }}
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="products.length > 0 && !isLoading && totalProducts > perPage" class="flex justify-end items-center w-full mt-8 gap-4 flex-wrap">
                <div>
                    <vue-awesome-paginate :total-items="totalProducts" :items-per-page="perPage" type="button"
                        :max-pages-shown="5" v-model="currentPage"
                        :hide-prev-next-when-ends="true"
                        @click="onClickHandler" />
                </div>
            </div>
        </div>

        <!-- Filter Canvas Drawer -->
        <TransitionRoot as="template" :show="showFilterCanvas">
            <Dialog as="div" class="relative z-10" @close="showFilterCanvas = false">
                <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100"
                    leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-30 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 flex max-w-full"
                            :class="master.langDirection == 'rtl' ? 'left-0 sm:pr-10' : 'right-0 sm:pl-10'">
                            <TransitionChild as="template"
                                enter="transform transition ease-in-out duration-500 sm:duration-700"
                                :enter-from="master.langDirection == 'rtl' ? '-translate-x-full' : 'translate-x-full'"
                                enter-to="translate-x-0"
                                leave="transform transition ease-in-out duration-500 sm:duration-700"
                                leave-from="translate-x-0"
                                :leave-to="master.langDirection == 'rtl' ? '-translate-x-full' : 'translate-x-full'">
                                <DialogPanel class="pointer-events-auto relative w-screen max-w-md">
                                    <TransitionChild as="template" enter="ease-in-out duration-500"
                                        enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-500"
                                        leave-from="opacity-100" leave-to="opacity-0">
                                        <div class="absolute left-0 top-0 -ml-8 flex pr-2 pt-4 sm:-ml-10 sm:pr-4"></div>
                                    </TransitionChild>
                                    <div
                                        class="flex h-full flex-col justify-between overflow-y-scroll bg-white shadow-xl">
                                        <div class="p-4 flex flex-col gap-7">
                                            <div class="flex justify-between items-center" aria-hidden="true">
                                                <div class="text-slate-950 text-xl font-bold leading-loose">
                                                    {{ $t("Filter") }}
                                                </div>
                                                <button
                                                    class="w-8 h-8 flex justify-center items-center bg-slate-100 rounded-full"
                                                    @click="showFilterCanvas = false">
                                                    <XMarkIcon class="w-5 h-5 text-slate-700" />
                                                </button>
                                            </div>

                                            <!-- Customer Review -->
                                            <div>
                                                <div class="text-slate-950 text-base font-medium leading-normal">
                                                    {{ $t("Customer Review") }}
                                                </div>
                                                <!-- Rating -->
                                                <div class="flex flex-col gap-2 mt-3">
                                                    <div v-for="ratingNumber in ratings" :key="ratingNumber">
                                                        <label :for="`rating${ratingNumber}`" class="cursor-pointer has-[:checked]:border-primary text-slate-800 flex items-center justify-between px-2 py-1.5 bg-white rounded-lg border border-slate-100 gap-1.5">
                                                            <div class="flex items-center gap-1">
                                                                <div class="flex items-center">
                                                                    <StarIcon v-for="i in 5" :key="i" class="w-5 h-5"  :class="i <= ratingNumber ? 'text-amber-500' : 'text-gray-200'" />
                                                                </div>
                                                                <div class="text-base font-medium leading-normal">
                                                                    {{ ratingNumber }}.0
                                                                </div>
                                                            </div>
                                                            <input type="radio" v-model="filterFormData.rating" :id="`rating${ratingNumber}`" name="rating" class="w-5 h-5 appearance-none checked:bg-primary rounded-full border-2 border-slate-300 shrink-0 transition duration-300" :value="ratingNumber" />
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sort by -->
                                            <div>
                                                <div class="text-slate-950 text-base font-medium leading-normal">
                                                    {{ $t('Sort by') }}
                                                </div>

                                                <select v-model="filterFormData.sort_type"
                                                    class="w-full mt-1 p-3 rounded bg-transparent border border-gray-100 outline-none">
                                                    <option v-for="shortBy in filterSortBy" :key="shortBy"
                                                        :value="shortBy.value">
                                                        {{ shortBy.name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div>
                                                <div class="flex justify-between items-center gap-2">
                                                    <div class="text-slate-950 text-base font-medium leading-normal">
                                                        {{
                                                            $t("Product Price")
                                                        }}
                                                    </div>
                                                    <div class="text-primary text-base font-normal leading-normal">
                                                        {{ master.showCurrency(priceRange[0]) }}
                                                        -
                                                        {{ master.showCurrency(priceRange[1]) }}
                                                    </div>
                                                </div>
                                                <div class="w-[98%]">
                                                    <vue-slider v-model="priceRange" :min="globalMinPrice"
                                                        :max="globalMaxPrice"></vue-slider>
                                                </div>
                                                <div
                                                    class="text-slate-400 text-xs font-normal leading-none flex justify-between mt-2">
                                                    <span>
                                                        {{ master.showCurrency(globalMinPrice) }}
                                                    </span>
                                                    <span>
                                                        {{ master.showCurrency(globalMaxPrice) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Brand -->
                                            <div>
                                                <div class="text-slate-950 text-base font-medium leading-normal">
                                                    {{ $t("Category") }}
                                                </div>

                                                <select v-model="filterFormData.category_id"
                                                    class="w-full mt-1 p-3 rounded bg-transparent border border-gray-100 outline-none">
                                                    <option value="" selected>
                                                        {{ $t("Select Category") }}
                                                    </option>
                                                    <option v-for="category in master.categories" :key="category.id"
                                                        :value="category.id">
                                                        {{ category.name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Brand -->
                                            <div>
                                                <div class="text-slate-950 text-base font-medium leading-normal">
                                                    {{ $t("Brand") }}
                                                </div>

                                                <select v-model="filterFormData.brand_id"
                                                    class="w-full mt-1 p-3 rounded bg-transparent border border-gray-100 outline-none">
                                                    <option value="" selected>
                                                        {{ $t("Select Brand") }}
                                                    </option>
                                                    <option v-for="brand in filter.brands" :key="brand.id"
                                                        :value="brand.id">
                                                        {{ brand.name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- color -->
                                            <div>
                                                <div class="text-slate-950 text-base font-medium leading-normal">
                                                    {{ $t("Color") }}
                                                </div>

                                                <div
                                                    class="flex flex-wrap gap-4 p-3 rounded-xl border border-slate-200 mt-1">
                                                    <label v-for="color in filter.colors" :key="color.id"
                                                        class="cursor-pointer has-[:checked]:text-primary text-slate-800 flex items-center p-2 bg-white gap-2">
                                                        <input type="radio" v-model="filterFormData.color_id
                                                            " :value="color.id" :id="'color-' +
                                                                color.id
                                                                " name="color"
                                                            class="w-5 h-5 appearance-none checked:bg-primary rounded-full border-2 border-slate-300 shrink-0 transition duration-300" />
                                                        <span>{{ color.name }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- size -->
                                            <div>
                                                <div class="text-slate-950 text-base font-medium leading-normal">
                                                    {{ $t("Size") }}
                                                </div>

                                                <div
                                                    class="flex flex-wrap gap-4 p-3 rounded-xl border border-slate-200 mt-1">
                                                    <label v-for="size in filter.sizes" :key="size.id"
                                                        class="cursor-pointer has-[:checked]:text-primary text-slate-800 flex items-center p-2 bg-white gap-2">
                                                        <input type="radio" v-model="filterFormData.size_id
                                                            " :value="size.id" :id="'size-' +
                                                                size.id
                                                                " name="size"
                                                            class="w-5 h-5 appearance-none checked:bg-primary rounded-full border-2 border-slate-300 shrink-0 transition duration-300" />
                                                        <span>{{ size.name }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- button Clear and Apply -->
                                        <div class="flex gap-6 p-6 border-t border-slate-200">
                                            <button
                                                class="grow px-4 py-3 rounded-[10px] border border-primary text-primary text-base font-medium leading-normal"
                                                @click="clearFilter">
                                                {{ $t("Clear") }}
                                            </button>
                                            <button
                                                class="grow px-4 py-3 bg-primary rounded-[10px] border border-primary text-white text-base font-medium leading-normal"
                                                @click="applyFilter">
                                                {{ $t("Apply") }}
                                            </button>
                                        </div>
                                    </div>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch, computed } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot, } from "@headlessui/vue";
import { FunnelIcon, XMarkIcon, ArrowLeftIcon, } from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";
import ProductCard from "../components/ProductCard.vue";
import { useMaster } from "../stores/MasterStore";

import VueSlider from "vue-slider-component";
import "vue-slider-component/theme/default.css";
import SkeletonLoader from "../components/SkeletonLoader.vue";

const priceRange = ref([0, 1000]);

const master = useMaster();
const isLoading = ref(true);

onMounted(() => {
    fetchProducts();
    window.scrollTo(0, 0);
    setRangeValue.value = true;
});

onBeforeUnmount(() => {
    setRangeValue.value = false;
    master.search = null;
});

const search = master.search;

watch(() => master.search, () => {
    fetchProducts();
});

const currentPage = ref(1);
const perPage = 12;

const onClickHandler = (page) => {
    currentPage.value = page;
    fetchProducts();
};

const showFilterCanvas = ref(false);

const filterFormData = ref({
    rating: null,
    sort_type: "default",
    brand_id: "",
    color_id: null,
    size_id: null,
    min_price: null,
    max_price: null,
    category_id: "",
});

const filter = ref({
    sizes: [],
    brands: [],
    colors: [],
    min_price: 0,
    max_price: 1000,
});

const ratings = [5, 4, 3, 2, 1];

const products = ref([]);
const totalProducts = ref(0);
const setRangeValue = ref(true);

const globalMinPrice = ref(0);
const globalMaxPrice = ref(1000);
const isBoundsInitialized = ref(false);

const hasFilter = computed(() => {
    const isPriceCustomized =
        filterFormData.value.min_price !== null ||
        filterFormData.value.max_price !== null ||
        (priceRange.value[0] > globalMinPrice.value || priceRange.value[1] < globalMaxPrice.value);

    return (
        filterFormData.value.rating !== null ||
        (filterFormData.value.sort_type && filterFormData.value.sort_type !== 'default') ||
        (filterFormData.value.brand_id !== '' && filterFormData.value.brand_id !== null) ||
        filterFormData.value.color_id !== null ||
        filterFormData.value.size_id !== null ||
        (filterFormData.value.category_id !== '' && filterFormData.value.category_id !== null) ||
        isPriceCustomized
    );
});

const toggleRating = (val) => {
    if (filterFormData.value.rating === val) {
        filterFormData.value.rating = null;
    } else {
        filterFormData.value.rating = val;
    }
};

const toggleColor = (val) => {
    if (filterFormData.value.color_id === val) {
        filterFormData.value.color_id = null;
    } else {
        filterFormData.value.color_id = val;
    }
};

const toggleSize = (val) => {
    if (filterFormData.value.size_id === val) {
        filterFormData.value.size_id = null;
    } else {
        filterFormData.value.size_id = val;
    }
};

const getProductSellingPrice = (prod) => {
    if (parseInt(prod?.first_variant_inward_product_id || 0) > 0 && parseFloat(prod?.first_variant_price || 0) > 0) {
        return parseFloat(prod.first_variant_price);
    }
    const dPrice = parseFloat(prod?.discount_price || 0);
    const pPrice = parseFloat(prod?.price || 0);
    if (dPrice > 0) return dPrice;
    return pPrice;
};

const fetchProducts = async () => {
    isLoading.value = true;
    window.scrollTo({
        top: 0,
        behavior: "smooth",
    });
    axios.get("/products", {
        params: {
            page: currentPage.value,
            per_page: perPage,
            search: master.search,
            ...filterFormData.value,
        },
        headers: {
            "Accept-Language": master.locale || "en",
        },
    }).then((response) => {
        totalProducts.value = response.data.data.total;
        products.value = response.data.data.products;

        const filtersData = response.data.data.filters || {};
        let minPrice = filtersData.min_price ? parseFloat(filtersData.min_price) : 0;
        let maxPrice = filtersData.max_price ? parseFloat(filtersData.max_price) : 0;

        if (products.value && products.value.length > 0) {
            const productPrices = products.value
                .map(getProductSellingPrice)
                .filter(p => p > 0);

            if (productPrices.length > 0) {
                const calculatedMin = Math.min(...productPrices);
                const calculatedMax = Math.max(...productPrices);
                if (minPrice === 0 || calculatedMin < minPrice) {
                    minPrice = calculatedMin;
                }
                if (calculatedMax > maxPrice) {
                    maxPrice = calculatedMax;
                }
            }
        }

        if (!isBoundsInitialized.value || minPrice < globalMinPrice.value) {
            globalMinPrice.value = Math.floor(minPrice);
        }
        if (!isBoundsInitialized.value || maxPrice > globalMaxPrice.value) {
            globalMaxPrice.value = Math.ceil(maxPrice);
        }
        isBoundsInitialized.value = true;

        filter.value = {
            ...filtersData,
            min_price: globalMinPrice.value,
            max_price: globalMaxPrice.value,
        };

        if (setRangeValue.value) {
            priceRange.value = [
                globalMinPrice.value,
                globalMaxPrice.value,
            ];
        }

        setTimeout(() => {
            isLoading.value = false;
        }, 200);

    }).catch((error) => {
        isLoading.value = false;
    })
};

const clearFilter = () => {
    filterFormData.value = {
        rating: null,
        sort_type: "default",
        brand_id: "",
        color_id: null,
        size_id: null,
        min_price: null,
        max_price: null,
        category_id: "",
    };
    priceRange.value = [
        globalMinPrice.value,
        globalMaxPrice.value,
    ];
    setRangeValue.value = true;
    currentPage.value = 1;
    showFilterCanvas.value = false;
    fetchProducts();
};

const applyFilter = () => {
    if (priceRange.value[0] > globalMinPrice.value || priceRange.value[1] < globalMaxPrice.value) {
        filterFormData.value.min_price = priceRange.value[0];
        filterFormData.value.max_price = priceRange.value[1];
    } else {
        filterFormData.value.min_price = null;
        filterFormData.value.max_price = null;
    }

    setRangeValue.value = false;
    currentPage.value = 1;
    showFilterCanvas.value = false;
    fetchProducts();
};

const filterSortBy = [
    {
        name: "Default Sorting",
        value: "default",
    },
    {
        name: "High to Low",
        value: "high_to_low",
    },
    {
        name: "Low to High",
        value: "low_to_high",
    },
    {
        name: "Most Selling",
        value: "top_selling",
    },
    {
        name: "New Product",
        value: "newest",
    },
];
</script>

<style>
.vue-slider-process {
    @apply bg-primary;
}

input[type="range"]::-webkit-slider-runnable-track,
input[type="range"]::-ms-track,
input[type="range"]::-moz-range-track {
    background: #000;
}

input[type="range"]::-moz-range-thumb,
input[type="range"]::-ms-thumb,
input[type="range"]::-webkit-slider-thumb {
    background: #000;
}
</style>
