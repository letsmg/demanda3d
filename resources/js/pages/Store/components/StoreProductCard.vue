<script setup lang="ts">
import { ExternalLink, ImageIcon, Minus, Plus, ShoppingBag, Star } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    product: any;
    getCartQty: (productId: number) => number;
    getCartItemId: (productId: number) => number | null;
    cartLoading: boolean;
    formatPrice: (value: string | number) => string;
    getImageUrl: (product: any, index?: number) => string | undefined;
}>();

const emit = defineEmits<{
    'open-gallery': [product: any, index: number];
    'open-share': [productName: string, productSlug: string];
    'remove-from-cart': [cartItemId: number];
    'add-to-cart': [productId: number];
}>();

function onOpenShare(name: string, slug: string): void {
    emit('open-share', name, slug);
}
</script>

<template>
    <Card class="flex flex-col overflow-hidden">
        <div
            class="relative flex h-56 w-full cursor-pointer items-center justify-center overflow-hidden bg-amber-100"
            @click="emit('open-gallery', product, 0)"
        >
            <img
                v-if="getImageUrl(product, 0)"
                :src="getImageUrl(product, 0)"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center"
            >
                <ImageIcon class="h-12 w-12 text-amber-200" />
            </div>

            <button
                type="button"
                class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-amber-700 shadow-sm transition hover:bg-white hover:text-amber-900"
                @click.stop="onOpenShare(product.name, product.slug)"
                :title="'Compartilhar: ' + product.name"
            >
                <ExternalLink class="h-4 w-4" />
            </button>
        </div>

        <div class="relative">
            <div
                v-if="product.images && product.images.length > 1"
                class="absolute right-2 bottom-2 left-2 flex gap-1"
            >
                <button
                    v-for="(img, idx) in product.images.slice(0, 5)"
                    :key="idx"
                    type="button"
                    class="h-10 w-10 flex-shrink-0 overflow-hidden rounded border-2 border-white/80 shadow-sm transition hover:border-blue-500"
                    @click.stop="emit('open-gallery', product, idx)"
                >
                    <img
                        :src="img.url"
                        :alt="`${product.name} ${idx + 1}`"
                        class="h-full w-full object-cover"
                    />
                </button>
            </div>
        </div>

        <CardHeader class="pb-2">
            <div class="flex items-start justify-between">
                <div>
                    <CardTitle class="text-base text-amber-900 cursor-pointer hover:text-amber-600 transition" @click="emit('open-gallery', product, 0)">{{ product.name }}</CardTitle>
                    <p v-if="product.categories && product.categories.length > 0" class="mt-0.5 text-[11px] text-amber-500">
                        {{ product.categories[0].name }}
                    </p>
                </div>
                <div class="flex items-center gap-1">
                    <div v-if="getCartQty(product.id) > 0" class="flex items-center gap-1">
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                            @click="emit('remove-from-cart', getCartItemId(product.id) ?? 0)"
                        >
                            <Minus class="h-3.5 w-3.5" />
                        </button>
                        <span class="min-w-[1.5rem] text-center text-sm font-medium">{{ getCartQty(product.id) }}</span>
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                            @click="emit('add-to-cart', product.id)"
                        >
                            <Plus class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <button
                        v-else
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-amber-900 transition hover:bg-amber-50 hover:text-amber-600"
                        @click="emit('add-to-cart', product.id)"
                        title="Adicionar ao carrinho"
                    >
                        <ShoppingBag class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <CardDescription v-if="product.description" class="line-clamp-2 text-xs">
                {{ product.description }}
            </CardDescription>
        </CardHeader>

        <CardContent class="flex-1 pb-2">
            <div class="space-y-1">
                <p class="text-xl font-bold text-emerald-700">
                    {{ formatPrice(product.sale_price) }}
                </p>
                <p class="text-xs text-amber-600">
                    <a v-if="product.tenant?.fantasy_slug" :href="`/tenant/${product.tenant.fantasy_slug}`" class="hover:text-amber-600 hover:underline">
                        {{ product.tenant.fantasy_name || product.tenant.company_name || 'Vendedor' }}
                    </a>
                    <span v-else>{{ product.tenant?.fantasy_name || product.tenant?.company_name || 'Vendedor' }}</span>
                </p>
                <div v-if="product.tenant?.rating_count > 0" class="flex items-center gap-1">
                    <Star class="h-3.5 w-3.5 fill-amber-900 text-amber-900" />
                    <span class="text-xs font-medium text-amber-700">{{ product.tenant.rating_average }}</span>
                    <span class="text-xs text-amber-900">({{ product.tenant.rating_count }})</span>
                </div>
                <p v-else class="text-xs text-amber-900">sem histórico de vendas</p>
            </div>
        </CardContent>

        <CardFooter class="pt-0">
            <Button
                type="button"
                class="w-full bg-amber-800 text-white hover:bg-amber-900"
                :variant="getCartQty(product.id) > 0 ? 'secondary' : 'default'"
                @click="emit('add-to-cart', product.id)"
                :disabled="cartLoading"
            >
                {{ getCartQty(product.id) > 0 ? 'Adicionar mais' : 'Adicionar' }}
            </Button>
        </CardFooter>
    </Card>
</template>