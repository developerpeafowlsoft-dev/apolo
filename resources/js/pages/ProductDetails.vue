<template>
  <div class="main-container pb-16 lg:pb-24">
    <div v-show="!isLoading" class="grid grid-cols-1 xl:grid-cols-4">
      <div class="xl:col-span-3 col-span-1 lg:pr-6">
        
        <!-- Modern Breadcrumb -->
        <nav aria-label="breadcrumb" class="pt-4 pb-1">
          <ol class="flex items-center gap-2 text-sm text-slate-500 overflow-hidden">
            <li>
              <router-link to="/" class="hover:text-primary transition-colors flex items-center gap-1 font-medium">
                <HomeIcon class="w-4 h-4 text-slate-400" />
                <span>{{ $t("Home") }}</span>
              </router-link>
            </li>
            <li class="text-slate-300">/</li>
            <li v-if="product.category">
              <span class="hover:text-primary transition-colors font-medium">{{ product.category }}</span>
            </li>
            <li v-if="product.category" class="text-slate-300">/</li>
            <li class="text-slate-900 font-semibold truncate max-w-[280px] sm:max-w-md" aria-current="page">
              {{ product.name }}
            </li>
          </ol>
        </nav>

        <!-- Product Hero Showcase: Media Gallery + Buy Box -->
        <div class="flex flex-wrap lg:flex-nowrap gap-6 xl:gap-8 mt-4">
          
          <!-- Left: Media Gallery & Swiper Showcase -->
          <div class="lg:w-[460px] xl:w-[480px] w-full shrink-0">
            <div class="w-full">
              <!-- Main Showcase Frame -->
              <div class="bg-gradient-to-b from-slate-50/70 to-white rounded-3xl border border-slate-200/80 p-4 sm:p-6 relative shadow-xs overflow-hidden">
                
                <!-- Floating Discount Badge on Image -->
                <div v-if="displayDiscount > 0 && displayDiscount < 100" class="absolute top-4 left-4 z-10">
                  <span class="bg-gradient-to-r from-rose-500 to-red-600 text-white font-extrabold text-xs px-3 py-1.5 rounded-full shadow-md tracking-wider uppercase">
                    {{ displayDiscount }}% {{ $t("OFF") }}
                  </span>
                </div>

                <!-- Stock Status Indicator -->
                <div class="absolute top-4 right-4 z-10">
                  <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px] px-2.5 py-1 rounded-full shadow-2xs">
                    {{ $t("In Stock") }}
                  </span>
                </div>

                <swiper :spaceBetween="10" :thumbs="{ swiper: thumbsSwiper }" :modules="modules"
                        class="product-details-slider">
                  <swiper-slide v-for="thumbnail in product.thumbnails" :key="thumbnail.id"
                                class="max-h-[448px] h-auto">
                    <div v-if="thumbnail.thumbnail" class="zoom-container h-full flex items-center justify-center"
                         @mousemove="handleMouseMove" @mouseleave="resetZoom"
                         @touchstart="handleMouseMove" @touchmove="handleMouseMove"
                         @touchend="resetZoom">
                      <img :src="thumbnail.thumbnail" alt="thumbnail"
                           class="zoom-image h-full w-full object-contain max-h-[400px]" />
                    </div>
                    <div v-else class="h-full w-full bg-slate-200 rounded-2xl flex justify-center items-center">
                      <video v-if="thumbnail.type == 'file'" controls class="w-full rounded-2xl">
                        <source :src="thumbnail.url" type="video/mp4">
                      </video>
                      <div v-else v-html="thumbnail.url" class="w-full overflow-hidden"
                           ref="iframeContainer"></div>
                    </div>
                  </swiper-slide>
                </swiper>
              </div>

              <!-- Thumbnails Slider Strip -->
              <div class="px-1 mt-3">
                <swiper @swiper="setThumbsSwiper" :spaceBetween="10" :slidesPerView="4" :freeMode="true"
                        :navigation="true" :watchSlidesProgress="true" :modules="modules"
                        class="product-details-thumbnail">
                  <swiper-slide v-for="thumbnail in product.thumbnails" :key="thumbnail.id"
                                class="rounded-xl overflow-hidden border border-slate-200 cursor-pointer hover:border-primary transition-all duration-200 shadow-2xs">
                    <img v-if="thumbnail.thumbnail" :src="thumbnail.thumbnail" alt=""
                         class="h-full w-full object-cover" />

                    <div v-else class="h-full w-full bg-slate-200 flex justify-center items-center">
                      <video v-if="thumbnail.type == 'file'" class="h-full w-full">
                        <source :src="thumbnail.url" type="video/mp4">
                      </video>
                      <div v-else
                           class="h-full w-full overflow-hidden flex justify-center items-center">
                        <img :src="'/assets/icons/video-player.svg'" alt="" width="50"
                             height="50">
                      </div>
                    </div>
                  </swiper-slide>
                </swiper>
              </div>

              <!-- Store Guarantee & Trust Perks -->
              <div class="grid grid-cols-3 gap-2 mt-4 p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200/70 text-center">
                <div class="flex flex-col items-center gap-1">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                  <span class="text-[11px] font-bold text-slate-700 leading-tight">{{ $t("Fast Delivery") }}</span>
                </div>
                <div class="flex flex-col items-center gap-1 border-x border-slate-200 px-1">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  <span class="text-[11px] font-bold text-slate-700 leading-tight">{{ $t("100% Genuine") }}</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  <span class="text-[11px] font-bold text-slate-700 leading-tight">{{ $t("Easy Returns") }}</span>
                </div>
              </div>

            </div>
          </div>

          <!-- Right: Core Product Details & Interactive Buy Box -->
          <div class="w-full lg:flex-1">
            
            <!-- Flash Sale Countdown Banner -->
            <div v-if="flashSale"
                 class="bg-slate-100 mb-3 sm:mb-4 rounded-xl flex items-center justify-start gap-2 sm:gap-4 overflow-hidden flex-col sm:flex-row shadow-2xs">
              <div class="px-4 sm:px-6 py-2 bg-gradient-to-l from-primary to-primary-800 w-full sm:w-auto">
                <div class="text-white text-sm font-bold leading-normal">
                  {{ $t("Flash Sale") }}
                </div>
              </div>

              <div class="h-full flex justify-center items-center flex-wrap pb-2 sm:pb-0">
                <div class="text-center text-primary text-xs font-semibold leading-tight pr-2">
                  {{ $t("Ending in") }}
                </div>

                <div class="flex justify-center items-center gap-1 text-white">
                  <div v-if="endDay > 0" class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-sm font-bold font-mono">{{ endDay }}</div>
                    <div class="text-center text-[#687387] text-[9.14px] font-normal leading-none">{{ $t("Days") }}</div>
                  </div>
                  <span v-if="endDay > 0" class="text-black text-sm font-bold">:</span>
                  <div class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-sm font-bold font-mono">{{ endHour }}</div>
                    <div class="text-center text-[#687387] text-[9.14px] font-normal leading-none">{{ $t("Hours") }}</div>
                  </div>
                  <span class="text-black text-sm font-bold">:</span>
                  <div class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-sm font-bold font-mono">{{ endMinute }}</div>
                    <div class="text-center text-[#687387] text-[9.14px] font-normal leading-none">{{ $t("Minutes") }}</div>
                  </div>
                  <span v-if="endDay <= 0" class="text-black text-sm font-bold">:</span>
                  <div v-if="endDay <= 0" class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-sm font-bold font-mono">{{ endSecond }}</div>
                    <div class="text-center text-[#687387] text-[9.14px] font-normal leading-none">{{ $t("Seconds") }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Brand Badge & SKU Row -->
            <div class="flex items-center gap-2 flex-wrap mb-2">
              <span v-if="product.brand" class="inline-flex items-center gap-1.5 text-primary font-bold text-xs uppercase tracking-wider px-3 py-1 bg-primary/10 rounded-full">
                <svg class="w-3.5 h-3.5 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>
                {{ product.brand }}
              </span>
              <span v-if="product.code" class="text-xs font-mono text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                SKU: {{ product.code }}
              </span>
            </div>

            <!-- Product Title -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-950 tracking-tight leading-snug">
              {{ product.name }}
            </h1>

            <!-- Short Description -->
            <p v-if="product.short_description" class="mt-2 text-slate-600 text-sm sm:text-base leading-relaxed">
              {{ product.short_description }}
            </p>

            <!-- Rating, Review, Sold, Share & Favorite Bar -->
            <div class="py-3.5 flex flex-wrap justify-start items-center gap-4 border-b border-slate-200/80 my-3">
              <div class="flex items-center gap-2">
                <div class="flex">
                  <StarIcon v-for="i in 5" :key="i" class="w-4 h-4"
                            :class="i <= (product.rating || 5) ? 'text-amber-400' : 'text-slate-200'" />
                </div>
                <div class="text-slate-900 text-sm font-bold">
                  {{ (product.rating || 5).toFixed(1) }}
                </div>
                <div class="text-slate-500 text-sm font-normal">
                  ({{ product.total_reviews || 0 }} {{ $t("Review") }})
                </div>
              </div>

              <div class="w-px h-4 bg-slate-200"></div>

              <div class="text-slate-700 text-sm font-medium">
                <span class="font-bold text-slate-900">{{ product.total_sold || 0 }}</span> {{ $t("Sold") }}
              </div>

              <div class="w-px h-4 bg-slate-200"></div>

              <!-- Share & Favorite Icons -->
              <div class="flex items-center gap-3">
                <Menu as="div" class="relative inline-block text-left">
                  <div>
                    <MenuButton class="flex items-center gap-1.5 text-slate-600 hover:text-slate-900 text-sm font-medium border-none bg-transparent cursor-pointer transition">
                      <ShareIcon class="w-4 h-4 text-slate-600" />
                      <span>{{ $t("Share") }}</span>
                    </MenuButton>
                  </div>

                  <transition enter-active-class="transition ease-out duration-100"
                              enter-from-class="transform opacity-0 scale-95"
                              enter-to-class="transform opacity-100 scale-100"
                              leave-active-class="transition ease-in duration-75"
                              leave-from-class="transform opacity-100 scale-100"
                              leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                        class="absolute right-0 z-20 mt-2 w-56 origin-top rounded-xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-hidden p-1">
                      <div class="py-1 divide-y divide-gray-100">
                        <MenuItem v-slot="{ active }" v-for="social in shareOptions"
                                  :key="social.name" class="cursor-pointer" @click="share(social.name)">
                          <div
                              class="flex items-center gap-2 justify-between px-3.5 py-2 hover:bg-slate-100 rounded-lg transition-all duration-200">
                            <div class="flex items-center gap-2">
                              <div class="w-7 h-7 p-1.5 flex justify-center items-center text-white rounded-full"
                                   :class="`bg-[${social.color}]`">
                                <FontAwesomeIcon :icon="social.icon" class="w-full h-full" />
                              </div>
                              <span class="capitalize text-sm font-medium text-slate-700">{{ social.name }}</span>
                            </div>
                            <div
                                class="w-5 h-5 p-1 flex justify-center items-center bg-slate-200 rounded-full rotate-45">
                              <FontAwesomeIcon :icon="faArrowUp"
                                               class="w-full h-full text-slate-500" />
                            </div>
                          </div>
                        </MenuItem>
                      </div>
                    </MenuItems>
                  </transition>
                </Menu>

                <button class="border-none bg-transparent p-1 cursor-pointer hover:scale-110 transition" @click="favoriteAddOrRemove" :title="$t('Wishlist')">
                  <HeartIcon v-if="!product.is_favorite" class="w-5 h-5 text-slate-500 hover:text-red-500 transition" />
                  <HeartIconFill v-else class="w-5 h-5 text-red-500" />
                </button>
              </div>
            </div>

            <!-- Price Highlight Box -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-primary/5 via-slate-50 to-white border border-primary/10 my-4 shadow-2xs">
              <div class="flex items-baseline gap-3 flex-wrap">
                <div class="text-primary text-3xl sm:text-4xl font-black font-mono tracking-tight">
                  {{ masterStore.showCurrency(parseFloat(displaySellingPrice).toFixed(2)) }}
                </div>

                <div v-if="displayDiscount > 0 && displayDiscount < 100"
                     class="text-slate-400 text-xl font-mono line-through font-normal">
                  {{ masterStore.showCurrency(parseFloat(displayOriginalPrice).toFixed(2)) }}
                </div>

                <div v-if="displayDiscount > 0 && displayDiscount < 100"
                     class="px-3 py-1 bg-gradient-to-r from-rose-500 to-red-600 rounded-full text-white text-xs font-extrabold shadow-sm tracking-wider uppercase">
                  {{ displayDiscount }}% {{ $t("OFF") }}
                </div>
              </div>
              <p class="text-xs text-slate-500 mt-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $t("Inclusive of all taxes • In Stock with fast dispatch") }}</span>
              </p>
            </div>

            <!-- ============================================================ -->
            <!-- ✅ MODERN INTERACTIVE VARIANT SELECTOR (Chips & Pills) -->
            <!-- ============================================================ -->
            <div v-if="hasColors || hasSizes && (validColorVariants.length > 0 || sizeOnlyVariants.length > 0)" class="py-4 border-b border-slate-200/80">
              
              <!-- 1. Color Selector with Visual Swatches -->
              <div v-if="hasColors && validColorVariants.length > 0" class="mb-4">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="text-xs uppercase font-bold text-slate-500 tracking-wider">{{ $t("Color") }}:</span>
                    <span class="text-sm font-extrabold text-slate-900">{{ selectedColorObj?.name || $t("Select Color") }}</span>
                  </div>
                  <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-full font-medium">
                    {{ validColorVariants.length }} {{ validColorVariants.length === 1 ? $t("Color") : $t("Colors") }}
                  </span>
                </div>

                <div class="flex flex-wrap gap-2.5">
                  <button v-for="color in validColorVariants" :key="color.id"
                          type="button"
                          @click="selectedColor = color.id; onColorChange()"
                          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-sm font-semibold transition-all duration-200 cursor-pointer"
                          :class="selectedColor == color.id 
                                  ? 'border-primary bg-primary-50/60 ring-2 ring-primary/25 text-slate-900 shadow-xs' 
                                  : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-slate-700'">
                    <span class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-2xs shrink-0"
                          :style="{ backgroundColor: color.color_code || '#ccc' }"></span>
                    <span>{{ color.name }}</span>
                  </button>
                </div>
              </div>

              <!-- 2. Size Selector with Interactive Pills -->
              <div v-if="hasSizes && availableSizes.length > 0" class="mb-4">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="text-xs uppercase font-bold text-slate-500 tracking-wider">{{ $t("Size") }}:</span>
                    <span class="text-sm font-extrabold text-slate-900 font-mono">{{ selectedSizeObj?.name || $t("Select Size") }}</span>
                  </div>
                  <span class="text-xs text-slate-400">{{ $t("Click size to update pricing") }}</span>
                </div>

                <div class="flex flex-wrap gap-2">
                  <button v-for="size in availableSizes" :key="size.id"
                          type="button"
                          @click="handleSizeSelect(size)"
                          class="h-11 min-w-[50px] px-3.5 rounded-xl border text-sm font-bold font-mono transition-all duration-200 flex items-center justify-center cursor-pointer"
                          :class="selectedSize == size.id 
                                  ? 'bg-primary text-white border-primary shadow-md shadow-primary/25 scale-[1.03]' 
                                  : 'bg-white text-slate-700 border-slate-200 hover:border-primary hover:text-primary hover:bg-slate-50'">
                    {{ size.name }}
                  </button>
                </div>
              </div>

              <!-- Selected Variant Summary Card -->
              <div v-if="(hasColors && selectedColor) || (!hasColors && selectedSize)"
                   class="mt-4 p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                  <div v-if="hasColors">
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">{{ $t("Selected Color") }}</span>
                    <div class="flex items-center gap-1.5">
                      <span class="w-3 h-3 rounded-full border border-black/10 inline-block shadow-2xs"
                            :style="{ backgroundColor: selectedColorObj?.color_code || '#ccc' }"></span>
                      <span class="font-bold text-sm text-slate-800">{{ selectedColorObj?.name }}</span>
                    </div>
                  </div>
                  <div v-if="hasSizes">
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">{{ $t("Selected Size") }}</span>
                    <div class="font-bold text-sm text-slate-800 font-mono">{{ selectedSizeObj?.name || '-' }}</div>
                  </div>
                  <div>
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">{{ $t("Physical MRP") }}</span>
                    <div class="font-bold text-slate-600 text-sm font-mono">
                      ₹{{ formatNumber(selectedMrp) }}
                    </div>
                  </div>
                  <div>
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">{{ $t("Online Price") }}</span>
                    <div class="font-extrabold text-primary text-sm font-mono">
                      ₹{{ formatNumber(displaySellingPrice) }}
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Error Messages -->
            <div v-if="validationError" class="mt-2 text-red-500 text-sm font-medium">
              {{ validationError }}
            </div>

            <!-- Quantity Stepper & CTA Action Buttons -->
            <div class="flex items-center gap-3.5 mt-6 flex-wrap sm:flex-nowrap w-full max-w-xl">
              
              <!-- Quantity Stepper when in cart -->
              <div v-if="cartProduct"
                   class="h-12 px-4 rounded-xl border border-slate-200 bg-slate-50 inline-flex items-center gap-3 shrink-0">
                <button class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center transition shadow-2xs cursor-pointer" @click="decrementQty">
                  <MinusIcon class="w-4 h-4 text-slate-700" />
                </button>
                <div class="w-8 text-center text-slate-900 text-base font-bold font-mono">
                  {{ cartProduct.quantity }}
                </div>
                <button class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center transition shadow-2xs cursor-pointer" @click="incrementQty">
                  <PlusIcon class="w-4 h-4 text-slate-700" />
                </button>
              </div>

              <!-- Add to Cart Button -->
              <button v-if="!cartProduct"
                      class="flex-1 min-w-[160px] h-12 justify-center items-center flex gap-2.5 px-6 rounded-xl border-2 font-bold text-base transition-all duration-200 shadow-xs cursor-pointer active:scale-[0.99]"
                      :class="canAddToCart ? 'text-primary border-primary bg-primary/5 hover:bg-primary hover:text-white' : 'text-slate-400 border-slate-200 cursor-not-allowed bg-slate-50'"
                      :disabled="!canAddToCart"
                      @click="addToCart">
                <div class="w-5 h-5">
                  <BagIcon :class="canAddToCart ? 'text-current' : 'text-slate-400'" />
                </div>
                <span>{{ $t("Add to Cart") }}</span>
              </button>

              <!-- Buy Now High Impact Button -->
              <button
                  class="flex-1 min-w-[160px] h-12 px-6 rounded-xl font-bold text-base text-white transition-all duration-200 shadow-md shadow-primary/25 hover:shadow-lg hover:shadow-primary/35 hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer"
                  :class="canAddToCart ? 'bg-primary hover:bg-primary-700' : 'bg-slate-300 cursor-not-allowed shadow-none'"
                  :disabled="!canAddToCart"
                  @click="buyNow">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>{{ $t("Buy Now") }}</span>
              </button>
            </div>

          </div>
        </div>

        <!-- Mobile Right Side View -->
        <div class="block xl:hidden w-full pt-8 border-t border-slate-200/80 mt-8">
          <ProductDetailsRightSide :product="product" :popularProducts="popularProducts" />
        </div>

        <!-- ============================================================ -->
        <!-- LOWER SECTION: MODERN TABS (About, Specifications & Reviews) -->
        <!-- ============================================================ -->
        <div class="flex items-center gap-2 sm:gap-6 border-b border-slate-200 mt-10 mb-6 overflow-x-auto no-scrollbar">
          <!-- Tab 1: About Product -->
          <button class="pb-3.5 px-2 transition text-base font-bold relative flex items-center gap-2 cursor-pointer whitespace-nowrap"
                  :class="aboutProduct ? 'text-primary' : 'text-slate-500 hover:text-slate-800'"
                  @click="selectTab('about')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>{{ $t("About Product") }}</span>
            <span v-if="aboutProduct" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"></span>
          </button>

          <!-- Tab 2: Specifications & Details -->
          <button class="pb-3.5 px-2 transition text-base font-bold relative flex items-center gap-2 cursor-pointer whitespace-nowrap"
                  :class="specifications ? 'text-primary' : 'text-slate-500 hover:text-slate-800'"
                  @click="selectTab('specs')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>{{ $t("Specifications & Details") }}</span>
            <span v-if="specifications" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"></span>
          </button>

          <!-- Tab 3: Reviews -->
          <button class="pb-3.5 px-2 transition text-base font-bold relative flex items-center gap-2 cursor-pointer whitespace-nowrap"
                  :class="review ? 'text-primary' : 'text-slate-500 hover:text-slate-800'"
                  @click="selectTab('reviews')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            <span>{{ $t("Reviews") }}</span>
            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold">{{ product.total_reviews ?? 0 }}</span>
            <span v-if="review" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"></span>
          </button>
        </div>

        <!-- Tab 1: About Product -->
        <div v-if="aboutProduct" class="product-description-wrapper">
          <div class="prose prose-slate max-w-none w-full text-slate-700 leading-relaxed font-normal text-base" v-html="product.description"></div>
        </div>

        <!-- Tab 2: Specifications & Details -->
        <div v-if="specifications" class="space-y-6">
          
          <!-- General Specs Card -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base">{{ $t("General Specifications") }}</h3>
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-full">
                {{ $t("Verified Details") }}
              </span>
            </div>

            <div class="divide-y divide-slate-100">
              <div v-if="product.brand" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Brand") }}</div>
                <div class="sm:col-span-2 text-sm font-bold text-slate-900">{{ product.brand }}</div>
              </div>

              <div v-if="product.category" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Category") }}</div>
                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ product.category }}</div>
              </div>

              <div v-if="getUnitName(product.unit)" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Unit") }}</div>
                <div class="sm:col-span-2 text-sm font-semibold text-slate-900 uppercase font-mono">{{ getUnitName(product.unit) }}</div>
              </div>

              <div v-if="product.code" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("SKU / Item Code") }}</div>
                <div class="sm:col-span-2 text-sm font-bold text-slate-900 font-mono">{{ product.code }}</div>
              </div>

              <div v-if="product.min_order_quantity" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Min Order Quantity") }}</div>
                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ product.min_order_quantity }} {{ getUnitName(product.unit) || 'Unit(s)' }}</div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Availability") }}</div>
                <div class="sm:col-span-2">
                  <span v-if="product.quantity > 0" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-md">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $t("In Stock") }} ({{ product.quantity }} {{ getUnitName(product.unit) || 'available' }})
                  </span>
                  <span v-else class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-md">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    {{ $t("Out of Stock") }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Physical & Packaging Specs -->
          <div v-if="product.length || product.width || product.height || product.weight"
               class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
              </div>
              <h3 class="font-bold text-slate-900 text-base">{{ $t("Dimensions & Packaging") }}</h3>
            </div>

            <div class="divide-y divide-slate-100">
              <div v-if="product.length || product.width || product.height" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Package Dimensions") }}</div>
                <div class="sm:col-span-2 text-sm font-bold text-slate-900 font-mono">
                  {{ product.length || '-' }} (L) × {{ product.width || '-' }} (W) × {{ product.height || '-' }} (H) cm
                </div>
              </div>

              <div v-if="product.weight" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Package Weight") }}</div>
                <div class="sm:col-span-2 text-sm font-bold text-slate-900 font-mono">{{ product.weight }} kg</div>
              </div>
            </div>
          </div>

          <!-- Variant Attributes Summary -->
          <div v-if="product.colors?.length > 0 || product.sizes?.length > 0"
               class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
              </div>
              <h3 class="font-bold text-slate-900 text-base">{{ $t("Available Variants & Options") }}</h3>
            </div>

            <div class="divide-y divide-slate-100">
              <div v-if="product.colors?.length > 0" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 items-center hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Available Colors") }}</div>
                <div class="sm:col-span-2 flex flex-wrap gap-2 items-center">
                  <span v-for="c in product.colors" :key="c.id"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800">
                    <span class="w-3 h-3 rounded-full border border-black/15 shrink-0" :style="{ backgroundColor: c.color_code }"></span>
                    {{ c.name }}
                  </span>
                </div>
              </div>

              <div v-if="product.sizes?.length > 0" class="grid grid-cols-1 sm:grid-cols-3 px-5 py-3.5 items-center hover:bg-slate-50/50 transition">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t("Available Sizes") }}</div>
                <div class="sm:col-span-2 flex flex-wrap gap-2 items-center">
                  <span v-for="s in product.sizes" :key="s.id"
                        class="px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 font-mono">
                    {{ s.name }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Trust & Authenticity Highlights -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              </div>
              <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wide">{{ $t("100% Genuine") }}</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ $t("Directly sourced authentic quality products.") }}</p>
              </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3">
              <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wide">{{ $t("Fast Delivery") }}</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ product.shop?.estimated_delivery_time || '2-4 days' }} {{ $t("standard shipping.") }}</p>
              </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3">
              <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              </div>
              <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wide">{{ $t("Hassle-Free Returns") }}</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ $t("Quick support and easy replacement policy.") }}</p>
              </div>
            </div>
          </div>

        </div>

        <!-- Tab 2: Reviews -->
        <div v-if="review" class="">
          <div class="text-slate-950 text-lg lg:text-2xl font-medium leading-loose mb-4">
            {{ $t("Rating and Review") }}
          </div>

          <div class="max-w-2xl">
            <ReviewRatings :reviewRatings="averageRatings.percentages"
                           :averageRating="averageRatings?.rating" :totalReview="averageRatings.total_review" />
          </div>

          <div class="border-t border-slate-200 mt-6">
            <div class="mt-4 lg:mt-6 text-slate-950 text-lg lg:text-2xl font-medium leading-loose">
              {{ $t("Reviews") }}
            </div>
            <div class="space-y-6 mt-6">
              <Review v-for="review in reviews" :key="review.id" :review="review" />
            </div>

            <div class="flex justify-between items-center w-full mt-8 gap-4 flex-wrap">
              <div class="text-slate-800 text-base font-normal leading-normal">
                {{ $t("Showing") }} {{ perPage * (currentPage - 1) + 1 }}
                {{ $t("to") }}
                {{ perPage * (currentPage - 1) + reviews.length }}
                {{ $t("of") }} {{ totalReviews }} {{ $t("results") }}
              </div>
              <div>
                <vue-awesome-paginate :total-items="totalReviews" :items-per-page="perPage"
                                      type="button" :max-pages-shown="3" v-model="currentPage"
                                      :hide-prev-next-when-ends="true" @click="onClickHandler" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right side Column -->
      <div class="hidden xl:block col-span-1 w-full pt-6 h-full xl:pt-14 border-slate-200 xl:pb-6"
           :class="masterStore.langDirection == 'rtl' ? 'xl:pr-8 xl:border-r' : 'xl:pl-8 xl:border-l'">
        <ProductDetailsRightSide :product="product" :popularProducts="popularProducts" />
      </div>
    </div>

    <!-- Similar Products -->
    <div v-if="relatedProducts.length > 0 && !isLoading">
      <div class="mt-4 xl:mt-6 text-slate-800 text-lg md:text-2xl lg:text-3xl font-bold leading-9">
        {{ $t("Similar Products") }}
      </div>
      <div
          class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-6 items-start my-6">
        <div v-for="product in relatedProducts" :key="product.id">
          <ProductCard :product="product" />
        </div>
      </div>
    </div>

    <!-- page loader -->
    <div v-if="isLoading" class="grid grid-cols-1 xl:grid-cols-4">
      <div class="xl:col-span-3 col-span-1 lg:pr-6">
        <div class="flex items-center gap-2 overflow-hidden pt-6">
          <SkeletonLoader class="w-40 h-4" />
        </div>
        <div class="flex flex-wrap lg:flex-nowrap gap-4 mt-6">
          <div class="lg:w-[480px] w-full lg:shrink-0">
            <SkeletonLoader class="w-full h-52 md:h-80 rounded-lg" />
            <div class="flex flex-grow gap-3 mt-4">
              <SkeletonLoader v-for="i in 4" class="w-20 h-16 grow" />
            </div>
            <div class="flex flex-col gap-3 mt-6">
              <SkeletonLoader class="w-11/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-10/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-11/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-full h-3 rounded-xl" />
            </div>
            <div class="flex flex-col gap-4 mt-4">
              <SkeletonLoader v-for="i in 3" class="w-full h-20 rounded-lg" />
            </div>
          </div>
          <div class="w-full pb-4">
            <div class="flex flex-col gap-3">
              <SkeletonLoader class="w-11/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-10/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-11/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-full h-3 rounded-xl" />
            </div>
            <div class="flex flex-col gap-4 mt-4">
              <SkeletonLoader v-for="i in 3" class="w-full h-20 rounded-lg" />
            </div>
            <div class="flex flex-col gap-3 mt-4">
              <SkeletonLoader class="w-11/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-10/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-11/12 h-3 rounded-xl" />
              <SkeletonLoader class="w-full h-3 rounded-xl" />
            </div>
            <div class="flex flex-col gap-4 mt-4">
              <SkeletonLoader v-for="i in 2" class="w-full h-20 md:h-36 rounded-lg" />
            </div>
          </div>
        </div>
      </div>
      <div
          class="hidden xl:block col-span-1 w-full pt-6 h-full xl:pt-16 xl:pl-8 xl:border-l border-slate-200 xl:pb-6">
        <div class="flex flex-col gap-4 mt-4">
          <SkeletonLoader v-for="i in 5" class="w-full h-20 md:h-36 rounded-lg" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch, computed } from "vue";
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { useRoute, useRouter } from "vue-router";
import { useMaster } from "../stores/MasterStore";

import { HeartIcon, HomeIcon, MinusIcon, PlusIcon, ShareIcon } from "@heroicons/vue/24/outline";
import { HeartIcon as HeartIconFill, StarIcon } from "@heroicons/vue/24/solid";
import { FreeMode, Navigation, Thumbs } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";

import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import { useBasketStore } from "../stores/BasketStore";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faFacebookF, faLinkedin, faTwitter, faPinterest, faRedditAlien, faWhatsapp, faTelegram } from '@fortawesome/free-brands-svg-icons';
import { faEnvelope, faArrowUp } from "@fortawesome/free-solid-svg-icons";
import { useShareLink } from "vue3-social-sharing";
const { shareLink } = useShareLink();

import ProductDetailsRightSide from "../components/ProductDetailsRightSide.vue";
import ToastSuccessMessage from "../components/ToastSuccessMessage.vue";
import BagIcon from "../icons/Bag.vue";
import SkeletonLoader from "../components/SkeletonLoader.vue";
import ReviewRatings from "../components/ReviewRatings.vue";
import ProductCard from "../components/ProductCard.vue";
import Review from "../components/Review.vue";

import "swiper/css";
import "swiper/css/free-mode";
import "swiper/css/navigation";
import "swiper/css/thumbs";

const toast = useToast();
const route = useRoute();
const router = useRouter();
const masterStore = useMaster();
const basketStore = useBasketStore();
const authStore = useAuth();

const thumbsSwiper = ref(null);
const modules = [FreeMode, Navigation, Thumbs];

const setThumbsSwiper = (swiper) => {
  thumbsSwiper.value = swiper;
};

const formData = ref({
  product_id: route.params.id,
  size: null,
  color: null,
  unit: null,
});

const product = ref({});
const productPrice = ref(0);
const mainPrice = ref(0);
const discountPercentage = ref(0);

const relatedProducts = ref([]);
const popularProducts = ref([]);

const aboutProduct = ref(true);
const specifications = ref(false);
const review = ref(false);

const selectTab = (tab) => {
  aboutProduct.value = (tab === 'about');
  specifications.value = (tab === 'specs');
  review.value = (tab === 'reviews');
  if (tab === 'reviews') {
    fetchReviews();
  }
};

const getUnitName = (unit) => {
  if (!unit) return '';
  if (typeof unit === 'object') {
    return unit.name || unit.unit || '';
  }
  return unit;
};

const cartProduct = ref(null);
const isLoading = ref(true);

// ============================================================
// ✅ Variant Selection
// ============================================================
const colorVariants = ref([]);
const sizeOnlyVariants = ref([]);
const selectedColor = ref('');
const selectedSize = ref('');
const selectedMrp = ref(0);
const selectedDiscount = ref(0);
const selectedQty = ref(0);
const validationError = ref('');

// ✅ Filter valid color variants (exclude N/A colors)
const validColorVariants = computed(() => {
  return colorVariants.value.filter(color => {
    if (color.name === 'N/A' || color.name === 'NA') {
      return false;
    }
    if (color.mrp <= 0) {
      return false;
    }
    return true;
  });
});

// ✅ Check if product has colors
const hasColors = computed(() => {
  return validColorVariants.value.length > 0;
});

// ✅ Check if product has sizes
const hasSizes = computed(() => {
  if (sizeOnlyVariants.value.length > 0) {
    return true;
  }
  return validColorVariants.value.some(color => {
    if (!color.sizes || !Array.isArray(color.sizes) || color.sizes.length === 0) {
      return false;
    }
    const validSizes = color.sizes.filter(s => s.name !== 'N/A' && s.name !== 'NA');
    return validSizes.length > 0;
  });
});

// ✅ Display color count
const displayColorCount = computed(() => {
  if (hasColors.value) {
    return validColorVariants.value.length;
  }
  return 0;
});

// Get selected color object
const selectedColorObj = computed(() => {
  return validColorVariants.value.find(c => c.id == selectedColor.value);
});

// Get available sizes for selected color
const availableSizes = computed(() => {
  // If only sizes exist
  if (sizeOnlyVariants.value.length > 0 && !hasColors.value) {
    return sizeOnlyVariants.value;
  }
  if (!selectedColor.value) return [];
  const color = validColorVariants.value.find(c => c.id == selectedColor.value);
  if (!color || !color.sizes || !Array.isArray(color.sizes) || color.sizes.length === 0) {
    return [];
  }
  return color.sizes.filter(s => s.name !== 'N/A' && s.name !== 'NA');
});

// Get selected size object
const selectedSizeObj = computed(() => {
  if (!selectedSize.value) return null;
  // Check in sizeOnlyVariants first
  const sizeOnly = sizeOnlyVariants.value.find(s => s.id == selectedSize.value);
  if (sizeOnly) return sizeOnly;
  // Check in color variants
  if (!selectedColorObj.value) return null;
  return selectedColorObj.value.sizes?.find(s => s.id == selectedSize.value) || null;
});

// ============================================================
// ✅ Check if can add to cart
// ============================================================
const canAddToCart = computed(() => {
  // If only sizes exist
  if (sizeOnlyVariants.value.length > 0 && !hasColors.value) {
    if (!selectedSize.value) {
      validationError.value = 'Please select size';
      return false;
    }
    if (selectedQty.value <= 0) {
      validationError.value = 'Product is out of stock';
      return false;
    }
    validationError.value = '';
    return true;
  }

  // If colors exist
  if (hasColors.value) {
    if (!selectedColor.value) {
      validationError.value = 'Please select color';
      return false;
    }
    if (hasSizes.value && !selectedSize.value) {
      validationError.value = 'Please select size';
      return false;
    }
    if (selectedQty.value <= 0) {
      validationError.value = 'Product is out of stock';
      return false;
    }
    validationError.value = '';
    return true;
  }

  // No variants - check product quantity
  if (product.value.quantity <= 0) {
    validationError.value = 'Product is out of stock';
    return false;
  }
  validationError.value = '';
  return true;
});

// ============================================================
// ✅ Display Price Computed Properties
// ============================================================

// Original Price (MRP) - Full price before discount
const displayOriginalPrice = computed(() => {
  // If variant selected with MRP
  if (selectedMrp.value > 0) {
    return selectedMrp.value;
  }
  // If product has MRP
  if (parseFloat(product.value.mrp || 0) > 0) {
    return parseFloat(product.value.mrp);
  }
  return parseFloat(product.value.price || 0);
});

// Selling Price - After discount
const displaySellingPrice = computed(() => {
  // If variant selected
  if (hasColors.value || hasSizes.value) {
    if (selectedMrp.value > 0) {
      const discount = displayDiscount.value;
      if (discount > 0 && discount < 100) {
        // Apply discount on MRP
        return Number((selectedMrp.value - (selectedMrp.value * discount / 100)).toFixed(2));
      }
      return selectedMrp.value;
    }
  }

  // Simple product (no variants) - Same logic as ProductCard on /products:
  const normalPrice = parseFloat(product.value.price || 0);
  const normalDiscountPrice = parseFloat(product.value.discount_price || 0);

  if (normalDiscountPrice > 0 && normalDiscountPrice < normalPrice) {
    return normalDiscountPrice;
  }

  if (displayDiscount.value > 0 && displayDiscount.value < 100) {
    const orig = displayOriginalPrice.value;
    if (orig > 0) {
      return Number((orig - (orig * displayDiscount.value / 100)).toFixed(2));
    }
  }

  return normalDiscountPrice > 0 ? normalDiscountPrice : normalPrice;
});

// ✅ Display discount percentage
const displayDiscount = computed(() => {
  // If variant selected with discount
  if ((selectedColor.value || selectedSize.value) && selectedDiscount.value > 0 && selectedDiscount.value < 100) {
    return selectedDiscount.value;
  }

  // If no variant selected or simple product, check product discount percentage
  const discountPercent = parseFloat(
    product.value.discount_percentage ||
    product.value.online_discount_percent ||
    0
  );

  if (discountPercent > 0 && discountPercent < 100) {
    return discountPercent;
  }

  // If discount_percentage is not explicitly provided, but discount_price is lower than price/mrp:
  const original = parseFloat(displayOriginalPrice.value || 0);
  const discounted = parseFloat(product.value.discount_price || 0);
  if (original > 0 && discounted > 0 && discounted < original) {
    return Number((((original - discounted) / original) * 100).toFixed(2));
  }

  return 0;
});


// ============================================================
// ✅ Helper Functions
// ============================================================

const getSizeNames = (sizes) => {
  if (!sizes || !Array.isArray(sizes) || sizes.length === 0) {
    return '';
  }
  const names = sizes.map(s => s.name).filter(name => name !== 'N/A' && name !== 'NA');
  return names.length > 0 ? names.join(', ') : '';
};

const onColorChange = () => {
  selectedSize.value = '';
  validationError.value = '';

  if (selectedColor.value) {
    const color = validColorVariants.value.find(c => c.id == selectedColor.value);

    if (hasSizes.value && color?.sizes?.length > 0) {
      const validSizes = color.sizes.filter(s => s.name !== 'N/A' && s.name !== 'NA');

      if (validSizes.length > 0) {
        selectedSize.value = validSizes[0].id;
      }
    }

    updateSelectedVariantPrice();
  } else {
    selectedMrp.value = 0;
    selectedDiscount.value = 0;
    selectedQty.value = 0;
  }
};

const updateSelectedVariantPrice = () => {
  validationError.value = '';

  if (hasColors.value && selectedColor.value) {
    const color = validColorVariants.value.find(c => c.id == selectedColor.value);

    if (!color) {
      selectedMrp.value = 0;
      selectedDiscount.value = 0;
      selectedQty.value = 0;
      return;
    }

    if (hasSizes.value && selectedSize.value) {
      const size = color.sizes?.find(s => s.id == selectedSize.value);

      if (size) {
        selectedMrp.value = parseFloat(size.mrp || 0);
        selectedDiscount.value = parseFloat(size.discount_percent || 0);
        selectedQty.value = parseInt(size.qty || 0);
        return;
      }
    }

    selectedMrp.value = parseFloat(color.mrp || 0);
    selectedDiscount.value = parseFloat(color.discount_percent || 0);
    selectedQty.value = parseInt(color.qty || 0);
    return;
  }

  if (!hasColors.value && selectedSize.value) {
    const size = sizeOnlyVariants.value.find(s => s.id == selectedSize.value);

    if (size) {
      selectedMrp.value = parseFloat(size.mrp || 0);
      selectedDiscount.value = parseFloat(size.discount_percent || 0);
      selectedQty.value = parseInt(size.qty || 0);
    }
  }
};

const onSizeOnlySelect = (size) => {
  selectedSize.value = size.id;
  selectedMrp.value = size.mrp || 0;
  selectedDiscount.value = size.discount_percent || 0;
  selectedQty.value = size.qty || 0;
  validationError.value = '';
};

const handleSizeSelect = (size) => {
  if (!hasColors.value && sizeOnlyVariants.value.length > 0) {
    onSizeOnlySelect(size);
  } else {
    selectedSize.value = size.id;
    updateSelectedVariantPrice();
  }
};

const formatNumber = (value) => {
  return Number(value).toFixed(2);
};

// ============================================================
// ✅ Computed: Total Qty & MRP
// ============================================================
const totalQty = computed(() => {
  if (sizeOnlyVariants.value.length > 0) {
    return sizeOnlyVariants.value.reduce((sum, item) => sum + (item.qty || 0), 0);
  }
  return validColorVariants.value.reduce((sum, item) => sum + (item.qty || 0), 0);
});

const totalMrp = computed(() => {
  if (sizeOnlyVariants.value.length > 0) {
    return sizeOnlyVariants.value.reduce((sum, item) => sum + (item.mrp || 0), 0);
  }
  return validColorVariants.value.reduce((sum, item) => sum + (item.mrp || 0), 0);
});

onMounted(() => {
  fetchProductDetails();
  window.scrollTo(0, 0);
  findProductInCart(route.params.id);
});

watch(formData, () => {
  calculateProductPrice();
}, { deep: true });

// watch([selectedColor, selectedSize], () => {
//   if (selectedColor.value) {
//     const color = validColorVariants.value.find(c => c.id == selectedColor.value);
//     selectedQty.value = color?.qty || 0;
//   }
// }, { deep: true });
watch([selectedColor, selectedSize], () => {
  updateSelectedVariantPrice();
}, { deep: true });

const shareOptions = [
  { name: "facebook", icon: faFacebookF, color: "#0d68f1" },
  { name: "linkedin", icon: faLinkedin, color: "#1275b1" },
  { name: "twitter", icon: faTwitter, color: "#47acdf" },
  { name: "pinterest", icon: faPinterest, color: "#bb0f23" },
  { name: "reddit", icon: faRedditAlien, color: "#fc471e" },
  { name: "whatsapp", icon: faWhatsapp, color: "#25d366" },
  { name: "email", icon: faEnvelope, color: "#bb0f23" },
  { name: "telegram", icon: faTelegram, color: "#47acdf" },
];

const share = (network) => {
  let description = product.value.short_description.replace(/<[^>]*>/g, "");
  let currentURL = window.location.href;
  let thumbnail = product.value.thumbnails[0];

  shareLink({
    network: network,
    url: currentURL,
    title: product.value.name,
    description: description,
    media: thumbnail ? thumbnail.url : null,
    quote: product.value.name,
    hashtags: product.value.meta_keywords,
    twitterUser: product.value.shop?.name
  })
}

const calculateProductPrice = () => {
  var colorPrice = 0;
  var sizePrice = 0;

  const color = product.value.colors?.find((color) => color.id == formData.value.color);
  const size = product.value.sizes?.find((size) => size.id == formData.value.size);

  if (color) {
    colorPrice = color.price ?? 0;
  }
  if (size) {
    sizePrice = size.price ?? 0;
  }

  if (product.value.discount_price > 0) {
    productPrice.value = product.value.discount_price + colorPrice + sizePrice;
    mainPrice.value = product.value.price + colorPrice + sizePrice;
  } else {
    productPrice.value = product.value.price + colorPrice + sizePrice;
    mainPrice.value = productPrice.value;
  }

  discountPercentage.value = (((mainPrice.value - productPrice.value) / mainPrice.value) * 100).toFixed(2);
}

// const buyNow = () => {
//   if (!canAddToCart.value) {
//     toast.error(validationError.value || 'Please select all required options');
//     return;
//   }
//   if (authStore.token === null) {
//     return (authStore.loginModal = true);
//   }
//
//   const productData = {
//     product_id: formData.value.product_id,
//     is_buy_now: true,
//     quantity: 1,
//     size: hasSizes.value ? selectedSize.value : null,
//     color: hasColors.value ? selectedColor.value : null,
//     unit: null,
//     price: displaySellingPrice.value,
//     mrp: displayOriginalPrice.value,
//     discount: displayDiscount.value,
//   };
//
//   basketStore.addToCart(productData, product.value);
//   basketStore.buyNowShopId = product.value?.shop.id;
//   router.push({ name: "buynow" });
// };


// const getSelectedVariantData = () => {
//   let price = 0;
//   let mrp = 0;
//   let discount = 0;
//   let inwardInvoiceId = null;
//   let inwardProductId = null;
//
//   // If only sizes exist
//   if (sizeOnlyVariants.value.length > 0 && !hasColors.value) {
//     const size = sizeOnlyVariants.value.find(s => s.id == selectedSize.value);
//     if (size) {
//       const discountPercent = size.discount_percent || 0;
//       const currentMrp = size.mrp || 0;
//
//       if (discountPercent > 0 && currentMrp > 0) {
//         const originalMrp = currentMrp + (currentMrp * discountPercent / (100 - discountPercent));
//         price = Math.round(originalMrp * 100) / 100;
//         mrp = currentMrp;
//       } else {
//         price = currentMrp;
//         mrp = currentMrp;
//       }
//
//       discount = discountPercent;
//       inwardInvoiceId = size.inward_invoice_id || null;
//       inwardProductId = size.inward_product_id || null;
//     }
//     return { price, mrp, discount, inwardInvoiceId, inwardProductId };
//   }
//
//   // If colors exist
//   if (hasColors.value) {
//     const color = validColorVariants.value.find(c => c.id == selectedColor.value);
//     if (color) {
//       const discountPercent = color.discount_percent || 0;
//       const currentMrp = color.mrp || 0;
//
//       if (discountPercent > 0 && currentMrp > 0) {
//         const originalMrp = currentMrp + (currentMrp * discountPercent / (100 - discountPercent));
//         price = Math.round(originalMrp * 100) / 100;
//         mrp = currentMrp;
//       } else {
//         price = currentMrp;
//         mrp = currentMrp;
//       }
//
//       discount = discountPercent;
//       inwardInvoiceId = color.inward_invoice_id || null;
//       inwardProductId = color.inward_product_id || null;
//
//       if (hasSizes.value && selectedSize.value) {
//         const size = color.sizes?.find(s => s.id == selectedSize.value);
//         if (size) {
//           const sizeExtra = size.price || 0;
//           price += sizeExtra;
//           mrp += sizeExtra;
//           inwardInvoiceId = size.inward_invoice_id || color.inward_invoice_id || null;
//           inwardProductId = size.inward_product_id || color.inward_product_id || null;
//         }
//       }
//     }
//     return { price, mrp, discount, inwardInvoiceId, inwardProductId };
//   }
//
//   // ✅ Default - simple product (no variants)
//   return {
//     price: product.value.price || 0,
//     mrp: product.value.mrp || product.value.price || 0,
//     discount: product.value.discount_price || 0,
//     // ✅ Use separate refs for simple product inward IDs
//     inwardInvoiceId: simpleProductInwardInvoiceId.value || null,
//     inwardProductId: simpleProductInwardProductId.value || null,
//   };
// };

const getSelectedVariantData = () => {
  let price = 0;
  let mrp = 0;
  let discount = 0;
  let inwardInvoiceId = null;
  let inwardProductId = null;

  // If only sizes exist
  if (sizeOnlyVariants.value.length > 0 && !hasColors.value) {
    const size = sizeOnlyVariants.value.find(s => s.id == selectedSize.value);
    if (size) {
      const discountPercent = parseFloat(size.discount_percent || 0);
      const currentMrp = parseFloat(size.mrp || 0);
      let sellingPrice = parseFloat(size.price || 0);
      if (!sellingPrice || sellingPrice <= 0) {
        sellingPrice = (discountPercent > 0 && discountPercent < 100)
          ? (currentMrp - (currentMrp * discountPercent / 100))
          : currentMrp;
      }

      price = currentMrp;
      mrp = Math.round(sellingPrice * 100) / 100;
      discount = discountPercent;
      inwardInvoiceId = size.inward_invoice_id || null;
      inwardProductId = size.inward_product_id || null;
    }
    return { price, mrp, discount, inwardInvoiceId, inwardProductId };
  }

  // If colors exist
  if (hasColors.value) {
    const color = validColorVariants.value.find(c => c.id == selectedColor.value);
    if (color) {
      const discountPercent = parseFloat(color.discount_percent || 0);
      const currentMrp = parseFloat(color.mrp || 0);
      let sellingPrice = parseFloat(color.price || 0);
      if (!sellingPrice || sellingPrice <= 0) {
        sellingPrice = (discountPercent > 0 && discountPercent < 100)
          ? (currentMrp - (currentMrp * discountPercent / 100))
          : currentMrp;
      }

      price = currentMrp;
      mrp = Math.round(sellingPrice * 100) / 100;
      discount = discountPercent;
      inwardInvoiceId = color.inward_invoice_id || null;
      inwardProductId = color.inward_product_id || null;

      if (hasSizes.value && selectedSize.value) {
        const size = color.sizes?.find(s => s.id == selectedSize.value);

        if (size) {
          const discountPercent = parseFloat(size.discount_percent || 0);
          const currentMrp = parseFloat(size.mrp || 0);
          let sellingPrice = parseFloat(size.price || 0);
          if (!sellingPrice || sellingPrice <= 0) {
            sellingPrice = (discountPercent > 0 && discountPercent < 100)
              ? (currentMrp - (currentMrp * discountPercent / 100))
              : currentMrp;
          }

          price = currentMrp;
          mrp = Math.round(sellingPrice * 100) / 100;
          discount = discountPercent;
          inwardInvoiceId = size.inward_invoice_id || null;
          inwardProductId = size.inward_product_id || null;
        }
      }
    }
    return { price, mrp, discount, inwardInvoiceId, inwardProductId };
  }

  // ✅ SIMPLE PRODUCT (No variants)
  // Inward data should come from API
  const inwardData = product.value.inward_data || {};

  // ✅ Use inward product data if available
  if (inwardData && inwardData.price) {
    // ✅ price = MRP (Original) - e.g., 350
    price = parseFloat(inwardData.price);
    // ✅ mrp = Selling Price (Discounted) - e.g., 350 (if no discount)
    mrp = parseFloat(inwardData.discount_price) > 0
        ? parseFloat(inwardData.discount_price)
        : parseFloat(inwardData.price);
    discount = parseFloat(inwardData.discount_percent || inwardData.discount_percentage || product.value.discount_percentage || product.value.online_discount_percent || 0);
    if (!discount && price > 0 && mrp < price) {
      discount = Math.round(((price - mrp) / price) * 100 * 100) / 100;
    }
    inwardInvoiceId = inwardData.inward_invoice_id || null;
    inwardProductId = inwardData.inward_product_id || null;
  } else {
    // ✅ Fallback to product price
    price = parseFloat(product.value.price || 0);  // 350
    // ✅ mrp = discount_price if available, else price
    mrp = parseFloat(product.value.discount_price > 0
        ? product.value.discount_price
        : product.value.price || 0);
    discount = parseFloat(product.value.discount_percentage || product.value.online_discount_percent || 0);
    if (!discount && price > 0 && mrp < price) {
      discount = Math.round(((price - mrp) / price) * 100 * 100) / 100;
    }
  }

  // ✅ If still no inward ID, try from product
  if (!inwardInvoiceId && product.value.inward_invoice_ids) {
    let ids = product.value.inward_invoice_ids;
    if (typeof ids === 'string') {
      try {
        ids = JSON.parse(ids);
      } catch(e) {
        ids = [];
      }
    }
    if (Array.isArray(ids) && ids.length > 0) {
      inwardInvoiceId = ids[0];
    }
  }

  return {
    price: price,      // ✅ MRP (Original) - 350
    mrp: mrp,          // ✅ Selling Price (Discounted) - 350
    discount: discount,
    inwardInvoiceId: inwardInvoiceId,
    inwardProductId: inwardProductId,
  };
};

const addToCart = () => {
  if (!canAddToCart.value) {
    toast.error(validationError.value || 'Please select all required options');
    return;
  }

  const variantData = getSelectedVariantData();

  console.log("Cart Data",variantData)

  const cartData = {
    product_id: formData.value.product_id,
    is_buy_now: false,
    quantity: 1,
    size: hasSizes.value ? selectedSize.value : null,
    color: hasColors.value ? selectedColor.value : null,
    unit: null,
    price: variantData.price,
    mrp: variantData.mrp,
    discount: variantData.discount,
    inward_invoice_id: variantData.inwardInvoiceId,  // ✅ Send
    inward_product_id: variantData.inwardProductId,  // ✅ Send
  };

  basketStore.addToCart(cartData, product.value);
  setTimeout(() => {
    findProductInCart(route.params.id);
  }, 200);
};

// ✅ Buy Now
const buyNow = () => {
  if (!canAddToCart.value) {
    toast.error(validationError.value || 'Please select all required options');
    return;
  }
  if (authStore.token === null) {
    return (authStore.loginModal = true);
  }

  const variantData = getSelectedVariantData();

  const productData = {
    product_id: formData.value.product_id,
    is_buy_now: true,
    quantity: 1,
    size: hasSizes.value ? selectedSize.value : null,
    color: hasColors.value ? selectedColor.value : null,
    unit: null,
    price: variantData.price,
    mrp: variantData.mrp,
    discount: variantData.discount,
    inward_invoice_id: variantData.inwardInvoiceId,  // ✅ Send
    inward_product_id: variantData.inwardProductId,  // ✅ Send
  };

  basketStore.addToCart(productData, product.value);
  basketStore.buyNowShopId = product.value?.shop.id;
  router.push({ name: "buynow" });
};


watch(route, async () => {
  await nextTick();
  window.scrollTo(0, 0);
  fetchProductDetails();
  selectTab('about');
  formData.value.product_id = route.params.id;
  findProductInCart(route.params.id);
});

watch(() => basketStore.products, () => {
  findProductInCart(route.params.id);
}, { deep: true });

const findProductInCart = (productId) => {
  let foundProduct = null;
  basketStore.products.forEach((item) => {
    item.products.find((product) => {
      if (product.id == productId) {
        return (foundProduct = product);
      }
    });
  });
  cartProduct.value = foundProduct;
  if (foundProduct) {
    formData.value.size = foundProduct.size?.id;
    formData.value.color = foundProduct.color?.id;
    formData.value.unit = foundProduct.unit;
  }
};

// const addToCart = () => {
//   if (!canAddToCart.value) {
//     toast.error(validationError.value || 'Please select all required options');
//     return;
//   }
//
//   const cartData = {
//     product_id: formData.value.product_id,
//     is_buy_now: false,
//     quantity: 1,
//     size: hasSizes.value ? selectedSize.value : null,
//     color: hasColors.value ? selectedColor.value : null,
//     unit: null,
//     price: displaySellingPrice.value,
//     mrp: displayOriginalPrice.value,
//     discount: displayDiscount.value,
//   };
//
//   basketStore.addToCart(cartData, product.value);
//   setTimeout(() => {
//     findProductInCart(route.params.id);
//   }, 200);
// };

const decrementQty = () => {
  basketStore.decrementQuantity(product.value);
  setTimeout(() => {
    findProductInCart(route.params.id);
  }, 200);
};

const incrementQty = () => {
  basketStore.incrementQuantity(product.value);
  setTimeout(() => {
    findProductInCart(route.params.id);
  }, 200);
};

const favoriteAddOrRemove = () => {
  if (authStore.token === null) {
    return (authStore.loginModal = true);
  }
  axios.post('/favorite-add-or-remove', {
    product_id: product.value.id
  }, {
    headers: {
      Authorization: authStore.token
    }
  }).then(() => {
    product.value.is_favorite = !product.value.is_favorite
    if (product.value.is_favorite === false) {
      const content = {
        component: ToastSuccessMessage,
        props: {
          title: 'Product removed from favorite',
          message: 'Product removed from favorite successfully',
        },
      };
      toast(content, {
        type: "default",
        hideProgressBar: true,
        icon: false,
        position: "top-right",
        toastClassName: "vue-toastification-alert",
        timeout: 3000
      });
    } else {
      const content = {
        component: ToastSuccessMessage,
        props: {
          title: 'Product added to favorite',
          message: 'Product added to favorite successfully',
        },
      };
      toast(content, {
        type: "default",
        hideProgressBar: true,
        icon: false,
        position: "top-right",
        toastClassName: "vue-toastification-alert",
        timeout: 3000
      });
    }
    authStore.fetchFavoriteProducts();
  }).catch((error) => {
    console.log(error);
  });
};

const showReview = () => {
  selectTab('reviews');
};

const flashSale = ref({});

// ============================================================
// ✅ fetchProductDetails
// ============================================================

const simpleProductInwardInvoiceId = ref(null);
const simpleProductInwardProductId = ref(null);
const fetchProductDetails = async () => {
  isLoading.value = true;
  axios.get("/product-details", {
    params: { product_id: route.params.id },
    headers: {
      Authorization: authStore.token,
    },
  }).then((response) => {
    product.value = response.data.data.product;
    relatedProducts.value = response.data.data.related_products;
    popularProducts.value = response.data.data.popular_products;
    flashSale.value = response.data.data.product.flash_sale;

    colorVariants.value = response.data.data.color_variants || [];
    sizeOnlyVariants.value = response.data.data.size_only_variants || [];

    // ✅ Store inward IDs for simple products (no variants)
    simpleProductInwardInvoiceId.value = response.data.data.inward_invoice_id || null;
    simpleProductInwardProductId.value = response.data.data.inward_product_id || null;

    console.log('Color Variants:', colorVariants.value);
    console.log('Size Only Variants:', sizeOnlyVariants.value);
    console.log('Has Colors:', hasColors.value);
    console.log('Has Sizes:', hasSizes.value);
    console.log('Inward Invoice ID:', simpleProductInwardInvoiceId.value);
    console.log('Inward Product ID:', simpleProductInwardProductId.value);

    // Auto-select first valid option
    if (sizeOnlyVariants.value.length > 0 && !hasColors.value) {
      // Only sizes exist
      const firstSize = sizeOnlyVariants.value[0];
      onSizeOnlySelect(firstSize);
    } else if (colorVariants.value.length > 0) {
      // Colors exist (with or without sizes)
      selectedColor.value = colorVariants.value[0].id;
      onColorChange();
    } else {
      // ✅ Simple product - no variants
      selectedMrp.value = parseFloat(product.value.mrp || product.value.price || 0);
      selectedDiscount.value = parseFloat(product.value.discount_percentage || product.value.online_discount_percent || 0);
      if (!selectedDiscount.value && selectedMrp.value > 0 && parseFloat(product.value.discount_price || 0) > 0 && parseFloat(product.value.discount_price) < selectedMrp.value) {
        selectedDiscount.value = Number((((selectedMrp.value - parseFloat(product.value.discount_price)) / selectedMrp.value) * 100).toFixed(2));
      }
      selectedQty.value = product.value.quantity || 0;
    }

    if (flashSale.value) {
      startCountdown();
    }

    if (product.value.colors?.length > 0) {
      formData.value.color = product.value.colors[0].id;
    } else {
      formData.value.color = null;
    }
    if (product.value.sizes?.length > 0) {
      formData.value.size = product.value.sizes[0].id;
    } else {
      formData.value.size = null;
    }
    calculateProductPrice();
    findProductInCart(route.params.id);

    setTimeout(() => {
      isLoading.value = false;
    }, 100);
  }).catch((error) => {
    console.error('Error fetching product:', error);
    isLoading.value = false;
  });
};

const averageRatings = ref({});

const totalReviews = ref(0);
const reviews = ref([]);

const currentPage = ref(1);
const perPage = ref(6);

const onClickHandler = (page) => {
  currentPage.value = page;
  fetchReviews();
};

const fetchReviews = async () => {
  axios.get("/reviews", {
    params: {
      product_id: route.params.id,
      page: currentPage.value,
      per_page: perPage.value,
    },
  }).then((response) => {
    totalReviews.value = response.data.data.total;
    reviews.value = response.data.data.reviews;
    averageRatings.value = response.data.data.average_rating_percentage;
  });
};

const endDay = ref("");
const endHour = ref("");
const endMinute = ref("");
const endSecond = ref("");
let countdownInterval = null;

const startCountdown = () => {
  const endDate = new Date(flashSale.value?.end_date).getTime();

  if (flashSale.value?.end_date) {
    countdownInterval = setInterval(() => {
      const now = new Date().getTime();
      const timeLeft = endDate - now;

      if (timeLeft <= 0) {
        clearInterval(countdownInterval);
        endDay.value = "00";
        endHour.value = "00";
        endMinute.value = "00";
        endSecond.value = "00";
      } else {
        endDay.value = String(Math.floor(timeLeft / (1000 * 60 * 60 * 24))).padStart(2, "0");
        endHour.value = String(Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, "0");
        endMinute.value = String(Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, "0");
        endSecond.value = String(Math.floor((timeLeft % (1000 * 60)) / 1000)).padStart(2, "0");
      }
    }, 1000);
  }
};

onUnmounted(() => {
  clearInterval(countdownInterval);
});

// Position variables to control zoom position
const mouseX = ref(0);
const mouseY = ref(0);

const handleMouseMove = (event) => {
  const container = event.currentTarget;
  const rect = container.getBoundingClientRect();

  let clientX, clientY;
  if (event.type === "touchmove" || event.type === "touchstart") {
    const touch = event.touches[0];
    clientX = touch.clientX;
    clientY = touch.clientY;
  } else {
    clientX = event.clientX;
    clientY = event.clientY;
  }

  mouseX.value = ((clientX - rect.left) / rect.width) * 100;
  mouseY.value = ((clientY - rect.top) / rect.height) * 100;
};

const resetZoom = () => {
  mouseX.value = 50;
  mouseY.value = 50;
};

watch([mouseX, mouseY], ([x, y]) => {
  document.documentElement.style.setProperty('--mouse-x', `${x}%`);
  document.documentElement.style.setProperty('--mouse-y', `${y}%`);
});
</script>

<style scoped>
.zoom-container {
  overflow: hidden;
  position: relative;
  cursor: zoom-in;
  width: 100%;
  height: 100%;
}

.zoom-image {
  transition: transform 0.3s ease, transform-origin 0.3s ease;
  transform: scale(1);
  transform-origin: center center;
}

.zoom-container:hover .zoom-image {
  transform: scale(3.5);
  transform-origin: calc(var(--mouse-x, 50%)) calc(var(--mouse-y, 50%));
}
</style>

<style>
.description img {
  max-width: 95% !important;
}

iframe {
  width: 100%;
  height: 300px !important;
}

@media (max-width: 500px) {
  iframe {
    height: 200px !important;
  }
}

@media (max-width: 375px) {
  iframe {
    height: 180px !important;
  }
}

@media (max-width: 320px) {
  iframe {
    height: 160px !important;
  }
}

.product-details-slider .swiper-slide {
  height: auto !important;
}

.product-details-thumbnail .swiper-slide {
  @apply h-20 md:h-[120px] lg:h-[100px];
}

.product-details-thumbnail .swiper-button-prev,
.product-details-thumbnail .swiper-button-next {
  @apply bg-white w-6 h-6 rounded-full shadow border border-slate-200 text-slate-600 -translate-y-1/2 mt-0;
}

.product-details-thumbnail .swiper-button-prev::after,
.product-details-thumbnail .swiper-button-next::after {
  @apply text-base;
}

.product-details-thumbnail .swiper-button-next {
  right: 0px;
}

.product-details-thumbnail .swiper-button-prev {
  left: 0px;
}

.product-details-thumbnail .swiper-slide {
  @apply border border-slate-100 rounded-lg transition overflow-hidden;
}

.product-details-thumbnail .swiper-slide-thumb-active {
  @apply border border-primary;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table-responsive table {
  min-width: 600px;
}
</style>