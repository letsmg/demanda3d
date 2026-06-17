<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Users, ShoppingCart, Box, TrendingUp, Calendar, Clock, DollarSign } from '@lucide/vue';
import { dashboard, clients, orders, inputs } from '@/routes';
import type { Order, Client, Input } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Types
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

// State
const stats = ref<DashboardStats | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

// Fetch dashboard data
const fetchDashboard = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [clientsRes, ordersRes, inputsRes] = await Promise.all([
            fetch('/api/clients?per_page=1'),
            fetch('/api/orders?per_page=5'),
            fetch('/api/inputs?per_page=3'),
        ]);

        const clientsData = await clientsRes.json();
        const ordersData = await ordersRes.json();
        const inputsData = await inputsRes.json();

        // Calculate stats
        const allOrders: (Order & { client?: Client })[] = ordersData.data || [];
        const monthlyOrders = allOrders.filter((o: Order) => {
            const orderDate = new Date(o.order_date);
            const now = new Date();
            return orderDate.getMonth() === now.getMonth() &&
                   orderDate.getFullYear() === now.getFullYear();
        });

        const monthlyRevenue = monthlyOrders.reduce((sum: number, o: Order) =>
            sum + Number(o.price), 0
        );

        const pendingDeliveries = allOrders.filter((o: Order) => {
            const deliveryDate = new Date(o.delivery_date);
            return deliveryDate > new Date();
        }).length;

        const totalRevenue = allOrders.reduce((sum: number, o: Order) =>
            sum + Number(o.price), 0
        );

        const avgOrderValue = allOrders.length > 0
            ? totalRevenue / allOrders.length
            : 0;

        stats.value = {
            clients_count: clientsData.total || 0,
            orders_count: ordersData.total || 0,
            inputs_count: inputsData.total || 0,
            monthly_revenue: monthlyRevenue,
            monthly_orders_count: monthlyOrders.length,
            pending_deliveries: pendingDeliveries,
            total_revenue: totalRevenue,
            avg_order_value: avgOrderValue,
            recent_orders: allOrders.slice(0, 5),
            recent_inputs: (inputsData.data || []).slice(0, 3),
        };
    } catch (err) {
        console.error('Error fetching dashboard data:', err);
        error.value = 'Failed to load dashboard data';
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');
};

onMounted(() => {
    fetchDashboard();
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Dashboard</h1>
                <p class="text-sm text-muted-foreground">
                    Overview of your 3D printing business
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <Calendar class="h-4 w-4" />
                <span>{{ new Date().toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
            </div>
        </div>

        <!-- Loading State -->
        <template v-if="loading">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Skeleton v-for="i in 4" :key="i" class="h-32 rounded-xl" />
            </div>
            <Skeleton class="h-64 rounded-xl" />
        </template>

        <!-- Error State -->
        <div v-else-if="error" class="flex flex-col items-center justify-center py-12 text-center">
            <p class="text-lg font-medium text-destructive">{{ error }}</p>
            <button
                @click="fetchDashboard"
                class="mt-4 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
            >
                Try Again
            </button>
        </div>

        <!-- Dashboard Content -->
        <template v-else-if="stats">
            <!-- KPI Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card class="transition-all hover:shadow-md">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Clients</CardTitle>
                        <div class="rounded-lg bg-blue-100 p-2 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <Users class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.clients_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Link :href="route('clients.index')" class="text-primary hover:underline">View all clients</Link>
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-all hover:shadow-md">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Orders</CardTitle>
                        <div class="rounded-lg bg-green-100 p-2 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <ShoppingCart class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.orders_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Link :href="route('orders.index')" class="text-primary hover:underline">View all orders</Link>
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-all hover:shadow-md">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
                        <div class="rounded-lg bg-purple-100 p-2 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            <TrendingUp class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.monthly_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span>{{ stats.monthly_orders_count }} orders this month</span>
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-all hover:shadow-md">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Pending Deliveries</CardTitle>
                        <div class="rounded-lg bg-amber-100 p-2 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <Clock class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending_deliveries }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-amber-600 dark:text-amber-400">Awaiting delivery</span>
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Secondary Stats -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Financial Overview</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b pb-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <DollarSign class="h-4 w-4 text-green-600" />
                                    <span>Total Revenue</span>
                                </div>
                                <span class="font-semibold">{{ formatCurrency(stats.total_revenue) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b pb-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <TrendingUp class="h-4 w-4 text-blue-600" />
                                    <span>Average Order Value</span>
                                </div>
                                <span class="font-semibold">{{ formatCurrency(stats.avg_order_value) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm">
                                    <Box class="h-4 w-4 text-purple-600" />
                                    <span>Input Types</span>
                                </div>
                                <span class="font-semibold">{{ stats.inputs_count }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Inputs -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Recent Inputs</CardTitle>
                        <CardDescription>Latest materials registered</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="stats.recent_inputs.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                            No inputs registered yet.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="input in stats.recent_inputs"
                                :key="input.id"
                                class="flex items-center justify-between rounded-lg border p-3 text-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary" />
                                    <span class="font-medium">{{ input.filaments }}</span>
                                </div>
                                <span class="text-muted-foreground">{{ formatCurrency(Number(input.cost_buy)) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <Link :href="route('inputs.index')" class="text-xs text-primary hover:underline">
                                View all inputs →
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Orders -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-lg">Recent Orders</CardTitle>
                        <CardDescription>Latest orders placed in the system</CardDescription>
                    </div>
                    <Link
                        :href="route('orders.index')"
                        class="text-sm text-primary hover:underline"
                    >
                        View all
                    </Link>
                </CardHeader>
                <CardContent>
                    <!-- Empty State -->
                    <div v-if="stats.recent_orders.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                        No orders found. Create your first order!
                    </div>

                    <!-- Orders List (Desktop Table + Mobile Cards) -->
                    <template v-else>
                        <!-- Desktop Table -->
                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b text-left text-sm font-medium text-muted-foreground">
                                        <th class="pb-3 pr-4">Client</th>
                                        <th class="pb-3 pr-4">Order Date</th>
                                        <th class="pb-3 pr-4">Delivery Date</th>
                                        <th class="pb-3 pr-4">Price</th>
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
                                                :href="route('orders.edit', { order: order.id })"
                                                class="text-primary hover:underline"
                                            >
                                                Client #{{ order.client_id }}
                                            </Link>
                                        </td>
                                        <td class="py-3 pr-4 text-sm">{{ formatDate(order.order_date) }}</td>
                                        <td class="py-3 pr-4 text-sm">{{ formatDate(order.delivery_date) }}</td>
                                        <td class="py-3 pr-4 text-sm font-medium">{{ formatCurrency(Number(order.price)) }}</td>
                                        <td class="py-3 text-right">
                                            <Badge
                                                :variant="new Date(order.delivery_date) > new Date() ? 'secondary' : 'default'"
                                            >
                                                {{ new Date(order.delivery_date) > new Date() ? 'Pending' : 'Delivered' }}
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
                                    <span class="font-medium">Client #{{ order.client_id }}</span>
                                    <Badge
                                        :variant="new Date(order.delivery_date) > new Date() ? 'secondary' : 'default'"
                                    >
                                        {{ new Date(order.delivery_date) > new Date() ? 'Pending' : 'Delivered' }}
                                    </Badge>
                                </div>
                                <div class="space-y-1 text-sm text-muted-foreground">
                                    <div class="flex justify-between">
                                        <span>Order:</span>
                                        <span>{{ formatDate(order.order_date) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Delivery:</span>
                                        <span>{{ formatDate(order.delivery_date) }}</span>
                                    </div>
                                    <div class="flex justify-between font-medium text-foreground">
                                        <span>Price:</span>
                                        <span>{{ formatCurrency(Number(order.price)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </CardContent>
            </Card>
        </template>
    </div>
</template>