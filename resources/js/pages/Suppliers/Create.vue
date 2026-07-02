<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Save } from '@lucide/vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as suppliersIndex } from '@/routes/suppliers';

const form = useForm({ name: '', document: '', contact: '' });

const nomes = ['3D Supplies Brasil', 'Filamentos Prime', 'ResinPro', 'MakerParts', 'Impressão Fácil'];
const docs = ['12345678000190', '98765432000110', '45678901000123', '67890123000145'];
const conts = ['(11) 4000-1001 / vendas@', '(21) 3500-2002 / contato@', '(48) 3200-3003 / pedidos@'];

function rnd<T>(arr: T[]): T { return arr[Math.floor(Math.random() * arr.length)]; }

function buildTestFields() {
    const n = rnd(nomes);
    return [
        { key: 'name', value: n },
        { key: 'document', value: rnd(docs) },
        { key: 'contact', value: rnd(conts) + n.toLowerCase().replace(/\s/g, '') + '.com.br' },
    ];
}

function handleFill() { const f = buildTestFields(); for (const x of f) { if (x.key in form) (form as any)[x.key] = x.value; } }
function handleClear() { form.reset(); }

// ── Máscara CNPJ via watch + nextTick ─────────────
function applyMask(raw: string): string {
    const d = raw.replace(/\D/g, '');
    if (!d) return '';
    let m = d;
    if (m.length > 12) m = m.slice(0, 2) + '.' + m.slice(2, 5) + '.' + m.slice(5, 8) + '/' + m.slice(8, 12) + '-' + m.slice(12, 14);
    else if (m.length > 8) m = m.slice(0, 2) + '.' + m.slice(2, 5) + '.' + m.slice(5, 8) + '/' + m.slice(8);
    else if (m.length > 5) m = m.slice(0, 2) + '.' + m.slice(2, 5) + '.' + m.slice(5);
    else if (m.length > 2) m = m.slice(0, 2) + '.' + m.slice(2);
    return m.slice(0, 18);
}

watch(() => form.document, (val: string) => {
    if (!val) return;
    const masked = applyMask(val);
    if (masked !== val) nextTick(() => { form.document = masked; });
});

const docError = ref('');

const submit = () => {
    docError.value = '';
    const d = (form.document || '').replace(/\D/g, '');
    if (!d) { docError.value = 'O documento é obrigatório.'; return; }
    if (d.length < 11) { docError.value = 'Documento muito curto (mín. 11 dígitos).'; return; }
    form.post('/suppliers', { preserveScroll: true });
};
</script>

<template>
    <Head title="Criar Fornecedor" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child><Link :href="suppliersIndex()"><ArrowLeft class="h-4 w-4" /></Link></Button>
            <div><h1 class="text-2xl font-bold tracking-tight md:text-3xl">Criar Fornecedor</h1><p class="text-sm text-muted-foreground">Cadastrar novo fornecedor</p></div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"><AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle><AlertDescription>Verifique os campos.</AlertDescription></Alert>
        <FormTestHelper :form="form" :fields="buildTestFields()" label="Fornecedor teste" @fill="handleFill" @clear="handleClear" />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>Informações do Fornecedor</CardTitle><CardDescription>Preencha os dados</CardDescription></CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2"><Label for="name">Nome *</Label><Input id="name" v-model="form.name" placeholder="Razão social" :class="{ 'border-destructive': form.errors.name }" /><span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span></div>
                    <div class="space-y-2">
                        <Label for="document">CNPJ/CPF *</Label>
                        <Input id="document" v-model="form.document" maxlength="18" placeholder="00.000.000/0001-00"
                            :class="{ 'border-destructive': form.errors.document || docError }" />
                        <span v-if="form.errors.document" class="text-sm text-destructive">{{ form.errors.document }}</span>
                        <span v-if="docError" class="text-sm text-destructive">{{ docError }}</span>
                    </div>
                    <div class="space-y-2"><Label for="contact">Contato *</Label><Input id="contact" v-model="form.contact" placeholder="(11) 99999-0000 / email" :class="{ 'border-destructive': form.errors.contact }" /><span v-if="form.errors.contact" class="text-sm text-destructive">{{ form.errors.contact }}</span></div>
                </CardContent>
            </Card>
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="suppliersIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing"><Save class="mr-2 h-4 w-4" />{{ form.processing ? 'Salvando...' : 'Salvar Fornecedor' }}</Button>
            </div>
        </form>
    </div>
</template>