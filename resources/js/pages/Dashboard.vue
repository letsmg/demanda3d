<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Users,
    ShoppingCart,
    Box,
    TrendingUp,
    Calendar,
    Clock,
    DollarSign,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import { index as clientsIndex } from '@/routes/clients';
import { index as ordersIndex, edit as ordersEdit } from '@/routes/orders';
import { index as inputsIndex } from '@/routes/inputs';
import type { Order, Client, Input } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Painel',
                href: dashboard(),
            },
        ],
    },
});

type DashboardStats = {
    clients_count: number;
    orders_count: number;
    inputs_count: number;
    monthly_revenue: number;
    monthly_orders_count: number;
    pending_deliveries: number;
    total_revenue: number;
    avg_order_value: number;
    recent_orders: (Order & { client?: Client })[];
    recent_inputs: Input[];
};

const props = defineProps<{
    stats: DashboardStats;
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');
};
</script>

<template>
    <Head title="Painel" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Painel
                </h1>
                <p class="text-sm text-muted-foreground">
                    Visão geral do seu negócio de impressão 3D
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <Calendar class="h-4 w-4" />
                <span>{{
                    new Date().toLocaleDateString('pt-BR', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                    })
                }}</span>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card class="transition-all hover:shadow-md">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Total de Clientes</CardTitle
                    >
                    <div
                        class="rounded-lg bg-blue-100 p-2 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                    >
                        <Users class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ stats.clients_count }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        <Link
                            :href="clientsIndex()"
                            class="text-primary hover:underline"
                            >Ver todos os clientes</Link
                        >
                    </p>
                </CardContent>
            </Card>

            <Card class="transition-all hover:shadow-md">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Total de Pedidos</CardTitle
                    >
                    <div
                        class="rounded-lg bg-green-100 p-2 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                    >
                        <ShoppingCart class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ stats.orders_count }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        <Link
                            :href="ordersIndex()"
                            class="text-primary hover:underline"
                            >Ver todos os pedidos</Link
                        >
                    </p>
                </CardContent>
            </Card>

            <Card class="transition-all hover:shadow-md">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Receita Mensal</CardTitle
                    >
                    <div
                        class="rounded-lg bg-purple-100 p-2 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400"
                    >
                        <TrendingUp class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ formatCurrency(stats.monthly_revenue) }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        <span
                            >{{ stats.monthly_orders_count }} pedidos este
                            mês</span
                        >
                    </p>
                </CardContent>
            </Card>

            <Card class="transition-all hover:shadow-md">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Entregas Pendentes</CardTitle
                    >
                    <div
                        class="rounded-lg bg-amber-100 p-2 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                    >
                        <Clock class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ stats.pending_deliveries }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        <span class="text-amber-600 dark:text-amber-400"
                            >Aguardando entrega</span
                        >
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Secondary Stats -->
        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Resumo Financeiro</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between border-b pb-2"
                        >
                            <div class="flex items-center gap-2 text-sm">
                                <DollarSign class="h-4 w-4 text-green-600" />
                                <span>Receita Total</span>
                            </div>
                            <span class="font-semibold">{{
                                formatCurrency(stats.total_revenue)
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between border-b pb-2"
                        >
                            <div class="flex items-center gap-2 text-sm">
                                <TrendingUp class="h-4 w-4 text-blue-600" />
                                <span>Valor Médio por Pedido</span>
                            </div>
                            <span class="font-semibold">{{
                                formatCurrency(stats.avg_order_value)
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm">
                                <Box class="h-4 w-4 text-purple-600" />
                                <span>Tipos de Insumos</span>
                            </div>
                            <span class="font-semibold">{{
                                stats.inputs_count
                            }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Inputs -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Insumos Recentes</CardTitle>
                    <CardDescription
                        >Últimos materiais cadastrados</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div
                        v-if="stats.recent_inputs.length === 0"
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        Nenhum insumo cadastrado ainda.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="input in stats.recent_inputs"
                            :key="input.id"
                            class="flex items-center justify-between rounded-lg border p-3 text-sm"
                        >
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-primary" />
                                <span class="font-medium">{{
                                    input.filaments
                                }}</span>
                            </div>
                            <span class="text-muted-foreground">{{
                                formatCurrency(Number(input.cost_buy))
                            }}</span>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <Link
                            :href="inputsIndex()"
                            class="text-xs text-primary hover:underline"
                        >
                            Ver todos os insumos →
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Orders -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-lg">Pedidos Recentes</CardTitle>
                    <CardDescription
                        >Últimos pedidos registrados no sistema</CardDescription
                    >
                </div>
                <Link
                    :href="ordersIndex()"
                    class="text-sm text-primary hover:underline"
                >
                    Ver todos
                </Link>
            </CardHeader>
            <CardContent>
                <!-- Empty State -->
                <div
                    v-if="stats.recent_orders.length === 0"
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    Nenhum pedido encontrado. Crie seu primeiro pedido!
                </div>

                <!-- Orders List -->
                <template v-else>
                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="border-b text-left text-sm font-medium text-muted-foreground"
                                >
                                    <th class="pr-4 pb-3">Cliente</th>
                                    <th class="pr-4 pb-3">Data do Pedido</th>
                                    <th class="pr-4 pb-3">Data de Entrega</th>
                                    <th class="pr-4 pb-3">Valor</th>
                                    <th class="pb-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="order in stats.recent_orders"
                                    :key="order.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-3 pr-4 text-sm font-medium">
                                        <Link
                                            :href="
                                                ordersEdit({ order: order.id })
                                            "
                                            class="text-primary hover:underline"
                                        >
                                            {{
                                                order.client?.name ||
                                                `Cliente #${order.client_id}`
                                            }}
                                        </Link>
                                    </td>
                                    <td class="py-3 pr-4 text-sm">
                                        {{ formatDate(order.order_date) }}
                                    </td>
                                    <td class="py-3 pr-4 text-sm">
                                        {{ formatDate(order.delivery_date) }}
                                    </td>
                                    <td class="py-3 pr-4 text-sm font-medium">
                                        {{
                                            formatCurrency(Number(order.price))
                                        }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <Badge
                                            :variant="
                                                new Date(order.delivery_date) >
                                                new Date()
                                                    ? 'secondary'
                                                    : 'default'
                                            "
                                        >
                                            {{
                                                new Date(order.delivery_date) >
                                                new Date()
                                                    ? 'Pendente'
                                                    : 'Entregue'
                                            }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="space-y-3 md:hidden">
                        <div
                            v-for="order in stats.recent_orders"
                            :key="order.id"
                            class="rounded-lg border p-4"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <span class="font-medium">{{
                                    order.client?.name ||
                                    `Cliente #${order.client_id}`
                                }}</span>
                                <Badge
                                    :variant="
                                        new Date(order.delivery_date) >
                                        new Date()
                                            ? 'secondary'
                                            : 'default'
                                    "
                                >
                                    {{
                                        new Date(order.delivery_date) >
                                        new Date()
                                            ? 'Pendente'
                                            : 'Entregue'
                                    }}
                                </Badge>
                            </div>
                            <div
                                class="space-y-1 text-sm text-muted-foreground"
                            >
                                <div class="flex justify-between">
                                    <span>Pedido:</span>
                                    <span>{{
                                        formatDate(order.order_date)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Entrega:</span>
                                    <span>{{
                                        formatDate(order.delivery_date)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between font-medium text-foreground"
                                >
                                    <span>Valor:</span>
                                    <span>{{
                                        formatCurrency(Number(order.price))
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </CardContent>
        </Card>
    </div>
</template>
