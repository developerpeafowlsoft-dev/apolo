<template>
    <div class="main-container pt-6 pb-16 min-h-[70vh]">

        <!-- Loading State for Categories -->
        <div v-if="isCategoriesLoading" class="grid grid-cols-12 gap-8 items-start">
            <div class="col-span-12 lg:col-span-3 space-y-4">
                <SkeletonLoader v-for="i in 8" :key="i" class="w-full h-8 rounded-lg" />
            </div>
            <div class="col-span-12 lg:col-span-9 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <SkeletonLoader v-for="i in 8" :key="i" class="w-full h-[360px] rounded-2xl" />
            </div>
        </div>

        <!-- Categories & Products Layout (Chawkbazar Image 2 Style) -->
        <div v-else-if="categories.length > 0" class="grid grid-cols-12 gap-8 items-start">
            
            <!-- Left Sidebar (Compact Width) -->
            <aside class="col-span-12 lg:col-span-2.5 xl:col-span-2">
                <!-- Breadcrumb: Home / Categories -->
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-4">
                    <router-link to="/" class="hover:text-slate-900 transition-colors">{{ $t('Home') }}</router-link>
                    <span class="text-slate-400">/</span>
                    <span class="text-slate-600 font-semibold">{{ $t('Categories') }}</span>
                </nav>

                <!-- Sidebar Heading: All Categories -->
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mb-3">
                    {{ $t('All Categories') }}
                </h2>
                <!-- Horizontal Divider Line -->
                <div class="w-full border-b border-slate-200/80 mb-5"></div>

                <!-- Category List -->
                <div class="flex lg:flex-col overflow-x-auto lg:overflow-x-visible gap-1.5 lg:gap-2 pb-4 lg:pb-0 no-scrollbar">
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="selectCategory(cat)"
                        class="text-left py-1.5 px-3 lg:px-0 rounded-xl lg:rounded-none whitespace-nowrap lg:whitespace-normal transition-colors shrink-0 lg:shrink flex items-center justify-between gap-3 group w-full"
                        :class="selectedCategory?.id === cat.id 
                            ? 'bg-slate-100 lg:bg-transparent' 
                            : 'hover:bg-slate-50 lg:hover:bg-transparent'"
                    >
                        <span class="truncate text-sm sm:text-base" :class="selectedCategory?.id === cat.id ? 'font-extrabold text-slate-950' : 'font-medium text-slate-700 group-hover:text-slate-950'">
                            {{ cat.name }}
                        </span>
                        
                        <span class="text-xs sm:text-sm shrink-0" :class="selectedCategory?.id === cat.id ? 'font-extrabold text-slate-950' : 'font-semibold text-slate-400 group-hover:text-slate-600'">
                            {{ getCategoryCount(cat) }}
                        </span>
                    </button>
                </div>
            </aside>

            <!-- Right Products Grid (Expanded Width) -->
            <main class="col-span-12 lg:col-span-9.5 xl:col-span-10">
                <!-- Selected Category Main Heading + Item Count -->
                <div class="flex justify-between items-baseline mb-4 pt-0">
                    <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 tracking-tight">
                        {{ selectedCategory?.name || $t('Collection') }}
                    </h1>
                    <span class="text-xs font-semibold text-slate-400">
                        {{ products.length }} {{ $t('items') }}
                    </span>
                </div>

                <!-- Subcategories Chips Filter Row (SEO-friendly & Responsive) -->
                <section v-if="subcategories.length > 0" aria-label="Subcategories" class="mb-6">
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 pt-1 no-scrollbar flex-nowrap sm:flex-wrap">
                        <!-- All Products Chip -->
                        <button
                            @click="selectSubcategory(null)"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 shrink-0 border cursor-pointer"
                            :class="selectedSubcategory === null
                                ? 'bg-slate-900 text-white border-slate-900 shadow-xs'
                                : 'bg-slate-100/90 hover:bg-slate-200/90 text-slate-700 border-slate-200/60'"
                            :aria-pressed="selectedSubcategory === null"
                        >
                            {{ $t('All Products') }}
                        </button>

                        <!-- Dynamic Subcategory Chips -->
                        <button
                            v-for="sub in subcategories"
                            :key="sub.id"
                            @click="selectSubcategory(sub)"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 shrink-0 border cursor-pointer"
                            :class="selectedSubcategory?.id === sub.id
                                ? 'bg-slate-900 text-white border-slate-900 shadow-xs'
                                : 'bg-slate-100/90 hover:bg-slate-200/90 text-slate-700 border-slate-200/60'"
                            :aria-pressed="selectedSubcategory?.id === sub.id"
                        >
                            {{ sub.name }}
                        </button>
                    </div>
                </section>

                <!-- Products Loading -->
                <div v-if="isProductsLoading" class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    <SkeletonLoader v-for="i in 8" :key="i" class="w-full h-[360px] rounded-2xl" />
                </div>

                <!-- Products Grid -->
                <div v-else-if="products.length > 0" class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 items-stretch">
                    <div v-for="product in products" :key="product.id" class="w-full">
                        <ProductCard :product="product" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-16 text-center bg-slate-50 rounded-3xl border border-slate-100">
                    <p class="text-slate-500 font-semibold text-base">
                        {{ $t('No Products Found in this Category') }}
                    </p>
                </div>
            </main>
        </div>

        <!-- Empty Categories State -->
        <div v-else class="py-20 text-center">
            <p class="text-slate-500 text-lg font-medium">
                {{ $t('No Categories Found') }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProductCard from '../components/ProductCard.vue';
import SkeletonLoader from '../components/SkeletonLoader.vue';

const route = useRoute();
const router = useRouter();

const categories = ref([]);
const selectedCategory = ref(null);
const subcategories = ref([]);
const selectedSubcategory = ref(null);
const products = ref([]);
const categoryProductCounts = ref({});
const isCategoriesLoading = ref(true);
const isProductsLoading = ref(false);

onMounted(() => {
    window.scrollTo(0, 0);
    fetchCategories();
});

const getCategoryCount = (category) => {
    if (!category) return 0;
    if (categoryProductCounts.value[category.id] !== undefined) {
        return categoryProductCounts.value[category.id];
    }
    if (category.total_products !== undefined && category.total_products !== null) return category.total_products;
    if (category.products_count !== undefined && category.products_count !== null) return category.products_count;
    if (category.products && Array.isArray(category.products)) return category.products.length;
    return 0;
};

const fetchCategoryProductCounts = () => {
    categories.value.forEach(async (cat) => {
        try {
            const res = await axios.get('/products', {
                params: { category_id: cat.id, per_page: 1 }
            });
            if (res.data?.data?.total !== undefined) {
                categoryProductCounts.value[cat.id] = res.data.data.total;
            }
        } catch (e) {
            console.error('Error fetching count for category', cat.id, e);
        }
    });
};

const fetchSubcategories = async (categoryId) => {
    subcategories.value = [];
    try {
        const response = await axios.get(`/sub-categories?category_id=${categoryId}`);
        subcategories.value = response.data?.data?.sub_categories || response.data?.data || [];
    } catch (e) {
        console.error('Failed to fetch subcategories:', e);
        subcategories.value = [];
    }
};

const fetchCategories = async () => {
    isCategoriesLoading.value = true;
    try {
        const rawCats = response.data.data.categories || [];
        categories.value = rawCats.filter(c => Boolean(c.show_in_hero));
        
        if (categories.value.length > 0) {
            fetchCategoryProductCounts();

            const match = route.params.slug 
                ? categories.value.find(c => String(c.id) === String(route.params.slug))
                : categories.value[0];
            
            selectCategory(match || categories.value[0], true);
        }
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    } finally {
        isCategoriesLoading.value = false;
    }
};

const selectCategory = async (category, checkUrlSubcategory = false) => {
    if (!category) return;
    selectedCategory.value = category;
    
    // Load subcategories for selected main category
    await fetchSubcategories(category.id);

    if (checkUrlSubcategory && route.query.subcategory) {
        const subId = route.query.subcategory;
        const matched = subcategories.value.find(s => String(s.id) === String(subId));
        selectedSubcategory.value = matched || { id: subId };
        fetchProducts(category.id, subId);
    } else {
        selectedSubcategory.value = null;
        fetchProducts(category.id, null);
    }
};

const selectSubcategory = async (sub) => {
    selectedSubcategory.value = sub;

    if (sub === null) {
        router.replace({ path: route.path, query: {} }).catch(() => {});
        fetchProducts(selectedCategory.value?.id, null);
    } else {
        router.replace({ path: route.path, query: { subcategory: sub.id } }).catch(() => {});
        fetchProducts(selectedCategory.value?.id, sub.id);
    }
};

const fetchProducts = async (categoryId, subcategoryId) => {
    if (!categoryId) return;
    isProductsLoading.value = true;

    const params = {
        category_id: categoryId,
        per_page: 24
    };
    if (subcategoryId) {
        params.sub_category_id = subcategoryId;
    }

    try {
        const response = await axios.get('/products', { params });
        products.value = response.data?.data?.products || [];
        
        if (!subcategoryId) {
            categoryProductCounts.value[categoryId] = response.data?.data?.total ?? products.value.length;
        }
    } catch (error) {
        console.error('Failed to fetch products:', error);
        products.value = [];
    } finally {
        isProductsLoading.value = false;
    }
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
