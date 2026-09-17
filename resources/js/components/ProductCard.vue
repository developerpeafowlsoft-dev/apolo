<template>
  <article
      class="bg-white rounded-2xl border border-slate-100 p-3 shadow-xs hover:shadow-lg transition-all duration-300 group flex flex-col justify-between h-full w-full cursor-pointer relative"
      @click="showProductDetails"
      :class="props.product?.quantity > 0 ? '' : 'opacity-80'"
  >
    <div class="flex flex-col">
      <!-- Consistent Compact Aspect Ratio Image Container -->
      <div
          class="w-full aspect-[4/4.2] rounded-xl bg-slate-100/80 overflow-hidden relative flex items-center justify-center mb-2.5"
          :class="props.product?.quantity > 0 ? '' : 'opacity-50'"
      >
        <img
            :src="props.product?.thumbnail"
            :alt="props.product?.name || 'Product Image'"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
            loading="lazy"
        />

        <!-- Brand Badge (Top-Left) -->
        <div
            v-if="brandName"
            class="px-2 py-0.5 bg-slate-900/85 backdrop-blur-md text-white text-[10px] font-bold rounded-md absolute top-2.5 left-2.5 shadow-xs uppercase tracking-wider z-1 max-w-[60%] truncate"
        >
          {{ brandName }}
        </div>

        <!-- Discount Badge -->
        <div
            v-if="cardDiscount > 0"
            class="px-2 py-0.5 bg-rose-600 text-white text-[10px] font-extrabold rounded-md absolute shadow-xs tracking-wide z-1"
            :class="brandName ? 'top-8 left-2.5' : 'top-2.5 left-2.5'"
        >
          {{ cardDiscount }}% {{ $t('OFF') }}
        </div>

        <!-- Wishlist Button (Top-Right, Prominent Large Red Heart Icon) -->
        <button
            class="absolute top-2.5 right-2.5 w-10 h-10 sm:w-11 sm:h-11 rounded-full justify-center items-center flex cursor-pointer bg-white/95 shadow-md hover:scale-110 active:scale-95 transition-all border border-slate-100/80 z-1 hover:bg-rose-50"
            @click.stop="favoriteAddOrRemove"
            :aria-label="$t('Wishlist')"
        >
          <HeartIcon v-if="props.product?.is_favorite || isFavorite" class="w-6 h-6 sm:w-7 sm:h-7 text-rose-600 fill-rose-600 drop-shadow-xs" />
          <HeartIconOutline v-else class="w-6 h-6 sm:w-7 sm:h-7 text-rose-500 hover:text-rose-600 transition-colors" />
        </button>
      </div>

      <!-- Product Information (Title, Description, Price) -->
      <div class="flex flex-col gap-1 px-0.5">
        <h3
            class="text-slate-900 text-sm sm:text-base font-extrabold leading-snug line-clamp-1 w-full group-hover:text-slate-700 transition-colors"
            :title="props.product?.name"
        >
          {{ props.product?.name }}
        </h3>

        <p class="text-[11px] sm:text-xs text-slate-400 line-clamp-1 leading-relaxed mb-1">
          {{ props.product?.short_description || props.product?.description || 'Quality and comfort guaranteed.' }}
        </p>

        <!-- Price -->
        <div class="flex items-center gap-2 flex-wrap mb-1">
          <div class="text-slate-950 text-base font-black tracking-tight">
            {{ masterStore.showCurrency(cardSellingPrice) }}
          </div>

          <div
              v-if="cardOriginalPrice > cardSellingPrice"
              class="text-slate-400 text-xs font-medium line-through"
          >
            {{ masterStore.showCurrency(cardOriginalPrice) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Action / Stock Bar -->
    <div class="w-full mt-1.5 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-1.5">
      <div class="flex items-center gap-1 shrink-0">
        <StarIcon class="w-3.5 h-3.5 text-amber-400 fill-amber-400" />
        <span class="text-xs font-extrabold text-slate-800">{{ props.product?.rating ? props.product.rating.toFixed(1) : '5.0' }}</span>
      </div>

      <div v-if="props.product?.quantity > 0" class="flex items-center gap-1.5 shrink-0">
        <!-- If already in cart, show quantity controls -->
        <div v-if="cartQuantity > 0" class="flex items-center gap-1.5 p-1 rounded-xl border border-slate-200 bg-slate-50 shrink-0" @click.stop>
          <button
              class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center transition-all active:scale-95 cursor-pointer shadow-xs"
              @click.stop="decrementCartQty"
              :title="$t('Decrease quantity')"
              :aria-label="$t('Decrease quantity')"
          >
            <MinusIcon class="w-3.5 h-3.5 text-slate-700" />
          </button>

          <span class="w-6 text-center text-slate-900 text-xs font-black">
            {{ cartQuantity }}
          </span>

          <button
              class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center transition-all active:scale-95 cursor-pointer shadow-xs"
              @click.stop="incrementCartQty"
              :title="$t('Increase quantity')"
              :aria-label="$t('Increase quantity')"
          >
            <PlusIcon class="w-3.5 h-3.5 text-slate-700" />
          </button>
        </div>

        <!-- Add to Cart Button (Centered Perfect Circle) -->
        <button
            v-else
            class="w-8 h-8 shrink-0 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 flex items-center justify-center p-0 transition-all active:scale-95 shadow-xs cursor-pointer"
            @click.stop="openVariantModal(false)"
            :title="$t('Add to Cart')"
            :aria-label="$t('Add to Cart')"
        >
          <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
        </button>

        <!-- Buy Now Button -->
        <button
            class="px-3 py-1.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all shadow-xs active:scale-95 cursor-pointer shrink-0"
            @click.stop="openVariantModal(true)"
        >
          {{ $t('Buy Now') }}
        </button>
      </div>

      <span v-else class="text-xs font-bold text-rose-500">
        {{ $t('Stock Out') }}
      </span>
    </div>

    <!-- Variant Modal -->
    <Teleport to="body">
      <div
          v-if="showVariantModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
      >
        <div
            class="absolute inset-0 bg-black/40"
            @click="closeVariantModal"
        ></div>

        <div class="relative bg-white rounded-2xl w-full max-w-md p-5 shadow-xl">
          <div class="flex justify-between items-center gap-3">
            <div>
              <div class="text-xl font-semibold text-slate-950">
                {{ props.product?.name }}
              </div>

              <div class="text-sm text-slate-500 mt-1">
                {{ $t('Select product variant') }}
              </div>
            </div>

            <button
                class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center"
                @click="closeVariantModal"
            >
              <XMarkIcon class="w-5 h-5 text-slate-700" />
            </button>
          </div>

          <div v-if="variantLoading" class="py-10 text-center text-slate-500">
            {{ $t('Loading') }}...
          </div>

          <div v-else class="mt-5">
            <!-- Color -->
            <div v-if="hasColors">
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Color') }}
              </label>

              <select
                  v-model="selectedColor"
                  class="w-full p-3 rounded-lg border border-slate-300 outline-none focus:border-primary"
                  @change="onColorChange"
              >
                <option
                    v-for="color in colorVariants"
                    :key="color.id"
                    :value="color.id"
                >
                  {{ color.name }}
                </option>
              </select>
            </div>

            <!-- Size -->
            <div v-if="hasSizes" class="mt-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Size') }}
              </label>

              <select
                  v-model="selectedSize"
                  class="w-full p-3 rounded-lg border border-slate-300 outline-none focus:border-primary"
                  @change="updateSelectedVariant"
              >
                <option value="">
                  {{ $t('Select Size') }}
                </option>

                <option
                    v-for="size in availableSizes"
                    :key="size.id"
                    :value="size.id"
                >
                  {{ size.name }}
                </option>
              </select>
            </div>

            <!-- Selected variant information -->
            <div class="mt-5 p-4 rounded-xl bg-slate-100">
              <div class="flex justify-between gap-3">
                                <span class="text-slate-600">
                                    {{ $t('Price') }}
                                </span>

                <span class="text-primary font-bold">
                                    {{ masterStore.showCurrency(selectedSellingPrice) }}
                                </span>
              </div>

              <div
                  v-if="selectedOriginalPrice > selectedSellingPrice"
                  class="flex justify-between gap-3 mt-2"
              >
                                <span class="text-slate-600">
                                    {{ $t('MRP') }}
                                </span>

                <span class="line-through text-slate-400">
                                    {{ masterStore.showCurrency(selectedOriginalPrice) }}
                                </span>
              </div>

              <div
                  v-if="selectedDiscount > 0"
                  class="flex justify-between gap-3 mt-2"
              >
                                <span class="text-slate-600">
                                    {{ $t('Discount') }}
                                </span>

                <span class="text-red-500 font-medium">
                                    {{ selectedDiscount }}%
                                </span>
              </div>
            </div>

            <div v-if="variantError" class="mt-3 text-sm text-red-500">
              {{ variantError }}
            </div>

            <button
                class="w-full mt-5 py-3 bg-primary text-white rounded-lg font-medium disabled:bg-slate-300"
                :disabled="!canSubmitVariant"
                @click="submitSelectedVariant"
            >
              {{ isBuyNowAction ? $t('Buy Now') : $t('Add to Cart') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </article>
</template>

<script setup>
import { HeartIcon as HeartIconOutline, XMarkIcon } from '@heroicons/vue/24/outline';
import { HeartIcon, StarIcon, MinusIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import BagIcon from '../icons/Bag.vue';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';

const router = useRouter();
const masterStore = useMaster();
const basketStore = useBasketStore();
const authStore = useAuth();
const toast = useToast();

const props = defineProps({
  product: Object,
});

/*
|--------------------------------------------------------------------------
| Cart status & quantity control
|--------------------------------------------------------------------------
*/
const cartProduct = computed(() => {
  if (!props.product?.id || !basketStore.products || !basketStore.products.length) return null;
  let found = null;
  for (const shop of basketStore.products) {
    if (shop.products && Array.isArray(shop.products)) {
      found = shop.products.find(p => p.id == props.product.id || p.product_id == props.product.id);
      if (found) break;
    }
  }
  return found;
});

const cartQuantity = computed(() => {
  if (!props.product?.id || !basketStore.products || !basketStore.products.length) return 0;
  let total = 0;
  for (const shop of basketStore.products) {
    if (shop.products && Array.isArray(shop.products)) {
      shop.products.forEach(p => {
        if (p.id == props.product.id || p.product_id == props.product.id) {
          total += parseInt(p.quantity || 1);
        }
      });
    }
  }
  return total;
});

const incrementCartQty = () => {
  if (cartProduct.value) {
    basketStore.incrementQuantity(cartProduct.value);
  }
};

const decrementCartQty = () => {
  if (cartProduct.value) {
    basketStore.decrementQuantity(cartProduct.value);
  }
};

const brandName = computed(() => {
  if (!props.product) return null;
  if (props.product.brand?.name) return props.product.brand.name;
  if (typeof props.product.brand === 'string' && props.product.brand.trim() !== '') return props.product.brand;
  if (props.product.brand_name) return props.product.brand_name;
  return null;
});

/*
|--------------------------------------------------------------------------
| Card price
|--------------------------------------------------------------------------
| Backend se first variant ki value aayegi.
| Fallback normal product price hai.
*/

const hasFirstVariant = computed(() => {
  return parseInt(props.product?.first_variant_inward_product_id || 0) > 0;
});

const cardSellingPrice = computed(() => {
  if (hasFirstVariant.value) {
    return parseFloat(props.product?.first_variant_price || 0);
  }

  const normalPrice = parseFloat(props.product?.price || 0);
  const normalDiscountPrice = parseFloat(
      props.product?.discount_price || 0
  );

  return normalDiscountPrice > 0
      ? normalDiscountPrice
      : normalPrice;
});

const cardOriginalPrice = computed(() => {
  if (hasFirstVariant.value) {
    return parseFloat(
        props.product?.first_variant_mrp ||
        props.product?.first_variant_price ||
        0
    );
  }

  return parseFloat(props.product?.price || 0);
});

const cardDiscount = computed(() => {
  if (hasFirstVariant.value) {
    return parseFloat(
        props.product?.first_variant_discount || 0
    );
  }

  return parseFloat(
      props.product?.discount_percentage || 0
  );
});

/*
|--------------------------------------------------------------------------
| Variant modal
|--------------------------------------------------------------------------
*/

const showVariantModal = ref(false);
const variantLoading = ref(false);
const isBuyNowAction = ref(false);

const colorVariants = ref([]);
const sizeOnlyVariants = ref([]);

const selectedColor = ref('');
const selectedSize = ref('');

const selectedSellingPrice = ref(0);
const selectedOriginalPrice = ref(0);
const selectedDiscount = ref(0);
const selectedQty = ref(0);

const selectedInwardInvoiceId = ref(null);
const selectedInwardProductId = ref(null);

const variantError = ref('');

const hasColors = computed(() => colorVariants.value.length > 0);

const hasSizes = computed(() => {
  if (sizeOnlyVariants.value.length > 0) {
    return true;
  }

  return colorVariants.value.some(color => {
    return Array.isArray(color.sizes) && color.sizes.length > 0;
  });
});

const availableSizes = computed(() => {
  if (!hasColors.value) {
    return sizeOnlyVariants.value;
  }

  const color = colorVariants.value.find(
      item => item.id == selectedColor.value
  );

  return color?.sizes || [];
});

const canSubmitVariant = computed(() => {
  if (hasColors.value && !selectedColor.value) {
    return false;
  }

  if (hasSizes.value && !selectedSize.value) {
    return false;
  }

  return selectedQty.value > 0;
});

const openVariantModal = async (buyNow = false) => {
  isBuyNowAction.value = buyNow;

  if (buyNow && authStore.token === null) {
    authStore.loginModal = true;
    return;
  }

  variantLoading.value = true;
  variantError.value = '';

  try {
    const response = await axios.get('/product-details', {
      params: {
        product_id: props.product.id,
      },
      headers: {
        Authorization: authStore.token,
      },
    });

    const data = response.data.data;

    colorVariants.value = (data.color_variants || []).filter(color => {
      return color.name !== 'N/A' && color.name !== 'NA';
    });

    sizeOnlyVariants.value = (data.size_only_variants || []).filter(size => {
      return size.name !== 'N/A' && size.name !== 'NA';
    });

    const productDetails = data.product || {};
    const inwardData = data.inward_data || null;

    /*
     |--------------------------------------------------------------------------
     | No variants: direct cart/buy now
     |--------------------------------------------------------------------------
     */

    if (
        colorVariants.value.length === 0 &&
        sizeOnlyVariants.value.length === 0
    ) {
      const price = parseFloat(
          inwardData?.price ||
          productDetails.price ||
          props.product.price ||
          0
      );

      const sellingPrice = parseFloat(
          inwardData?.discount_price > 0
              ? inwardData.discount_price
              : inwardData?.mrp ||
              productDetails.discount_price ||
              productDetails.price ||
              price
      );

      const directData = {
        product_id: props.product.id,
        is_buy_now: buyNow,
        quantity: 1,
        size: null,
        color: null,
        unit: null,

        // carts.price = original MRP
        price: price,

        // carts.mrp = final selling price
        mrp: sellingPrice,

        discount: parseFloat(
            inwardData?.discount_percent ||
            inwardData?.discount_percentage ||
            props.product.discount_percentage ||
            props.product.online_discount_percent ||
            (price > 0 && sellingPrice < price ? Math.round(((price - sellingPrice) / price) * 100 * 100) / 100 : 0)
        ),
        inward_invoice_id:
            inwardData?.inward_invoice_id ||
            data.inward_invoice_id ||
            null,
        inward_product_id:
            inwardData?.inward_product_id ||
            data.inward_product_id ||
            null,
      };

      await processCart(directData, buyNow);
      return;
    }

    showVariantModal.value = true;

    /*
     |--------------------------------------------------------------------------
     | Auto select first variant
     |--------------------------------------------------------------------------
     */

    if (colorVariants.value.length > 0) {
      selectedColor.value = colorVariants.value[0].id;
      onColorChange();
    } else if (sizeOnlyVariants.value.length > 0) {
      selectedSize.value = sizeOnlyVariants.value[0].id;
      updateSelectedVariant();
    }
  } catch (error) {
    toast.error(
        error.response?.data?.message || 'Unable to load product variants'
    );
  } finally {
    variantLoading.value = false;
  }
};

const onColorChange = () => {
  selectedSize.value = '';

  const color = colorVariants.value.find(
      item => item.id == selectedColor.value
  );

  if (!color) {
    resetSelectedVariant();
    return;
  }

  if (Array.isArray(color.sizes) && color.sizes.length > 0) {
    selectedSize.value = color.sizes[0].id;
  }

  updateSelectedVariant();
};

const updateSelectedVariant = () => {
  variantError.value = '';

  /*
   |--------------------------------------------------------------------------
   | Color + size
   |--------------------------------------------------------------------------
   */

  if (hasColors.value) {
    const color = colorVariants.value.find(
        item => item.id == selectedColor.value
    );

    if (!color) {
      resetSelectedVariant();
      return;
    }

    if (hasSizes.value && selectedSize.value) {
      const size = color.sizes?.find(
          item => item.id == selectedSize.value
      );

      if (size) {
        applyVariantData(size);
        return;
      }
    }

    applyVariantData(color);
    return;
  }

  /*
   |--------------------------------------------------------------------------
   | Size only
   |--------------------------------------------------------------------------
   */

  const size = sizeOnlyVariants.value.find(
      item => item.id == selectedSize.value
  );

  if (size) {
    applyVariantData(size);
  }
};

const applyVariantData = variant => {
  const physicalMrp = parseFloat(variant.mrp || 0);
  const discountPercent = parseFloat(variant.discount_percent || 0);

  let sellingPrice = parseFloat(variant.price || 0);
  if (!sellingPrice || sellingPrice <= 0) {
    if (discountPercent > 0 && discountPercent < 100) {
      sellingPrice = physicalMrp - (physicalMrp * discountPercent / 100);
    } else {
      sellingPrice = physicalMrp;
    }
  }

  selectedSellingPrice.value = Number(sellingPrice.toFixed(2));
  selectedOriginalPrice.value = Number(physicalMrp.toFixed(2));
  selectedDiscount.value = discountPercent;
  selectedQty.value = parseInt(variant.qty || 0);

  selectedInwardInvoiceId.value =
      variant.inward_invoice_id || null;

  selectedInwardProductId.value =
      variant.inward_product_id || null;
};

const resetSelectedVariant = () => {
  selectedSellingPrice.value = 0;
  selectedOriginalPrice.value = 0;
  selectedDiscount.value = 0;
  selectedQty.value = 0;
  selectedInwardInvoiceId.value = null;
  selectedInwardProductId.value = null;
};

const submitSelectedVariant = async () => {
  if (!canSubmitVariant.value) {
    variantError.value = 'Please select available variant';
    return;
  }

  const cartData = {
    product_id: props.product.id,
    is_buy_now: isBuyNowAction.value,
    quantity: 1,

    size: hasSizes.value ? selectedSize.value : null,
    color: hasColors.value ? selectedColor.value : null,
    unit: null,

    // Original MRP
    price: selectedOriginalPrice.value,

    // Final selling price
    mrp: selectedSellingPrice.value,

    discount: selectedDiscount.value,

    inward_invoice_id: selectedInwardInvoiceId.value,
    inward_product_id: selectedInwardProductId.value,
  };

  await processCart(cartData, isBuyNowAction.value);
};

const processCart = async (cartData, buyNow) => {
  try {
    if (buyNow) {
      // Frontend old buy now state clear
      basketStore.buyNowProduct = null;
      basketStore.buyNowShopId = null;

      // Backend old buy now cart clear
      await axios.post(
          '/cart/buy-now-clear',
          {},
          {
            headers: {
              Authorization: authStore.token,
            },
          }
      );
    } else if (cartProduct.value) {
      // If product is already in cart, increment quantity of existing cart item
      await basketStore.incrementQuantity(cartProduct.value);
      showVariantModal.value = false;
      toast.success('Product quantity updated in cart', {
        position:
            masterStore.langDirection === 'rtl'
                ? 'bottom-right'
                : 'bottom-left',
      });
      return;
    }

    // New product add
    await basketStore.addToCart(cartData, props.product);

    showVariantModal.value = false;

    if (buyNow) {
      basketStore.buyNowShopId = props.product?.shop.id;
      router.push({ name: 'buynow' });
    } else {
      toast.success('Product added to cart', {
        position:
            masterStore.langDirection === 'rtl'
                ? 'bottom-right'
                : 'bottom-left',
      });
    }
  } catch (error) {
    toast.error(
        error.response?.data?.message || 'Something went wrong',
        {
          position:
              masterStore.langDirection === 'rtl'
                  ? 'bottom-right'
                  : 'bottom-left',
        }
    );
  }
};

const closeVariantModal = () => {
  showVariantModal.value = false;
  variantError.value = '';
};

const isFavorite = ref(props.product?.is_favorite);

const favoriteAddOrRemove = () => {
  if (authStore.token === null) {
    authStore.loginModal = true;
    return;
  }

  axios.post(
      '/favorite-add-or-remove',
      {
        product_id: props.product.id,
      },
      {
        headers: {
          Authorization: authStore.token,
        },
      }
  ).then(response => {
    props.product.is_favorite = !props.product.is_favorite;
    isFavorite.value = response.data.data.product.is_favorite;

    if (isFavorite.value === false) {
      toast.warning('Product removed from favorite', {
        position:
            masterStore.langDirection === 'rtl'
                ? 'bottom-right'
                : 'bottom-left',
      });
    } else {
      toast.success('Product added to favorite', {
        position:
            masterStore.langDirection === 'rtl'
                ? 'bottom-right'
                : 'bottom-left',
      });
    }

    authStore.favoriteRemove = true;
    authStore.fetchFavoriteProducts();
  });
};

const showProductDetails = () => {
  if (props.product.quantity > 0) {
    router.push({
      name: 'productDetails',
      params: {
        id: props.product.id,
      },
    });
  }
};
</script>