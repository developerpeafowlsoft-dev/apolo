<template>
    <div class="bg-slate-950 text-slate-400 py-4 border-t border-slate-900">
        <div class="main-container flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
            <!-- Copyright -->
            <div class="text-center sm:text-left text-slate-400 font-medium">
                © {{ new Date().getFullYear() }} {{ master.footerText || 'APOLO Store. All rights reserved.' }}
            </div>
            
            <div class="flex items-center gap-4 text-slate-500 font-medium">
                <span>Secure Payments</span>
                <span>•</span>
                <span>PAN India Express Shipping</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useMaster } from '../stores/MasterStore';

const master = useMaster();

</script>

