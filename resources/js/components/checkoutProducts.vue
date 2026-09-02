<template>
  <div class="space-y-4 mt-3 transition duration-300">
    <div
        v-for="shopProduct in basketStore.checkoutProducts"
        :key="shopProduct.shop_id"
        class="px-4 py-3 bg-slate-50 rounded-xl border border-slate-100"
    >
      <!-- Shop Name -->
      <div class="text-slate-950 text-base font-medium leading-normal">
        {{ shopProduct.shop_name }}
      </div>

      <div class="space-y-2 divide-y divide-slate-200">
        <!-- item -->
        <div
            v-for="product in shopProduct.products"
            :key="product.id"
            class="flex gap-4 justify-start w-full items-start pt-1"
        >
          <div class="w-[72px] h-[95px] shrink-0">
            <img
                :src="product.thumbnail"
                class="w-full h-full object-contain"
                loading="lazy"
                alt="product"
            />
          </div>
          <div class="flex flex-col gap-1 w-full min-w-0">
            <!-- Brand -->
            <div class="text-primary text-xs font-normal leading-none">
              {{ product.brand }}
            </div>
            <!-- Product Name -->
            <div class="text-slate-950 text-base font-normal leading-normal truncate">
              {{ product.name }}
            </div>

            <!-- Size, Color, HSN, Tax -->
            <div class="flex flex-wrap items-center gap-1">
              <!-- Size -->
              <div
                  v-if="product.size_name && product.size_name !== 'N/A'"
                  class="min-w-8 text-center px-2 py-1 bg-slate-100 rounded text-slate-800 text-xs font-normal"
              >
                {{ product.size_name }}
              </div>
              <!-- Color -->
              <div
                  v-if="product.color_name && product.color_name !== 'N/A'"
                  class="px-2 py-1 bg-slate-100 rounded text-slate-800 text-xs font-normal"
              >
                {{ product.color_name }}
              </div>
              <!-- HSN Code -->
<!--              <div-->
<!--                  v-if="product.hsn_code"-->
<!--                  class="px-2 py-1 bg-slate-200 rounded text-slate-600 text-xs font-normal"-->
<!--              >-->
<!--                HSN: {{ product.hsn_code }}-->
<!--              </div>-->
              <!-- Tax Percentage -->
<!--              <div-->
<!--                  v-if="product.tax_percentage > 0"-->
<!--                  class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium"-->
<!--              >-->
<!--                {{ product.vat_tax_name || 'GST' }} {{ product.tax_percentage }}%-->
<!--              </div>-->
            </div>

            <!-- Price and Quantity -->
            <div class="flex flex-wrap justify-between items-center gap-3 mt-1">
              <div class="text-slate-800 text-base font-normal leading-normal">
                {{ product.quantity }} ×
                {{
                  master.showCurrency(
                      product.discount_price > 0 && product.discount_price < product.price
                          ? product.discount_price
                          : product.price
                  )
                }}
              </div>
            </div>

            <!-- Show MRP Strikethrough if discount exists -->
            <div v-if="product.discount_price > 0 && product.discount_price < product.price" class="text-xs text-slate-400">
              MRP: <span class="line-through">{{ master.showCurrency(product.price) }}</span>
              <span class="text-green-600 ml-1">(Saved: {{ master.showCurrency(product.price - product.discount_price) }})</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useAuth} from "../stores/AuthStore";
import {useBasketStore} from "../stores/BasketStore";
import {useMaster} from "../stores/MasterStore";

const AuthStore = useAuth();
const master = useMaster();
const basketStore = useBasketStore();
</script>

<style lang="scss" scoped></style>