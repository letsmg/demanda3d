<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Save } from '@lucide/vue';
import FormTestHelper, { type TestField } from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const form = useForm({
    name: '', doc_type: 'CNPJ', document: '', ie: '',
    address: '', number: '', district: '', city: '', state: '', zipcode: '',
    contact1: '', phone1: '', contact2: '', phone2: '',
    email: '', website: '', notes: '', is_active: true,
});

const docType = ref('CNPJ');
watch(docType, () => { form.doc_type = docType.value; form.document = ''; });

const nomes = ['Rápido Transportes', 'ExpressLog', 'CargaBrasil', 'TransVale', 'LogFácil', 'Flash Frete'];
const docs = ['12345678000190', '98765432000110', '45678901000123', '67890123000145', '23456789000156'];

function rnd<T>(arr: T[]): T { return arr[Math.floor(Math.random() * arr.length)]; }

const testFields: TestField[] = [
    { key: 'name', value: rnd(nomes) },
    { key: 'doc_type', value: 'CNPJ' },
    { key: 'document', value: rnd(docs) },
    { key: 'address', value: 'Avenida dos Transportes, 1000' },
    { key: 'number', value: '1000' },
    { key: 'district', value: 'Distrito Industrial' },
    { key: 'city', value: 'São Paulo' },
    { key: 'state', value: 'SP' },
    { key: 'zipcode', value: '01000-000' },
    { key: 'contact1', value: 'Carlos Motta' },
    { key: 'phone1', value: '(11) 99999-0000' },
    { key: 'contact2', value: 'Ana Freitas' },
    { key: 'phone2', value: '(11) 3333-0000' },
    { key: 'email', value: 'contato@transportadora.com.br' },
];

function handleFill() {
    const n = rnd(nomes);
    form.name = n;
    form.doc_type = 'CNPJ';
    form.document = rnd(docs);
    form.address = 'Avenida dos Transportes, ' + Math.floor(Math.random() * 9000 + 100);
    form.number = String(Math.floor(Math.random() * 9000 + 100));
    form.district = 'Distrito Industrial';
    form.city = 'São Paulo';
    form.state = 'SP';
    form.zipcode = '01000-000';
    form.contact1 = rnd(['Carlos Motta', 'Roberto Gomes', 'Juliana Alves']);
    form.phone1 = '(11) 9' + Math.floor(Math.random() * 9000 + 1000) + '-' + Math.floor(Math.random() * 9000 + 1000);
    form.contact2 = rnd(['Ana Freitas', 'Marcos Ribeiro', 'Patrícia Lima']);
    form.phone2 = '(11) 3' + Math.floor(Math.random() * 9000 + 1000) + '-' + Math.floor(Math.random() * 9000 + 1000);
    form.email = n.toLowerCase().replace(/\s/g, '') + '@transporte.com.br';
}

function handleClear() { form.reset(); }

const submit = () => { form.post('/carriers', { preserveScroll: true }); };
</script>

<template>
    <Head title="Nova Transportadora" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child><Link href="/carriers"><ArrowLeft class="h-4 w-4" /></Link></Button>
            <div><h1 class="text-2xl font-bold">Nova Transportadora</h1><p class="text-sm text-muted-foreground">Cadastrar transportadora de frete</p></div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"><AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle><AlertDescription>Verifique os campos.</AlertDescription></Alert>
        <FormTestHelper :form="form" :fields="testFields" label="Transportadora teste" @fill="handleFill" @clear="handleClear" />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>Dados da Transportadora</CardTitle></CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2 sm:col-span-2"><Label for="name">Nome *</Label><Input id="name" v-model="form.name" placeholder="Razão social" :class="{ 'border-destructive': form.errors.name }" /><span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span></div>
                        <div class="space-y-2"><Label for="is_active">Ativo</Label><Select v-model="form.is_active"><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem :value="true">Sim</SelectItem><SelectItem :value="false">Não</SelectItem></SelectContent></Select></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2"><Label>Tipo *</Label><Select v-model="docType"><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="CPF">CPF</SelectItem><SelectItem value="CNPJ">CNPJ</SelectItem></SelectContent></Select></div>
                        <div class="space-y-2"><Label for="document">Documento *</Label><Input id="document" v-model="form.document" maxlength="18" :placeholder="docType === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00'" :class="{ 'border-destructive': form.errors.document }" /><span v-if="form.errors.document" class="text-sm text-destructive">{{ form.errors.document }}</span></div>
                        <div class="space-y-2"><Label for="ie">IE</Label><Input id="ie" v-model="form.ie" placeholder="Inscrição Estadual" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2 sm:col-span-2"><Label for="address">Endereço</Label><Input id="address" v-model="form.address" placeholder="Rua, Avenida..." /></div>
                        <div class="space-y-2"><Label for="number">Número</Label><Input id="number" v-model="form.number" placeholder="123" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-4">
                        <div class="space-y-2"><Label for="district">Bairro</Label><Input id="district" v-model="form.district" /></div>
                        <div class="space-y-2"><Label for="city">Cidade</Label><Input id="city" v-model="form.city" /></div>
                        <div class="space-y-2"><Label for="state">UF</Label><Input id="state" v-model="form.state" maxlength="2" /></div>
                        <div class="space-y-2"><Label for="zipcode">CEP</Label><Input id="zipcode" v-model="form.zipcode" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2"><Label for="contact1">Contato 1</Label><Input id="contact1" v-model="form.contact1" placeholder="Nome" /></div>
                        <div class="space-y-2"><Label for="phone1">Telefone 1</Label><Input id="phone1" v-model="form.phone1" placeholder="(11) 99999-0000" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2"><Label for="contact2">Contato 2</Label><Input id="contact2" v-model="form.contact2" placeholder="Nome" /></div>
                        <div class="space-y-2"><Label for="phone2">Telefone 2</Label><Input id="phone2" v-model="form.phone2" /></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2"><Label for="email">E-mail</Label><Input id="email" v-model="form.email" type="email" /></div>
                        <div class="space-y-2"><Label for="website">Site</Label><Input id="website" v-model="form.website" type="url" /></div>
                    </div>
                    <div class="space-y-2"><Label for="notes">Observações</Label><Textarea id="notes" v-model="form.notes" rows="3" /></div>
                </CardContent>
            </Card>
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link href="/carriers">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing"><Save class="mr-2 h-4 w-4" />{{ form.processing ? 'Salvando...' : 'Salvar' }}</Button>
            </div>
        </form>
    </div>
</template>