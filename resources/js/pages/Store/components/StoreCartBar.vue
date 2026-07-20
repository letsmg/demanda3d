<script setup lang="ts">
import { ShoppingBag } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    isMounted: boolean;
    isAuthenticated: boolean;
    cartCount: number;
    cartTotal: string;
}>();

const emit = defineEmits<{
    'clear-cart': [];
}>();
</script>

<template>
    <div
        v-if="isMounted && isAuthenticated && cartCount > 0"
        class="fixed right-0 bottom-0 left-0 z-40 border-t border-amber-200 bg-white shadow-lg"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <ShoppingBag class="h-5 w-5 text-amber-500" />
                <span class="text-sm text-amber-600">
                    <strong class="text-amber-900">{{ cartCount }}</strong> item(ns)
                </span>
                <span class="text-lg font-bold text-amber-900">{{ cartTotal }}</span>
            </div>
            <Button type="button" variant="ghost" size="sm" @click="emit('clear-cart')">Limpar</Button>
        </div>
    </div>
</template>