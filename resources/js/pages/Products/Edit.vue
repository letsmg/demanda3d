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
import { Textarea } from '@/components/ui/textarea';
import { index as productsIndex } from '@/routes/products';
import type { Product } from '@/types';
import FormTestHelper, { type TestField } from '@/components/FormTestHelper.vue';

const props = defineProps<{
    product: Product;
}>();

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    price_sale: props.product.price_sale,
    discount_cash: props.product.discount_cash,
    is_active: props.product.is_active,
    image: null as File | null,
});

const testFields: TestField[] = [
    { key: 'name', value: 'Base para Notebook Articulada' },
    { key: 'description', value: 'Base ajustável com ventilação integrada para notebooks de 13 a 17 polegadas.' },
    { key: 'price_sale', value: '149.90' },
    { key: 'discount_cash', value: '15' },
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
    form.discount_cash = '0';
}

const submit = () => {
    form.put(`/products/${props.product.id}`, {
        preserveScroll: true,
    });
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image = target.files[0];
    }
};
</script>

<template>
    <Head title="Editar Produto" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="productsIndex()">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Editar Produto</h1>
                <p class="text-sm text-muted-foreground">Editando: {{ product.name }}</p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <FormTestHelper :form="form" :fields="testFields" label="Editar Produto" @fill="handleFill" @clear="handleClear" />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Produto</CardTitle>
                    <CardDescription>Edite os dados do produto</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Nome *</Label>
                            <Input id="name" v-model="form.name" placeholder="Nome do produto"
                                :class="{ 'border-destructive': form.errors.name }" />
                            <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="price_sale">Preço de Venda *</Label>
                            <Input id="price_sale" v-model="form.price_sale" type="number" step="0.01" placeholder="0.00"
                                :class="{ 'border-destructive': form.errors.price_sale }" />
                            <span v-if="form.errors.price_sale" class="text-sm text-destructive">{{ form.errors.price_sale }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Descrição</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Descrição do produto" rows={4} />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="discount_cash">Desconto à Vista (%)</Label>
                            <Input id="discount_cash" v-model="form.discount_cash" type="number" step="0.01" min="0" max="100" placeholder="0" />
                        </div>
                        <div class="space-y-2">
                            <Label for="image">Imagem do Produto</Label>
                            <Input id="image" type="file" accept="image/*" @input="onFileChange" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Label for="is_active">Produto ativo na vitrine?</Label>
                        <input id="is_active" type="checkbox" v-model="form.is_active" class="h-4 w-4" />
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="productsIndex()">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                </Button>
            </div>
        </form>
    </div>
</template>