<template>
    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:border-slate-300 transition-all duration-300 group flex flex-col justify-between h-full w-full">
        <!-- Category Title -->
        <router-link :to="routeUrl" class="text-slate-900 text-lg font-extrabold tracking-tight truncate mb-4 group-hover:text-primary transition-colors block">
            {{ props.category?.name }}
        </router-link>

        <!-- Top 3 Subcategories Grid (Reference Style) -->
        <div class="grid grid-cols-3 gap-2.5 w-full">
            <template v-if="subcategoriesList.length > 0">
                <router-link
                    v-for="sub in subcategoriesList"
                    :key="sub.id"
                    :to="getSubcategoryUrl(sub)"
                    class="w-full min-h-[120px] sm:min-h-[130px] rounded-xl bg-slate-100/90 flex flex-col items-center justify-between p-2 hover:scale-105 transition-transform duration-300 relative group/sub"
                    :title="sub.name"
                >
                    <div class="w-full grow flex items-center justify-center overflow-hidden min-h-[65px] sm:min-h-[75px]">
                        <img :src="sub.thumbnail || sub.image || props.category?.thumbnail"
                            :alt="sub.name"
                            class="w-full h-full max-h-[75px] sm:max-h-[85px] object-contain group-hover/sub:scale-110 transition-transform duration-500" loading="lazy" />
                    </div>

                    <!-- Subcategory Name -->
                    <span class="w-full text-[10px] sm:text-[11px] font-bold text-slate-800 line-clamp-2 text-center leading-snug mt-1 group-hover/sub:text-primary transition-colors break-words">
                        {{ sub.name }}
                    </span>
                </router-link>
            </template>

            <!-- Fallback if no subcategories exist -->
            <template v-else>
                <router-link
                    :to="routeUrl"
                    class="w-full min-h-[120px] sm:min-h-[130px] rounded-xl bg-slate-100/90 flex flex-col items-center justify-between p-2 hover:scale-105 transition-transform duration-300 relative group/sub"
                    :title="props.category?.name"
                >
                    <div class="w-full grow flex items-center justify-center overflow-hidden min-h-[65px] sm:min-h-[75px]">
                        <img :src="props.category?.thumbnail"
                            :alt="props.category?.name"
                            class="w-full h-full max-h-[75px] sm:max-h-[85px] object-contain hover:scale-110 transition-transform duration-500" loading="lazy" />
                    </div>

                    <span class="w-full text-[10px] sm:text-[11px] font-bold text-slate-800 line-clamp-2 text-center leading-snug mt-1 group-hover/sub:text-primary transition-colors break-words">
                        {{ props.category?.name }}
                    </span>
                </router-link>
            </template>
        </div>
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { ref, onMounted, watch } from 'vue';

const route = useRoute();

const props = defineProps({
    category: Object
});

const routeUrl = ref(`/categories/${props.category?.id}`);
const subcategoriesList = ref([]);

const getSubcategoryUrl = (sub) => {
    if (route.name === 'shop-detail') {
        return `/shops/${route.params.id}/categories/${props.category?.id}?subcategory=${sub.id}`;
    }
    return `/categories/${props.category?.id}?subcategory=${sub.id}`;
};

const loadSubcategories = async () => {
    if (!props.category) return;

    // Check if subcategories already attached on category prop
    const existing = props.category?.sub_categories || props.category?.subcategories || props.category?.children;
    if (Array.isArray(existing) && existing.length > 0) {
        subcategoriesList.value = existing.slice(0, 3);
        return;
    }

    // Fetch subcategories for this category from API
    if (props.category?.id) {
        try {
            const response = await axios.get(`/sub-categories?category_id=${props.category.id}`);
            const fetched = response.data?.data?.sub_categories || response.data?.data || [];
            if (Array.isArray(fetched) && fetched.length > 0) {
                subcategoriesList.value = fetched.slice(0, 3);
            }
        } catch (e) {
            console.error('Failed to load subcategories for category', props.category?.id, e);
        }
    }
};

onMounted(() => {
    if (route.name === 'shop-detail') {
        routeUrl.value = `/shops/${route.params.id}/categories/${props.category?.id}`;
    }
    loadSubcategories();
});

watch(() => props.category, () => {
    loadSubcategories();
}, { deep: true });
</script>

<style scoped></style>
