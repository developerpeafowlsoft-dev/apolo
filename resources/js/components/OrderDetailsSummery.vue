<template>
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <h3 class="text-base sm:text-lg font-extrabold text-slate-900">
                    {{ $t('Order Summary') }}
                </h3>
            </div>

            <div class="space-y-3.5 text-xs sm:text-sm">
                <!-- Items Count -->
                <div class="flex justify-between items-center text-slate-600">
                    <span class="font-medium">{{ $t('Items') }}</span>
                    <span class="font-bold text-slate-900">{{ order.quantity }}</span>
                </div>

                <!-- Subtotal -->
                <div class="flex justify-between items-center text-slate-600">
                    <span class="font-medium">{{ $t('Subtotal') }}</span>
                    <span class="font-bold text-slate-900">{{ master.showCurrency(order?.total_amount) }}</span>
                </div>

                <!-- Savings -->
                <div v-if="order.total_mrp > order.total_amount" class="flex justify-between items-center text-emerald-700 bg-emerald-50/80 px-3.5 py-2.5 rounded-xl border border-emerald-200/60">
                    <span class="font-semibold">{{ $t('You Save') }}</span>
                    <span class="font-extrabold">- {{ master.showCurrency(order.total_mrp - order.total_amount) }}</span>
                </div>

                <!-- Shipping Charge -->
                <div class="flex justify-between items-center text-slate-600">
                    <span class="font-medium">{{ $t('Shipping Charge') }}</span>
                    <span class="font-bold text-slate-900">{{ master.showCurrency(order?.delivery_charge) }}</span>
                </div>

                <!-- Divider -->
                <div class="border-t border-slate-100 my-3"></div>

                <!-- Total Amount -->
                <div class="flex justify-between items-center pt-1">
                    <span class="text-sm sm:text-base font-extrabold text-slate-900">{{ $t('Total Amount') }}</span>
                    <span class="text-lg sm:text-2xl font-black text-primary tracking-tight">{{ master.showCurrency(order?.payable_amount) }}</span>
                </div>
            </div>

            <!-- Payment Method Details Box -->
            <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                <div class="text-2xs uppercase tracking-wider font-extrabold text-slate-400">
                    {{ $t('PAYMENT METHOD') }}
                </div>
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-sm sm:text-base font-bold text-slate-900">{{ order.payment_method }}</span>

                    <span
                        v-if="order.payment_status === 'Pending'"
                        class="inline-flex items-center gap-1.5 text-2xs font-bold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60 uppercase"
                    >
                        <ExclamationTriangleIcon class="w-3.5 h-3.5" />
                        {{ order.payment_status }}
                    </span>

                    <span
                        v-if="order.payment_status === 'Paid'"
                        class="inline-flex items-center gap-1.5 text-2xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 uppercase"
                    >
                        <CheckCircleIcon class="w-3.5 h-3.5" />
                        {{ order.payment_status }}
                    </span>
                </div>
            </div>

            <!-- Documents Download Actions -->
            <div class="mt-5">
                <button
                    type="button"
                    class="w-full p-3 rounded-xl border border-slate-200/80 bg-slate-50/80 hover:bg-slate-100 text-slate-700 hover:text-primary transition-all flex items-center justify-center gap-2 cursor-pointer shadow-2xs group active:scale-98"
                    @click="downloadInvoice"
                >
                    <img :src="'/assets/icons/cloud.svg'" alt="Download" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    <span class="text-xs font-bold">{{ $t('Download') }}</span>
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2">
            <div class="flex gap-2">
                <button
                    v-if="order.order_status === 'Pending'"
                    type="button"
                    class="w-full py-3.5 px-4 bg-white hover:bg-rose-50/80 text-rose-600 border border-rose-200/80 rounded-xl text-sm font-bold transition-all shadow-2xs cursor-pointer active:scale-98"
                    @click="cancelModal = true"
                >
                    {{ $t('Cancel Order') }}
                </button>

                <button
                    v-if="props.order?.payment_status == 'Pending' && props.order?.payment_method == 'Online Payment'"
                    type="button"
                    class="w-full py-3.5 px-4 bg-primary hover:bg-primary/90 text-white rounded-xl text-sm font-bold transition-all shadow-sm cursor-pointer active:scale-98"
                    @click="makePaymentModal = true"
                >
                    {{ $t('Make Payment') }}
                </button>
            </div>

            <button
                v-if="order.order_status === 'Delivered'"
                type="button"
                class="w-full py-3.5 px-4 bg-primary hover:bg-primary/90 text-white rounded-xl text-sm font-bold transition-all shadow-sm cursor-pointer active:scale-98"
                @click="againOrder = true"
            >
                {{ $t('Order Again') }}
            </button>
        </div>

        <!-- Cancel Confirmation Modal -->
        <TransitionRoot as="template" :show="cancelModal">
            <Dialog as="div" class="relative z-50" @close="cancelModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                                <div class="bg-white p-6 sm:p-8 text-center">
                                    <div class="bg-rose-100 text-rose-600 w-16 h-16 rounded-full mx-auto flex justify-center items-center mb-4">
                                        <XMarkIcon class="w-8 h-8" />
                                    </div>

                                    <h3 class="text-xl font-bold text-slate-900 mb-2">
                                        {{ $t('Cancel the Order') }}!
                                    </h3>

                                    <p class="text-sm text-slate-600 mb-6">
                                        {{ $t('Are you sure want to cancel this order') }}?
                                    </p>

                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            class="w-1/2 py-3 px-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors"
                                            @click="cancelModal = false"
                                        >
                                            {{ $t('Cancel') }}
                                        </button>

                                        <button
                                            type="button"
                                            class="w-1/2 py-3 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition-colors shadow-sm cursor-pointer"
                                            @click="cancelOrder"
                                        >
                                            {{ $t('Yes') }}
                                        </button>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <OrderDetailsPaymentAndAgainOrder :order="order" :makePayment="makePaymentModal" :againOrder="againOrder"
            @update:makePayment="makePaymentModal = false" @update:orderAgain="againOrder = false"
            @update:paymentSuccess="orderPaymentSuccess" />
    </div>
</template>

<script setup>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { CheckCircleIcon, ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import { useAuth } from '../stores/AuthStore';
import { useMaster } from '../stores/MasterStore';
import OrderDetailsPaymentAndAgainOrder from './OrderDetailsPaymentAndAgainOrder.vue';

const master = useMaster();
const toast = useToast();
const authStore = useAuth();

const cancelModal = ref(false);
const againOrder = ref(false);
const makePaymentModal = ref(false);
const showPaymentButton = ref(false);

const emit = defineEmits(['update:paymentSuccess']);

const orderPaymentSuccess = () => {
    emit('update:paymentSuccess', true);
}

const props = defineProps({
    order: Object
});

const cancelOrder = () => {
    axios.post('/orders/cancel', {
        order_id: props.order.id
    }, {
        headers: {
            Authorization: authStore.token
        }
    }).then((response) => {
        toast.success(response.data.message, {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
        authStore.orderCancel = true;
    }).catch((err) => {
        toast.error(err.response?.data?.message || 'Error cancelling order');
    });
    cancelModal.value = false;
}

const downloadInvoice = () => {
    if (props.order.invoice_url) {
        window.open(props.order.invoice_url, '_blank');
    } else {
        toast.error('Invoice not found', {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
    }
}

const downloadPaymentSlip = () => {
    if (props.order?.payment_receipt_url) {
        window.open(props.order.payment_receipt_url, '_blank');
    } else {
        toast.error('Payment Slip not found', {
            position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
    }
}
</script>
