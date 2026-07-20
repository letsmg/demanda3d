<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    orders: {
        data: {
            id: number;
            status: string;
            order_date: string;
            delivery_date: string;
            amount_total: number;
            client: { display_name: string } | null;
            tenant: { fantasy_name: string } | null;
            items: { snapshot_product_name: string; quantity: number }[];
        }[];
    };
}>();
</script>

<template>
    <Head title="Pedidos — Transportadora" />
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <h1 class="text-xl font-bold">
            Pedidos de Todos os Vendedores (Acordos Ativos)
        </h1>

        <Card>
            <CardContent class="pt-4">
                <div
                    v-if="orders.data.length === 0"
                    class="text-sm text-gray-500"
                >
                    Nenhum pedido encontrado.
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-600">
                            <th class="py-2">Pedido</th>
                            <th>Status</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="border-b hover:bg-gray-50"
                        >
                            <td class="py-2">#{{ order.id }}</td>
                            <td>
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        order.status === 'delivered'
                                            ? 'bg-green-100 text-green-700'
                                            : order.status === 'paid'
                                              ? 'bg-blue-100 text-blue-700'
                                              : 'bg-amber-100 text-amber-700'
                                    "
                                    >{{ order.status }}</span
                                >
                            </td>
                            <td>{{ order.tenant?.fantasy_name ?? '—' }}</td>
                            <td>{{ order.client?.display_name ?? '—' }}</td>
                            <td>
                                {{
                                    order.items
                                        ?.map((i) => i.snapshot_product_name)
                                        .join(', ') ?? '—'
                                }}
                            </td>
                            <td class="text-right">
                                R$ {{ Number(order.amount_total).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <Link
            href="/carrier/dashboard"
            class="text-sm text-blue-600 hover:underline"
            >← Voltar ao Painel</Link
        >
    </div>
</template>
