<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs overflow-hidden relative">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <!-- Order ID & Icon Badge -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-sm shrink-0">
                    #
                </div>
                <div>
                    <span class="text-2xs uppercase tracking-wider font-bold text-slate-400 block mb-0.5">
                        {{ $t('Order ID') }}
                    </span>
                    <h1 class="text-base sm:text-xl font-extrabold text-slate-900 leading-tight">
                        {{ formattedOrderId }}
                    </h1>
                </div>
            </div>

            <!-- Actions & Status Pill -->
            <div class="flex items-center gap-3">
                <button
                    v-if="order.shop?.id"
                    @click="showChat(order.shop.id)"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-slate-200/80 text-xs sm:text-sm font-semibold transition-all shadow-2xs cursor-pointer"
                    aria-label="Chat with Shop"
                >
                    <img src="/public/assets/icons/shop-chat/chat.svg" alt="Chat" class="w-4 h-4">
                    <span>{{ $t('Chat with Shop') }}</span>
                </button>

                <div
                    class="text-xs sm:text-sm font-bold px-4 py-2 rounded-full inline-flex items-center gap-2 shadow-2xs"
                    :class="order.order_status"
                >
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                    <span>{{ order.order_status }}</span>
                </div>
            </div>
        </div>

        <!-- Dates Timeline Footer Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 text-xs sm:text-sm">
            <div class="flex items-center gap-2.5 text-slate-600">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <CalendarIcon class="w-4 h-4" />
                </div>
                <div>
                    <span class="text-slate-400 text-2xs font-bold uppercase tracking-wider block">{{ $t('Placed on') }}</span>
                    <span class="font-bold text-slate-800">{{ order.placed_at || '-' }}</span>
                </div>
            </div>

            <div v-if="order.estimated_delivery_date" class="flex items-center gap-2.5 text-slate-600">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                    <TruckIcon class="w-4 h-4" />
                </div>
                <div>
                    <span class="text-slate-400 text-2xs font-bold uppercase tracking-wider block">{{ $t('Est. delivery') }}</span>
                    <span class="font-bold text-slate-800">{{ order.estimated_delivery_date }}</span>
                </div>
            </div>
        </div>

        <!-- Chat Sidebar -->
        <RightChatSidebar :show="showSidebar" @close="showSidebar = false" :shop="order?.shop" />
    </div>
</template>

<script setup>
import { CalendarIcon, TruckIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import { useAuth } from '../stores/AuthStore';
import { useMaster } from '../stores/MasterStore';
import RightChatSidebar from './RightChatSidebar.vue';

const props = defineProps({
    order: Object
});

const authStore = useAuth();
const showSidebar = ref(false);

const formattedOrderId = computed(() => {
    const code = props.order?.order_code || props.order?.id || '';
    if (!code) return '';
    return String(code).startsWith('#') ? code : `#${code}`;
});

const showChat = async (sid) => {
    if (!sid) return;
    try {
        await axios.post('/store-message', {
            shop_id: sid,
            user_id: authStore.user?.id,
            type: 'user',
        }, {
            headers: {
                Authorization: authStore.token,
            }
        });
        showSidebar.value = true;
    } catch(e) {
        showSidebar.value = true;
    }
}
</script>

<style scoped>
.Pending {
    @apply bg-amber-50 text-amber-800 border border-amber-200/80;
}

.Confirm {
    @apply bg-blue-50 text-blue-800 border border-blue-200/80;
}

.Processing,
.On,
.Pickup {
    @apply bg-indigo-50 text-indigo-800 border border-indigo-200/80;
}

.Delivered {
    @apply bg-emerald-50 text-emerald-800 border border-emerald-200/80;
}

.Cancelled {
    @apply bg-rose-50 text-rose-800 border border-rose-200/80;
}
</style>
