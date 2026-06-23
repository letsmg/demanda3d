<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { ShoppingBag, Search, X, Plus, Minus, ChevronDown, ImageIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({
    layout: WelcomeLayout,
});

const props = defineProps<{
    products: any[];
    filters: {
        search?: string;
        min_price?: number;
        max_price?: number;
        sort?: string;
        sort_dir?: string;
    };
}>();

const page = usePage();
const auth = computed(() => page.props.auth);
const authClient = computed(() => (page.props as any).auth_client);

// Search & filter state
const search = ref(props.filters.search || '');
const minPrice = ref(props.filters.min_price?.toString() || '');
const maxPrice = ref(props.filters.max_price?.toString() || '');
const sort = ref(props.filters.sort || 'name');
const sortDir = ref(props.filters.sort_dir || 'asc');

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Watch for search changes with debounce (minimum 3 chars)
watch(search, (newVal) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    if (newVal.length >= 3 || newVal.length === 0) {
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 400);
    }
});

// Cart state (local storage to persist)
const cart = ref<Record<number, number>>(loadCart());

function loadCart(): Record<number, number> {
    try {
        const saved = localStorage.getItem('demanda3d_cart');
        return saved ? JSON.parse(saved) : {};
    } catch {
        return {};
    }
}

function saveCart(): void {
    localStorage.setItem('demanda3d_cart', JSON.stringify(cart.value));
}

function addToCart(productId: number): void {
    if (!authClient.value?.user) {
        window.location.href = '/login_cli';
        return;
    }
    if (!cart.value[productId]) {
        cart.value[productId] = 1;
    } else {
        cart.value[productId]++;
    }
    saveCart();
}

function removeFromCart(productId: number): void {
    if (cart.value[productId]) {
        cart.value[productId]--;
        if (cart.value[productId] <= 0) {
            delete cart.value[productId];
        }
        saveCart();
    }
}

function getCartQty(productId: number): number {
    return cart.value[productId] || 0;
}

function cartTotal(): number {
    let total = 0;
    for (const product of props.products) {
        const qty = cart.value[product.id] || 0;
        if (qty > 0) {
            total += Number(product.price_sale) * qty;
        }
    }
    return total;
}

function cartCount(): number {
    const quantities = Object.values(cart.value) as number[];
    return quantities.reduce((acc: number, qty: number) => acc + qty, 0);
}

function clearCart(): void {
    cart.value = {};
    saveCart();
}

// Image gallery state
const selectedProduct = ref<any>(null);
const currentImageIndex = ref(0);

function openGallery(product: any, index: number = 0): void {
    selectedProduct.value = product;
    currentImageIndex.value = index;
}

function closeGallery(): void {
    selectedProduct.value = null;
    currentImageIndex.value = 0;
}

function prevImage(): void {
    if (!selectedProduct.value?.images?.length) {
        return;
    }
    currentImageIndex.value =
        (currentImageIndex.value - 1 + selectedProduct.value.images.length) %
        selectedProduct.value.images.length;
}

function nextImage(): void {
    if (!selectedProduct.value?.images?.length) {
        return;
    }
    currentImageIndex.value =
        (currentImageIndex.value + 1) % selectedProduct.value.images.length;
}

function applyFilters(): void {
    const params: Record<string, any> = {};
    if (search.value) {
        params.search = search.value;
    }
    if (minPrice.value) {
        params.min_price = minPrice.value;
    }
    if (maxPrice.value) {
        params.max_price = maxPrice.value;
    }
    params.sort = sort.value;
    params.sort_dir = sortDir.value;

    router.get('/store', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters(): void {
    search.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    sort.value = 'name';
    sortDir.value = 'asc';
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return (
        search.value ||
        minPrice.value ||
        maxPrice.value ||
        sort.value !== 'name' ||
        sortDir.value !== 'asc'
    );
});

// Sort options
const sortOptions = [
    { value: 'name_asc', label: 'Nome A-Z' },
    { value: 'name_desc', label: 'Nome Z-A' },
    { value: 'price_sale_asc', label: 'Menor Preço' },
    { value: 'price_sale_desc', label: 'Maior Preço' },
    { value: 'created_at_desc', label: 'Mais Recentes' },
    { value: 'created_at_asc', label: 'Mais Antigos' },
];

function onSortChange(value: string): void {
    const [field, dir] = value.split('_');
    sort.value = field;
    sortDir.value = dir;
    applyFilters();
}

function getCurrentSortValue(): string {
    return `${sort.value}_${sortDir.value}`;
}

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
}

function calcCashPrice(price: string | number, discount: string | number): number {
    const p = Number(price);
    const d = Number(discount);
    return p - (p * d) / 100;
}

const getImageUrl = (product: any, index: number = 0): string | undefined => {
    if (product.images && product.images.length > 0 && product.images[index]) {
        return product.images[index].url;
    }
    return undefined;
};
</script>

<template>
    <Head title="Loja - Demanda3D">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-gray-50">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Hero / Title -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Loja de Produtos</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Produtos disponíveis para impressão 3D de todos os nossos produtores parceiros
                </p>
            </div>

            <!-- Search & Filters -->
            <div class="mb-8 space-y-4">
                <!-- Search bar -->
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Buscar produtos por nome ou descrição..."
                        class="w-full pl-10 pr-10"
                    />
                    <button
                        v-if="search"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        @click="search = ''; applyFilters()"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <p v-if="search.length > 0 && search.length < 3" class="text-xs text-muted-foreground">
                    Digite pelo menos 3 caracteres para buscar
                </p>

                <!-- Filter row -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Preço:</label>
                        <Input
                            v-model="minPrice"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Mín"
                            class="w-24"
                            @change="applyFilters"
                        />
                        <span class="text-gray-400">-</span>
                        <Input
                            v-model="maxPrice"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Máx"
                            class="w-24"
                            @change="applyFilters"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Ordenar:</label>
                        <Select :model-value="getCurrentSortValue()" @update:model-value="onSortChange">
                            <SelectTrigger class="w-44">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        class="text-gray-500"
                        @click="clearFilters"
                    >
                        <X class="mr-1 h-4 w-4" />
                        Limpar filtros
                    </Button>
                </div>
            </div>

            <!-- Product Grid -->
            <div v-if="products.length === 0" class="py-16 text-center">
                <ShoppingBag class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900">Nenhum produto encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Tente ajustar os filtros ou buscar por outros termos.
                </p>
                <Button variant="outline" class="mt-4" @click="clearFilters">
                    Limpar filtros
                </Button>
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="product in products" :key="product.id" class="flex flex-col overflow-hidden">
                    <!-- Image Gallery Carousel -->
                    <div class="relative">
                        <div
                            class="flex h-56 w-full cursor-pointer items-center justify-center overflow-hidden bg-gray-100"
                            @click="openGallery(product, 0)"
                        >
                            <img
                                v-if="getImageUrl(product, 0)"
                                :src="getImageUrl(product, 0)"
                                :alt="product.name"
                                class="h-full w-full object-cover transition-transform hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center">
                                <ImageIcon class="h-12 w-12 text-gray-300" />
                            </div>
                        </div>

                        <!-- Thumbnail strip if multiple images -->
                        <div
                            v-if="product.images && product.images.length > 1"
                            class="absolute bottom-2 left-2 right-2 flex gap-1"
                        >
                            <button
                                v-for="(img, idx) in product.images.slice(0, 5)"
                                :key="idx"
                                class="h-10 w-10 flex-shrink-0 overflow-hidden rounded border-2 border-white/80 shadow-sm transition hover:border-blue-500"
                                @click.stop="openGallery(product, idx)"
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
                                <CardTitle class="text-base">{{ product.name }}</CardTitle>
                                <p v-if="product.tenant?.display_name" class="mt-0.5 text-xs text-gray-400">
                                    {{ product.tenant.display_name }}
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <!-- Quantity controls -->
                                <div v-if="getCartQty(product.id) > 0" class="flex items-center gap-1">
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-gray-200"
                                        @click="removeFromCart(product.id)"
                                    >
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span class="min-w-[1.5rem] text-center text-sm font-medium">
                                        {{ getCartQty(product.id) }}
                                    </span>
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-blue-600 transition hover:bg-blue-200"
                                        @click="addToCart(product.id)"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <button
                                    v-else
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-blue-50 hover:text-blue-600"
                                    @click="addToCart(product.id)"
                                    :title="authClient?.user ? 'Adicionar ao carrinho' : 'Faça login para comprar'"
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
                            <p class="text-xl font-bold text-gray-900">{{ formatPrice(product.price_sale) }}</p>
                            <p v-if="Number(product.discount_cash) > 0" class="text-xs text-green-600">
                                À vista: {{ formatPrice(calcCashPrice(product.price_sale, product.discount_cash)) }}
                                ({{ product.discount_cash }}% off)
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="pt-0">
                        <Button
                            class="w-full"
                            :variant="getCartQty(product.id) > 0 ? 'secondary' : 'default'"
                            @click="addToCart(product.id)"
                        >
                            {{ getCartQty(product.id) > 0 ? 'Adicionar mais' : 'Adicionar' }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </main>

        <!-- Image Gallery Dialog -->
        <Dialog :open="selectedProduct !== null" @update:open="closeGallery">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{{ selectedProduct?.name }}</DialogTitle>
                    <DialogDescription v-if="selectedProduct?.description">
                        {{ selectedProduct.description }}
                    </DialogDescription>
                </DialogHeader>

                <div class="relative" v-if="selectedProduct">
                    <div class="flex items-center justify-center">
                        <img
                            v-if="selectedProduct.images && selectedProduct.images[currentImageIndex]"
                            :src="selectedProduct.images[currentImageIndex].url"
                            :alt="`${selectedProduct.name} - Imagem ${currentImageIndex + 1}`"
                            class="max-h-[60vh] rounded-lg object-contain"
                        />
                        <div v-else class="flex h-64 w-full items-center justify-center bg-gray-100 rounded-lg">
                            <ImageIcon class="h-16 w-16 text-gray-300" />
                        </div>
                    </div>

                    <button
                        v-if="selectedProduct.images && selectedProduct.images.length > 1"
                        class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                        @click="prevImage"
                    >
                        <ChevronDown class="h-5 w-5 rotate-90 text-gray-600" />
                    </button>
                    <button
                        v-if="selectedProduct.images && selectedProduct.images.length > 1"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                        @click="nextImage"
                    >
                        <ChevronDown class="h-5 w-5 -rotate-90 text-gray-600" />
                    </button>

                    <div
                        v-if="selectedProduct.images && selectedProduct.images.length > 1"
                        class="mt-4 flex justify-center gap-2"
                    >
                        <button
                            v-for="(img, idx) in selectedProduct.images"
                            :key="idx"
                            class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="idx === currentImageIndex ? 'border-blue-500' : 'border-transparent opacity-60 hover:opacity-100'"
                            @click="currentImageIndex = idx"
                        >
                            <img
                                :src="img.url"
                                :alt="`${selectedProduct.name} thumb ${idx + 1}`"
                                class="h-full w-full object-cover"
                            />
                        </button>
                    </div>

                    <div class="mt-4 flex items-center justify-between rounded-lg bg-gray-50 p-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatPrice(selectedProduct.price_sale) }}
                            </p>
                            <p v-if="Number(selectedProduct.discount_cash) > 0" class="text-sm text-green-600">
                                À vista: {{ formatPrice(calcCashPrice(selectedProduct.price_sale, selectedProduct.discount_cash)) }}
                                ({{ selectedProduct.discount_cash }}% off)
                            </p>
                        </div>
                        <Button @click="addToCart(selectedProduct.id)">
                            {{ getCartQty(selectedProduct.id) > 0 ? `Adicionar mais (${getCartQty(selectedProduct.id)})` : 'Adicionar ao carrinho' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Cart Summary Floating Bar -->
        <div v-if="cartCount() > 0" class="fixed bottom-0 left-0 right-0 z-40 border-t bg-white shadow-lg">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <ShoppingBag class="h-5 w-5 text-blue-600" />
                    <span class="text-sm text-gray-600">
                        <strong class="text-gray-900">{{ cartCount() }}</strong> item(ns) no carrinho
                    </span>
                    <span class="text-lg font-bold text-gray-900">{{ formatPrice(cartTotal()) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="ghost" size="sm" @click="clearCart">
                        Limpar
                    </Button>
                    <Button variant="default" size="sm" as-child>
                        <Link :href="'/login_cli'">Finalizar Pedido</Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>