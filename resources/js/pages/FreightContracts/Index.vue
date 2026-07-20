<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Truck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{ contracts: any }>();

const deleteContract = (id: number) => {
    if (confirm('Tem certeza que deseja excluir este contrato?')) {
        router.delete(`/freight-contracts/${id}`, { preserveScroll: true });
    }
};

const statusPt: Record<string, string> = {
    pending: 'Pendente',
    in_transit: 'Em trânsito',
    delivered: 'Entregue',
    cancelled: 'Cancelado',
};

const formatCurrency = (v: number) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(v);
const formatDate = (d: string) =>
    new Date(d + 'T00:00:00').toLocaleDateString('pt-BR');
</script>

<template>
    <Head title="Contratos de Frete" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Contratos de Frete
                </h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie fretes contratados
                </p>
            </div>
            <Button as-child
                ><Link href="/freight-contracts/create"
                    ><Plus class="mr-2 h-4 w-4" />Novo Contrato</Link
                ></Button
            >
        </div>
        <Card>
            <CardHeader>
                <CardTitle>Todos os Contratos</CardTitle>
                <CardDescription
                    >{{
                        contracts.data?.length || 0
                    }}
                    contratos</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">
                                    Transportadora
                                </th>
                                <th class="px-4 py-3 font-medium">Coleta</th>
                                <th class="px-4 py-3 font-medium">
                                    Entrega Est.
                                </th>
                                <th class="px-4 py-3 font-medium">Valor</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="c in contracts.data"
                                :key="c.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ c.carrier?.name }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(c.pickup_date) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(c.estimated_delivery_date) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ formatCurrency(c.freight_value) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ statusPt[c.status] || c.status }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="ghost" size="icon" as-child
                                        ><Link
                                            :href="`/freight-contracts/${c.id}/edit`"
                                            ><Pencil class="h-4 w-4" /></Link
                                    ></Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteContract(c.id)"
                                        ><Trash2 class="h-4 w-4 text-red-500"
                                    /></Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
