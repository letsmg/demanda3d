<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Save } from '@lucide/vue';
import FormTestHelper, { type TestField } from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as clientsIndex } from '@/routes/clients';
import type { Client } from '@/types';

const props = defineProps<{ client: Client }>();

const form = useForm({
    first_name: props.client.first_name,
    last_name: props.client.last_name,
    display_name: props.client.display_name || '',
    doc_type: props.client.doc_type || 'CPF',
    doc: props.client.doc || '',
    address: props.client.address || '',
    number: props.client.number || '',
    state: props.client.state || '',
    zipcode: props.client.zipcode || '',
    city: props.client.city || '',
    phone1: props.client.phone1 || '',
    phone2: props.client.phone2 || '',
    contact1: props.client.contact1 || '',
    contact2: props.client.contact2 || '',
});

const firstNames = ['João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Juliana', 'Rafael', 'Beatriz', 'Lucas', 'Camila', 'Gabriel', 'Fernanda', 'Marcos', 'Patrícia', 'Felipe', 'Larissa', 'Bruno', 'Amanda', 'Daniel', 'Natália'];
const lastNames = ['Silva', 'Santos', 'Oliveira', 'Costa', 'Pereira', 'Rodrigues', 'Almeida', 'Nascimento', 'Lima', 'Araújo', 'Barbosa', 'Cardoso', 'Dias', 'Ferreira', 'Gomes', 'Moreira'];

function rnd<T>(arr: T[]): T { return arr[Math.floor(Math.random() * arr.length)]; }

// ── Gerador de CPF sempre válido e diferente ──────
function generateValidCpf(): string {
    const digits: number[] = [];
    for (let i = 0; i < 9; i++) digits.push(Math.floor(Math.random() * 10));
    let sum = 0;
    for (let i = 0; i < 9; i++) sum += digits[i] * (10 - i);
    let d1 = sum % 11;
    d1 = d1 < 2 ? 0 : 11 - d1;
    digits.push(d1);
    sum = 0;
    for (let i = 0; i < 10; i++) sum += digits[i] * (11 - i);
    let d2 = sum % 11;
    d2 = d2 < 2 ? 0 : 11 - d2;
    digits.push(d2);
    return digits.join('');
}

const testFields: TestField[] = [
    { key: 'first_name', value: 'Maria' },
    { key: 'last_name', value: 'Oliveira Costa' },
    { key: 'display_name', value: 'Maria Oliveira Costa' },
    { key: 'doc_type', value: 'CPF' },
    { key: 'doc', value: generateValidCpf() },
    { key: 'address', value: 'Avenida Paulista' },
    { key: 'number', value: '1000' },
    { key: 'state', value: 'SP' },
    { key: 'zipcode', value: '01310-100' },
    { key: 'city', value: 'São Paulo' },
    { key: 'phone1', value: '(11) 98888-7777' },
    { key: 'phone2', value: '(11) 3333-4444' },
    { key: 'contact1', value: 'Carlos Andrade' },
    { key: 'contact2', value: 'Ana Beatriz' },
];

function handleFill() {
    const firstName = rnd(firstNames);
    const lastName = rnd(lastNames);
    form.first_name = firstName;
    form.last_name = lastName;
    form.display_name = `${firstName} ${lastName}`;
    form.doc_type = 'CPF';
    form.doc = applyMask(generateValidCpf(), 'CPF');
    form.address = 'Avenida Paulista';
    form.number = String(Math.floor(Math.random() * 9000) + 100);
    form.state = 'SP';
    form.zipcode = '01310-100';
    form.city = 'São Paulo';
    form.phone1 = '(11) 9' + Math.floor(Math.random() * 9000 + 1000) + '-' + Math.floor(Math.random() * 9000 + 1000);
    form.phone2 = '(11) 3333-4444';
    form.contact1 = rnd(firstNames) + ' ' + rnd(lastNames);
    form.contact2 = rnd(firstNames) + ' ' + rnd(lastNames);
    docError.value = '';
}

function handleClear(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) (form as any)[f.key] = '';
    }
    docError.value = '';
}

// ── Tipo de documento ────────────────────────────
const docType = ref(props.client.doc_type || 'CPF');

watch(docType, () => {
    form.doc_type = docType.value;
    form.doc = '';
    docError.value = '';
    typeMismatchWarning.value = '';
});

// ── Máscara via watch + nextTick ──────────────────
function applyMask(raw: string, type: string): string {
    const digits = raw.replace(/\D/g, '');
    if (!digits) return '';
    if (type === 'CPF') {
        let m = digits;
        if (m.length > 9) m = m.slice(0, 3) + '.' + m.slice(3, 6) + '.' + m.slice(6, 9) + '-' + m.slice(9, 11);
        else if (m.length > 6) m = m.slice(0, 3) + '.' + m.slice(3, 6) + '.' + m.slice(6);
        else if (m.length > 3) m = m.slice(0, 3) + '.' + m.slice(3);
        return m.slice(0, 14);
    }
    let m = digits;
    if (m.length > 12) m = m.slice(0, 2) + '.' + m.slice(2, 5) + '.' + m.slice(5, 8) + '/' + m.slice(8, 12) + '-' + m.slice(12, 14);
    else if (m.length > 8) m = m.slice(0, 2) + '.' + m.slice(2, 5) + '.' + m.slice(5, 8) + '/' + m.slice(8);
    else if (m.length > 5) m = m.slice(0, 2) + '.' + m.slice(2, 5) + '.' + m.slice(5);
    else if (m.length > 2) m = m.slice(0, 2) + '.' + m.slice(2);
    return m.slice(0, 18);
}

watch(() => form.doc, (val: string) => {
    if (!val) return;
    const masked = applyMask(val, form.doc_type);
    if (masked !== val) nextTick(() => { form.doc = masked; });
});

// ── Validação ─────────────────────────────────────
const docError = ref('');
const typeMismatchWarning = ref('');

function detectedType(digits: string): 'CPF' | 'CNPJ' {
    return digits.length === 11 ? 'CPF' : 'CNPJ';
}

function validateDoc(): boolean {
    const digits = (form.doc || '').replace(/\D/g, '');
    typeMismatchWarning.value = '';
    if (!digits) { docError.value = 'O documento é obrigatório.'; return false; }
    const actual = detectedType(digits);
    const selected = form.doc_type;
    if (actual !== selected) {
        typeMismatchWarning.value = `Você digitou um ${actual}, mas o tipo selecionado é ${selected}. Altere o tipo para ${actual} ou corrija o documento.`;
        return false;
    }
    if (selected === 'CPF') {
        if (digits.length !== 11) { docError.value = 'CPF deve ter 11 dígitos.'; return false; }
        if (!isValidCpf(digits)) { docError.value = 'CPF inválido.'; return false; }
    } else {
        if (digits.length !== 14) { docError.value = 'CNPJ deve ter 14 dígitos.'; return false; }
        if (!isValidCnpj(digits)) { docError.value = 'CNPJ inválido.'; return false; }
    }
    docError.value = '';
    return true;
}

function isValidCpf(d: string): boolean {
    let s = 0; for (let i = 0; i < 9; i++) s += parseInt(d[i]) * (10 - i);
    let d1 = s % 11; d1 = d1 < 2 ? 0 : 11 - d1;
    if (d1 !== parseInt(d[9])) return false;
    s = 0; for (let i = 0; i < 10; i++) s += parseInt(d[i]) * (11 - i);
    let d2 = s % 11; d2 = d2 < 2 ? 0 : 11 - d2;
    return d2 === parseInt(d[10]);
}

function isValidCnpj(d: string): boolean {
    const w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    const w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let s = 0; for (let i = 0; i < 12; i++) s += parseInt(d[i]) * w1[i];
    let d1 = s % 11; d1 = d1 < 2 ? 0 : 11 - d1;
    if (d1 !== parseInt(d[12])) return false;
    s = 0; for (let i = 0; i < 13; i++) s += parseInt(d[i]) * w2[i];
    let d2 = s % 11; d2 = d2 < 2 ? 0 : 11 - d2;
    return d2 === parseInt(d[13]);
}

const submit = () => {
    docError.value = '';
    typeMismatchWarning.value = '';
    if (!validateDoc()) return;
    form.put(`/clients/${props.client.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Editar Cliente" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child><Link :href="clientsIndex()"><ArrowLeft class="h-4 w-4" /></Link></Button>
            <div><h1 class="text-2xl font-bold tracking-tight md:text-3xl">Editar Cliente</h1><p class="text-sm text-muted-foreground">Editando: {{ props.client.first_name }} {{ props.client.last_name }}</p></div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"><AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle><AlertDescription>Verifique os campos abaixo.</AlertDescription></Alert>
        <FormTestHelper :form="form" :fields="testFields" label="Editar Cliente" @fill="handleFill" @clear="handleClear" />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>Informações do Cliente</CardTitle><CardDescription>Edite os dados abaixo</CardDescription></CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2"><Label for="first_name">Nome *</Label><Input id="first_name" v-model="form.first_name" placeholder="Primeiro nome" :class="{ 'border-destructive': form.errors.first_name }" /><span v-if="form.errors.first_name" class="text-sm text-destructive">{{ form.errors.first_name }}</span></div>
                        <div class="space-y-2"><Label for="last_name">Sobrenome *</Label><Input id="last_name" v-model="form.last_name" placeholder="Sobrenome" :class="{ 'border-destructive': form.errors.last_name }" /><span v-if="form.errors.last_name" class="text-sm text-destructive">{{ form.errors.last_name }}</span></div>
                        <div class="space-y-2"><Label for="display_name">Nome de Exibição</Label><Input id="display_name" v-model="form.display_name" placeholder="Opcional" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="doc_type">Tipo *</Label>
                            <Select v-model="docType">
                                <SelectTrigger id="doc_type"><SelectValue placeholder="Selecione" /></SelectTrigger>
                                <SelectContent><SelectItem value="CPF">CPF</SelectItem><SelectItem value="CNPJ">CNPJ</SelectItem></SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="doc">Documento *</Label>
                            <Input id="doc" v-model="form.doc" maxlength="18"
                                :placeholder="form.doc_type === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00'"
                                :class="{ 'border-destructive': form.errors.doc || docError }" />
                            <span v-if="form.errors.doc" class="text-sm text-destructive">{{ form.errors.doc }}</span>
                            <span v-if="docError" class="text-sm text-destructive">{{ docError }}</span>
                        </div>
                    </div>
                    <Alert v-if="typeMismatchWarning" variant="default" class="border-amber-300 bg-amber-50 text-amber-900">
                        <AlertCircle class="h-4 w-4" />
                        <AlertTitle>Atenção</AlertTitle>
                        <AlertDescription>{{ typeMismatchWarning }}</AlertDescription>
                    </Alert>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2 sm:col-span-2"><Label for="address">Endereço *</Label><Input id="address" v-model="form.address" placeholder="Rua, Avenida..." :class="{ 'border-destructive': form.errors.address }" /><span v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</span></div>
                        <div class="space-y-2"><Label for="number">Número *</Label><Input id="number" v-model="form.number" placeholder="123" :class="{ 'border-destructive': form.errors.number }" /><span v-if="form.errors.number" class="text-sm text-destructive">{{ form.errors.number }}</span></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2"><Label for="city">Cidade *</Label><Input id="city" v-model="form.city" placeholder="São Paulo" :class="{ 'border-destructive': form.errors.city }" /><span v-if="form.errors.city" class="text-sm text-destructive">{{ form.errors.city }}</span></div>
                        <div class="space-y-2"><Label for="state">UF *</Label><Input id="state" v-model="form.state" placeholder="SP" maxlength="2" :class="{ 'border-destructive': form.errors.state }" /><span v-if="form.errors.state" class="text-sm text-destructive">{{ form.errors.state }}</span></div>
                        <div class="space-y-2"><Label for="zipcode">CEP *</Label><Input id="zipcode" v-model="form.zipcode" placeholder="00000-000" :class="{ 'border-destructive': form.errors.zipcode }" /><span v-if="form.errors.zipcode" class="text-sm text-destructive">{{ form.errors.zipcode }}</span></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2"><Label for="phone1">Telefone 1 *</Label><Input id="phone1" v-model="form.phone1" placeholder="(11) 99999-0000" :class="{ 'border-destructive': form.errors.phone1 }" /><span v-if="form.errors.phone1" class="text-sm text-destructive">{{ form.errors.phone1 }}</span></div>
                        <div class="space-y-2"><Label for="phone2">Telefone 2</Label><Input id="phone2" v-model="form.phone2" placeholder="(11) 3333-0000" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2"><Label for="contact1">Contato 1</Label><Input id="contact1" v-model="form.contact1" placeholder="Nome do contato" /></div>
                        <div class="space-y-2"><Label for="contact2">Contato 2</Label><Input id="contact2" v-model="form.contact2" placeholder="Nome do contato" /></div>
                    </div>
                </CardContent>
            </Card>
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="clientsIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing"><Save class="mr-2 h-4 w-4" />{{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}</Button>
            </div>
        </form>
    </div>
</template>