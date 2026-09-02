<template>
    <div>
        <div class="mt-6 sm:mt-8 flex justify-between items-center gap-2">
            <div class="text-slate-950 text-xl font-medium leading-7">
                {{ $t('Shipping Address') }}
            </div>

            <button v-if="authStore.addresses.length > 0" class="text-slate-950 text-base font-normal leading-normal"
                @click="authStore.showChangeAddressModal = true">
                {{ $t('Change') }}
            </button>
        </div>

        <!-- Shipping Address form -->
        <Transition leave-active-class="transition ease-in duration-300"
            enter-active-class="transition ease-out duration-300" enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="authStore.addresses.length == 0">
                <address-form />
            </div>
        </Transition>

        <!-- Selected Address -->
        <div v-if="authStore.addresses.length > 0"
            class="mt-4 p-4 flex gap-6 rounded-lg border border-slate-200 w-full">
            <div
                class="flex w-[60px] sm:w-[88px] bg-slate-50 rounded-lg flex-col gap-2 justify-center items-center shrink-0">
                <MapPinIcon class="w-6 h-6 text-primary-600" />
                <div class="px-1.5 py-[3px] bg-slate-800 rounded-md text-white text-xs font-medium uppercase">
                    {{ basketStore.address?.address_type }}
                </div>
            </div>
            <div class="overflow-hidden">
                <div class="text-slate-950 text-lg font-medium leading-normal tracking-tight">
                    {{ basketStore.address?.name }}
                </div>
                <div class="text-slate-500 text-base font-normal leading-normal">
                    {{ basketStore.address?.phone }}
                </div>
<!--                <div class="text-slate-500 text-base font-normal leading-normal truncate">-->
<!--                    {{ (basketStore.address?.flat_no ? basketStore.address?.flat_no + ', ' : '') + basketStore.address?.address_line + ', ' +-->
<!--                (basketStore.address?.address_line2 ? basketStore.address?.address_line2 + ', ' : '') }} {{ basketStore.address?.area + '-' +-->
<!--                basketStore.address?.post_code }}-->
<!--                </div>-->
              <div class="text-slate-500 text-base font-normal leading-normal">
    <span v-if="basketStore.address?.flat_no">
        {{ basketStore.address.flat_no }},
    </span>

                {{ basketStore.address?.address_line }},
                {{ basketStore.address?.area }},
                {{ basketStore.address?.post_code }},
                {{ basketStore.address?.country }}
              </div>
            </div>
        </div>

        <!-- Change Address Dialog Modal -->
        <AddressChangeDialogModal />
        <!-- End Change Address Dialog Modal -->

        <!-- new Address Dialog Modal -->
        <AddressFormModal />
        <!-- End new Address Dialog Modal -->

    </div>
</template>

<!--<script setup>-->
<!--import { onMounted, ref } from 'vue';-->

<!--import { MapPinIcon } from '@heroicons/vue/24/solid';-->
<!--import AddressChangeDialogModal from './AddressChangeDialogModal.vue';-->
<!--import AddressForm from './AddressForm.vue';-->
<!--import AddressFormModal from './AddressFormModal.vue';-->

<!--import { useAuth } from '../stores/AuthStore';-->
<!--import { useBasketStore } from '../stores/BasketStore';-->

<!--const basketStore = useBasketStore();-->

<!--const changeAddress = ref(false);-->

<!--const authStore = useAuth();-->

<!--onMounted(() => {-->
<!--    authStore.fetchAddresses()-->
<!--    fetchADefaultAddress()-->
<!--})-->

<!--const fetchADefaultAddress = () => {-->
<!--    if (!basketStore.address) {-->
<!--        authStore.addresses.forEach((address) => {-->
<!--            if (address.is_default) {-->
<!--                basketStore.address = address-->
<!--                return true;-->
<!--            }-->
<!--        })-->
<!--    }-->
<!--}-->

<!--</script>-->
<script setup>
import { onMounted, watch } from 'vue';

import { MapPinIcon } from '@heroicons/vue/24/solid';
import AddressChangeDialogModal from './AddressChangeDialogModal.vue';
import AddressForm from './AddressForm.vue';
import AddressFormModal from './AddressFormModal.vue';

import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';

const basketStore = useBasketStore();
const authStore = useAuth();

const emit = defineEmits([
  'address-selected',
]);

const selectDefaultAddress = () => {
  if (basketStore.address) {
    emit('address-selected', basketStore.address);
    return;
  }

  const defaultAddress = authStore.addresses.find(
      address => address.is_default
  );

  const selectedAddress =
      defaultAddress ||
      authStore.addresses[0] ||
      null;

  if (selectedAddress) {
    basketStore.address = selectedAddress;
    emit('address-selected', selectedAddress);
  }
};

onMounted(async () => {
  await authStore.fetchAddresses();
  selectDefaultAddress();

  // Address available hai lekin manually select nahi hua,
  // to page open hote hi change-address popup open kar do.
  if (
      authStore.addresses.length > 0 &&
      !basketStore.address
  ) {
    authStore.showChangeAddressModal = true;
  }
});

/*
|--------------------------------------------------------------------------
| Selected address change watch
|--------------------------------------------------------------------------
| AddressChangeDialogModal ya AddressFormModal basketStore.address update kare,
| tab parent BuyNow.vue ko event milega.
*/

watch(
    () => basketStore.address?.id,
    () => {
      if (basketStore.address) {
        emit('address-selected', basketStore.address);
      }
    }
);
</script>