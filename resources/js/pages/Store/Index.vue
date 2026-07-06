<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import {
    ShoppingBag,
    Search,
    X,
    Plus,
    Minus,
    ChevronDown,
    ExternalLink,
    ImageIcon,
    Star,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { setCartCount } from '@/stores/cartStore';
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
    categories: Array<{ slug: string; name: string }>;
    filters: {
        search?: string;
        min_price?: number;
        max_price?: number;
        sort?: string;
        sort_dir?: string;
        category?: string;
    };
}>();

const activeCategory = ref(props.filters.category || '');

const page = usePage();
const authClient = computed(() => (page.props as any).auth_client?.user);

// ============================================================
// Lazy loading state ("Mostrar mais")
// ============================================================
const products = ref<any[]>([...props.products]);
const hasMoreProducts = ref(props.products.length >= 8);
const currentPage = ref(1);
const loadingMore = ref(false);

function resetProducts(): void {
    products.value = [...props.products];
    hasMoreProducts.value = props.products.length >= 8;
    currentPage.value = 1;
}

async function loadMore(): Promise<void> {
    if (loadingMore.value || !hasMoreProducts.value) return;

    loadingMore.value = true;
    const nextPage = currentPage.value + 1;

    try {
        const params = new URLSearchParams();
        params.set('page', String(nextPage));
        if (search.value) params.set('search', search.value);
        if (minPrice.value) params.set('min_price', minPrice.value);
        if (maxPrice.value) params.set('max_price', maxPrice.value);
        if (activeCategory.value) params.set('category', activeCategory.value);
        params.set('sort', storeSort.value);
        params.set('sort_dir', storeSortDir.value);

        const res = await fetch(`/api/store/products?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });

        if (res.ok) {
            const json = await res.json();
            products.value.push(...json.data);
            hasMoreProducts.value = json.has_more;
            currentPage.value = nextPage;
        }
    } finally {
        loadingMore.value = false;
    }
}

// Search & filter state
const search = ref(props.filters.search || '');
const minPrice = ref(props.filters.min_price?.toString() || '');
const maxPrice = ref(props.filters.max_price?.toString() || '');
const  storeSort = ref(props.filters.sort || 'name');
const  storeSortDir = ref(props.filters.sort_dir || 'asc');

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Watch for prop changes (Inertia re-navigation on filter change)
watch(
    () => props.products,
    (newProducts) => {
        products.value = [...newProducts];
        hasMoreProducts.value = newProducts.length >= 8;
        currentPage.value = 1;
    }
);

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

// Cart state (via backend API)
const cartItems = ref<any[]>([]);
const cartTotal = ref(0);
const cartCount = ref(0);
const cartLoading = ref(false);

async function fetchCart() {
    if (!authClient.value) return;
    try {
        const res = await fetch('/cart/items', { credentials: 'include' });
        if (res.ok) {
            const data = await res.json();
            cartItems.value = data.items || [];
            cartTotal.value = data.total || 0;
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } catch {
        // ignore
    }
}

async function addToCart(productId: number) {
    if (!authClient.value) {
        window.location.href = '/login_cli';
        return;
    }
    cartLoading.value = true;
    try {
        const res = await fetch('/cart', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        });
        if (res.ok) {
            const data = await res.json();
            cartItems.value = data.items || [];
            cartTotal.value = data.total || 0;
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } finally {
        cartLoading.value = false;
    }
}

async function removeFromCart(cartItemId: number) {
    const item = cartItems.value.find((i) => i.id === cartItemId);
    if (!item) return;
    if (item.quantity <= 1) {
        await removeCartItem(cartItemId);
        return;
    }
    try {
        const res = await fetch('/cart/' + cartItemId, {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ quantity: item.quantity - 1 }),
        });
        if (res.ok) {
            const data = await res.json();
            cartItems.value = data.items || [];
            cartTotal.value = data.total || 0;
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } catch {
        // ignore
    }
}

async function removeCartItem(cartItemId: number) {
    try {
        const res = await fetch('/cart/' + cartItemId, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
        if (res.ok) {
            const data = await res.json();
            cartItems.value = data.items || [];
            cartTotal.value = data.total || 0;
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } catch {
        // ignore
    }
}

async function clearCart() {
    try {
        const res = await fetch('/cart/clear', {
            method: 'POST',
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
        if (res.ok) {
            cartItems.value = [];
            cartTotal.value = 0;
            cartCount.value = 0;
            setCartCount(0);
        }
    } catch {
        // ignore
    }
}

function getCartQty(productId: number): number {
    const item = cartItems.value.find((i) => i.product_id === productId);
    return item ? item.quantity : 0;
}

function getCartItemId(productId: number): number | null {
    const item = cartItems.value.find((i) => i.product_id === productId);
    return item ? item.id : null;
}

function csrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta as HTMLMetaElement).content : '';
}

onMounted(() => {
    fetchCart();
});

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
    if (activeCategory.value) {
        params.category = activeCategory.value;
    }
    params.sort = storeSort.value;
    params.sort_dir = storeSortDir.value;

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
    storeSort.value = 'name';
    storeSortDir.value = 'asc';
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return (
        search.value ||
        minPrice.value ||
        maxPrice.value ||
        storeSort.value !== 'name' ||
        storeSortDir.value !== 'asc'
    );
});

const sortOptions = [
    { value: 'name_asc', label: 'Nome A-Z' },
    { value: 'name_desc', label: 'Nome Z-A' },
    { value: 'sale_price_asc', label: 'Menor Preço' },
    { value: 'sale_price_desc', label: 'Maior Preço' },
    { value: 'created_at_desc', label: 'Mais Recentes' },
    { value: 'created_at_asc', label: 'Mais Antigos' },
];

function onSortChange(value: string): void {
    const [field, dir] = value.split('_');
    storeSort.value = field;
    storeSortDir.value = dir;
    applyFilters();
}

function getCurrentSortValue(): string {
    return `${storeSort.value}_${storeSortDir.value}`;
}

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
}

function calcCashPrice(
    price: string | number,
    discount: string | number,
): number {
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

    <div class="min-h-screen bg-amber-50">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-amber-900">
                    Loja de Produtos
                </h1>
                <p class="mt-1 text-sm text-amber-600">
                    Produtos disponíveis para impressão 3D de todos os nossos
                    produtores parceiros
                </p>
            </div>

            <div class="mb-8 space-y-4">
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-amber-400"
                    />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Buscar produtos por nome ou descrição..."
                        class="w-full border-amber-300 bg-white! pr-10 pl-10 text-amber-900! placeholder:text-amber-800! focus:border-amber-500 focus:ring-amber-500"
                    />
                    <button
                        v-if="search"
                        class="absolute top-1/2 right-3 -translate-y-1/2 text-amber-400 hover:text-amber-600"
                        @click="
                            search = '';
                            applyFilters();
                        "
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <p
                    v-if="search.length > 0 && search.length < 3"
                    class="text-xs text-muted-foreground"
                >
                    Digite pelo menos 3 caracteres para buscar
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-amber-700">Preço:</label>
                        <Input
                            v-model="minPrice"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Mín"
                            class="w-24 border-amber-300 bg-white! text-amber-900! placeholder:text-amber-800!"
                            @change="applyFilters"
                        />
                        <span class="text-amber-600">-</span>
                        <Input
                            v-model="maxPrice"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Máx"
                            class="w-24 border-amber-300 bg-white! text-amber-900! placeholder:text-amber-800!"
                            @change="applyFilters"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-amber-700">Ordenar:</label>
                        <Select
                            :model-value="getCurrentSortValue()"
                            @update:model-value="onSortChange"
                        >
                            <SelectTrigger
                                class="w-44 border-amber-300 bg-white! text-amber-800 placeholder:text-amber-800!"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in sortOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        class="text-amber-600"
                        @click="clearFilters"
                    >
                        <X class="mr-1 h-4 w-4" />Limpar filtros
                    </Button>
                </div>

                <!-- Categorias -->
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-sm font-medium text-amber-700">Categorias:</label>
                    <button
                        class="rounded-full border px-3 py-1 text-xs font-medium transition"
                        :class="!activeCategory ? 'border-amber-500 bg-amber-100 text-amber-800' : 'border-amber-200 text-amber-600 hover:border-amber-400'"
                        @click="activeCategory = ''; applyFilters()"
                    >
                        Todas
                    </button>
                    <button
                        v-for="cat in props.categories"
                        :key="cat.slug"
                        class="rounded-full border px-3 py-1 text-xs font-medium transition"
                        :class="activeCategory === cat.slug ? 'border-amber-500 bg-amber-100 text-amber-800' : 'border-amber-200 text-amber-600 hover:border-amber-400'"
                        @click="activeCategory = cat.slug; applyFilters()"
                    >
                        {{ cat.name }}
                    </button>
                </div>
            </div>

            <div v-if="products.length === 0" class="py-16 text-center">
                <ShoppingBag class="mx-auto h-12 w-12 text-amber-300" />
                <h3 class="mt-2 text-sm font-semibold text-amber-800">
                    Nenhum produto encontrado
                </h3>
                <p class="mt-1 text-sm text-amber-600">
                    Tente ajustar os filtros ou buscar por outros termos.
                </p>
                <Button variant="outline" class="mt-4" @click="clearFilters"
                    >Limpar filtros</Button
                >
            </div>

            <div
                v-else
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <Card
                    v-for="product in products"
                    :key="product.id"
                    class="flex flex-col overflow-hidden"
                >
                    <div
                        class="relative flex h-56 w-full cursor-pointer items-center justify-center overflow-hidden bg-amber-100"
                        @click="openGallery(product, 0)"
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
                    </div>
                    <div class="relative">
                        <div
                            v-if="product.images && product.images.length > 1"
                            class="absolute right-2 bottom-2 left-2 flex gap-1"
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
                                <CardTitle class="text-base text-amber-900">{{
                                    product.name
                                }}</CardTitle>
                                <p
                                    v-if="product.tenant?.display_name"
                                    class="mt-0.5 text-xs text-amber-400"
                                >
                                    {{ product.tenant.display_name }}
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <div
                                    v-if="getCartQty(product.id) > 0"
                                    class="flex items-center gap-1"
                                >
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                        @click="
                                            removeFromCart(
                                                getCartItemId(product.id)!,
                                            )
                                        "
                                    >
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span
                                        class="min-w-[1.5rem] text-center text-sm font-medium"
                                        >{{ getCartQty(product.id) }}</span
                                    >
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                        @click="addToCart(product.id)"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <button
                                    v-else
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-amber-400 transition hover:bg-amber-50 hover:text-amber-600"
                                    @click="addToCart(product.id)"
                                    :title="
                                        authClient
                                            ? 'Adicionar ao carrinho'
                                            : 'Faça login para comprar'
                                    "
                                >
                                    <ShoppingBag class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <CardDescription
                            v-if="product.description"
                            class="line-clamp-2 text-xs"
                        >
                            {{ product.description }}
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="flex-1 pb-2">
                        <div class="space-y-1">
                            <p class="text-xl font-bold text-emerald-700">
                                {{ formatPrice(product.sale_price) }}
                            </p>
                            <p class="text-xs text-amber-600">
                                {{
                                    product.tenant?.user?.display_name ||
                                    product.tenant?.user
                                        ?.first_name_encrypted ||
                                    'Vendedor'
                                }}
                            </p>
                            <div
                                v-if="product.tenant?.rating_count > 0"
                                class="flex items-center gap-1"
                            >
                                <Star
                                    class="h-3.5 w-3.5 fill-amber-400 text-amber-400"
                                />
                                <span
                                    class="text-xs font-medium text-amber-700"
                                    >{{ product.tenant.rating_average }}</span
                                >
                                <span class="text-xs text-amber-400"
                                    >({{ product.tenant.rating_count }})</span
                                >
                            </div>
                            <p v-else class="text-xs text-amber-400">
                                sem histórico de vendas
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="pt-0">
                        <Button
                            class="w-full"
                            :variant="
                                getCartQty(product.id) > 0
                                    ? 'secondary'
                                    : 'default'
                            "
                            @click="addToCart(product.id)"
                            :disabled="cartLoading"
                        >
                            {{
                                getCartQty(product.id) > 0
                                    ? 'Adicionar mais'
                                    : 'Adicionar'
                            }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- "Mostrar mais" button -->
            <div
                v-if="hasMoreProducts"
                class="mt-8 flex justify-center pb-8"
            >
                <Button
                    variant="outline"
                    size="lg"
                    class="border-amber-300 text-amber-700 hover:bg-amber-50"
                    :disabled="loadingMore"
                    @click="loadMore"
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
        </main>

        <!-- Image Gallery Dialog -->
        <Dialog :open="selectedProduct !== null" @update:open="closeGallery">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{{ selectedProduct?.name }}</DialogTitle>
                    <DialogDescription v-if="selectedProduct?.description">{{
                        selectedProduct.description
                    }}</DialogDescription>
                </DialogHeader>
                <div class="relative" v-if="selectedProduct">
                    <div class="flex items-center justify-center">
                        <img
                            v-if="
                                selectedProduct.images &&
                                selectedProduct.images[currentImageIndex]
                            "
                            :src="selectedProduct.images[currentImageIndex].url"
                            :alt="`${selectedProduct.name} - Imagem ${currentImageIndex + 1}`"
                            class="max-h-[60vh] rounded-lg object-contain"
                        />
                        <div
                            v-else
                            class="flex h-64 w-full items-center justify-center rounded-lg bg-amber-100"
                        >
                            <ImageIcon class="h-16 w-16 text-amber-300" />
                        </div>
                    </div>
                    <button
                        v-if="
                            selectedProduct.images &&
                            selectedProduct.images.length > 1
                        "
                        class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                        @click="prevImage"
                    >
                        <ChevronDown class="h-5 w-5 rotate-90 text-gray-600" />
                    </button>
                    <button
                        v-if="
                            selectedProduct.images &&
                            selectedProduct.images.length > 1
                        "
                        class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                        @click="nextImage"
                    >
                        <ChevronDown class="h-5 w-5 -rotate-90 text-gray-600" />
                    </button>
                    <div
                        v-if="
                            selectedProduct.images &&
                            selectedProduct.images.length > 1
                        "
                        class="mt-4 flex justify-center gap-2"
                    >
                        <button
                            v-for="(img, idx) in selectedProduct.images"
                            :key="idx"
                            class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="
                                idx === currentImageIndex
                                    ? 'border-amber-500'
                                    : 'border-transparent opacity-60 hover:opacity-100'
                            "
                            @click="currentImageIndex = idx"
                        >
                            <img
                                :src="img.url"
                                :alt="`${selectedProduct.name} thumb ${idx + 1}`"
                                class="h-full w-full object-cover"
                            />
                        </button>
                    </div>
                    <div class="mt-4 space-y-3 rounded-lg bg-amber-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-emerald-700">
                                    {{
                                        formatPrice(selectedProduct.sale_price)
                                    }}
                                </p>
                                <p class="text-sm text-amber-600">
                                    {{
                                        selectedProduct.tenant?.user
                                            ?.display_name ||
                                        selectedProduct.tenant?.user
                                            ?.first_name_encrypted ||
                                        'Vendedor'
                                    }}
                                </p>
                                <div
                                    v-if="
                                        selectedProduct.tenant?.rating_count > 0
                                    "
                                    class="mt-1 flex items-center gap-1"
                                >
                                    <Star
                                        class="h-4 w-4 fill-amber-400 text-amber-400"
                                    />
                                    <span
                                        class="text-sm font-medium text-amber-700"
                                        >{{
                                            selectedProduct.tenant
                                                .rating_average
                                        }}</span
                                    >
                                    <span class="text-sm text-amber-400"
                                        >({{
                                            selectedProduct.tenant.rating_count
                                        }}
                                        avaliações)</span
                                    >
                                </div>
                                <p v-else class="mt-1 text-sm text-amber-400">
                                    sem histórico de vendas
                                </p>
                            </div>
                            <Button @click="addToCart(selectedProduct.id)">
                                {{
                                    getCartQty(selectedProduct.id) > 0
                                        ? `Adicionar mais (${getCartQty(selectedProduct.id)})`
                                        : 'Adicionar ao carrinho'
                                }}
                            </Button>
                        </div>
                        <a
                            :href="`/store/${selectedProduct.slug}`"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700"
                            @click="closeGallery"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Ver mais detalhes
                        </a>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Cart Summary Floating Bar -->
        <div
            v-if="cartCount > 0"
            class="fixed right-0 bottom-0 left-0 z-40 border-t border-amber-200 bg-white shadow-lg"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-4">
                    <ShoppingBag class="h-5 w-5 text-amber-500" />
                    <span class="text-sm text-amber-600">
                        <strong class="text-amber-900">{{ cartCount }}</strong>
                        item(ns) no carrinho
                    </span>
                    <span class="text-lg font-bold text-amber-900">{{
                        formatPrice(cartTotal)
                    }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="ghost" size="sm" @click="clearCart"
                        >Limpar</Button
                    >
                </div>
            </div>
        </div>
    </div>
</template>
