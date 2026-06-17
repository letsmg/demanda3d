<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Package, Plus, Search, Edit, Trash2, Calendar, DollarSign } from '@lucide/vue';
import { create as ordersCreate, edit as ordersEdit } from '@/routes/orders';
import type { Order, Client } from '@/types';

type PaginatedData = {
    data: (Order & { client?: Client })[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
};

const orders = ref<PaginatedData | null>(null);
const loading = ref(true);
const page = ref(1);
const searchQuery = ref('');

const showDeleteDialog = ref(false);
const deletingOrder = ref<Order | null>(null);
const deleting = ref(false);

const fetchOrders = async (pageNumber: number = 1) => {
    loading.value = true;
    try {
        const params = new URLSearchParams({
            page: pageNumber.toString(),
            per_page: '10',
        });
        const response = await fetch(`/api/orders?${params}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) {
            orders.value = { data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
            return;
        }
        const data = await response.json();
        orders.value = data;
    } catch (error) {
        console.error('Error fetching orders:', error);
        orders.value = { data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
    } finally {
        loading.value = false;
    }
};

const confirmDelete = (order: Order) => {
    deletingOrder.value = order;
    showDeleteDialog.value = true;
};

const executeDelete = async () => {
    if (!deletingOrder.value) return;
    deleting.value = true;
    try {
        await fetch(`/api/orders/${deletingOrder.value.id}`, { method: 'DELETE' });
        showDeleteDialog.value = false;
        deletingOrder.value = null;
        fetchOrders(page.value);
    } catch (error) {
        console.error('Error deleting order:', error);
    } finally {
        deleting.value = false;
    }
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

const formatDate = (dateStr: string) =>
    new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');

const isPending = (deliveryDate: string) => new Date(deliveryDate) > new Date();

onMounted(() => fetchOrders());
</script>

<template>
    <Head title="Orders" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Orders</h1>
                <p class="text-sm text-muted-foreground">Manage 3D printing orders</p>
            </div>
            <Button as-child>
                <Link :href="ordersCreate()">
                    <Plus class="mr-2 h-4 w-4" /> New Order
                </Link>
            </Button>
        </div>

        <div class="relative">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="searchQuery" placeholder="Search orders..." class="pl-10" />
        </div>

        <template v-if="loading && !orders">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Skeleton v-for="i in 6" :key="i" class="h-36 rounded-xl" />
            </div>
        </template>

        <div v-else-if="orders && orders.data.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <Package class="mb-4 h-12 w-12 text-muted-foreground/50" />
            <h3 class="mb-2 text-lg font-semibold">No orders found</h3>
            <p class="mb-6 text-sm text-muted-foreground">Get started by creating your first order.</p>
            <Button as-child>
                <Link :href="ordersCreate()"><Plus class="mr-2 h-4 w-4" /> Create Order</Link>
            </Button>
        </div>

        <template v-else-if="orders">
            <div class="hidden overflow-hidden rounded-xl border md:block">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-left text-sm font-medium text-muted-foreground">
                            <th class="px-6 py-4">Client</th>
                            <th class="px-6 py-4">Order Date</th>
                            <th class="px-6 py-4">Delivery Date</th>
                            <th class="px-6 py-4">Price</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders.data" :key="order.id" class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                            <td class="px-6 py-4 font-medium">Client #{{ order.client_id }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatDate(order.order_date) }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatDate(order.delivery_date) }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ formatCurrency(Number(order.price)) }}</td>
                            <td class="px-6 py-4">
                                <Badge :variant="isPending(order.delivery_date) ? 'secondary' : 'default'">
                                    {{ isPending(order.delivery_date) ? 'Pending' : 'Delivered' }}
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
                            <CardTitle class="text-base">Client #{{ order.client_id }}</CardTitle>
                            <Badge :variant="isPending(order.delivery_date) ? 'secondary' : 'default'">
                                {{ isPending(order.delivery_date) ? 'Pending' : 'Delivered' }}
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
                                <Link :href="ordersEdit({ order: order.id })"><Edit class="mr-1 h-3 w-3" /> Edit</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="flex-1 text-destructive hover:bg-destructive/10" @click="confirmDelete(order)">
                                <Trash2 class="mr-1 h-3 w-3" /> Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="orders.last_page > 1" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                <p class="text-sm text-muted-foreground">Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders</p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="orders.current_page === 1" @click="page--; fetchOrders(page)">Previous</Button>
                    <span class="flex items-center px-4 text-sm">Page {{ orders.current_page }} of {{ orders.last_page }}</span>
                    <Button variant="outline" size="sm" :disabled="orders.current_page === orders.last_page" @click="page++; fetchOrders(page)">Next</Button>
                </div>
            </div>
        </template>
    </div>

    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Order</DialogTitle>
                <DialogDescription>Are you sure you want to delete order #{{ deletingOrder?.id }}? This action cannot be undone.</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false" :disabled="deleting">Cancel</Button>
                <Button variant="destructive" @click="executeDelete" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>