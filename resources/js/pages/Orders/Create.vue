<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as ordersIndex } from '@/routes/orders';
import type { Client } from '@/types';

const { clients } = defineProps<{
    clients: Client[];
}>();

const form = useForm({
    client_id: '',
    order_date: '',
    delivery_date: '',
    price: '',
    contracted_description: '',
});

const submit = () => {
    form.post('/orders', { preserveScroll: true });
};
</script>

<template>
    <Head title="Criar Pedido" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="ordersIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Criar Pedido
                </h1>
                <p class="text-sm text-muted-foreground">
                    Registrar um novo pedido de impressão 3D
                </p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Pedido</CardTitle>
                    <CardDescription
                        >Preencha os dados do pedido abaixo</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="client_id">Cliente *</Label>
                        <select
                            id="client_id"
                            v-model="form.client_id"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{
                                'border-destructive': form.errors.client_id,
                            }"
                        >
                            <option value="">Selecione um cliente</option>
                            <option
                                v-for="client in clients"
                                :key="client.id"
                                :value="client.id"
                            >
                                {{ client.name }} - {{ client.doc }}
                            </option>
                        </select>
                        <span
                            v-if="form.errors.client_id"
                            class="text-sm text-destructive"
                            >{{ form.errors.client_id }}</span
                        >
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="order_date">Data do Pedido *</Label>
                            <Input
                                id="order_date"
                                type="date"
                                v-model="form.order_date"
                                :class="{
                                    'border-destructive':
                                        form.errors.order_date,
                                }"
                            />
                            <span
                                v-if="form.errors.order_date"
                                class="text-sm text-destructive"
                                >{{ form.errors.order_date }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="delivery_date">Data de Entrega *</Label>
                            <Input
                                id="delivery_date"
                                type="date"
                                v-model="form.delivery_date"
                                :class="{
                                    'border-destructive':
                                        form.errors.delivery_date,
                                }"
                            />
                            <span
                                v-if="form.errors.delivery_date"
                                class="text-sm text-destructive"
                                >{{ form.errors.delivery_date }}</span
                            >
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="price">Valor *</Label>
                        <Input
                            id="price"
                            type="number"
                            step="0.01"
                            v-model="form.price"
                            placeholder="0.00"
                            :class="{ 'border-destructive': form.errors.price }"
                        />
                        <span
                            v-if="form.errors.price"
                            class="text-sm text-destructive"
                            >{{ form.errors.price }}</span
                        >
                    </div>

                    <div class="space-y-2">
                        <Label for="contracted_description"
                            >Descrição do Serviço *</Label
                        >
                        <textarea
                            id="contracted_description"
                            v-model="form.contracted_description"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{
                                'border-destructive':
                                    form.errors.contracted_description,
                            }"
                            placeholder="Descreva o serviço contratado..."
                        ></textarea>
                        <span
                            v-if="form.errors.contracted_description"
                            class="text-sm text-destructive"
                            >{{ form.errors.contracted_description }}</span
                        >
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child
                    ><Link :href="ordersIndex()">Cancelar</Link></Button
                >
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Pedido' }}
                </Button>
            </div>
        </form>
    </div>
</template>
