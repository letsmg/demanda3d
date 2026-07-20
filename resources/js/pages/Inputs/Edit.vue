<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft, AlertCircle } from 'lucide-vue-next';
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
import { index as inputsIndex } from '@/routes/inputs';
import type { Input as InputType } from '@/types';
import FormTestHelper, {
    type TestField,
} from '@/components/FormTestHelper.vue';

const props = defineProps<{
    input: InputType;
}>();

const form = useForm({
    supplier_id: props.input.supplier_id,
    description: props.input.description,
    brand: props.input.brand || '',
    purchase_date: props.input.purchase_date || '',
    quantity: String(props.input.quantity || ''),
    shipping_cost: String(props.input.shipping_cost || ''),
    cost_value: String(props.input.cost_value || ''),
});

const testFields: TestField[] = [
    { key: 'description', value: 'PETG 1.75mm Translúcido' },
    { key: 'brand', value: '3DLab' },
    { key: 'purchase_date', value: '2026-06-20' },
    { key: 'quantity', value: '2000' },
    { key: 'shipping_cost', value: '15.90' },
    { key: 'cost_value', value: '99.90' },
];

function handleFill(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = f.value;
        }
    }
}

function handleClear(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = '';
        }
    }
}

const submit = () => {
    form.put(`/inputs/${props.input.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Editar Insumo" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="inputsIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Editar Insumo
                </h1>
                <p class="text-sm text-muted-foreground">
                    Editando: {{ props.input.description }}
                </p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <FormTestHelper
            :form="form"
            :fields="testFields"
            label="Editar Insumo"
            @fill="handleFill"
            @clear="handleClear"
        />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Insumo</CardTitle>
                    <CardDescription
                        >Edite os dados do material abaixo</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="description">Filamento / Material *</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Ex: PLA 1.75mm"
                            :class="{
                                'border-destructive': form.errors.description,
                            }"
                        />
                        <span
                            v-if="form.errors.description"
                            class="text-sm text-destructive"
                            >{{ form.errors.description }}</span
                        >
                    </div>

                    <div class="space-y-2">
                        <Label for="brand">Marca *</Label>
                        <Input
                            id="brand"
                            v-model="form.brand"
                            placeholder="Ex: 3DLab, Creality"
                            :class="{ 'border-destructive': form.errors.brand }"
                        />
                        <span
                            v-if="form.errors.brand"
                            class="text-sm text-destructive"
                            >{{ form.errors.brand }}</span
                        >
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="purchase_date">Data da Compra *</Label>
                            <Input
                                id="purchase_date"
                                type="date"
                                v-model="form.purchase_date"
                                :class="{
                                    'border-destructive':
                                        form.errors.purchase_date,
                                }"
                            />
                            <span
                                v-if="form.errors.purchase_date"
                                class="text-sm text-destructive"
                                >{{ form.errors.purchase_date }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="cost_value">Custo de Compra *</Label>
                            <Input
                                id="cost_value"
                                type="number"
                                step="0.01"
                                v-model="form.cost_value"
                                placeholder="0.00"
                                :class="{
                                    'border-destructive':
                                        form.errors.cost_value,
                                }"
                            />
                            <span
                                v-if="form.errors.cost_value"
                                class="text-sm text-destructive"
                                >{{ form.errors.cost_value }}</span
                            >
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="quantity">Quantidade (gramas) *</Label>
                            <Input
                                id="quantity"
                                type="number"
                                step="1"
                                v-model="form.quantity"
                                placeholder="0"
                                :class="{
                                    'border-destructive': form.errors.quantity,
                                }"
                            />
                            <span
                                v-if="form.errors.quantity"
                                class="text-sm text-destructive"
                                >{{ form.errors.quantity }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="shipping_cost">Frete *</Label>
                            <Input
                                id="shipping_cost"
                                type="number"
                                step="0.01"
                                v-model="form.shipping_cost"
                                placeholder="0.00"
                                :class="{
                                    'border-destructive':
                                        form.errors.shipping_cost,
                                }"
                            />
                            <span
                                v-if="form.errors.shipping_cost"
                                class="text-sm text-destructive"
                                >{{ form.errors.shipping_cost }}</span
                            >
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="inputsIndex()">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                </Button>
            </div>
        </form>
    </div>
</template>
