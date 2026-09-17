<template>
    <div class="bg-[#070a12] text-slate-400 py-5 border-t border-slate-800/80">
        <div class="main-container flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
            <!-- Copyright & Brand -->
            <div class="text-center sm:text-left text-slate-400 font-medium flex items-center gap-1.5 flex-wrap justify-center sm:justify-start">
                <span>© {{ new Date().getFullYear() }}</span>
                <span class="text-white font-bold tracking-tight">{{ master.appName || 'APOLO' }}</span>
                <span class="text-slate-600">•</span>
                <span>{{ master.footerText || $t('All rights reserved.') }}</span>
            </div>
            
            <!-- Reassurance & Payment Info -->
            <div class="flex items-center gap-3 text-slate-400 font-medium flex-wrap justify-center text-[11px] sm:text-xs">
                <div class="flex items-center gap-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-xs"></span>
                    <span class="font-semibold">{{ $t('100% Secure Payments') }}</span>
                </div>
                <span class="text-slate-700">•</span>
                <span>{{ $t('PAN India Express Delivery') }}</span>
                <span class="text-slate-700">•</span>
                <span>{{ $t('Easy COD') }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useMaster } from '../stores/MasterStore';

const master = useMaster();
</script>
