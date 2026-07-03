<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ShoppingBag,
    Trash2,
    Minus,
    Plus,
    ArrowLeft,
    CreditCard,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { setCartCount } from '@/stores/cartStore';

defineOptions({
    layout: null, // Usa o layout padrão resolvido pelo app.ts (ClientPageLayout para Client/*)
});

const props = defineProps<{
    items: Array<{
        id: number;
        product_id: number;
        quantity: number;
        product: {
            id: number;
            name: string;
            sale_price: string;
            image_url: string | null;
        };
    }>;
    total: number;
    count: number;
}>();

function getCsrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta as HTMLMetaElement).content : '';
}

function updateLocalCart(data: any) {
    setCartCount(data.count || 0);
}

async function decrease(itemId: number, currentQty: number) {
    if (currentQty <= 1) {
        await removeItem(itemId);
        return;
    }
    try {
        const res = await fetch('/cart/' + itemId, {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ quantity: currentQty - 1 }),
        });
        if (res.ok) {
            const data = await res.json();
            updateLocalCart(data);
            router.reload({ only: ['items', 'total', 'count'] });
        }
    } catch {
        /* ignore */
    }
}

async function increase(itemId: number, currentQty: number) {
    try {
        const res = await fetch('/cart/' + itemId, {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ quantity: currentQty + 1 }),
        });
        if (res.ok) {
            const data = await res.json();
            updateLocalCart(data);
            router.reload({ only: ['items', 'total', 'count'] });
        }
    } catch {
        /* ignore */
    }
}

async function removeItem(itemId: number) {
    try {
        const res = await fetch('/cart/' + itemId, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        });
        if (res.ok) {
            const data = await res.json();
            updateLocalCart(data);
            router.reload({ only: ['items', 'total', 'count'] });
        }
    } catch {
        /* ignore */
    }
}

async function clearCart() {
    try {
        const res = await fetch('/cart/clear', {
            method: 'POST',
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        });
        if (res.ok) {
            const data = await res.json();
            updateLocalCart(data);
            router.reload({ only: ['items', 'total', 'count'] });
        }
    } catch {
        /* ignore */
    }
}

function goToCheckout() {
    router.post(
        '/checkout',
        {},
        {
            onSuccess: (response) => {
                const url = (response as any).props?.stripe_url;
                if (url) {
                    window.location.href = url;
                }
            },
        },
    );
}

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
}
</script>

<template>
    <Head title="Cart - Demanda3D">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-amber-50">
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-amber-900"
                    >
                        Cart
                    </h1>
                    <p class="mt-1 text-sm text-amber-600">
                        {{ count }} {{ count === 1 ? 'item' : 'items' }}
                    </p>
                </div>
                <Link
                    href="/store"
                    class="inline-flex items-center gap-1 text-sm font-medium text-amber-600 transition hover:text-amber-700"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Continue Shopping
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="items.length === 0" class="py-16 text-center">
                <ShoppingBag class="mx-auto h-16 w-16 text-amber-200" />
                <h2 class="mt-4 text-lg font-semibold text-amber-700">
                    Your cart is empty
                </h2>
                <p class="mt-1 text-sm text-amber-500">
                    Add products from the store to continue.
                </p>
                <Link href="/store">
                    <Button class="mt-6">Go to Store</Button>
                </Link>
            </div>

            <!-- Cart Items -->
            <div v-else class="space-y-4">
                <Card v-for="item in items" :key="item.id">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center gap-4">
                            <!-- Product Image -->
                            <div
                                class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-amber-100"
                            >
                                <img
                                    v-if="item.product.image_url"
                                    :src="item.product.image_url"
                                    :alt="item.product.name"
                                    class="h-full w-full object-cover"
                                />
                                <ShoppingBag
                                    v-else
                                    class="h-full w-full p-4 text-gray-300"
                                />
                            </div>

                            <!-- Product Info -->
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="truncate text-base font-semibold text-amber-900"
                                >
                                    {{ item.product.name }}
                                </h3>
                                <p
                                    class="mt-1 text-lg font-bold text-emerald-600"
                                >
                                    {{ formatPrice(item.product.sale_price) }}
                                </p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    @click="decrease(item.id, item.quantity)"
                                >
                                    <Minus class="h-3.5 w-3.5" />
                                </Button>
                                <span
                                    class="min-w-[2rem] text-center text-sm font-medium"
                                >
                                    {{ item.quantity }}
                                </span>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8"
                                    @click="increase(item.id, item.quantity)"
                                >
                                    <Plus class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </div>

                        <!-- Item Row Footer -->
                        <div
                            class="mt-3 flex items-center justify-between border-t border-amber-200 pt-3"
                        >
                            <span class="text-sm font-medium text-amber-700">
                                Subtotal:
                                {{
                                    formatPrice(
                                        Number(item.product.sale_price) *
                                            item.quantity,
                                    )
                                }}
                            </span>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-rose-500 hover:bg-rose-50 hover:text-rose-600"
                                @click="removeItem(item.id)"
                            >
                                <Trash2 class="mr-1 h-4 w-4" />
                                Remove
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg text-amber-900"
                            >Order Summary</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            class="flex justify-between text-sm text-amber-600"
                        >
                            <span>Subtotal ({{ count }} items)</span>
                            <span>{{ formatPrice(total) }}</span>
                        </div>
                        <div
                            class="flex justify-between text-sm text-amber-600"
                        >
                            <span>Shipping</span>
                            <span class="font-medium text-amber-500"
                                >To be calculated</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-t border-amber-200 pt-3 text-lg font-bold text-amber-900"
                        >
                            <span>Total</span>
                            <span>{{ formatPrice(total) }}</span>
                        </div>
                    </CardContent>
                    <CardFooter
                        class="flex flex-col gap-3 sm:flex-row sm:justify-between"
                    >
                        <Button
                            variant="outline"
                            class="w-full sm:w-auto"
                            @click="clearCart"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Clear Cart
                        </Button>
                        <Button
                            class="w-full bg-amber-500 text-white hover:bg-amber-600 sm:w-auto"
                            size="lg"
                            @click="goToCheckout"
                        >
                            <CreditCard class="mr-2 h-5 w-5" />
                            Checkout
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </main>
    </div>
</template>
