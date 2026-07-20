<script setup lang="ts">
import {
    ChevronDown,
    ExternalLink,
    ImageIcon,
    Minus,
    Plus,
    Star,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    product: any;
    currentImageIndex: number;
    formatPrice: (value: string | number) => string;
    getCartQty: (productId: number) => number;
    getCartItemId: (productId: number) => number | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'prev-image': [];
    'next-image': [];
    'select-image': [index: number];
    'add-to-cart': [productId: number];
    'remove-from-cart': [cartItemId: number];
    close: [];
}>();
</script>

<template>
    <Dialog :open="product !== null" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-3xl">
            <DialogHeader>
                <DialogTitle>{{ product?.name }}</DialogTitle>
                <DialogDescription v-if="product?.description">{{
                    product.description
                }}</DialogDescription>
            </DialogHeader>
            <div v-if="product" class="relative">
                <div class="flex items-center justify-center">
                    <img
                        v-if="
                            product.images && product.images[currentImageIndex]
                        "
                        :src="product.images[currentImageIndex].url"
                        :alt="`${product.name} - Imagem ${currentImageIndex + 1}`"
                        class="max-h-[60vh] rounded-lg object-contain"
                    />
                    <div
                        v-else
                        class="flex h-64 w-full items-center justify-center rounded-lg bg-amber-100"
                    >
                        <ImageIcon class="h-16 w-16 text-amber-900" />
                    </div>
                </div>
                <button
                    v-if="product.images && product.images.length > 1"
                    type="button"
                    class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                    @click="emit('prev-image')"
                >
                    <ChevronDown class="h-5 w-5 rotate-90 text-gray-600" />
                </button>
                <button
                    v-if="product.images && product.images.length > 1"
                    type="button"
                    class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                    @click="emit('next-image')"
                >
                    <ChevronDown class="h-5 w-5 -rotate-90 text-gray-600" />
                </button>
                <div
                    v-if="product.images && product.images.length > 1"
                    class="mt-4 flex justify-center gap-2"
                >
                    <button
                        v-for="(img, idx) in product.images"
                        :key="idx"
                        type="button"
                        class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg border-2 transition"
                        :class="
                            idx === currentImageIndex
                                ? 'border-amber-500'
                                : 'border-transparent opacity-60 hover:opacity-100'
                        "
                        @click="emit('select-image', idx)"
                    >
                        <img
                            :src="img.url"
                            :alt="`${product.name} thumb ${idx + 1}`"
                            class="h-full w-full object-cover"
                        />
                    </button>
                </div>
                <div class="mt-4 space-y-3 rounded-lg bg-amber-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-emerald-700">
                                {{ formatPrice(product.sale_price) }}
                            </p>
                            <p class="text-sm text-amber-600">
                                {{ product.tenant?.fantasy_name || 'Vendedor' }}
                            </p>
                            <div
                                v-if="product.tenant?.rating_count > 0"
                                class="mt-1 flex items-center gap-1"
                            >
                                <Star
                                    class="h-4 w-4 fill-amber-900 text-amber-900"
                                />
                                <span
                                    class="text-sm font-medium text-amber-700"
                                    >{{ product.tenant.rating_average }}</span
                                >
                                <span class="text-sm text-amber-900"
                                    >({{
                                        product.tenant.rating_count
                                    }}
                                    avaliações)</span
                                >
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                v-if="getCartQty(product.id) > 0"
                                class="flex items-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                    @click="
                                        emit(
                                            'remove-from-cart',
                                            getCartItemId(product.id) ?? 0,
                                        )
                                    "
                                >
                                    <Minus class="h-4 w-4" />
                                </button>
                                <span
                                    class="min-w-[1.5rem] text-center text-sm font-bold text-amber-900"
                                    >{{ getCartQty(product.id) }}</span
                                >
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                    @click="emit('add-to-cart', product.id)"
                                >
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>
                            <Button
                                v-else
                                type="button"
                                class="bg-amber-800 text-white hover:bg-amber-900"
                                @click="emit('add-to-cart', product.id)"
                            >
                                Adicionar ao carrinho
                            </Button>
                        </div>
                    </div>
                    <a
                        :href="`/store/${product.slug}`"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-amber-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-900"
                        @click="emit('close')"
                        ><ExternalLink class="h-4 w-4" /> Ver mais detalhes</a
                    >
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
