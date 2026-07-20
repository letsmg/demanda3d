<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Copy,
    ImageIcon,
    Link2,
    MessageCircle,
    Minus,
    Package,
    Plus,
    Share2,
    ShoppingBag,
    Star,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import { setCartCount } from '@/stores/cartStore';

defineOptions({
    layout: WelcomeLayout,
});

const props = defineProps<{
    product: any;
    relatedProducts: any[];
}>();

const page = usePage();
const authClient = computed(() => (page.props as any).auth_client?.user);

// ── Compartilhamento ──────────────────────────────────────
const shareDialogOpen = ref(false);
const shareUrl = ref('');
const shareTitle = ref('');
const shareCopied = ref(false);

function openShare() {
    shareUrl.value = window.location.href;
    shareTitle.value = props.product.name;
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

// Image gallery
const currentImageIndex = ref(0);
const productImages = computed(() => props.product.images || []);

function prevImage(): void {
    if (productImages.value.length === 0) {
        return;
    }

    currentImageIndex.value =
        (currentImageIndex.value - 1 + productImages.value.length) %
        productImages.value.length;
}

function nextImage(): void {
    if (productImages.value.length === 0) {
        return;
    }

    currentImageIndex.value =
        (currentImageIndex.value + 1) % productImages.value.length;
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

function getCartItemId(productId: number): number | null {
    const item = cartItems.value.find((i: any) => i.product_id === productId);
    return item ? item.id : null;
}

async function removeFromCart(productId: number): Promise<void> {
    if (!authClient.value) return;

    const item = cartItems.value.find((i: any) => i.product_id === productId);
    if (!item || item.quantity <= 1) {
        await removeCartItem(getCartItemId(productId) ?? 0);
        return;
    }

    try {
        const res = await fetch(`/cart/${item.id}`, {
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
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } catch {
        // ignore
    }
}

async function removeCartItem(cartItemId: number): Promise<void> {
    try {
        const res = await fetch(`/cart/${cartItemId}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });

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

async function addToCart(productId: number): Promise<void> {
    if (!authClient.value) {
        const redirectTo = encodeURIComponent(window.location.href);
        window.location.href = `/login_cli?redirect_to=${redirectTo}`;

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
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } finally {
        cartLoading.value = false;
    }
}

onMounted(() => {
    fetchCart();
    injectSeoScripts();
});

/**
 * Injeta schema_markup (JSON-LD) e google_tag_manager no <head> de forma segura.
 * Esses campos contêm código JS/JSON que NÃO deve ser sanitizado.
 *
 * Remove scripts injetados anteriormente para evitar duplicação durante
 * navegação SPA via Inertia.
 */
function injectSeoScripts(): void {
    const head = document.head;

    head.querySelectorAll('[data-seo="schema-markup"]').forEach((el) =>
        el.remove(),
    );
    head.querySelectorAll('[data-seo="google-tag-manager"]').forEach((el) =>
        el.remove(),
    );

    if (seo.value.schema_markup) {
        const script = document.createElement('script');

        script.type = 'application/ld+json';
        script.textContent = seo.value.schema_markup;
        script.setAttribute('data-seo', 'schema-markup');
        head.appendChild(script);
    }

    if (seo.value.google_tag_manager) {
        const container = document.createElement('div');

        container.setAttribute('data-seo', 'google-tag-manager');
        container.style.display = 'none';

        const template = document.createElement('template');

        template.innerHTML = seo.value.google_tag_manager;
        head.appendChild(template.content);
    }
}

// SEO
const seo = computed(() => props.product.seo || {});
const currentImage = computed(() => {
    if (productImages.value.length === 0) {
        return null;
    }

    return productImages.value[currentImageIndex.value];
});

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
</script>

<template>
    <Head :title="seo.meta_title || product.name">
        <meta name="description" :content="seo.meta_description" />
        <meta
            v-if="seo.meta_keywords"
            name="keywords"
            :content="seo.meta_keywords"
        />
        <meta property="og:title" :content="seo.meta_title || product.name" />
        <meta property="og:description" :content="seo.meta_description" />
        <meta property="og:image" :content="seo.og_image" />
        <meta property="og:type" content="product" />
        <link rel="canonical" :href="seo.canonical_url" />
    </Head>

    <div class="min-h-screen bg-amber-50">
        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-amber-600">
                <Link href="/" class="transition hover:text-amber-800">
                    Início
                </Link>
                <span>/</span>
                <Link href="/store" class="transition hover:text-amber-800">
                    Loja
                </Link>
                <span>/</span>
                <span class="line-clamp-1 font-medium text-amber-900">{{
                    product.name
                }}</span>
            </nav>
        </div>

        <main class="mx-auto max-w-7xl px-4 py-2 pb-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Left: Image Gallery -->
                <div class="space-y-4">
                    <div
                        class="relative flex items-center justify-center overflow-hidden rounded-xl bg-amber-100"
                        style="aspect-ratio: 1 / 1"
                    >
                        <img
                            v-if="currentImage"
                            :src="currentImage.url"
                            :alt="`${product.name} - ${currentImageIndex + 1}`"
                            class="h-full w-full object-contain"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center"
                        >
                            <ImageIcon class="h-24 w-24 text-amber-900" />
                        </div>

                        <button
                            v-if="productImages.length > 1"
                            class="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow transition hover:bg-white"
                            @click="prevImage"
                        >
                            <ChevronLeft class="h-5 w-5 text-gray-600" />
                        </button>
                        <button
                            v-if="productImages.length > 1"
                            class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-white/80 p-2 shadow transition hover:bg-white"
                            @click="nextImage"
                        >
                            <ChevronRight class="h-5 w-5 text-gray-600" />
                        </button>

                        <div
                            class="absolute right-2 bottom-2 left-2 flex justify-center gap-1"
                        >
                            <button
                                v-for="(img, idx) in productImages"
                                :key="idx"
                                class="border-amber-910 h-2 w-2 rounded-full border transition"
                                :class="
                                    idx === currentImageIndex
                                        ? 'bg-amber-500'
                                        : 'bg-white/50'
                                "
                                @click="selectImage(idx)"
                            />
                        </div>
                    </div>

                    <div
                        v-if="productImages.length > 1"
                        class="flex gap-2 overflow-x-auto pb-2"
                    >
                        <button
                            v-for="(img, idx) in productImages"
                            :key="idx"
                            class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="
                                idx === currentImageIndex
                                    ? 'border-amber-500'
                                    : 'border-transparent opacity-60 hover:opacity-100'
                            "
                            @click="selectImage(idx)"
                        >
                            <img
                                :src="img.url"
                                :alt="`${product.name} thumb ${idx + 1}`"
                                class="h-full w-full object-cover"
                            />
                        </button>
                    </div>
                </div>

                <!-- Right: Product Details -->
                <div class="space-y-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-amber-900">
                                {{ seo.h1_text || product.name }}
                            </h1>
                            <p
                                v-if="product.tenant?.display_name"
                                class="mt-1 text-sm text-amber-500"
                            >
                                por
                                <Link
                                    v-if="product.tenant.fantasy_slug"
                                    :href="`/tenant/${product.tenant.fantasy_slug}`"
                                    class="hover:text-amber-700 hover:underline"
                                >
                                    {{ product.tenant.display_name }}
                                </Link>
                                <span v-else>{{
                                    product.tenant.display_name
                                }}</span>
                            </p>
                        </div>

                        <!-- Botão compartilhar -->
                        <Button
                            variant="outline"
                            size="sm"
                            class="flex items-center gap-1"
                            @click="openShare"
                        >
                            <Share2 class="h-4 w-4" />
                            Compartilhar
                        </Button>
                    </div>

                    <!-- Price -->
                    <div class="flex items-end gap-3">
                        <p class="text-3xl font-bold text-emerald-700">
                            {{ formatPrice(product.sale_price) }}
                        </p>
                    </div>

                    <!-- Rating -->
                    <div
                        v-if="product.tenant?.rating_count > 0"
                        class="flex items-center gap-2"
                    >
                        <div class="flex items-center">
                            <Star
                                v-for="i in 5"
                                :key="i"
                                class="h-4 w-4"
                                :class="
                                    i <=
                                    Math.round(product.tenant.rating_average)
                                        ? 'fill-amber-910 text-amber-910'
                                        : 'text-amber-200'
                                "
                            />
                        </div>
                        <span class="text-sm text-amber-600"
                            >{{ product.tenant.rating_average }} ({{
                                product.tenant.rating_count
                            }}
                            avaliações)</span
                        >
                    </div>

                    <!-- Categorias -->
                    <div
                        v-if="
                            product.categories && product.categories.length > 0
                        "
                        class="flex flex-wrap gap-2"
                    >
                        <span
                            v-for="cat in product.categories"
                            :key="cat.id"
                            class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700"
                        >
                            {{ cat.name }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div
                        v-if="product.description"
                        class="prose prose-sm max-w-none"
                    >
                        <h2 class="text-lg font-semibold text-amber-900">
                            Descrição
                        </h2>
                        <p class="whitespace-pre-line text-amber-800">
                            {{ product.description }}
                        </p>
                    </div>

                    <!-- Product Specs -->
                    <div
                        v-if="
                            product.material_type ||
                            product.print_time ||
                            product.approximate_weight
                        "
                        class="space-y-3"
                    >
                        <h3 class="text-sm font-semibold text-amber-900">
                            Especificações Técnicas
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                v-if="product.material_type"
                                class="rounded-lg border border-amber-100 bg-white p-3 shadow-sm"
                            >
                                <p class="text-xs text-amber-500">Material</p>
                                <p
                                    class="text-sm font-medium text-amber-900 capitalize"
                                >
                                    {{ product.material_type }}
                                </p>
                            </div>
                            <div
                                v-if="product.print_time"
                                class="rounded-lg border border-amber-100 bg-white p-3 shadow-sm"
                            >
                                <p class="text-xs text-amber-500">
                                    Tempo de Impressão
                                </p>
                                <p class="text-sm font-medium text-amber-900">
                                    {{ product.print_time }} min
                                </p>
                            </div>
                            <div
                                v-if="product.approximate_weight"
                                class="rounded-lg border border-amber-100 bg-white p-3 shadow-sm"
                            >
                                <p class="text-xs text-amber-500">
                                    Peso Aproximado
                                </p>
                                <p class="text-sm font-medium text-amber-900">
                                    {{ product.approximate_weight }} g
                                </p>
                            </div>
                            <div
                                v-if="product.height || product.width"
                                class="rounded-lg border border-amber-100 bg-white p-3 shadow-sm"
                            >
                                <p class="text-xs text-amber-500">Dimensões</p>
                                <p class="text-sm font-medium text-amber-900">
                                    {{ product.height || '-' }} ×
                                    {{ product.width || '-' }} mm
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Add to Cart Section -->
                    <div class="pt-2">
                        <div
                            v-if="getCartQty(product.id) > 0"
                            class="flex items-center gap-3"
                        >
                            <button
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                @click="removeFromCart(product.id)"
                            >
                                <Minus class="h-5 w-5" />
                            </button>
                            <span
                                class="min-w-[2rem] text-center text-lg font-bold text-amber-900"
                                >{{ getCartQty(product.id) }}</span
                            >
                            <button
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                @click="addToCart(product.id)"
                            >
                                <Plus class="h-5 w-5" />
                            </button>
                        </div>
                        <Button
                            v-else
                            size="lg"
                            class="w-full sm:w-auto"
                            :disabled="cartLoading"
                            @click="addToCart(product.id)"
                        >
                            <ShoppingBag class="mr-2 h-5 w-5" />
                            Adicionar ao carrinho
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <section
                v-if="relatedProducts && relatedProducts.length > 0"
                class="mt-16"
            >
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-amber-900">
                        Produtos Relacionados
                    </h2>
                    <p class="mt-1 text-sm text-amber-600">
                        Outros produtos da mesma categoria que você pode gostar
                    </p>
                </div>

                <div
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <Card
                        v-for="rp in relatedProducts"
                        :key="rp.id"
                        class="flex flex-col overflow-hidden transition-shadow hover:shadow-md"
                    >
                        <Link :href="`/store/${rp.slug}`" class="block">
                            <div
                                class="flex h-48 items-center justify-center overflow-hidden bg-amber-100"
                            >
                                <img
                                    v-if="getImageUrl(rp, 0)"
                                    :src="getImageUrl(rp, 0)"
                                    :alt="rp.name"
                                    class="h-full w-full object-cover transition-transform hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center"
                                >
                                    <Package class="h-10 w-10 text-amber-900" />
                                </div>
                            </div>
                        </Link>
                        <CardHeader class="pb-2">
                            <Link :href="`/store/${rp.slug}`" class="block">
                                <CardTitle
                                    class="line-clamp-1 text-base text-amber-900 transition hover:text-amber-600"
                                    >{{ rp.name }}</CardTitle
                                >
                            </Link>
                            <CardDescription
                                v-if="rp.description"
                                class="line-clamp-2 text-xs"
                                >{{ rp.description }}</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="flex-1 pb-2">
                            <p class="text-lg font-bold text-emerald-700">
                                {{ formatPrice(rp.sale_price) }}
                            </p>
                        </CardContent>
                        <CardFooter class="pt-0">
                            <div
                                v-if="getCartQty(rp.id) > 0"
                                class="flex w-full items-center justify-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                    @click="removeFromCart(rp.id)"
                                >
                                    <Minus class="h-3.5 w-3.5" />
                                </button>
                                <span
                                    class="min-w-[1.5rem] text-center text-sm font-medium"
                                    >{{ getCartQty(rp.id) }}</span
                                >
                                <button
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition hover:bg-amber-200"
                                    @click="addToCart(rp.id)"
                                >
                                    <Plus class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <Button
                                v-else
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

        <!-- Diálogo de compartilhamento -->
        <Dialog :open="shareDialogOpen" @update:open="shareDialogOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Compartilhar Produto</DialogTitle>
                    <DialogDescription>
                        {{ shareTitle }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Copiar Link -->
                    <Button
                        variant="outline"
                        class="flex items-center justify-center gap-2"
                        @click="copyLink"
                    >
                        <Copy v-if="!shareCopied" class="h-4 w-4" />
                        <Link2 v-else class="h-4 w-4 text-green-600" />
                        {{ shareCopied ? 'Copiado!' : 'Copiar Link' }}
                    </Button>

                    <!-- WhatsApp -->
                    <Button
                        variant="outline"
                        class="flex items-center justify-center gap-2 bg-green-50 hover:bg-green-100"
                        @click="shareWhatsApp"
                    >
                        <MessageCircle class="h-4 w-4 text-green-600" />
                        WhatsApp
                    </Button>

                    <!-- Facebook -->
                    <Button
                        variant="outline"
                        class="flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100"
                        @click="shareFacebook"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#1877F2">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                            />
                        </svg>
                        Facebook
                    </Button>

                    <!-- X (Twitter) -->
                    <Button
                        variant="outline"
                        class="flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100"
                        @click="shareTwitter"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#0F1419">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                            />
                        </svg>
                        X
                    </Button>

                    <!-- Telegram -->
                    <Button
                        variant="outline"
                        class="flex items-center justify-center gap-2 bg-sky-50 hover:bg-sky-100"
                        @click="shareTelegram"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#0088CC">
                            <path
                                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.26.333-.54.333l.194-2.76 5.02-4.536c.223-.198-.048-.308-.346-.11l-6.2 3.9-2.67-.834c-.58-.182-.592-.58.122-.862l10.43-4.02c.483-.18.904.117.746.876z"
                            />
                        </svg>
                        Telegram
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
