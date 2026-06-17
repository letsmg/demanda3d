<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Package, Plus, Edit, Trash2, Calendar, DollarSign } from '@lucide/vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { create as ordersCreate, edit as ordersEdit } from '@/routes/orders';
import type { Order, Client } from '@/types';

type PaginatedOrders = {
    data: (Order & { client?: Client })[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
};

const props = defineProps<{
    orders: PaginatedOrders;
}>();

const showDeleteDialog = ref(false);
const deletingOrder = ref<Order | null>(null);
const deleteForm = useForm({});

const goToPage = (pageNumber: number) => {
    router.get('/orders', { page: pageNumber }, { preserveState: true, replace: true });
};

const confirmDelete = (order: Order) => {
    deletingOrder.value = order;
    showDeleteDialog.value = true;
};

const executeDelete = () => {
    if (!deletingOrder.value) return;
    deleteForm.delete(`/orders/${deletingOrder.value.id}`, {
        preserveState: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingOrder.value = null;
        },
    });
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

const formatDate = (dateStr: string) =>
    new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');

const isPending = (deliveryDate: string) => new Date(deliveryDate) > new Date();
</script>

<template>
    <Head title="Pedidos" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Pedidos</h1>
                <p class="text-sm text-muted-foreground">Gerenciar pedidos de impressão 3D ({{ orders.total }} total)</p>
            </div>
            <Button as-child>
                <Link :href="ordersCreate()">
                    <Plus class="mr-2 h-4 w-4" /> Novo Pedido
                </Link>
            </Button>
        </div>

        <div v-if="orders.data.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <Package class="mb-4 h-12 w-12 text-muted-foreground/50" />
            <h3 class="mb-2 text-lg font-semibold">Nenhum pedido encontrado</h3>
            <p class="mb-6 text-sm text-muted-foreground">Comece criando seu primeiro pedido.</p>
            <Button as-child>
                <Link :href="ordersCreate()"><Plus class="mr-2 h-4 w-4" /> Criar Pedido</Link>
            </Button>
        </div>

        <template v-else>
            <div class="hidden overflow-hidden rounded-xl border md:block">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-left text-sm font-medium text-muted-foreground">
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Data Pedido</th>
                            <th class="px-6 py-4">Data Entrega</th>
                            <th class="px-6 py-4">Valor</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders.data" :key="order.id" class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ order.client?.name || `Cliente #${order.client_id}` }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatDate(order.order_date) }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatDate(order.delivery_date) }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ formatCurrency(Number(order.price)) }}</td>
                            <td class="px-6 py-4">
                                <Badge :variant="isPending(order.delivery_date) ? 'secondary' : 'default'">
                                    {{ isPending(order.delivery_date) ? 'Pendente' : 'Entregue' }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="ordersEdit({ order: order.id })"><Edit class="h-3 w-3" /></Link>
                                    </Button>
                                    <Button variant="outline" size="sm" class="text-destructive hover:bg-destructive/10" @click="confirmDelete(order)">
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 md:hidden">
                <Card v-for="order in orders.data" :key="order.id" class="border-border/50">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <CardTitle class="text-base">{{ order.client?.name || `Cliente #${order.client_id}` }}</CardTitle>
                            <Badge :variant="isPending(order.delivery_date) ? 'secondary' : 'default'">
                                {{ isPending(order.delivery_date) ? 'Pendente' : 'Entregue' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <Calendar class="h-3.5 w-3.5" />
                                <span>{{ formatDate(order.order_date) }} → {{ formatDate(order.delivery_date) }}</span>
                            </div>
                            <div class="flex items-center gap-2 font-medium">
                                <DollarSign class="h-3.5 w-3.5 text-green-600" />
                                <span>{{ formatCurrency(Number(order.price)) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Button variant="outline" size="sm" class="flex-1" as-child>
                                <Link :href="ordersEdit({ order: order.id })"><Edit class="mr-1 h-3 w-3" /> Editar</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="flex-1 text-destructive" @click="confirmDelete(order)">
                                <Trash2 class="mr-1 h-3 w-3" /> Excluir
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="orders.last_page > 1" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                <p class="text-sm text-muted-foreground">Mostrando {{ orders.from }} a {{ orders.to }} de {{ orders.total }} pedidos</p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="orders.current_page === 1" @click="goToPage(orders.current_page - 1)">Anterior</Button>
                    <span class="flex items-center px-4 text-sm">Página {{ orders.current_page }} de {{ orders.last_page }}</span>
                    <Button variant="outline" size="sm" :disabled="orders.current_page === orders.last_page" @click="goToPage(orders.current_page + 1)">Próxima</Button>
                </div>
            </div>
        </template>
    </div>

    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Excluir Pedido</DialogTitle>
                <DialogDescription>Tem certeza que deseja excluir o pedido #{{ deletingOrder?.id }}? Esta ação não pode ser desfeita.</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false">Cancelar</Button>
                <Button variant="destructive" @click="executeDelete">Excluir</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>