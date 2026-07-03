<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BarChart3,
    PackageOpen,
    ShoppingBag,
    DollarSign,
    ArrowRight,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    totals: {
        total_revenue: number;
        total_orders: number;
        avg_ticket: number;
    };
}>();

function formatPrice(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}
</script>

<template>
    <Head title="Relatórios" />

    <div class="space-y-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                Relatórios
            </h1>
            <p class="text-sm text-muted-foreground">
                Dashboard de relatórios para gestão e administradores
            </p>
        </div>

        <!-- Resumo geral -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Receita Total</CardTitle
                    >
                    <DollarSign class="h-4 w-4 text-emerald-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ formatPrice(totals.total_revenue) }}
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Total de Pedidos</CardTitle
                    >
                    <ShoppingBag class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ totals.total_orders }}
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Ticket Médio</CardTitle
                    >
                    <BarChart3 class="h-4 w-4 text-violet-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ formatPrice(totals.avg_ticket) }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Links para relatórios -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <PackageOpen class="h-4 w-4" />
                        Insumos por Estoque
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="mb-4 text-sm text-muted-foreground">
                        Visualize insumos com filtro de quantidade mínima em
                        estoque.
                    </p>
                    <Button as-child variant="outline" class="w-full">
                        <Link href="/reports/inputs">
                            Acessar <ArrowRight class="ml-2 h-4 w-4" />
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <ShoppingBag class="h-4 w-4" />
                        Produtos Ativos
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="mb-4 text-sm text-muted-foreground">
                        Lista de produtos ativos na vitrine com dados completos
                        de impressão 3D.
                    </p>
                    <Button as-child variant="outline" class="w-full">
                        <Link href="/reports/products">
                            Acessar <ArrowRight class="ml-2 h-4 w-4" />
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <DollarSign class="h-4 w-4" />
                        Vendas
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="mb-4 text-sm text-muted-foreground">
                        Relatório de vendas com filtro por período e totais
                        agregados.
                    </p>
                    <Button as-child variant="outline" class="w-full">
                        <Link href="/reports/sales">
                            Acessar <ArrowRight class="ml-2 h-4 w-4" />
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
