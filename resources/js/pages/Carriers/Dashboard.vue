<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    carrier: {
        id: number;
        fantasy_name: string;
        company_name: string;
        document_type: string;
        document: string;
        phone: string;
        address: string;
        email: string;
        website_url: string | null;
        coverageRanges: {
            id: number;
            title: string;
            cep_start: string;
            cep_end: string;
        }[];
        rating_average: number;
        rating_count: number;
    };
    activeAgreementsCount: number;
    pendingAgreementsCount: number;
    recentOrders: {
        id: number;
        status: string;
        order_date: string;
        delivery_date: string;
        amount_total: number;
        client: { display_name: string } | null;
        items: { snapshot_product_name: string; quantity: number }[];
    }[];
}>();
</script>

<template>
    <Head title="Painel da Transportadora" />
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Painel — {{ carrier.fantasy_name }}
        </h1>

        <!-- Resumo -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Acordos Ativos</CardTitle
                    ></CardHeader
                >
                <CardContent
                    ><p class="text-3xl font-bold text-green-600">
                        {{ activeAgreementsCount }}
                    </p></CardContent
                >
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Convites Pendentes</CardTitle
                    ></CardHeader
                >
                <CardContent
                    ><p class="text-3xl font-bold text-amber-500">
                        {{ pendingAgreementsCount }}
                    </p></CardContent
                >
            </Card>
            <Card>
                <CardHeader
                    ><CardTitle class="text-sm"
                        >Avaliação</CardTitle
                    ></CardHeader
                >
                <CardContent>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ carrier.rating_average ?? '—' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ carrier.rating_count }} avaliações
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Orders recentes -->
        <Card>
            <CardHeader>
                <CardTitle>Pedidos Recentes (todos os vendedores)</CardTitle>
            </CardHeader>
            <CardContent>
                <div
                    v-if="recentOrders.length === 0"
                    class="text-sm text-gray-500"
                >
                    Nenhum pedido recente.
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-600">
                            <th class="py-2">Pedido</th>
                            <th>Status</th>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in recentOrders"
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

        <!-- Links -->
        <div class="flex gap-4">
            <Link
                href="/carrier/profile"
                class="text-sm text-blue-600 hover:underline"
                >Editar Perfil</Link
            >
            <Link
                href="/carrier/agreements"
                class="text-sm text-blue-600 hover:underline"
                >Contratos / Acordos</Link
            >
            <Link
                href="/carrier/orders"
                class="text-sm text-blue-600 hover:underline"
                >Ver todos os Pedidos</Link
            >
        </div>
    </div>
</template>
