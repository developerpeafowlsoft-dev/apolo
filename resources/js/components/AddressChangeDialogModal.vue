<template>
    <TransitionRoot as="template" :show="authStore.showChangeAddressModal">
        <Dialog as="div" class="relative z-[9999]" @close="handleOuterClose">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity z-[9998]" />
            </TransitionChild>
            <div class="fixed inset-0 z-[9999] w-screen overflow-hidden">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all my-auto w-full md:max-w-2xl max-h-[85vh] sm:max-h-[90vh] flex flex-col">
                            <div class="bg-white p-5 sm:p-7 flex flex-col h-full max-h-[85vh] sm:max-h-[90vh]">
                                
                                <!-- Header with Close Button -->
                                <div class="flex justify-between items-center pb-4 border-b border-slate-100 shrink-0">
                                    <DialogTitle class="text-slate-950 text-xl sm:text-2xl font-bold">
                                        {{ $t('Saved Address') }}
                                    </DialogTitle>
                                    <button type="button" class="w-9 h-9 bg-slate-100 rounded-full flex justify-center items-center cursor-pointer hover:bg-slate-200 transition-colors"
                                        @click="authStore.showChangeAddressModal = false">
                                        <XMarkIcon class="w-5 h-5 text-slate-600" />
                                    </button>
                                </div>

                                <!-- Address List (Scrollable) -->
                                <div class="my-4 space-y-4 flex-1 overflow-y-auto pr-1 sm:pr-2">
                                    <label v-for="address in authStore.addresses" :key="address.id" :for="'address' + address.id"
                                        class="p-4 flex gap-4 sm:gap-6 rounded-xl border border-slate-200 has-[:checked]:border-primary cursor-pointer hover:border-slate-300 transition-colors" @click="basketStore.address = address">
                                        <!-- Tag -->
                                        <div
                                            class="flex sm:h-20 w-[60px] sm:w-[88px] bg-slate-50 rounded-lg flex-col gap-2 justify-center items-center shrink-0">
                                            <MapPinIcon class="w-6 h-6 text-primary-600" />
                                            <div
                                                class="px-1.5 py-[3px] bg-slate-800 rounded-md text-white text-xs font-medium uppercase">
                                                {{ address.address_type }}
                                            </div>
                                        </div>
                                        <div class="grow overflow-hidden">
                                            <!-- Name and Actions -->
                                            <div class="flex justify-between items-center mb-1 pr-1">
                                                <div
                                                    class="text-slate-950 text-base sm:text-lg font-bold leading-normal tracking-tight">
                                                    {{ address.name }}
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <!-- Delete Button -->
                                                    <button type="button" 
                                                        class="p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
                                                        title="Delete Address"
                                                        @click.stop.prevent="openDeleteConfirm(address)">
                                                        <TrashIcon class="w-5 h-5 text-red-500" />
                                                    </button>
                                                    <!-- input radio -->
                                                    <input type="radio" name="address" :id="'address' + address.id" value=""
                                                        class="radioBtn2" :checked="basketStore.address?.id == address.id">
                                                </div>
                                            </div>
                                            <!-- Phone -->
                                            <div class="text-slate-500 text-sm sm:text-base font-normal leading-normal">
                                                {{ address.phone }}
                                            </div>
                                            <!-- Address -->
                                            <div class="text-slate-500 text-sm sm:text-base font-normal leading-normal">
                                                <span v-if="address.flat_no">
                                                    {{ address.flat_no }},
                                                </span>
                                                {{ address.address_line }},
                                                {{ address.area }},
                                                {{ address.post_code }},
                                                {{ address.country }}
                                            </div>

                                            <div v-if="address.is_default"
                                                class="text-blue-500 mt-1 text-xs sm:text-sm font-normal leading-[18px] italic">
                                                {{ $t('Default Address') }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <!-- End Address List -->

                                <!-- New Address Button -->
                                <div class="pt-3 border-t border-slate-100 shrink-0">
                                    <button
                                        class="px-6 py-3.5 rounded-xl border border-primary flex justify-center items-center w-full text-primary hover:bg-primary hover:text-white transition-colors gap-2 font-medium"
                                        @click="addressModalShow()">
                                        <PlusIcon class="w-5 h-5" />
                                        <div class="text-base font-medium leading-normal">
                                            {{ $t('New Address') }}
                                        </div>
                                    </button>
                                </div>

                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Delete Confirmation Modal -->
    <TransitionRoot as="template" :show="showDeleteModal">
        <Dialog as="div" class="relative z-[10000]" @close="closeDeleteModal">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity z-[9999]" />
            </TransitionChild>

            <div class="fixed inset-0 z-[10000] w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all my-8 md:my-0 w-full md:w-[480px]">
                            <div class="bg-white p-5 sm:p-8 relative">

                                <div class="w-16 h-16 bg-red-100 rounded-full flex justify-center items-center mx-auto">
                                    <TrashIcon class="w-8 h-8 text-red-500" />
                                </div>

                                <div class="mt-4 text-center text-slate-950 text-xl font-bold">
                                    {{ $t('Delete address') }}
                                </div>

                                <div class="mt-2 text-center text-slate-600 text-base font-normal">
                                    {{ $t('Are you sure want to delete this address') }}?
                                </div>

                                <div class="flex justify-between items-center gap-4 mt-6">
                                    <button
                                        type="button"
                                        class="text-slate-800 grow text-base font-medium px-4 py-3 rounded-xl border border-slate-300 hover:bg-slate-50 transition-colors"
                                        @click.stop.prevent="closeDeleteModal">
                                        {{ $t('Cancel') }}
                                    </button>

                                    <button
                                        type="button"
                                        class="text-white grow bg-red-500 hover:bg-red-600 text-base font-medium px-4 py-3 rounded-xl transition-colors flex items-center justify-center gap-2"
                                        :disabled="isDeleting"
                                        @click.stop.prevent="confirmDelete">
                                        <span>{{ isDeleting ? $t('Deleting...') : $t('Yes, Delete') }}</span>
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { MapPinIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import ToastSuccessMessage from './ToastSuccessMessage.vue';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';

const authStore = useAuth();
const basketStore = useBasketStore();
const masterStore = useMaster();
const toast = useToast();

const showDeleteModal = ref(false);
const addressToDelete = ref(null);
const isDeleting = ref(false);

const handleOuterClose = () => {
    if (showDeleteModal.value) {
        return;
    }
    authStore.showChangeAddressModal = false;
};

const openDeleteConfirm = (address) => {
    addressToDelete.value = address;
    showDeleteModal.value = true;
};

const closeDeleteModal = (e) => {
    if (e && typeof e.stopPropagation === 'function') {
        e.stopPropagation();
    }
    showDeleteModal.value = false;
    addressToDelete.value = null;
};

const toastContent = {
    component: ToastSuccessMessage,
    props: {
        title: 'Address Deleted!',
        message: 'Address deleted successfully',
    },
};

const confirmDelete = async () => {
    if (!addressToDelete.value) return;
    isDeleting.value = true;
    try {
        await authStore.deleteAddress(addressToDelete.value.id);
        toast(toastContent, {
            type: "default",
            hideProgressBar: true,
            icon: false,
            position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            toastClassName: "vue-toastification-alert",
        });
    } catch (err) {
        console.error('Failed to delete address:', err);
    } finally {
        isDeleting.value = false;
        showDeleteModal.value = false;
        addressToDelete.value = null;
    }
};

const addressModalShow = () => {
    authStore.showChangeAddressModal = false;
    authStore.showAddressModal = true;
}

</script>

<style lang="scss" scoped></style>
