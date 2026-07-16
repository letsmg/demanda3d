<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ChevronDown,
    Copy,
    ExternalLink,
    ImageIcon,
    Link2,
    MessageCircle,
    Minus,
    Plus,
    RotateCw,
    Search,
    ShoppingBag,
    SlidersHorizontal,
    Star,
    X,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import { setCartCount } from '@/stores/cartStore';

defineOptions({
    layout: WelcomeLayout,
});

type FilterParams = Record<string, string>;

const props = defineProps<{
    products: any[];
    categories: Array<{ slug: string; name: string }>;
    filters: {
        search?: string;
        min_price?: number;
        max_price?: number;
        sort?: string;
        sort_dir?: string;
        categories?: string;
    };
}>();

// ═══ Hydration guard — evita mismatch entre SSR e cliente ═══
const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;

    if (isAuthenticated()) {
        fetchCartData();
    }
});

// ── Cart state (só carrega se autenticado) ────────────────
const cartItems = ref<any[]>([]);
const cartTotal = ref(0);
const cartCount = ref(0);
const cartLoading = ref(false);

function isAuthenticated(): boolean {
    const page = (window as any).__inertia_page?.props;

    return !!(page?.auth_client?.user);
}

async function fetchCartData() {
    if (!isAuthenticated()) {
        return;
    }

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
        // ignore (401 for unauthenticated visitors is expected)
    }
}

async function addToCart(productId: number) {
    if (!isAuthenticated()) {
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

    if (!item) {
        return;
    }

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

// ══════════════════════════════════════════════════════════════
// FILTROS — Estado sincronizado com URL (Inertia props)
// ══════════════════════════════════════════════════════════════

function parseCategories(raw: string | undefined): string[] {
    if (!raw) {
        return [];
    }

    return raw.split(',').filter(Boolean);
}

const searchTerm = ref(props.filters.search || '');
const priceMin = ref(props.filters.min_price?.toString() || '');
const priceMax = ref(props.filters.max_price?.toString() || '');
const selectedCategories = ref<string[]>(parseCategories(props.filters.categories));
const sortBy = ref(props.filters.sort || 'name');
const sortOrder = ref(props.filters.sort_dir || 'asc');

// ⚡ Sincroniza refs com props quando Inertia atualiza os filtros
watch(
    () => props.filters,
    (newFilters) => {
        if (!newFilters) {
            return;
        }

        searchTerm.value = newFilters.search || '';
        priceMin.value = newFilters.min_price?.toString() || '';
        priceMax.value = newFilters.max_price?.toString() || '';
        selectedCategories.value = parseCategories(newFilters.categories);
        sortBy.value = newFilters.sort || 'name';
        sortOrder.value = newFilters.sort_dir || 'asc';
    },
);

// ══════════════════════════════════════════════════════════════
// Autocomplete suggestions
// ══════════════════════════════════════════════════════════════
const suggestions = ref<string[]>([]);
const showSuggestions = ref(false);
const highlightedIndex = ref(-1);
let abortController: AbortController | null = null;

async function fetchSuggestions() {
    // Cancela requisição anterior
    if (abortController) {
        abortController.abort();
    }

    const val = searchTerm.value.trim();

    if (val.length < 1) {
        suggestions.value = [];
        showSuggestions.value = false;

        return;
    }

    abortController = new AbortController();

    try {
        const res = await fetch(`/api/search/suggestions?q=${encodeURIComponent(val)}`, {
            signal: abortController.signal,
            headers: { Accept: 'application/json' },
        });

        if (res.ok) {
            const json = await res.json();

            suggestions.value = json.suggestions || [];
            showSuggestions.value = suggestions.value.length > 0;
            highlightedIndex.value = -1;
        }
    } catch (e: any) {
        if (e.name !== 'AbortError') {
            suggestions.value = [];
            showSuggestions.value = false;
        }
    }
}

function selectSuggestion(text: string) {
    searchTerm.value = text;
    showSuggestions.value = false;
    highlightedIndex.value = -1;
    applyStoreFilters();
}

function onSearchInput() {
    fetchSuggestions();
}

function onSearchKeydown(e: KeyboardEvent) {
    if (!showSuggestions.value || suggestions.value.length === 0) {
        if (e.key === 'Enter') {
            applyStoreFilters();
        }

        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightedIndex.value = Math.min(highlightedIndex.value + 1, suggestions.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1);
    } else if (e.key === 'Enter') {
        e.preventDefault();

        if (highlightedIndex.value >= 0 && highlightedIndex.value < suggestions.value.length) {
            selectSuggestion(suggestions.value[highlightedIndex.value]);
        } else {
            showSuggestions.value = false;
            applyStoreFilters();
        }
    } else if (e.key === 'Escape') {
        showSuggestions.value = false;
    }
}

// Fecha sugestões ao clicar fora
function onWindowClick(e: MouseEvent) {
    const target = e.target as HTMLElement;

    if (!target.closest('[data-search-area]')) {
        showSuggestions.value = false;
    }
}

onMounted(() => {
    window.addEventListener('click', onWindowClick);
});

onUnmounted(() => {
    window.removeEventListener('click', onWindowClick);
});

/**
 * Limpa o campo de pesquisa e refoca.
 */
function clearSearch() {
    searchTerm.value = '';
    suggestions.value = [];
    showSuggestions.value = false;
    highlightedIndex.value = -1;
    applyStoreFilters();

    const input = document.querySelector<HTMLInputElement>('input[placeholder*="Buscar"]');

    input?.focus();
}

/**
 * Filtro de preço — dispara ao perder foco ou teclar Enter.
 * Valida que min <= max antes de enviar.
 */
function onPriceBlur() {
    const min = parseFloat(priceMin.value);
    const max = parseFloat(priceMax.value);

    if (!isNaN(min) && !isNaN(max) && min > max) {
        const tmp = priceMin.value;

        priceMin.value = priceMax.value;
        priceMax.value = tmp;
    }

    applyStoreFilters();
}

function onPriceEnter(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        (e.target as HTMLInputElement).blur();
    }
}

/**
 * Alterna categoria (multi-seleção).
 */
function toggleCategory(catSlug: string) {
    const idx = selectedCategories.value.indexOf(catSlug);

    if (idx >= 0) {
        selectedCategories.value.splice(idx, 1);
    } else {
        selectedCategories.value.push(catSlug);
    }

    applyStoreFilters();
}

/**
 * Aplica todos os filtros de uma vez via Inertia router.
 */
function applyStoreFilters() {
    // Não reseta visibleProducts — o watcher em props.products cuida da sincronização
    const params: FilterParams = {};
    const trimmedSearch = searchTerm.value.trim();

    if (trimmedSearch.length >= 3) {
        params.search = trimmedSearch;
    }

    if (priceMin.value) {
        params.min_price = priceMin.value;
    }

    if (priceMax.value) {
        params.max_price = priceMax.value;
    }

    if (selectedCategories.value.length > 0) {
        params.categories = selectedCategories.value.join(',');
    }

    params.sort = sortBy.value;
    params.sort_dir = sortOrder.value;

    // Navegação direta (funciona com Inertia e atualiza URL no browser)
    const qs = new URLSearchParams(params).toString();
    window.location.href = '/store' + (qs ? '?' + qs : '');
}

function clearFiltersOnly() {
    priceMin.value = '';
    priceMax.value = '';
    selectedCategories.value = [];
    sortBy.value = 'name';
    sortOrder.value = 'asc';

    applyStoreFilters();
}

function clearAllFilters() {
    searchTerm.value = '';
    suggestions.value = [];
    showSuggestions.value = false;
    priceMin.value = '';
    priceMax.value = '';
    selectedCategories.value = [];
    sortBy.value = 'name';
    sortOrder.value = 'asc';

    router.get('/store', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function handleSortChange(value: string | null) {
    onSortOptionChange(value);
}

function onSortOptionChange(value: string | null) {
    if (!value) {
        return;
    }

    const parts = value.split('_');
    const dir = parts.pop()!;
    const field = parts.join('_'); // 'created_at', 'sale_price', 'name'

    sortBy.value = field;
    sortOrder.value = dir;
    applyStoreFilters();
}

function getCurrentSort(): string {
    return sortBy.value + '_' + sortOrder.value;
}

const hasFilters = computed(() => {
    return (
        searchTerm.value.trim().length >= 3 ||
        priceMin.value ||
        priceMax.value ||
        selectedCategories.value.length > 0 ||
        sortBy.value !== 'name' ||
        sortOrder.value !== 'asc'
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

// ══════════════════════════════════════════════════════════════
// "Mostrar mais" lazy loading
// ══════════════════════════════════════════════════════════════
const visibleProducts = ref<any[]>(Array.isArray(props.products) ? [...props.products] : []);
const hasMore = ref(Array.isArray(props.products) ? props.products.length >= 8 : false);
const currentPage = ref(1);
const loadingMore = ref(false);

watch(
    () => props.products,
    (newList) => {
        if (!newList || !Array.isArray(newList)) {
            return;
        }

        visibleProducts.value = [...newList];
        hasMore.value = newList.length >= 8;
        currentPage.value = 1;
    },
    { immediate: true },
);

async function loadMoreProducts() {
    if (loadingMore.value || !hasMore.value) {
        return;
    }

    loadingMore.value = true;

    const nextPage = currentPage.value + 1;

    try {
        const qs = new URLSearchParams();

        qs.set('page', String(nextPage));

        const trimmedSearch = searchTerm.value.trim();

        if (trimmedSearch.length >= 3) {
            qs.set('search', trimmedSearch);
        }

        if (priceMin.value) {
            qs.set('min_price', priceMin.value);
        }

        if (priceMax.value) {
            qs.set('max_price', priceMax.value);
        }

        if (selectedCategories.value.length > 0) {
            qs.set('categories', selectedCategories.value.join(','));
        }

        qs.set('sort', sortBy.value);
        qs.set('sort_dir', sortOrder.value);

        const url = '/api/store/products?' + qs.toString();
        const res = await fetch(url, { headers: { Accept: 'application/json' } });

        if (res.ok) {
            const json = await res.json();

            visibleProducts.value.push(...json.data);
            hasMore.value = json.has_more;
            currentPage.value = nextPage;
        }
    } finally {
        loadingMore.value = false;
    }
}

// ══════════════════════════════════════════════════════════════
// Image gallery
// ══════════════════════════════════════════════════════════════
const selectedProduct = ref<any>(null);
const currentImageIndex = ref(0);

function openGallery(product: any, index: number = 0) {
    selectedProduct.value = product;
    currentImageIndex.value = index;
}

function closeGallery() {
    selectedProduct.value = null;
    currentImageIndex.value = 0;
}

function prevImage() {
    if (!selectedProduct.value?.images?.length) {
        return;
    }

    currentImageIndex.value =
        (currentImageIndex.value - 1 + selectedProduct.value.images.length) %
        selectedProduct.value.images.length;
}

function nextImage() {
    if (!selectedProduct.value?.images?.length) {
        return;
    }

    currentImageIndex.value =
        (currentImageIndex.value + 1) % selectedProduct.value.images.length;
}

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
}

function getImageUrl(product: any, index: number = 0): string | undefined {
    if (product.images && product.images.length > 0 && product.images[index]) {
        return product.images[index].url;
    }

    return undefined;
}

// ══════════════════════════════════════════════════════════════
// Compartilhamento
// ══════════════════════════════════════════════════════════════
const shareDialogOpen = ref(false);
const shareUrl = ref('');
const shareTitle = ref('');
const shareCopied = ref(false);

function openShare(productName: string) {
    shareUrl.value = window.location.href;
    shareTitle.value = productName;
    shareDialogOpen.value = true;
    shareCopied.value = false;
}

function copyLink() {
    navigator.clipboard.writeText(shareUrl.value).then(() => {
        shareCopied.value = true;
        setTimeout(() => {
            shareCopied.value = false;
        }, 2000);
    });
}

function shareWhatsApp() {
    const text = encodeURIComponent(`${shareTitle.value} — ${shareUrl.value}`);
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    const url = isMobile
        ? `whatsapp://send?text=${text}`
        : `https://wa.me/?text=${text}`;

    window.open(url, '_blank');
}

function shareFacebook() {
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`;

    window.open(url, '_blank');
}

function shareTwitter() {
    const text = encodeURIComponent(shareTitle.value);
    const url = `https://x.com/intent/post?text=${text}&url=${encodeURIComponent(shareUrl.value)}`;

    window.open(url, '_blank');
}

function shareTelegram() {
    const text = encodeURIComponent(`${shareTitle.value} — ${shareUrl.value}`);
    const url = `https://t.me/share/url?url=${encodeURIComponent(shareUrl.value)}&text=${text}`;

    window.open(url, '_blank');
}
</script>

<template>
    <Head title="Loja - Demanda3D">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-brand-amberBg">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-amber-900">
                    Loja de Produtos
                </h1>
                <p class="mt-1 text-sm text-amber-700">
                    Produtos disponíveis para impressão 3D de todos os nossos vendedores
                </p>
            </div>

            <!-- ═══════ HEADER: Busca + Ordenação lado a lado ═══════ -->
            <div class="mb-6 flex flex-col gap-3 sm:flex-row" data-search-area>
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-amber-700"
                    />
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar produtos por nome ou descrição..."
                        class="store-amber-input w-full rounded-md border border-brand-amberInputBorder px-10 py-2 text-brand-amberDark placeholder:text-brand-amberPrimary/60 focus:border-amber-500 focus:ring-amber-500 focus:outline-none"
                        @input="onSearchInput"
                        @keydown="onSearchKeydown"
                    />
                    <button
                        v-if="searchTerm"
                        class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-0.5 text-amber-700 hover:text-amber-900"
                        @click="clearSearch"
                        title="Limpar pesquisa"
                    >
                        <X class="h-4 w-4" />
                    </button>

                    <!-- Autocomplete dropdown -->
                    <div
                        v-if="showSuggestions && suggestions.length > 0"
                        class="absolute z-50 mt-1 w-full rounded-lg border border-amber-200 bg-white shadow-lg"
                    >
                        <div
                            v-for="(s, idx) in suggestions"
                            :key="idx"
                            class="flex cursor-pointer items-center gap-2 px-4 py-2.5 text-sm transition"
                            :class="idx === highlightedIndex ? 'bg-amber-100 text-amber-900' : 'text-gray-700 hover:bg-amber-50'"
                            @mousedown.prevent="selectSuggestion(s)"
                        >
                            <Search class="h-3.5 w-3.5 text-amber-500" />
                            {{ s }}
                        </div>
                    </div>
                </div>

                <!-- Ordenação lado a lado -->
                <Select
                    :model-value="getCurrentSort()"
                    @update:model-value="handleSortChange"
                >
                    <SelectTrigger class="w-44 border-brand-amberInputBorder text-brand-amberDark">
                        <SelectValue placeholder="Nome A-Z" />
                    </SelectTrigger>
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

            <!-- ═══════ CARD DE FILTROS (Dark Amber) ═══════ -->
            <Card class="mb-8 border-amber-700 bg-amber-600 text-white shadow-md">
                <CardContent class="space-y-4 pt-5">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Preço -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-amber-50">Preço:</label>
                            <input
                                v-model="priceMin"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Mín"
                                class="store-amber-input w-24 rounded-md border border-brand-amberInputBorder px-2 py-2 text-brand-amberDark placeholder:text-brand-amberPrimary/60 focus:outline-none"
                                @blur="onPriceBlur"
                                @keyup="onPriceEnter"
                            />
                            <span class="text-brand-amberPrimary">-</span>
                            <input
                                v-model="priceMax"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Máx"
                                class="store-amber-input w-24 rounded-md border border-brand-amberInputBorder px-2 py-2 text-brand-amberDark placeholder:text-brand-amberPrimary/60 focus:outline-none"
                                @blur="onPriceBlur"
                                @keyup="onPriceEnter"
                            />
                        </div>

                        <!-- Ações -->
                        <Button
                            size="sm"
                            class="flex items-center gap-1.5 bg-amber-800 text-white hover:bg-amber-900"
                            @click="applyStoreFilters"
                        >
                            <SlidersHorizontal class="h-4 w-4" />
                            Aplicar Filtros
                        </Button>

                        <Button
                            v-if="hasFilters"
                            variant="outline"
                            size="sm"
                            class="border-amber-200 text-amber-50 hover:bg-amber-700 hover:text-white"
                            @click="clearFiltersOnly"
                        >
                            <RotateCw class="mr-1 h-4 w-4" />Limpar Filtros
                        </Button>
                    </div>

                    <!-- Categorias multi-seleção -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-amber-50">Categorias:</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                class="rounded-full border px-3 py-1 text-xs font-medium transition"
                                :class="selectedCategories.length === 0
                                    ? 'border-amber-50 bg-amber-800 text-white'
                                    : 'border-amber-300 bg-transparent text-amber-50 hover:border-amber-100 hover:bg-amber-700'"
                                @click="selectedCategories = []; applyStoreFilters()"
                            >
                                Todas
                            </button>
                            <label
                                v-for="cat in props.categories"
                                :key="cat.slug"
                                class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition"
                                :class="selectedCategories.includes(cat.slug)
                                    ? 'border-amber-50 bg-amber-800 text-white'
                                    : 'border-amber-300 bg-transparent text-amber-50 hover:border-amber-100 hover:bg-amber-700'"
                            >
                                <Checkbox
                                    :checked="selectedCategories.includes(cat.slug)"
                                    class="h-3 w-3 border-amber-300"
                                    @update:checked="toggleCategory(cat.slug)"
                                />
                                {{ cat.name }}
                            </label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Lista vazia -->
            <div v-if="visibleProducts.length === 0" class="py-16 text-center">
                <ShoppingBag class="mx-auto h-12 w-12 text-amber-400" />
                <h3 class="mt-2 text-sm font-semibold text-amber-700">
                    Nenhum produto disponível no momento.
                </h3>
                <p class="mt-1 text-sm text-amber-500">
                    Tente ajustar os filtros ou limpar a busca.
                </p>
                <Button variant="outline" class="mt-4 border-amber-500 text-amber-700 hover:bg-amber-50" @click="clearAllFilters">
                    <RotateCw class="mr-2 h-4 w-4" />Limpar tudo
                </Button>
            </div>

            <!-- Grid de produtos -->
            <div
                v-else
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <Card
                    v-for="product in visibleProducts"
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

                        <button
                            class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-amber-700 shadow-sm transition hover:bg-white hover:text-amber-900"
                            @click.stop="openShare(product.name)"
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
                                <CardTitle class="text-base text-amber-900">{{ product.name }}</CardTitle>
                            </div>
                            <div class="flex items-center gap-1">
                                <div v-if="getCartQty(product.id) > 0" class="flex items-center gap-1">
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                        @click="removeFromCart(getCartItemId(product.id) ?? 0)"
                                    >
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span class="min-w-[1.5rem] text-center text-sm font-medium">{{ getCartQty(product.id) }}</span>
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                        @click="addToCart(product.id)"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <button
                                    v-else
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-amber-900 transition hover:bg-amber-50 hover:text-amber-600"
                                    @click="addToCart(product.id)"
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
                            class="w-full bg-amber-800 text-white hover:bg-amber-900"
                            :variant="getCartQty(product.id) > 0 ? 'secondary' : 'default'"
                            @click="addToCart(product.id)"
                            :disabled="cartLoading"
                        >
                            {{ getCartQty(product.id) > 0 ? 'Adicionar mais' : 'Adicionar' }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- "Mostrar mais" -->
            <div v-if="hasMore" class="mt-8 flex justify-center pb-8">
                <Button
                    variant="outline"
                    size="lg"
                    class="border-amber-700 text-amber-700 hover:bg-amber-50"
                    :disabled="loadingMore"
                    @click="loadMoreProducts"
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

        <!-- Diálogo de compartilhamento -->
        <Dialog :open="shareDialogOpen" @update:open="shareDialogOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Compartilhar Produto</DialogTitle>
                    <DialogDescription>{{ shareTitle }}</DialogDescription>
                </DialogHeader>
                <div class="grid grid-cols-2 gap-3">
                    <Button variant="outline" class="flex items-center justify-center gap-2" @click="copyLink">
                        <Copy v-if="!shareCopied" class="h-4 w-4" />
                        <Link2 v-else class="h-4 w-4 text-green-600" />
                        {{ shareCopied ? 'Copiado!' : 'Copiar Link' }}
                    </Button>
                    <Button variant="outline" class="flex items-center justify-center gap-2 bg-green-50 hover:bg-green-100" @click="shareWhatsApp">
                        <MessageCircle class="h-4 w-4 text-green-600" /> WhatsApp
                    </Button>
                    <Button variant="outline" class="flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100" @click="shareFacebook">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </Button>
                    <Button variant="outline" class="flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100" @click="shareTwitter">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#0F1419"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        X
                    </Button>
                    <Button variant="outline" class="flex items-center justify-center gap-2 bg-sky-50 hover:bg-sky-100" @click="shareTelegram">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#0088CC"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.26.333-.54.333l.194-2.76 5.02-4.536c.223-.198-.048-.308-.346-.11l-6.2 3.9-2.67-.834c-.58-.182-.592-.58.122-.862l10.43-4.02c.483-.18.904.117.746.876z"/></svg>
                        Telegram
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Image Gallery Dialog -->
        <Dialog :open="selectedProduct !== null" @update:open="closeGallery">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{{ selectedProduct?.name }}</DialogTitle>
                    <DialogDescription v-if="selectedProduct?.description">{{ selectedProduct.description }}</DialogDescription>
                </DialogHeader>
                <div v-if="selectedProduct" class="relative">
                    <div class="flex items-center justify-center">
                        <img
                            v-if="selectedProduct.images && selectedProduct.images[currentImageIndex]"
                            :src="selectedProduct.images[currentImageIndex].url"
                            :alt="`${selectedProduct.name} - Imagem ${currentImageIndex + 1}`"
                            class="max-h-[60vh] rounded-lg object-contain"
                        />
                        <div v-else class="flex h-64 w-full items-center justify-center rounded-lg bg-amber-100">
                            <ImageIcon class="h-16 w-16 text-amber-900" />
                        </div>
                    </div>
                    <button v-if="selectedProduct.images && selectedProduct.images.length > 1"
                        class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                        @click="prevImage"><ChevronDown class="h-5 w-5 rotate-90 text-gray-600" /></button>
                    <button v-if="selectedProduct.images && selectedProduct.images.length > 1"
                        class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white"
                        @click="nextImage"><ChevronDown class="h-5 w-5 -rotate-90 text-gray-600" /></button>
                    <div v-if="selectedProduct.images && selectedProduct.images.length > 1" class="mt-4 flex justify-center gap-2">
                        <button v-for="(img, idx) in selectedProduct.images" :key="idx"
                            class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="idx === currentImageIndex ? 'border-amber-500' : 'border-transparent opacity-60 hover:opacity-100'"
                            @click="currentImageIndex = idx">
                            <img :src="img.url" :alt="`${selectedProduct.name} thumb ${idx + 1}`" class="h-full w-full object-cover" />
                        </button>
                    </div>
                    <div class="mt-4 space-y-3 rounded-lg bg-amber-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-2xl font-bold text-emerald-700">{{ formatPrice(selectedProduct.sale_price) }}</p>
                                <p class="text-sm text-amber-600">{{ selectedProduct.tenant?.fantasy_name || 'Vendedor' }}</p>
                                <div v-if="selectedProduct.tenant?.rating_count > 0" class="mt-1 flex items-center gap-1">
                                    <Star class="h-4 w-4 fill-amber-900 text-amber-900" />
                                    <span class="text-sm font-medium text-amber-700">{{ selectedProduct.tenant.rating_average }}</span>
                                    <span class="text-sm text-amber-900">({{ selectedProduct.tenant.rating_count }} avaliações)</span>
                                </div>
                            </div>
                            <Button class="bg-amber-800 text-white hover:bg-amber-900" @click="addToCart(selectedProduct.id)">
                                {{ getCartQty(selectedProduct.id) > 0 ? `Adicionar mais (${getCartQty(selectedProduct.id)})` : 'Adicionar ao carrinho' }}
                            </Button>
                        </div>
                        <a :href="`/store/${selectedProduct.slug}`"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-amber-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-900"
                            @click="closeGallery"><ExternalLink class="h-4 w-4" /> Ver mais detalhes</a>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Cart Summary Floating Bar (só aparece se autenticado + tem itens) -->
        <div
            v-if="isMounted && isAuthenticated() && cartCount > 0"
            class="fixed right-0 bottom-0 left-0 z-40 border-t border-amber-200 bg-white shadow-lg"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <ShoppingBag class="h-5 w-5 text-amber-500" />
                    <span class="text-sm text-amber-600">
                        <strong class="text-amber-900">{{ cartCount }}</strong> item(ns)
                    </span>
                    <span class="text-lg font-bold text-amber-900">{{ formatPrice(cartTotal) }}</span>
                </div>
                <Button variant="ghost" size="sm" @click="clearCart">Limpar</Button>
            </div>
        </div>
    </div>
</template>