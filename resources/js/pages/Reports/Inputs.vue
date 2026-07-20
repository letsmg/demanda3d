<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, PackageOpen, Filter } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    inputs: any;
    filters: { threshold: string | null };
}>();

const threshold = ref('');

function applyFilter() {
    const params: Record<string, string> = {};
    if (threshold.value) {
        params.threshold = threshold.value;
    }
    router.get('/reports/inputs', params, {
        preserveState: true,
        replace: true,
    });
}

function clearFilter() {
    threshold.value = '';
    router.get('/reports/inputs', {}, { preserveState: true, replace: true });
}

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Relatório de Insumos" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link href="/reports">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Insumos por Estoque
                </h1>
                <p class="text-sm text-muted-foreground">
                    Relatório de insumos com filtro de quantidade mínima
                </p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <Filter class="h-4 w-4" /> Filtro de Estoque
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-end gap-4">
                    <div class="space-y-2">
                        <Label for="threshold"
                            >Quantidade máxima (exibe insumos abaixo deste
                            valor)</Label
                        >
                        <Input
                            id="threshold"
                            v-model="threshold"
                            type="number"
                            placeholder="Ex: 500"
                            class="w-48"
                        />
                    </div>
                    <Button @click="applyFilter">Filtrar</Button>
                    <Button
                        v-if="filters.threshold"
                        variant="ghost"
                        @click="clearFilter"
                        >Limpar</Button
                    >
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <PackageOpen class="h-4 w-4" />
                    {{ inputs.meta?.total || inputs.data?.length || 0 }} insumos
                    encontrados
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Descrição</th>
                                <th class="px-4 py-3 font-medium">Marca</th>
                                <th class="px-4 py-3 font-medium">
                                    Fornecedor
                                </th>
                                <th class="px-4 py-3 font-medium">Qtd</th>
                                <th class="px-4 py-3 font-medium">Data</th>
                                <th class="px-4 py-3 font-medium">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="input in Array.isArray(inputs)
                                    ? inputs
                                    : inputs.data"
                                :key="input.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ input.description }}
                                </td>
                                <td class="px-4 py-3">{{ input.brand }}</td>
                                <td class="px-4 py-3">
                                    {{ input.supplier?.name || '—' }}
                                </td>
                                <td class="px-4 py-3">{{ input.quantity }}</td>
                                <td class="px-4 py-3">
                                    {{ formatDate(input.purchase_date) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatPrice(input.cost_value) }}
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !(
                                        Array.isArray(inputs)
                                            ? inputs
                                            : inputs.data
                                    )?.length
                                "
                            >
                                <td
                                    colspan="6"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    Nenhum insumo encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
