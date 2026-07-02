<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, AlertCircle, ChevronDown, Save } from '@lucide/vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTestData } from '@/composables/useTestData';
import { index as productsIndex } from '@/routes/products';
import type { Product } from '@/types';

const { randomProductName, randomProductDescription, randomPrice } = useTestData();
const NEWLINE = String.fromCharCode(10);

function generateGtm(name: string, price: string): string {
    const dataLayer = {
        event: 'product_detail_view',
        ecommerce: {
            detail: {
                products: [{
                    name: name,
                    price: price,
                }],
            },
        },
    };
    const jsonStr = JSON.stringify(dataLayer, null, 2);
    const parts = [
        '<!-- Google Tag Manager -->',
        '<script>',
        '  window.dataLayer = window.dataLayer || [];',
        '  dataLayer.push(' + jsonStr + ');',
        '</script>',
    ];
    return parts.join(NEWLINE);
}

function generateSeoFromData(name: string, description: string): Record<string, string> {
    const cleanDesc = description.replace(/<[^>]+>/g, '').trim();
    const words = name.toLowerCase().split(/\s+/).filter(w => w.length >= 3);

    return {
        meta_title: name.substring(0, 120),
        meta_description: (cleanDesc || name).substring(0, 320),
        meta_keywords: [name.toLowerCase(), ...words, 'impressão 3d', 'produto 3d', 'marketplace 3d'].join(', ').substring(0, 255),
        canonical_url: '',
        og_image: '',
        schema_markup: JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'Product',
            'name': name,
            'description': cleanDesc || name,
            'offers': {
                '@type': 'Offer',
                'price': form.sale_price || '0',
                'priceCurrency': 'BRL',
                'availability': 'https://schema.org/InStock',
            },
        }, null, 2),
        google_tag_manager: generateGtm(name, form.sale_price || '0'),
    };
}

const props = defineProps<{ product: Product & {
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    canonical_url?: string;
    og_image?: string;
    schema_markup?: string;
    google_tag_manager?: string;
} }>();

const seoOpen = ref(false);

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    sale_price: props.product.sale_price,
    is_active: props.product.is_active,
    image: null as File | null,
    // SEO fields
    meta_title: (props.product as any).meta_title || '',
    meta_description: (props.product as any).meta_description || '',
    meta_keywords: (props.product as any).meta_keywords || '',
    canonical_url: (props.product as any).canonical_url || '',
    og_image: (props.product as any).og_image || '',
    schema_markup: (props.product as any).schema_markup || '',
    google_tag_manager: (props.product as any).google_tag_manager || '',
});

function buildTestFields() {
    return [
        { key: 'name', value: randomProductName() },
        { key: 'description', value: randomProductDescription() },
        { key: 'sale_price', value: randomPrice() },
    ];
}

function handleFill() {
    const fresh = buildTestFields();
    for (const f of fresh) {
        if (f.key in form) {
            (form as any)[f.key] = f.value;
        }
    }

    const seoFields = generateSeoFromData(form.name, form.description);
    for (const [key, value] of Object.entries(seoFields)) {
        if (key in form) {
            (form as any)[key] = value;
        }
    }
}

function handleClear() {
    form.reset();
}

const submit = () => {
    form.put(`/products/${props.product.id}`, { preserveScroll: true });
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
                <Link :href="productsIndex()"><ArrowLeft class="h-4 w-4" /></Link>
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

        <FormTestHelper :form="form" :fields="buildTestFields()" label="Editar Produto" @fill="handleFill" @clear="handleClear" />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>Informações do Produto</CardTitle><CardDescription>Edite os dados do produto</CardDescription></CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Nome *</Label>
                            <Input id="name" v-model="form.name" placeholder="Nome" :class="{ 'border-destructive': form.errors.name }" />
                            <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="sale_price">Preço de Venda *</Label>
                            <Input id="sale_price" v-model="form.sale_price" type="number" step="0.01" placeholder="0.00" :class="{ 'border-destructive': form.errors.sale_price }" />
                            <span v-if="form.errors.sale_price" class="text-sm text-destructive">{{ form.errors.sale_price }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Descrição</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Descrição" rows={4} />
                    </div>
                    <div class="space-y-2">
                        <Label for="image">Imagem do Produto</Label>
                        <Input id="image" type="file" accept="image/*" @input="onFileChange" />
                    </div>
                    <div class="flex items-center gap-2">
                        <Label for="is_active">Produto ativo?</Label>
                        <input id="is_active" type="checkbox" v-model="form.is_active" class="h-4 w-4" />
                    </div>
                </CardContent>
            </Card>

            <!-- SEO Section -->
            <Card class="mt-6">
                <CardHeader class="cursor-pointer" @click="seoOpen = !seoOpen">
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-lg">SEO — Otimização para Buscadores</CardTitle>
                            <CardDescription>Configure meta tags, schema markup e Google Tag Manager</CardDescription>
                        </div>
                        <ChevronDown class="h-5 w-5 transition-transform" :class="{ 'rotate-180': seoOpen }" />
                    </div>
                </CardHeader>
                <CardContent v-show="seoOpen" class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="meta_title">Meta Title (máx. 120 caracteres)</Label>
                            <Input id="meta_title" v-model="form.meta_title" placeholder="Título para SEO" maxlength="120"
                                :class="{ 'border-destructive': form.errors.meta_title }" />
                            <span v-if="form.errors.meta_title" class="text-sm text-destructive">{{ form.errors.meta_title }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="meta_keywords">Meta Keywords (máx. 255 caracteres)</Label>
                            <Input id="meta_keywords" v-model="form.meta_keywords" placeholder="palavra-chave1, palavra-chave2"
                                :class="{ 'border-destructive': form.errors.meta_keywords }" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="meta_description">Meta Description (máx. 320 caracteres)</Label>
                        <Textarea id="meta_description" v-model="form.meta_description" placeholder="Descrição para mecanismos de busca" rows={3} maxlength="320"
                            :class="{ 'border-destructive': form.errors.meta_description }" />
                        <span v-if="form.errors.meta_description" class="text-sm text-destructive">{{ form.errors.meta_description }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="canonical_url">URL Canônica</Label>
                            <Input id="canonical_url" v-model="form.canonical_url" type="url" placeholder="https://seu-dominio.com/produto"
                                :class="{ 'border-destructive': form.errors.canonical_url }" />
                        </div>
                        <div class="space-y-2">
                            <Label for="og_image">URL da Imagem Open Graph</Label>
                            <Input id="og_image" v-model="form.og_image" type="url" placeholder="https://seu-dominio.com/imagem.jpg"
                                :class="{ 'border-destructive': form.errors.og_image }" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="schema_markup">Schema Markup (JSON-LD) — Aceita código JSON estruturado</Label>
                        <Textarea id="schema_markup" v-model="form.schema_markup" placeholder='{"@context": "https://schema.org", ...}' rows={6}
                            class="font-mono text-sm" />
                        <p class="text-xs text-muted-foreground">Este campo aceita JSON/HTML para dados estruturados. Não será sanitizado.</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="google_tag_manager">Google Tag Manager — Aceita código JS/HTML</Label>
                        <Textarea id="google_tag_manager" v-model="form.google_tag_manager" placeholder="<!-- Google Tag Manager --> ..." rows={6}
                            class="font-mono text-sm" />
                        <p class="text-xs text-muted-foreground">Este campo aceita scripts de tracking. Não será sanitizado.</p>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="productsIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing"><Save class="mr-2 h-4 w-4" />{{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}</Button>
            </div>
        </form>
    </div>
</template>