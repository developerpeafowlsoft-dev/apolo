<template>
    <div class="group relative">
        <button type="button"
            class="p-3 group/btn bg-white hover:bg-slate-50 rounded-xl border border-slate-200/90 hover:border-primary/60 justify-start items-center gap-2 flex flex-col transition-all duration-200 w-full shadow-2xs hover:shadow-md cursor-pointer"
            :class="(props.category?.id == route.params?.slug) ? 'border-primary bg-primary/5' : ''"
            @click="onClick">
            <div class="w-14 h-14 rounded-lg bg-slate-50 p-0.5 flex items-center justify-center overflow-hidden border border-slate-100 group-hover/btn:scale-105 transition-transform duration-200">
                <img :src="props.category?.thumbnail"
                    class="w-full h-full object-cover rounded-md" loading="lazy" />
            </div>
            <div class="text-slate-800 text-xs sm:text-sm font-medium leading-tight flex gap-1 items-center justify-center text-center group-hover/btn:text-primary transition-colors w-full px-0.5">
                <span class="whitespace-normal text-center break-words">{{ props.category?.name }}</span>
                <ChevronDownIcon v-if="props.category?.sub_categories?.length > 0" class="w-3.5 h-3.5 text-slate-400 group-hover/btn:text-primary transition-colors shrink-0" />
            </div>
        </button>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
            >
            <div v-if="props.category?.sub_categories?.length > 0" class="w-60 absolute left-0 z-50 hidden group-hover:block pt-2">
                <div
                    class="p-2 bg-white rounded-xl shadow-xl border border-slate-200 flex flex-col gap-1 transition-all duration-150">
                    <button v-for="subcategory in props.category?.sub_categories" :key="subcategory.id"
                        class="p-2 hover:bg-slate-50 rounded-lg text-slate-700 hover:text-primary text-xs sm:text-sm font-medium flex items-center gap-2.5 transition-colors w-full border border-transparent hover:border-slate-100 group/sub text-left" @click="subcategoryProduct(subcategory)">
                        <img :src="subcategory?.thumbnail"
                            class="w-9 h-9 object-cover rounded-md bg-slate-50 border border-slate-100 group-hover/sub:scale-105 transition-transform duration-200 shrink-0" loading="lazy" />
                        <div class="text-xs sm:text-sm font-medium leading-tight group-hover/sub:font-semibold transition-all">
                            {{ subcategory?.name }}
                        </div>
                    </button>
                </div>
            </div>
        </transition>

    </div>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    category: Object
});

const emit = defineEmits(['update:click']);

const router = useRouter();
const route = useRoute();

const onClick = () => {
    emit('update:click', true);
    router.push('/categories/' + props.category?.id);
}

const subcategoryProduct = (subcategory) => {
    emit('update:click', true);
    router.push('/categories/' + props.category?.id + '?subcategory=' + subcategory?.id);
}

</script>
