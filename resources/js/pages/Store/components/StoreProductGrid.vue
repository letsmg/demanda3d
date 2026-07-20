<script setup lang="ts">
import { ShoppingBag, RotateCw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import StoreProductCard from './StoreProductCard.vue';

defineProps<{
    products: any[];
    hasMore: boolean;
    loadingMore: boolean;
    getCartQty: (productId: number) => number;
    getCartItemId: (productId: number) => number | null;
    cartLoading: boolean;
    formatPrice: (value: string | number) => string;
    getImageUrl: (product: any, index?: number) => string | undefined;
}>();

const emit = defineEmits<{
    'open-gallery': [product: any, index: number];
    'open-share': [productName: string];
    'remove-from-cart': [cartItemId: number];
    'add-to-cart': [productId: number];
    'load-more': [];
    'clear-all': [];
}>();

function onOpenGallery(product: any, index: number): void {
    emit('open-gallery', product, index);
}

function onOpenShare(name: string): void {
    emit('open-share', name);
}

function onRemoveFromCart(id: number): void {
    emit('remove-from-cart', id);
}

function onAddToCart(id: number): void {
    emit('add-to-cart', id);
}
</script>

<template>
    <!-- Lista vazia -->
    <div v-if="products.length === 0" class="py-16 text-center">
        <ShoppingBag class="mx-auto h-12 w-12 text-amber-400" />
        <h3 class="mt-2 text-sm font-semibold text-amber-700">
            Nenhum produto disponível no momento.
        </h3>
        <p class="mt-1 text-sm text-amber-500">
            Tente ajustar os filtros ou limpar a busca.
        </p>
        <Button type="button" variant="outline" class="mt-4 border-amber-500 text-amber-700 hover:bg-amber-50" @click="emit('clear-all')">
            <RotateCw class="mr-2 h-4 w-4" />Limpar tudo
        </Button>
    </div>

    <!-- Grid de produtos -->
    <div
        v-else
        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
        <StoreProductCard
            v-for="product in products"
            :key="product.id"
            :product="product"
            :get-cart-qty="getCartQty"
            :get-cart-item-id="getCartItemId"
            :cart-loading="cartLoading"
            :format-price="formatPrice"
            :get-image-url="getImageUrl"
            @open-gallery="onOpenGallery"
            @open-share="onOpenShare"
            @remove-from-cart="onRemoveFromCart"
            @add-to-cart="onAddToCart"
        />
    </div>

    <!-- "Mostrar mais" -->
    <div v-if="hasMore" class="mt-8 flex justify-center pb-8">
        <Button
            type="button"
            variant="outline"
            size="lg"
            class="border-amber-700 text-amber-700 hover:bg-amber-50"
            :disabled="loadingMore"
            @click="emit('load-more')"
        >
            <span v-if="loadingMore" class="flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Carregando...
            </span>
            <span v-else>Mostrar mais</span>
        </Button>
    </div>
</template>