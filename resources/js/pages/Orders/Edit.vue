<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as ordersIndex } from '@/routes/orders';
import type { Client, Order } from '@/types';

const props = defineProps<{ order: Order }>();

const form = ref({
    client_id: String(props.order.client_id),
    order_date: props.order.order_date,
    delivery_date: props.order.delivery_date,
    price: String(props.order.price),
    contracted_description: props.order.contracted_description,
});

const clients = ref<Client[]>([]);
const errors = ref<Record<string, string>>({});
const loading = ref(false);

const fetchClients = async () => {
    try {
        const response = await fetch('/api/clients?per_page=100');
        const data = await response.json();
        clients.value = data.data || [];
    } catch (error) {
        console.error('Error fetching clients:', error);
    }
};

const submit = async () => {
    loading.value = true;
    errors.value = {};
    try {
        const response = await fetch(`/api/orders/${props.order.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                ...form.value,
                client_id: parseInt(form.value.client_id),
                price: parseFloat(form.value.price),
            }),
        });
        if (!response.ok) {
            const data = await response.json();
            errors.value = data.errors || {};
            if (data.message && !data.errors) errors.value._general = data.message;
        } else {
            router.visit(ordersIndex());
        }
    } catch (error) {
        errors.value._general = 'An unexpected error occurred.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => fetchClients());
</script>

<template>
    <Head :title="`Edit Order #${order.id}`" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="ordersIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Edit Order #{{ order.id }}</h1>
                <p class="text-sm text-muted-foreground">Update order details</p>
            </div>
        </div>

        <Alert v-if="errors._general" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{{ errors._general }}</AlertDescription>
        </Alert>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Order Details</CardTitle>
                    <CardDescription>Update the order information</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label>Client *</Label>
                        <Select v-model="form.client_id">
                            <SelectTrigger :class="{ 'border-destructive': errors.client_id }">
                                <SelectValue placeholder="Select a client" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="client in clients" :key="client.id" :value="String(client.id)">
                                    {{ client.name }} - {{ client.doc }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="errors.client_id" class="text-sm text-destructive">{{ errors.client_id }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Order Date *</Label>
                            <Input type="date" v-model="form.order_date" :class="{ 'border-destructive': errors.order_date }" />
                            <span v-if="errors.order_date" class="text-sm text-destructive">{{ errors.order_date }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label>Delivery Date *</Label>
                            <Input type="date" v-model="form.delivery_date" :class="{ 'border-destructive': errors.delivery_date }" />
                            <span v-if="errors.delivery_date" class="text-sm text-destructive">{{ errors.delivery_date }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Price (R$) *</Label>
                        <Input type="number" step="0.01" min="0" v-model="form.price" :class="{ 'border-destructive': errors.price }" />
                        <span v-if="errors.price" class="text-sm text-destructive">{{ errors.price }}</span>
                    </div>

                    <div class="space-y-2">
                        <Label>Contracted Description *</Label>
                        <Textarea v-model="form.contracted_description" rows="5" :class="{ 'border-destructive': errors.contracted_description }" />
                        <span v-if="errors.contracted_description" class="text-sm text-destructive">{{ errors.contracted_description }}</span>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="ordersIndex()">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="loading">
                    <Save class="mr-2 h-4 w-4" />
                    {{ loading ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>