<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingBag } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = defineProps<{
    products: any[];
}>();

const formatPrice = (value: string | number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
};

const calcCashPrice = (price: string | number, discount: string | number) => {
    const p = Number(price);
    const d = Number(discount);
    return p - (p * d / 100);
};
</script>

<template>
    <Head title="Vitrine - Demanda3D" />

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Vitrine</h1>
                        <p class="mt-1 text-sm text-gray-500">Produtos disponíveis de todos os nossos produtores</p>
                    </div>
                    <ShoppingBag class="h-8 w-8 text-blue-600" />
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div v-if="products.length === 0" class="text-center py-16">
                <ShoppingBag class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900">Nenhum produto disponível</h3>
                <p class="mt-1 text-sm text-gray-500">Volte mais tarde para conferir nossos produtos.</p>
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="product in products" :key="product.id" class="flex flex-col">
                    <CardHeader>
                        <div v-if="product.image_path" class="mb-4 h-48 w-full overflow-hidden rounded-lg bg-gray-200">
                            <img :src="product.image_path" :alt="product.name" class="h-full w-full object-cover" />
                        </div>
                        <div v-else class="mb-4 flex h-48 w-full items-center justify-center rounded-lg bg-gray-100">
                            <ShoppingBag class="h-12 w-12 text-gray-300" />
                        </div>
                        <CardTitle class="text-lg">{{ product.name }}</CardTitle>
                        <CardDescription v-if="product.description" class="line-clamp-2">
                            {{ product.description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <div class="space-y-1">
                            <p class="text-2xl font-bold text-gray-900">{{ formatPrice(product.price_sale) }}</p>
                            <p v-if="Number(product.discount_cash) > 0" class="text-sm text-green-600">
                                À vista: {{ formatPrice(calcCashPrice(product.price_sale, product.discount_cash)) }}
                                ({{ product.discount_cash }}% off)
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button class="w-full" variant="default">
                            Entrar em Contato
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </main>
    </div>
</template>