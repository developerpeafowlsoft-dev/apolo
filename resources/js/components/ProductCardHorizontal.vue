<template>
    <article class="p-4 sm:p-4.5 bg-white rounded-2xl border border-slate-100 shadow-xs hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group flex items-center gap-4 sm:gap-5 cursor-pointer w-full h-full min-h-[150px] relative"
        @click="showProductDetails">

        <!-- Left Image Box (Properly Fitted & Centered) -->
        <div class="w-32 sm:w-40 h-32 sm:h-36 shrink-0 rounded-xl bg-slate-50 overflow-hidden flex items-center justify-center relative border border-slate-100/60"
            :class="props.product?.quantity > 0 ? '' : 'opacity-40'">
            <img 
                :src="props.product?.thumbnail" 
                :alt="props.product?.name ? `${props.product.name} - Top Product` : 'Product Image'" 
                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 ease-out" 
                loading="lazy"
            />
            
            <div v-if="cardDiscount > 0"
                class="px-2 py-0.5 bg-rose-600 rounded-md text-white text-[10px] font-extrabold absolute top-2 left-2 shadow-xs z-10 tracking-wide">
                {{ cardDiscount }}% {{ $t('OFF') }}
            </div>
        </div>

        <!-- Right Product Info -->
        <div class="flex flex-col justify-center grow overflow-hidden">
            <!-- Brand Badge (If available) -->
            <div v-if="brandName" class="inline-block px-2 py-0.5 bg-slate-900/85 backdrop-blur-md text-white text-[9px] font-bold rounded-md uppercase tracking-wider mb-1 max-w-[fit-content] truncate">
                {{ brandName }}
            </div>

            <!-- Product Title (SEO-friendly Heading + Router Link) -->
            <h3 class="text-slate-900 font-extrabold text-sm sm:text-base leading-snug line-clamp-1 w-full group-hover:text-slate-700 transition-colors mb-1"
                :title="props.product?.name">
                <router-link 
                    v-if="props.product?.id" 
                    :to="{ name: 'productDetails', params: { id: props.product.id } }"
                    @click.stop
                    class="hover:underline focus:outline-none"
                >
                    {{ props.product?.name }}
                </router-link>
                <span v-else>{{ props.product?.name }}</span>
            </h3>

            <p class="text-xs text-slate-400 line-clamp-1 leading-relaxed mb-2">
                {{ props.product?.short_description || props.product?.description || 'Quality and comfort guaranteed.' }}
            </p>

            <div class="flex items-center gap-2 flex-wrap">
                <div class="text-slate-950 font-black text-base sm:text-lg tracking-tight">
                    {{ masterStore.showCurrency(cardSellingPrice) }}
                </div>
                <div v-if="cardOriginalPrice > cardSellingPrice"
                    class="text-slate-400 text-xs font-medium line-through leading-tight">
                    {{ masterStore.showCurrency(cardOriginalPrice) }}
                </div>
            </div>
        </div>
    </article>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';

const router = useRouter();
const authStore = useAuth();
const basketStore = useBasketStore();
const masterStore = useMaster();

const props = defineProps({
    product: Object
});

const brandName = computed(() => {
    if (!props.product) return null;
    if (props.product.brand?.name) return props.product.brand.name;
    if (typeof props.product.brand === 'string' && props.product.brand.trim() !== '') return props.product.brand;
    if (props.product.brand_name) return props.product.brand_name;
    return null;
});

const hasFirstVariant = computed(() => {
    return parseInt(props.product?.first_variant_inward_product_id || 0) > 0;
});

const cardSellingPrice = computed(() => {
    if (hasFirstVariant.value) {
        return parseFloat(props.product?.first_variant_price || 0);
    }
    const normalPrice = parseFloat(props.product?.price || 0);
    const normalDiscountPrice = parseFloat(props.product?.discount_price || 0);
    return (normalDiscountPrice > 0 && normalDiscountPrice < normalPrice) ? normalDiscountPrice : normalPrice;
});

const cardOriginalPrice = computed(() => {
    if (hasFirstVariant.value) {
        return parseFloat(props.product?.first_variant_mrp || props.product?.price || 0);
    }
    return parseFloat(props.product?.price || 0);
});

const cardDiscount = computed(() => {
    if (hasFirstVariant.value) {
        return parseFloat(props.product?.first_variant_discount || 0);
    }
    return parseFloat(props.product?.discount_percentage || 0);
});

const showProductDetails = () => {
    if (props.product?.id) {
        router.push({ name: 'productDetails', params: { id: props.product.id } });
    }
};
</script>
