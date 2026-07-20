<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, ChevronRight, ShoppingBag } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

defineProps<{
    client: any;
    orders: {
        data: Array<{
            id: number;
            status: string;
            order_date: string;
            delivery_date: string | null;
            price: number;
            amount_total: number;
            contracted_description: string | null;
            product: { id: number; name: string; slug: string } | null;
            created_at: string;
        }>;
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

function statusLabel(status: string): string {
    const map: Record<string, string> = {
        pending: 'Pendente',
        paid: 'Pago',
        shipped: 'Enviado',
        delivered: 'Entregue',
        canceled: 'Cancelado',
    };
    return map[status] || status;
}

function statusColor(status: string): string {
    const map: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800 border-yellow-300',
        paid: 'bg-blue-100 text-blue-800 border-blue-300',
        shipped: 'bg-purple-100 text-purple-800 border-purple-300',
        delivered: 'bg-green-100 text-green-800 border-green-300',
        canceled: 'bg-red-100 text-red-800 border-red-300',
    };
    return map[status] || 'bg-gray-100 text-gray-800 border-gray-300';
}
</script>

<template>
    <Head title="Meus Pedidos" />

    <h1 class="mb-6 text-2xl font-bold tracking-tight text-amber-900">
        Meus Pedidos
    </h1>

    <Card v-if="orders.data.length === 0">
        <CardHeader class="py-12 text-center">
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100"
            >
                <ShoppingBag class="h-8 w-8 text-amber-500" />
            </div>
            <CardTitle class="text-lg">Nenhum pedido encontrado</CardTitle>
            <CardDescription
                >Você ainda não fez nenhum pedido. Visite nossa loja para
                começar.</CardDescription
            >
            <Link
                href="/store"
                class="mt-4 inline-flex items-center gap-2 rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700"
            >
                <ShoppingBag class="h-4 w-4" />
                Ir para a Loja
            </Link>
        </CardHeader>
    </Card>

    <div v-else class="space-y-4">
        <Card
            v-for="order in orders.data"
            :key="order.id"
            class="overflow-hidden"
        >
            <div class="p-4 sm:p-6">
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex-1">
                        <div class="mb-2 flex items-center gap-3">
                            <Package class="h-5 w-5 text-amber-600" />
                            <h3 class="font-semibold text-amber-900">
                                Pedido #{{ String(order.id).padStart(6, '0') }}
                            </h3>
                            <Badge
                                :class="statusColor(order.status)"
                                variant="outline"
                            >
                                {{ statusLabel(order.status) }}
                            </Badge>
                        </div>

                        <div class="ml-8 space-y-1 text-sm">
                            <div
                                v-if="order.items && order.items.length > 0"
                                class="text-amber-800"
                            >
                                <span class="text-muted-foreground"
                                    >Produtos:</span
                                >
                                <template
                                    v-for="item in order.items"
                                    :key="item.id"
                                >
                                    <Link
                                        :href="`/store/${item.snapshot_product_name?.toLowerCase().replace(/ /g, '-')}`"
                                        class="ml-1 font-medium text-amber-700 hover:underline"
                                    >
                                        {{ item.snapshot_product_name }}
                                    </Link>
                                    <span class="text-xs text-muted-foreground"
                                        >x{{ item.quantity }}</span
                                    >
                                </template>
                            </div>
                            <p class="text-muted-foreground">
                                Data:
                                {{
                                    new Date(
                                        order.order_date,
                                    ).toLocaleDateString('pt-BR')
                                }}
                            </p>
                            <p
                                v-if="order.delivery_date"
                                class="text-muted-foreground"
                            >
                                Entrega prevista:
                                {{
                                    new Date(
                                        order.delivery_date,
                                    ).toLocaleDateString('pt-BR')
                                }}
                            </p>
                            <p
                                v-if="order.contracted_description"
                                class="line-clamp-2 text-muted-foreground"
                            >
                                {{ order.contracted_description }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right sm:min-w-[120px]">
                        <p class="text-lg font-bold text-amber-900">
                            R$
                            {{
                                Number(
                                    order.amount_total || order.price,
                                ).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                })
                            }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{
                                new Date(order.created_at).toLocaleDateString(
                                    'pt-BR',
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </Card>

        <!-- Paginação -->
        <div
            v-if="orders.last_page > 1"
            class="flex items-center justify-center gap-2 pt-4"
        >
            <Link
                v-for="link in orders.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm"
                :class="{
                    'bg-amber-600 font-bold text-white': link.active,
                    'text-amber-700 hover:bg-amber-100':
                        !link.active && link.url,
                    'pointer-events-none text-muted-foreground opacity-50':
                        !link.url,
                }"
            />
        </div>
    </div>
</template>
