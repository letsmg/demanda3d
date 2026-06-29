<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { ShoppingBag, Star, ChevronLeft, ChevronRight, Package, ImageIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { setCartCount } from '@/stores/cartStore';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({
    layout: WelcomeLayout,
});

const props = defineProps<{
    product: any;
    relatedProducts: any[];
}>();

const page = usePage();
const authClient = computed(() => (page.props as any).auth_client?.user);

// Image gallery
const currentImageIndex = ref(0);
const productImages = computed(() => props.product.images || []);

function prevImage(): void {
    if (productImages.value.length === 0) {
        return;
    }

    currentImageIndex.value = (currentImageIndex.value - 1 + productImages.value.length) % productImages.value.length;
}

function nextImage(): void {
    if (productImages.value.length === 0) {
        return;
    }

    currentImageIndex.value = (currentImageIndex.value + 1) % productImages.value.length;
}

function selectImage(index: number): void {
    currentImageIndex.value = index;
}

// Cart logic
const cartItems = ref<any[]>([]);
const cartCount = ref(0);
const cartLoading = ref(false);

async function fetchCart(): Promise<void> {
    if (!authClient.value) {
        return;
    }

    try {
        const res = await fetch('/cart/items', { credentials: 'include' });

        if (res.ok) {
            const data = await res.json();

            cartItems.value = data.items || [];
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } catch {
        // ignore
    }
}

function getCartQty(productId: number): number {
    const item = cartItems.value.find((i: any) => i.product_id === productId);

    return item ? item.quantity : 0;
}

function csrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? (meta as HTMLMetaElement).content : '';
}

async function addToCart(productId: number): Promise<void> {
    if (!authClient.value) {
        window.location.href = '/login_cli';

        return;
    }

    cartLoading.value = true;

    try {
        const res = await fetch('/cart', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        });

        if (res.ok) {
            const data = await res.json();

            cartItems.value = data.items || [];
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } finally {
        cartLoading.value = false;
    }
}

onMounted(() => {
    fetchCart();
});

// SEO
const seo = computed(() => props.product.seo || {});
const currentImage = computed(() => {
    if (productImages.value.length === 0) {
        return null;
    }

    return productImages.value[currentImageIndex.value];
});

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value));
}

function getImageUrl(product: any, index: number = 0): string | undefined {
    if (product.images && product.images.length > 0 && product.images[index]) {
        return product.images[index].url;
    }

    return undefined;
}
</script>

<template>
    <Head :title="seo.meta_title || product.name">
        <meta name="description" :content="seo.meta_description" />
        <meta property="og:title" :content="seo.meta_title || product.name" />
        <meta property="og:description" :content="seo.meta_description" />
        <meta property="og:image" :content="seo.og_image" />
        <link rel="canonical" :href="seo.canonical_url" />
    </Head>

    <div class="min-h-screen bg-amber-50">
        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-amber-600">
                <Link href="/" class="hover:text-amber-800 transition">
                    Início
                </Link>
                <span>/</span>
                <Link href="/store" class="hover:text-amber-800 transition">
                    Loja
                </Link>
                <span>/</span>
                <span class="text-amber-900 font-medium line-clamp-1">{{ product.name }}</span>
            </nav>
        </div>

        <main class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8 pb-16">
            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Left: Image Gallery -->
                <div class="space-y-4">
                    <div class="relative flex items-center justify-center overflow-hidden rounded-xl bg-amber-100" style="aspect-ratio: 1 / 1;">
                        <img
                            v-if="currentImage"
                            :src="currentImage.url"
                            :alt="`${product.name} - ${currentImageIndex + 1}`"
                            class="h-full w-full object-contain"
                        >
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <ImageIcon class="h-24 w-24 text-amber-300" />
                        </div>

                        <button
                            v-if="productImages.length > 1"
                            class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white transition"
                            @click="prevImage"
                        >
                            <ChevronLeft class="h-5 w-5 text-gray-600" />
                        </button>
                        <button
                            v-if="productImages.length > 1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow hover:bg-white transition"
                            @click="nextImage"
                        >
                            <ChevronRight class="h-5 w-5 text-gray-600" />
                        </button>

                        <div class="absolute bottom-2 left-2 right-2 flex justify-center gap-1">
                            <button
                                v-for="(img, idx) in productImages"
                                :key="idx"
                                class="h-2 w-2 rounded-full transition border border-amber-400"
                                :class="idx === currentImageIndex ? 'bg-amber-500' : 'bg-white/50'"
                                @click="selectImage(idx)"
                            />
                        </div>
                    </div>

                    <div v-if="productImages.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                        <button
                            v-for="(img, idx) in productImages"
                            :key="idx"
                            class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="idx === currentImageIndex ? 'border-amber-500' : 'border-transparent opacity-60 hover:opacity-100'"
                            @click="selectImage(idx)"
                        >
                            <img :src="img.url" :alt="`${product.name} thumb ${idx + 1}`" class="h-full w-full object-cover">
                        </button>
                    </div>
                </div>

                <!-- Right: Product Details -->
                <div class="space-y-6">
                    <div>
                        <h1 class="text-2xl font-bold text-amber-900">{{ seo.h1_text || product.name }}</h1>
                        <p v-if="product.tenant?.display_name" class="mt-1 text-sm text-amber-500">
                            por {{ product.tenant.display_name }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div class="flex items-end gap-3">
                        <p class="text-3xl font-bold text-emerald-700">
                            {{ formatPrice(product.sale_price) }}
                        </p>
                    </div>

                    <!-- Rating -->
                    <div v-if="product.tenant?.rating_count > 0" class="flex items-center gap-2">
                        <div class="flex items-center">
                            <Star
                                v-for="i in 5"
                                :key="i"
                                class="h-4 w-4"
                                :class="i <= Math.round(product.tenant.rating_average) ? 'fill-amber-400 text-amber-400' : 'text-amber-200'"
                            />
                        </div>
                        <span class="text-sm text-amber-600">{{ product.tenant.rating_average }} ({{ product.tenant.rating_count }} avaliações)</span>
                    </div>

                    <!-- Categorias -->
                    <div v-if="product.categorias && product.categorias.length > 0" class="flex flex-wrap gap-2">
                        <span
                            v-for="cat in product.categorias"
                            :key="cat.id"
                            class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700"
                        >
                            {{ cat.nome }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div v-if="product.description" class="prose prose-sm max-w-none">
                        <h2 class="text-lg font-semibold text-amber-900">Descrição</h2>
                        <p class="text-amber-800 whitespace-pre-line">{{ product.description }}</p>
                    </div>

                    <!-- Product Specs -->
                    <div v-if="product.material_type || product.print_time || product.approximate_weight" class="space-y-3">
                        <h3 class="text-sm font-semibold text-amber-900">Especificações Técnicas</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-if="product.material_type" class="rounded-lg bg-white p-3 shadow-sm border border-amber-100">
                                <p class="text-xs text-amber-500">Material</p>
                                <p class="text-sm font-medium text-amber-900 capitalize">{{ product.material_type }}</p>
                            </div>
                            <div v-if="product.print_time" class="rounded-lg bg-white p-3 shadow-sm border border-amber-100">
                                <p class="text-xs text-amber-500">Tempo de Impressão</p>
                                <p class="text-sm font-medium text-amber-900">{{ product.print_time }} min</p>
                            </div>
                            <div v-if="product.approximate_weight" class="rounded-lg bg-white p-3 shadow-sm border border-amber-100">
                                <p class="text-xs text-amber-500">Peso Aproximado</p>
                                <p class="text-sm font-medium text-amber-900">{{ product.approximate_weight }} g</p>
                            </div>
                            <div v-if="product.height || product.width" class="rounded-lg bg-white p-3 shadow-sm border border-amber-100">
                                <p class="text-xs text-amber-500">Dimensões</p>
                                <p class="text-sm font-medium text-amber-900">
                                    {{ product.height || '-' }} × {{ product.width || '-' }} mm
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Add to Cart Button -->
                    <div class="pt-2">
                        <Button
                            size="lg"
                            class="w-full sm:w-auto"
                            :disabled="cartLoading"
                            @click="addToCart(product.id)"
                        >
                            <ShoppingBag class="mr-2 h-5 w-5" />
                            {{ getCartQty(product.id) > 0 ? `Adicionar mais (${getCartQty(product.id)})` : 'Adicionar ao carrinho' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <section v-if="relatedProducts && relatedProducts.length > 0" class="mt-16">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-amber-900">Produtos Relacionados</h2>
                    <p class="mt-1 text-sm text-amber-600">Outros produtos da mesma categoria que você pode gostar</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Card v-for="rp in relatedProducts" :key="rp.id" class="flex flex-col overflow-hidden hover:shadow-md transition-shadow">
                        <Link :href="`/store/${rp.slug}`" class="block">
                            <div class="flex h-48 items-center justify-center overflow-hidden bg-amber-100">
                                <img
                                    v-if="getImageUrl(rp, 0)"
                                    :src="getImageUrl(rp, 0)"
                                    :alt="rp.name"
                                    class="h-full w-full object-cover transition-transform hover:scale-105"
                                >
                                <div v-else class="flex h-full w-full items-center justify-center">
                                    <Package class="h-10 w-10 text-amber-300" />
                                </div>
                            </div>
                        </Link>
                        <CardHeader class="pb-2">
                            <Link :href="`/store/${rp.slug}`" class="block">
                                <CardTitle class="text-base text-amber-900 hover:text-amber-600 transition line-clamp-1">{{ rp.name }}</CardTitle>
                            </Link>
                            <CardDescription v-if="rp.description" class="line-clamp-2 text-xs">{{ rp.description }}</CardDescription>
                        </CardHeader>
                        <CardContent class="flex-1 pb-2">
                            <p class="text-lg font-bold text-emerald-700">
                                {{ formatPrice(rp.sale_price) }}
                            </p>
                        </CardContent>
                        <CardFooter class="pt-0">
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full"
                                @click="addToCart(rp.id)"
                            >
                                <ShoppingBag class="mr-1 h-4 w-4" />
                                Adicionar
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </section>
        </main>
    </div>
</template>