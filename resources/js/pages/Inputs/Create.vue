<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as inputsIndex } from '@/routes/inputs';
import FormTestHelper, { type TestField } from '@/components/FormTestHelper.vue';
import { useTestData } from '@/composables/useTestData';

const { randomInputDescription, randomBrand, randomQuantity, randomShippingCost, randomCostValue } = useTestData();

defineProps<{ suppliers: { id: number; name: string }[] }>();

const form = useForm({
    supplier_id: '',
    description: '',
    brand: '',
    purchase_date: '',
    quantity: '',
    shipping_cost: '',
    cost_value: '',
});

function buildTestFields(): TestField[] {
    return [
        { key: 'description', value: randomInputDescription() },
        { key: 'brand', value: randomBrand() },
        { key: 'purchase_date', value: new Date().toISOString().split('T')[0] },
        { key: 'quantity', value: randomQuantity() },
        { key: 'shipping_cost', value: randomShippingCost() },
        { key: 'cost_value', value: randomCostValue() },
    ];
}

function handleFill() {
    const fresh = buildTestFields();
    for (const f of fresh) {
        if (f.key in form) (form as any)[f.key] = f.value;
    }
}

function handleClear() {
    form.reset();
}

const submit = () => {
    form.post('/inputs', { preserveScroll: true });
};
</script>

<template>
    <Head title="Criar Insumo" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="inputsIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Criar Insumo</h1>
                <p class="text-sm text-muted-foreground">Registrar um novo filamento ou material</p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <FormTestHelper :form="form" :fields="buildTestFields()" label="Insumo teste" @fill="handleFill" @clear="handleClear" />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Insumo</CardTitle>
                    <CardDescription>Preencha os dados do material abaixo</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="supplier_id">Fornecedor *</Label>
                        <Select v-model="form.supplier_id">
                            <SelectTrigger :class="{ 'border-destructive': form.errors.supplier_id }">
                                <SelectValue placeholder="Selecione um fornecedor" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="s in $props.suppliers" :key="s.id" :value="String(s.id)">
                                    {{ s.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="form.errors.supplier_id" class="text-sm text-destructive">{{ form.errors.supplier_id }}</span>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Descrição *</Label>
                        <Input id="description" v-model="form.description" placeholder="Ex: Filamento PLA 1.75mm 1kg Preto"
                            :class="{ 'border-destructive': form.errors.description }" />
                        <span v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="brand">Marca *</Label>
                            <Input id="brand" v-model="form.brand" placeholder="Ex: 3DLab"
                                :class="{ 'border-destructive': form.errors.brand }" />
                            <span v-if="form.errors.brand" class="text-sm text-destructive">{{ form.errors.brand }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="purchase_date">Data da Compra *</Label>
                            <Input id="purchase_date" type="date" v-model="form.purchase_date"
                                :class="{ 'border-destructive': form.errors.purchase_date }" />
                            <span v-if="form.errors.purchase_date" class="text-sm text-destructive">{{ form.errors.purchase_date }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="quantity">Quantidade (g) *</Label>
                            <Input id="quantity" type="number" v-model="form.quantity" placeholder="1000"
                                :class="{ 'border-destructive': form.errors.quantity }" />
                            <span v-if="form.errors.quantity" class="text-sm text-destructive">{{ form.errors.quantity }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="shipping_cost">Frete (R$) *</Label>
                            <Input id="shipping_cost" type="number" step="0.01" v-model="form.shipping_cost" placeholder="0.00"
                                :class="{ 'border-destructive': form.errors.shipping_cost }" />
                            <span v-if="form.errors.shipping_cost" class="text-sm text-destructive">{{ form.errors.shipping_cost }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="cost_value">Valor Pago (R$) *</Label>
                            <Input id="cost_value" type="number" step="0.01" v-model="form.cost_value" placeholder="0.00"
                                :class="{ 'border-destructive': form.errors.cost_value }" />
                            <span v-if="form.errors.cost_value" class="text-sm text-destructive">{{ form.errors.cost_value }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="inputsIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Insumo' }}
                </Button>
            </div>
        </form>
    </div>
</template>