<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as suppliersIndex } from '@/routes/suppliers';

const props = defineProps<{ supplier: any }>();

const form = useForm({
    name: props.supplier.name,
    document: props.supplier.document || '',
    contact: props.supplier.contact || '',
});

const nomesFornecedores = ['3D Supplies Brasil', 'Filamentos Prime', 'ResinPro', 'MakerParts', 'Impressão Fácil', 'Plásticos Técnicos', 'PrintSupply', 'EcoFilamentos'];
const documentos = ['12.345.678/0001-90', '98.765.432/0001-10', '45.678.901/0001-23', '67.890.123/0001-45', '23.456.789/0001-56', '34.567.890/0001-67'];
const contatos = ['(11) 4000-1001 / vendas@', '(21) 3500-2002 / contato@', '(48) 3200-3003 / pedidos@', '(31) 3100-4004 / sac@', '(19) 3400-5005 / atendimento@'];

function randomElement<T>(arr: T[]): T { return arr[Math.floor(Math.random() * arr.length)]; }

function buildTestFields() {
    const nome = randomElement(nomesFornecedores);
    return [
        { key: 'name', value: nome },
        { key: 'document', value: randomElement(documentos) },
        { key: 'contact', value: randomElement(contatos) + nome.toLowerCase().replace(/\s/g, '') + '.com.br' },
    ];
}

function handleFill() { const fresh = buildTestFields(); for (const f of fresh) { if (f.key in form) (form as any)[f.key] = f.value; } }
function handleClear() { form.reset(); }

const submit = () => { form.put(`/suppliers/${props.supplier.id}`, { preserveScroll: true }); };
</script>

<template>
    <Head title="Editar Fornecedor" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child><Link :href="suppliersIndex()"><ArrowLeft class="h-4 w-4" /></Link></Button>
            <div><h1 class="text-2xl font-bold tracking-tight md:text-3xl">Editar Fornecedor</h1><p class="text-sm text-muted-foreground">Editando: {{ supplier.name }}</p></div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"><AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle><AlertDescription>Verifique os campos.</AlertDescription></Alert>
        <FormTestHelper :form="form" :fields="buildTestFields()" label="Editar Fornecedor" @fill="handleFill" @clear="handleClear" />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>Informações do Fornecedor</CardTitle><CardDescription>Edite os dados abaixo</CardDescription></CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="name">Nome *</Label>
                        <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                        <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                    </div>
                    <div class="space-y-2">
                        <Label for="document">CNPJ/CPF *</Label>
                        <Input id="document" v-model="form.document" placeholder="00.000.000/0001-00" :class="{ 'border-destructive': form.errors.document }" />
                        <span v-if="form.errors.document" class="text-sm text-destructive">{{ form.errors.document }}</span>
                    </div>
                    <div class="space-y-2">
                        <Label for="contact">Contato *</Label>
                        <Input id="contact" v-model="form.contact" :class="{ 'border-destructive': form.errors.contact }" />
                        <span v-if="form.errors.contact" class="text-sm text-destructive">{{ form.errors.contact }}</span>
                    </div>
                </CardContent>
            </Card>
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="suppliersIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing"><Save class="mr-2 h-4 w-4" />{{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}</Button>
            </div>
        </form>
    </div>
</template>