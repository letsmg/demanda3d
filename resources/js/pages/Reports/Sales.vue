<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    DollarSign,
    ShoppingBag,
    BarChart3,
    Calendar,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    sales: any;
    totals: { total_revenue: number; total_orders: number; avg_ticket: number };
    filters: { date_from: string | null; date_to: string | null };
}>();

const dateFrom = ref('');
const dateTo = ref('');

function applyFilter() {
    const params: Record<string, string> = {};

    if (dateFrom.value) {
        params.date_from = dateFrom.value;
    }

    if (dateTo.value) {
        params.date_to = dateTo.value;
    }

    router.get('/reports/sales', params, {
        preserveState: true,
        replace: true,
    });
}

function clearFilter() {
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/reports/sales', {}, { preserveState: true, replace: true });
}

function formatPrice(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Relatório de Vendas" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link href="/reports">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Vendas
                </h1>
                <p class="text-sm text-muted-foreground">
                    Relatório de vendas com filtro por período
                </p>
            </div>
        </div>

        <!-- Totais -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Receita</CardTitle>
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
                    <CardTitle class="text-sm font-medium">Pedidos</CardTitle>
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

        <!-- Filtro -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <Calendar class="h-4 w-4" /> Filtro por Período
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap items-end gap-4">
                    <div class="space-y-2">
                        <Label for="date_from">Data Início</Label>
                        <Input
                            id="date_from"
                            v-model="dateFrom"
                            type="date"
                            class="w-48"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_to">Data Fim</Label>
                        <Input
                            id="date_to"
                            v-model="dateTo"
                            type="date"
                            class="w-48"
                        />
                    </div>
                    <Button @click="applyFilter">Filtrar</Button>
                    <Button
                        v-if="filters.date_from || filters.date_to"
                        variant="ghost"
                        @click="clearFilter"
                        >Limpar</Button
                    >
                </div>
            </CardContent>
        </Card>

        <!-- Tabela -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <ShoppingBag class="h-4 w-4" />
                    {{ sales.meta?.total || sales.data?.length || 0 }} vendas
                    encontradas
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Pedido</th>
                                <th class="px-4 py-3 font-medium">Cliente</th>
                                <th class="px-4 py-3 font-medium">Produto</th>
                                <th class="px-4 py-3 font-medium">Data</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sale in Array.isArray(sales)
                                    ? sales
                                    : sales.data"
                                :key="sale.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    #{{ sale.id }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ sale.client?.display_name || '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ sale.product?.name || '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatDate(sale.created_at) }}
                                </td>
                                <td class="px-4 py-3">{{ sale.status }}</td>
                                <td class="px-4 py-3">
                                    {{ formatPrice(sale.price) }}
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !(Array.isArray(sales) ? sales : sales.data)
                                        ?.length
                                "
                            >
                                <td
                                    colspan="6"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    Nenhuma venda encontrada.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
