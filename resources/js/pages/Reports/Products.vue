<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingBag } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    products: any;
}>();

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}
</script>

<template>
    <Head title="Relatório de Produtos Ativos" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link href="/reports">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Produtos Ativos</h1>
                <p class="text-sm text-muted-foreground">Relatório de produtos disponíveis na vitrine</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <ShoppingBag class="h-4 w-4" />
                    {{ products.meta?.total || products.data?.length || 0 }} produtos ativos
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3 px-4 font-medium">Nome</th>
                                <th class="py-3 px-4 font-medium">Material</th>
                                <th class="py-3 px-4 font-medium">Peso (g)</th>
                                <th class="py-3 px-4 font-medium">Impressão</th>
                                <th class="py-3 px-4 font-medium">Pintura</th>
                                <th class="py-3 px-4 font-medium">Custo</th>
                                <th class="py-3 px-4 font-medium">Venda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in (Array.isArray(products) ? products : products.data)" :key="product.id" class="border-b hover:bg-muted/50">
                                <td class="py-3 px-4 font-medium">{{ product.name }}</td>
                                <td class="py-3 px-4">{{ product.material_type }}</td>
                                <td class="py-3 px-4">{{ product.approximate_weight }}</td>
                                <td class="py-3 px-4">{{ product.print_time }}min</td>
                                <td class="py-3 px-4">{{ product.painting_time ? product.painting_time + 'min' : '—' }}</td>
                                <td class="py-3 px-4">{{ formatPrice(product.approximate_cost) }}</td>
                                <td class="py-3 px-4">{{ formatPrice(product.sale_price) }}</td>
                            </tr>
                            <tr v-if="!(Array.isArray(products) ? products : products.data)?.length">
                                <td colspan="7" class="py-8 text-center text-muted-foreground">Nenhum produto ativo encontrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>