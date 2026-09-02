<template>
  <div class="main-container">
    <div v-show="!isLoading" class="grid grid-cols-1 xl:grid-cols-4">
      <div class="xl:col-span-3 col-span-1 lg:pr-6">
        <div class="flex items-center gap-2 overflow-hidden pt-4">
          <router-link to="/" class="w-6 h-6">
            <HomeIcon class="w-5 h-5 text-slate-600" />
          </router-link>

          <div class="grow w-full overflow-hidden">
            <div class="space-x-1 text-slate-600 text-sm font-normal truncate">
              <router-link to="/">{{ $t("Home") }}</router-link>
              <span>/</span>
              <span>{{ product.name }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap lg:flex-nowrap gap-4 mt-6">
          <div class="lg:w-[480px] w-full">
            <div class="w-full">
              <div class="bg-slate-50 rounded-xl border border-slate-100 px-6">
                <swiper :spaceBetween="10" :thumbs="{ swiper: thumbsSwiper }" :modules="modules"
                        class="product-details-slider">
                  <swiper-slide v-for="thumbnail in product.thumbnails" :key="thumbnail.id"
                                class="max-h-[448px] h-auto">
                    <div v-if="thumbnail.thumbnail" class="zoom-container h-full"
                         @mousemove="handleMouseMove" @mouseleave="resetZoom"
                         @touchstart="handleMouseMove" @touchmove="handleMouseMove"
                         @touchend="resetZoom">
                      <img :src="thumbnail.thumbnail" alt="thumbnail"
                           class="zoom-image h-full w-full object-contain" />
                    </div>
                    <div v-else class="h-full w-full bg-slate-200 flex justify-center items-center">
                      <video v-if="thumbnail.type == 'file'" controls class="w-full">
                        <source :src="thumbnail.url" type="video/mp4">
                      </video>
                      <div v-else v-html="thumbnail.url" class="w-full overflow-hidden"
                           ref="iframeContainer"></div>
                    </div>
                  </swiper-slide>
                </swiper>
              </div>
              <div class="px-1 mt-2">
                <swiper @swiper="setThumbsSwiper" :spaceBetween="10" :slidesPerView="4" :freeMode="true"
                        :navigation="true" :watchSlidesProgress="true" :modules="modules"
                        class="product-details-thumbnail">
                  <swiper-slide v-for="thumbnail in product.thumbnails" :key="thumbnail.id">
                    <img v-if="thumbnail.thumbnail" :src="thumbnail.thumbnail" alt=""
                         class="h-full w-full object-cover" />

                    <div v-else class="h-full w-full bg-slate-200 flex justify-center items-center">
                      <video v-if="thumbnail.type == 'file'" class="h-full w-full">
                        <source :src="thumbnail.url" type="video/mp4">
                      </video>
                      <div v-else
                           class="h-full w-full overflow-hidden flex justify-center items-center">
                        <img :src="'/assets/icons/video-player.svg'" alt="" width="70"
                             height="70">
                      </div>
                    </div>
                  </swiper-slide>
                </swiper>
              </div>
            </div>
          </div>

          <div class="w-full sm:w-auto">
            <!-- Flash Sale -->
            <div v-if="flashSale"
                 class="bg-slate-100 mb-3 sm:mb-6 rounded-lg sm:rounded-[44px] flex items-center justify-start gap-2 sm:gap-5 overflow-hidden flex-col sm:flex-row">
              <div
                  class="px-4 sm:px-8 py-2 bg-gradient-to-l from-primary to-primary-800 w-full sm:w-auto">
                <div class="text-white text-sm sm:text-base font-bold leading-normal">
                  {{ $t("Flash Sale") }}
                </div>
              </div>

              <div class="h-full flex justify-center items-center flex-wrap pb-2 sm:pb-0">
                <div class="text-center text-primary text-sm font-normal leading-tight pr-2">
                  {{ $t("Ending in") }}
                </div>

                <div class="flex justify-center items-center gap-1 text-white">
                  <div v-if="endDay > 0" class="p-1 justify-center items-center gap-1 inline-flex">
                    <div
                        class="text-center text-primary text-base font-semibold font-['Inter'] leading-none">
                      {{ endDay }}
                    </div>
                    <div
                        class="text-center text-[#687387] text-[9.14px] font-normal font-['Inter'] leading-none">
                      {{ $t("Days") }}
                    </div>
                  </div>

                  <span v-if="endDay > 0" class="text-black text-base font-bold">:</span>
                  <div class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-base font-semibold font-['Inter']">
                      {{ endHour }}
                    </div>
                    <div
                        class="text-center text-[#687387] text-[9.14px] font-normal font-['Inter'] leading-none">
                      {{ $t("Hours") }}
                    </div>
                  </div>

                  <span class="text-black text-base font-bold">:</span>
                  <div class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-base font-semibold font-['Inter']">
                      {{ endMinute }}
                    </div>
                    <div
                        class="text-center text-[#687387] text-[9.14px] font-normal font-['Inter'] leading-none">
                      {{ $t("Minutes") }}
                    </div>
                  </div>

                  <span v-if="endDay <= 0" class="text-black text-base font-bold">:</span>
                  <div v-if="endDay <= 0" class="p-1 justify-center items-center gap-1 inline-flex">
                    <div class="text-center text-primary text-base font-semibold font-['Inter']">
                      {{ endSecond }}
                    </div>
                    <div
                        class="text-center text-[#687387] text-[9.14px] font-normal font-['Inter'] leading-none">
                      {{ $t("Seconds") }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Brand -->
            <span class="text-primary text-xs font-normal leading-none px-1.5 py-1 bg-primary-50 rounded">
                            {{ product.brand ?? "" }}
                        </span>

            <!-- Title -->
            <div class="mt-3 text-slate-950 text-2xl font-medium leading-normal">
              {{ product.name }}
            </div>

            <!-- Short Description -->
            <div class="mt-2 text-slate-700 text-base font-normal leading-normal">
              {{ product.short_description }}
            </div>

            <!-- Rating, review, sold and share -->
            <div class="py-5 flex flex-wrap justify-start items-center gap-4 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <div class="flex">
                  <StarIcon v-for="i in 5" :key="i" class="w-6 h-6 2xl:block hidden"
                            :class="i <= product.rating ? 'text-amber-500' : 'text-gray-300'" />
                </div>
                <div class="text-slate-800 text-base font-bold">
                  {{ product.rating }}
                </div>
                <div class="text-slate-500 text-base font-normal">
                  {{ product.total_reviews }} {{ $t("Review") }}
                </div>
              </div>

              <div class="w-[1px] h-4 bg-slate-200"></div>

              <div class="text-slate-800 text-base font-normal leading-normal">
                {{ product.total_sold }} {{ $t("Sold") }}
              </div>

              <div class="w-[1px] h-4 bg-slate-200"></div>

              <Menu as="div" class="relative inline-block text-left">
                <div>
                  <MenuButton class="flex items-center gap-2 border-none">
                    <ShareIcon class="w-[18px] text-slate-600" />
                    <span class="text-slate-800 text-base font-normal leading-normal">
                                            {{ $t("Share") }}
                                        </span>
                  </MenuButton>
                </div>

                <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                  <MenuItems
                      class="absolute right-0 tr z-10 mt-2 w-56 origin-top rounded-md bg-white ring-1 shadow-lg ring-black/5 focus:outline-hidden">
                    <div class="py-1 divide-y divide-gray-100">
                      <MenuItem v-slot="{ active }" v-for="social in shareOptions"
                                :key="social.name" class="cursor-pointer" @click="share(social.name)">
                        <div
                            class="flex items-center gap-2 justify-between px-4 py-2 hover:bg-slate-100 transition-all duration-200">
                          <div class="flex items-center gap-1.5">
                            <div class="w-7 h-7 p-1.5 flex justify-center items-center text-white rounded-full"
                                 :class="`bg-[${social.color}]`">
                              <FontAwesomeIcon :icon="social.icon" class="w-full h-full" />
                            </div>
                            <span class="capitalize">{{ social.name }}</span>
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

              <div class="w-[1px] h-4 bg-slate-200"></div>

              <button class="border-none" @click="favoriteAddOrRemove">
                <HeartIcon v-if="!product.is_favorite" class="w-6 h-6 text-slate-600" />
                <HeartIconFill v-else class="w-6 h-6 text-red-500" />
              </button>
            </div>

            <!-- Price part -->
            <div class="flex items-center gap-3 py-4 border-b border-slate-200 flex-wrap">
              <div class="text-primary text-3xl font-bold leading-9">
                {{ masterStore.showCurrency(parseFloat(displaySellingPrice).toFixed(2)) }}
              </div>

              <div v-if="displayDiscount > 0 && displayDiscount < 100"
                   class="text-slate-400 text-2xl font-normal line-through leading-loose">
                {{ masterStore.showCurrency(parseFloat(displayOriginalPrice).toFixed(2)) }}
              </div>

              <div v-if="displayDiscount > 0 && displayDiscount < 100"
                   class="px-2 py-1 bg-red-500 rounded-2xl text-white text-base font-medium">
                {{ displayDiscount }}% {{ $t("OFF") }}
              </div>
            </div>

            <!-- ============================================================ -->
            <!-- ✅ Color & Size Variant Selector - Dynamic with DROPDOWNS -->
            <!-- ============================================================ -->
            <div v-if="hasColors || hasSizes && (validColorVariants.length > 0 || sizeOnlyVariants.length > 0)" class="py-4 border-b border-slate-200">
              <!-- Show title based on what exists -->
              <div v-if="hasColors && hasSizes" class="text-slate-800 text-base font-semibold mb-3">
                {{ $t("Select Variant") }}
                <span class="bg-primary text-white ml-2 px-2 py-0.5 rounded-full text-xs">
                      {{ displayColorCount }} {{ $t("Colors") }}
                </span>
              </div>
              <div v-else-if="hasColors && !hasSizes" class="text-slate-800 text-base font-semibold mb-3">
                {{ $t("Select Color") }}
                <span class="bg-primary text-white ml-2 px-2 py-0.5 rounded-full text-xs">
                                    {{ displayColorCount }} {{ $t("Colors") }}
                                </span>
              </div>
              <div v-else-if="!hasColors && hasSizes" class="text-slate-800 text-base font-semibold mb-3">
                {{ $t("Select Size") }}
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Color Select - Only if colors exist -->
                <div v-if="hasColors">
                  <label class="block text-sm font-medium text-slate-600 mb-1">
                    {{ $t("Color") }}
                  </label>
                  <select v-model="selectedColor" @change="onColorChange"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-white">
                    <option v-for="color in validColorVariants" :key="color.id" :value="color.id">
                      {{ color.name }}
                    </option>
                  </select>
                </div>

                <!-- Size Select - Only if sizes exist -->
                <div v-if="hasSizes">
                  <label class="block text-sm font-medium text-slate-600 mb-1">
                    {{ $t("Size") }}
                  </label>
                  <select v-model="selectedSize"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-white"
                          :disabled="!selectedColor && hasColors">
                    <option value="">{{ $t("Select Size") }}</option>
                    <option v-for="size in availableSizes" :key="size.id" :value="size.id">
                      {{ size.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Selected Variant Details -->
              <div v-if="(hasColors && selectedColor) || (!hasColors && selectedSize)"
                   class="mt-3 p-3 bg-primary-50 rounded-lg border border-primary-200">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <div v-if="hasColors">
                    <span class="text-xs text-slate-500">{{ $t("Color") }}</span>
                    <div class="flex items-center gap-2 mt-1">
                                            <span class="w-4 h-4 rounded-full border border-slate-300 inline-block"
                                                  :style="{ backgroundColor: selectedColorObj?.color_code || '#ccc' }"></span>
                      <span class="font-medium text-sm">{{ selectedColorObj?.name }}</span>
                    </div>
                  </div>
                  <div v-if="hasSizes">
                    <span class="text-xs text-slate-500">{{ $t("Size") }}</span>
                    <div class="font-medium text-sm mt-1">{{ selectedSizeObj?.name || '-' }}</div>
                  </div>
                  <div>
                    <span class="text-xs text-slate-500">{{ $t("MRP") }}</span>
                    <div class="font-bold text-primary text-sm mt-1">
                      ₹{{ formatNumber(selectedMrp) }}
                    </div>
                  </div>
                  <div class="hidden">
                    <span class="text-xs text-slate-500">{{ $t("Qty Available") }}</span>
                    <div class="font-bold text-sm mt-1" :class="selectedQty > 0 ? 'text-green-600' : 'text-red-600'">
                      {{ selectedQty }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- All Variants Table - Dynamic Columns -->
              <div class="mt-4 overflow-x-auto hidden">
                <table class="w-full text-xs border-collapse">
                  <thead>
                  <tr class="bg-slate-100">
                    <!-- Color column only if colors exist -->
                    <th v-if="hasColors" class="p-1.5 text-left font-semibold">{{ $t("Color") }}</th>
                    <!-- Size column only if sizes exist -->
                    <th v-if="hasSizes" class="p-1.5 text-left font-semibold">{{ $t("Sizes") }}</th>
                    <th class="p-1.5 text-center font-semibold">{{ $t("Qty") }}</th>
                    <th class="p-1.5 text-right font-semibold">{{ $t("MRP") }}</th>
                    <th class="p-1.5 text-center font-semibold">{{ $t("Disc %") }}</th>
                  </tr>
                  </thead>
                  <tbody>
                  <!-- For Color + Size products -->
                  <tr v-for="color in validColorVariants" :key="color.id"
                      v-if="hasColors && hasSizes"
                      class="border-b border-slate-100 hover:bg-slate-50 transition cursor-pointer"
                      @click="selectedColor = color.id; onColorChange()"
                      :class="{ 'bg-primary-50': selectedColor == color.id }">
                    <td class="p-1.5">
                      <div class="flex items-center gap-1.5">
                                                    <span class="w-3 h-3 rounded-full border border-slate-300 inline-block"
                                                          :style="{ backgroundColor: color.color_code || '#ccc' }"></span>
                        <span>{{ color.name }}</span>
                      </div>
                    </td>
                    <td class="p-1.5">
                      <span class="text-xs">{{ getSizeNames(color.sizes) }}</span>
                    </td>
                    <td class="p-1.5 text-center">{{ color.qty }}</td>
                    <td class="p-1.5 text-right font-medium text-primary">
                      ₹{{ formatNumber(color.mrp) }}
                    </td>
                    <td class="p-1.5 text-center">
                                                <span v-if="color.discount_percent > 0"
                                                      class="bg-red-100 text-red-600 px-1.5 py-0.5 rounded text-xs">
                                                    {{ color.discount_percent }}%
                                                </span>
                      <span v-else class="text-slate-300">-</span>
                    </td>
                  </tr>

                  <!-- For Only Color products -->
                  <tr v-for="color in validColorVariants" :key="color.id"
                      v-if="hasColors && !hasSizes"
                      class="border-b border-slate-100 hover:bg-slate-50 transition cursor-pointer"
                      @click="selectedColor = color.id; onColorChange()"
                      :class="{ 'bg-primary-50': selectedColor == color.id }">
                    <td class="p-1.5">
                      <div class="flex items-center gap-1.5">
                                                    <span class="w-3 h-3 rounded-full border border-slate-300 inline-block"
                                                          :style="{ backgroundColor: color.color_code || '#ccc' }"></span>
                        <span>{{ color.name }}</span>
                      </div>
                    </td>
                    <td class="p-1.5 text-center">{{ color.qty }}</td>
                    <td class="p-1.5 text-right font-medium text-primary">
                      ₹{{ formatNumber(color.mrp) }}
                    </td>
                    <td class="p-1.5 text-center">
                                                <span v-if="color.discount_percent > 0"
                                                      class="bg-red-100 text-red-600 px-1.5 py-0.5 rounded text-xs">
                                                    {{ color.discount_percent }}%
                                                </span>
                      <span v-else class="text-slate-300">-</span>
                    </td>
                  </tr>

                  <!-- For Only Size products -->
                  <tr v-for="size in sizeOnlyVariants" :key="size.id"
                      v-if="!hasColors && hasSizes"
                      class="border-b border-slate-100 hover:bg-slate-50 transition cursor-pointer"
                      @click="selectedSize = size.id; onSizeOnlySelect(size)"
                      :class="{ 'bg-primary-50': selectedSize == size.id }">
                    <td class="p-1.5">
                      <span>{{ size.name }}</span>
                    </td>
                    <td class="p-1.5 text-center">{{ size.qty }}</td>
                    <td class="p-1.5 text-right font-medium text-primary">
                      ₹{{ formatNumber(size.mrp) }}
                    </td>
                    <td class="p-1.5 text-center">
                                                <span v-if="size.discount_percent > 0"
                                                      class="bg-red-100 text-red-600 px-1.5 py-0.5 rounded text-xs">
                                                    {{ size.discount_percent }}%
                                                </span>
                      <span v-else class="text-slate-300">-</span>
                    </td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- ============================================================ -->

            <!-- Error Messages -->
            <div v-if="validationError" class="mt-2 text-red-500 text-sm">
              {{ validationError }}
            </div>

            <div class="flex flex-wrap gap-4 mt-4">
              <div v-if="cartProduct"
                   class="p-2 rounded-[10px] border border-slate-100 inline-flex gap-4">
                <button class="bg-slate-200 p-2 rounded" @click="decrementQty">
                  <MinusIcon class="w-6 h-6 text-slate-800" />
                </button>
                <div
                    class="w-6 flex items-center justify-center text-center text-slate-950 text-base font-medium leading-normal">
                  {{ cartProduct.quantity }}
                </div>
                <button class="bg-slate-100 p-2 rounded" @click="incrementQty">
                  <PlusIcon class="w-6 h-6 text-slate-800" />
                </button>
              </div>

              <button v-if="!cartProduct"
                      class="grow max-w-56 justify-center items-center flex gap-2 px-6 py-4 rounded-[10px] border transition-all duration-200"
                      :class="canAddToCart ? 'text-primary border-primary hover:bg-primary hover:text-white' : 'text-slate-400 border-slate-200 cursor-not-allowed'"
                      :disabled="!canAddToCart"
                      @click="addToCart">
                <div class="w-5 h-5">
                  <BagIcon :class="canAddToCart ? 'text-primary' : 'text-slate-400'" />
                </div>
                <div class="text-base font-medium leading-normal">
                  {{ $t("Add to Cart") }}
                </div>
              </button>

              <button
                  class="grow px-6 py-4 rounded-[10px] border transition-all duration-200 max-w-[50%]"
                  :class="canAddToCart ? 'text-white bg-primary border-primary hover:bg-primary-800' : 'text-slate-400 bg-slate-200 border-slate-200 cursor-not-allowed'"
                  :disabled="!canAddToCart"
                  @click="buyNow">
                                <span class="text-base font-medium leading-normal">
                                    {{ $t("Buy Now") }}
                                </span>
              </button>
            </div>
          </div>
        </div>

        <div class="block xl:hidden w-full pt-6 border-slate-200">
          <ProductDetailsRightSide :product="product" :popularProducts="popularProducts" />
        </div>

        <div class="flex items-center gap-8 flex-wrap border-b mt-3 mb-4 xl:my-6">
          <button class="py-3 transition text-base font-medium leading-normal border-b"
                  :class="aboutProduct ? 'text-primary border-primary' : 'text-slate-600 border-transparent'"
                  @click="aboutProduct = true; review = false;">
            {{ $t("About Product") }}
          </button>
          <button class="py-3 transition text-base font-medium leading-normal border-b"
                  :class="review ? 'text-primary border-primary' : 'text-slate-600 border-transparent'"
                  @click="showReview()">
            {{ $t("Reviews") }}
          </button>
        </div>

        <!-- About Product -->
        <div v-if="aboutProduct" class="description">
          <div class="prose max-w-none w-full m-0" v-html="product.description"></div>
        </div>

        <!-- Reviews -->
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

      <!-- Right side -->
      <div class="hidden xl:block col-span-1 w-full pt-6 h-full xl:pt-16 border-slate-200 xl:pb-6"
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
const review = ref(false);

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
  if (selectedColor.value && selectedMrp.value > 0) {
    const discountAdd = product.value.discount_price
    const productPrice = product.value.price * discountAdd / 100;
    return selectedMrp.value + productPrice;
  }
  // If product has MRP
  if (product.value.mrp > 0) {
    return product.value.mrp;
  }
  return product.value.price || 0;
});

// Selling Price - After discount
const displaySellingPrice = computed(() => {
  // If variant selected
  if (selectedColor.value && selectedMrp.value > 0) {
    // const discount = displayDiscount.value;
    // if (discount > 0 && discount < 100) {
    //   // Apply discount on MRP
    //   return selectedMrp.value - (selectedMrp.value * discount / 100);
    // }
    return selectedMrp.value;
  }

  // Product price (if no variant selected)
  if (displayDiscount.value > 0 && displayDiscount.value < 100) {
    return product.value.discount_price || product.value.price || 0;
  }
  return product.value.price || 0;
});

// ✅ Display discount percentage - Only for selected variant with discount
const displayDiscount = computed(() => {
  // If variant selected with discount
  if (selectedColor.value && selectedDiscount.value > 0) {
    return selectedDiscount.value;
  }

  // ✅ Only show product discount if NO variant is selected
  // AND product has discount
  if (!selectedColor.value && parseFloat(product.value.discount_price) > 0) {
    return parseFloat(product.value.discount_price);
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
      const discountPercent = size.discount_percent || 0;
      const currentMrp = size.mrp || 0;

      if (discountPercent > 0 && currentMrp > 0) {
        const originalMrp = currentMrp + (currentMrp * discountPercent / (100 - discountPercent));
        price = Math.round(originalMrp * 100) / 100;
        mrp = currentMrp;
      } else {
        price = currentMrp;
        mrp = currentMrp;
      }

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
      const discountPercent = color.discount_percent || 0;
      const currentMrp = color.mrp || 0;

      if (discountPercent > 0 && currentMrp > 0) {
        const originalMrp = currentMrp + (currentMrp * discountPercent / (100 - discountPercent));
        price = Math.round(originalMrp * 100) / 100;
        mrp = currentMrp;
      } else {
        price = currentMrp;
        mrp = currentMrp;
      }

      discount = discountPercent;
      inwardInvoiceId = color.inward_invoice_id || null;
      inwardProductId = color.inward_product_id || null;

      if (hasSizes.value && selectedSize.value) {
        const size = color.sizes?.find(s => s.id == selectedSize.value);

        if (size) {
          const discountPercent = parseFloat(size.discount_percent || 0);
          const currentMrp = parseFloat(size.mrp || 0);

          if (discountPercent > 0 && currentMrp > 0) {
            const originalMrp = currentMrp + (currentMrp * discountPercent / (100 - discountPercent));
            price = Math.round(originalMrp * 100) / 100;
            mrp = currentMrp;
          } else {
            price = currentMrp;
            mrp = currentMrp;
          }

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
    discount = parseFloat(inwardData.discount_price) || 0;
    inwardInvoiceId = inwardData.inward_invoice_id || null;
    inwardProductId = inwardData.inward_product_id || null;
  } else {
    // ✅ Fallback to product price
    price = product.value.price || 0;  // 350
    // ✅ mrp = discount_price if available, else price
    mrp = product.value.discount_price > 0
        ? product.value.discount_price
        : product.value.price || 0;
    discount = product.value.discount_price || 0;
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
  aboutProduct.value = true;
  review.value = false;
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
  aboutProduct.value = false;
  review.value = true;
  fetchReviews();
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
      selectedMrp.value = product.value.price || 0;
      selectedDiscount.value = product.value.discount_price || 0;
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