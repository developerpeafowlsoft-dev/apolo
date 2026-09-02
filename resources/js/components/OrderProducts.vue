<template>
    <div class="space-y-3.5">
        <div
            v-for="product in order.products"
            :key="product.id"
            class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center p-4 rounded-xl border border-slate-200/70 hover:border-slate-300 bg-white transition-all duration-200 shadow-2xs hover:shadow-xs group"
        >
            <!-- Product Image & Details -->
            <div class="flex gap-4 items-center grow min-w-0">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-slate-50 border border-slate-100 p-2 shrink-0 flex items-center justify-center overflow-hidden group-hover:scale-[1.02] transition-transform">
                    <img :src="product.thumbnail" :alt="product.name" class="w-full h-full object-contain" loading="lazy" />
                </div>
                
                <div class="space-y-1 grow min-w-0">
                    <div v-if="product.brand" class="text-2xs font-bold text-primary uppercase tracking-wider">
                        {{ product.brand }}
                    </div>

                    <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug truncate">
                        {{ product.name }}
                    </h4>

                    <!-- Size & Color Chips -->
                    <div v-if="product.size || product.color" class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span v-if="product.size" class="px-2.5 py-0.5 bg-slate-100 rounded-md text-slate-700 text-2xs font-bold border border-slate-200/60 uppercase">
                            {{ product.size }}
                        </span>
                        <span v-if="product.color" class="px-2.5 py-0.5 bg-slate-100 rounded-md text-slate-700 text-2xs font-bold border border-slate-200/60 capitalize">
                            {{ product.color }}
                        </span>
                    </div>

                    <!-- Review Rating Action for Delivered -->
                    <div v-if="order.order_status === 'Delivered'" class="pt-2">
                        <button
                            v-if="!product.rating"
                            class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 rounded-lg text-white text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95"
                            @click="showRating(product.id)"
                        >
                            {{ $t('Review') }}
                        </button>
                        <div v-else class="flex items-center gap-0.5">
                            <span v-for="i in 5" :key="i">
                                <StarIcon class="w-4 h-4" :class="i <= product.rating ? 'text-amber-400' : 'text-slate-200'" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="sm:text-right shrink-0 w-full sm:w-auto border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                <div class="text-slate-900 text-base sm:text-lg font-extrabold">
                    <span class="text-slate-500 font-medium text-xs sm:text-sm mr-1">{{ product.order_qty }} ×</span>
                    {{ master.showCurrency(product.price || 0) }}
                </div>

                <div v-if="product.mrp > product.price" class="text-xs text-slate-400 mt-1 flex sm:justify-end items-center gap-1.5 flex-wrap">
                    <span>MRP: <span class="line-through">{{ master.showCurrency(product.mrp) }}</span></span>
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full text-2xs border border-emerald-200/60">
                        {{ $t('Saved') }}: {{ master.showCurrency((product.mrp - product.price) * product.order_qty) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Rating Modal -->
        <TransitionRoot as="template" :show="showRatingModal">
            <Dialog as="div" class="relative z-50" @close="showRatingModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                    leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all my-8 w-full max-w-lg">
                                <div class="bg-white p-6 sm:p-8 relative">
                                    <button
                                        type="button"
                                        class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-full absolute top-4 right-4 flex justify-center items-center cursor-pointer transition-colors"
                                        @click="showRatingModal = false"
                                    >
                                        <XMarkIcon class="w-5 h-5 text-slate-600" />
                                    </button>

                                    <h3 class="text-lg font-bold text-slate-900 mb-4">{{ $t('Rate Your Experience') }}</h3>

                                    <div class="flex flex-col items-center justify-center gap-3 mb-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                        <Vue3StarRatings v-model="rating" :star-size="36" inactiveColor="#cbd5e1" />
                                        <div class="text-slate-700 text-sm font-semibold">
                                            <span>{{ $t('Rating') }}: </span>
                                            <span class="text-amber-500 font-bold text-base">{{ rating }} / 5</span>
                                        </div>
                                    </div>

                                    <div class="space-y-1 mb-6">
                                        <label for="rating-description" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">
                                            {{ $t('Message') }}
                                        </label>
                                        <textarea
                                            id="rating-description"
                                            v-model="description"
                                            class="border border-slate-200 p-3 w-full rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-slate-800 text-sm transition-all"
                                            rows="4"
                                            :placeholder="$t('Enter your description')"
                                        ></textarea>
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-5 rounded-xl transition-colors shrink-0"
                                            @click="showRatingModal = false"
                                        >
                                            {{ $t('Cancel') }}
                                        </button>
                                        <button
                                            type="button"
                                            class="bg-primary hover:bg-primary/90 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-sm cursor-pointer grow"
                                            @click="submitRating()"
                                        >
                                            {{ $t('Submit') }}
                                        </button>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from "@heroicons/vue/24/solid";
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import Vue3StarRatings from "vue3-star-ratings";
import { useAuth } from "../stores/AuthStore";
import { useMaster } from "../stores/MasterStore";

const toast = useToast();
const authStore = useAuth();

const props = defineProps({
    order: Object,
    default: () => { },
});

const emit = defineEmits(['refresh']);

const master = useMaster();
const rating = ref(0);
const description = ref('');
const showRatingModal = ref(false);
const productID = ref('');

const showRating = (id) => {
    productID.value = id;
    showRatingModal.value = true;
};

const submitRating = () => {
    if (rating.value === 0) {
        toast.error('Please select rating', {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
        return;
    }
    axios.post('/product-review', {
        product_id: productID.value,
        rating: rating.value,
        review: description.value,
    }, {
        headers: {
            Authorization: authStore.token,
        }
    }).then((response) => {
        toast.success(response.data.message, {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
        showRatingModal.value = false;
        rating.value = 0;
        description.value = '';
        emit('refresh');
    }).catch((error) => {
        toast.error(error.response?.data?.message || 'Failed to submit review', {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
    });
};
</script>
