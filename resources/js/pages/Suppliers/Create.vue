<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, ChevronDown, Save } from '@lucide/vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useTestData } from '@/composables/useTestData';
import { index as suppliersIndex } from '@/routes/suppliers';

const { randomCNPJ, randomCPF } = useTestData();

const addressOpen = ref(false);

const form = useForm({
    name: '',
    doc_type: 'CNPJ',
    document: '',
    ie: '',
    contact: '',
    email: '',
    website: '',
    phone1: '',
    phone2: '',
    contact1: '',
    contact2: '',
    address: '',
    number: '',
    district: '',
    city: '',
    state: '',
    zipcode: '',
    notes: '',
});

const nomesFornecedores = [
    '3D Supplies Brasil Ltda',
    'Filamentos Prime Comércio',
    'ResinPro Indústria Química',
    'MakerParts Distribuidora',
    'Impressão Fácil Importação',
    'PrintTech Componentes',
    'Filamento Express Varejo',
    'Tecno3D Insumos',
    'MegaPrint Comercial',
    'Polímeros Brasil Atacado',
];

function rnd<T>(arr: T[]): T {
    return arr[Math.floor(Math.random() * arr.length)];
}

function buildTestFields() {
    const name = rnd(nomesFornecedores);
    const isCNPJ = Math.random() > 0.3;
    const doc = isCNPJ ? randomCNPJ() : randomCPF();

    return [
        { key: 'name', value: name },
        { key: 'doc_type', value: isCNPJ ? 'CNPJ' : 'CPF' },
        { key: 'document', value: doc },
        { key: 'ie', value: isCNPJ ? String(Math.floor(Math.random() * 900000000) + 100000000) : '' },
        { key: 'contact', value: rnd(['Vendas', 'Comercial', 'Atendimento']) + ' - ' + name.split(' ')[0].toLowerCase() },
        { key: 'email', value: 'contato@' + name.toLowerCase().replace(/\s+/g, '').replace(/[^a-z]/g, '') + '.com.br' },
        { key: 'website', value: 'https://www.' + name.toLowerCase().replace(/\s+/g, '').replace(/[^a-z]/g, '') + '.com.br' },
        { key: 'phone1', value: '(11) 9' + Math.floor(Math.random() * 9000 + 1000) + '-' + Math.floor(Math.random() * 9000 + 1000) },
        { key: 'phone2', value: Math.random() > 0.5 ? '(11) 3' + Math.floor(Math.random() * 9000 + 1000) + '-' + Math.floor(Math.random() * 9000 + 1000) : '' },
        { key: 'contact1', value: rnd(['João Silva', 'Maria Santos', 'Pedro Costa', 'Ana Oliveira', 'Carlos Souza']) },
        { key: 'contact2', value: Math.random() > 0.5 ? rnd(['Financeiro', 'Logística', 'Suporte Técnico']) : '' },
        { key: 'address', value: rnd(['Rua dos Pinheiros', 'Av. Paulista', 'Rua Augusta', 'Alameda Santos', 'Rua Oscar Freire']) },
        { key: 'number', value: String(Math.floor(Math.random() * 4000) + 1) },
        { key: 'district', value: rnd(['Centro', 'Jardins', 'Vila Mariana', 'Pinheiros', 'Moema']) },
        { key: 'city', value: rnd(['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre']) },
        { key: 'state', value: rnd(['SP', 'RJ', 'MG', 'PR', 'RS']) },
        { key: 'zipcode', value: String(Math.floor(Math.random() * 90000) + 10000) + '-' + String(Math.floor(Math.random() * 900) + 100) },
        { key: 'notes', value: rnd(['Fornecedor premium com entrega rápida.', 'Preços competitivos para pedidos em volume.', 'Atendimento excelente, suporte técnico dedicado.', 'Entrega em até 48h na capital.']) },
    ];
}

function handleFill() {
    const fields = buildTestFields();
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = f.value;
        }
    }
}

function handleClear() {
    form.reset();
}

function applyMask(raw: string): string {
    const d = raw.replace(/\D/g, '');
    if (!d) return '';
    if (d.length <= 11) {
        let m = d;
        if (m.length > 9) m = m.slice(0, 3) + '.' + m.slice(3, 6) + '.' + m.slice(6, 9) + '-' + m.slice(9);
        else if (m.length > 6) m = m.slice(0, 3) + '.' + m.slice(3, 6) + '.' + m.slice(6);
        else if (m.length > 3) m = m.slice(0, 3) + '.' + m.slice(3);
        return m.slice(0, 14);
    }
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
            <Button variant="outline" size="icon" as-child>
                <Link :href="suppliersIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Criar Fornecedor</h1>
                <p class="text-sm text-muted-foreground">Cadastrar novo fornecedor</p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle><AlertDescription>Verifique os campos.</AlertDescription>
        </Alert>

        <FormTestHelper :form="form" :fields="buildTestFields()" label="Fornecedor teste" @fill="handleFill" @clear="handleClear" />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Fornecedor</CardTitle>
                    <CardDescription>Preencha os dados cadastrais</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="name">Nome / Razão Social *</Label>
                        <Input id="name" v-model="form.name" placeholder="Razão social"
                            :class="{ 'border-destructive': form.errors.name }" />
                        <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="doc_type">Tipo Documento *</Label>
                            <Select v-model="form.doc_type">
                                <SelectTrigger :class="{ 'border-destructive': form.errors.doc_type }">
                                    <SelectValue placeholder="Selecione" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="CNPJ">CNPJ</SelectItem>
                                    <SelectItem value="CPF">CPF</SelectItem>
                                    <SelectItem value="IE">IE</SelectItem>
                                </SelectContent>
                            </Select>
                            <span v-if="form.errors.doc_type" class="text-sm text-destructive">{{ form.errors.doc_type }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="document">Documento *</Label>
                            <Input id="document" v-model="form.document" maxlength="18" placeholder="00.000.000/0001-00"
                                :class="{ 'border-destructive': form.errors.document || docError }" />
                            <span v-if="form.errors.document" class="text-sm text-destructive">{{ form.errors.document }}</span>
                            <span v-if="docError" class="text-sm text-destructive">{{ docError }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="ie">Inscrição Estadual</Label>
                            <Input id="ie" v-model="form.ie" placeholder="Isento ou nº IE" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="email">E-mail</Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="contato@fornecedor.com.br"
                                :class="{ 'border-destructive': form.errors.email }" />
                        </div>
                        <div class="space-y-2">
                            <Label for="website">Website</Label>
                            <Input id="website" v-model="form.website" type="url" placeholder="https://www.fornecedor.com.br" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="contact">Contato Principal *</Label>
                            <Input id="contact" v-model="form.contact" placeholder="Nome ou descrição do contato"
                                :class="{ 'border-destructive': form.errors.contact }" />
                            <span v-if="form.errors.contact" class="text-sm text-destructive">{{ form.errors.contact }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="phone1">Telefone Principal</Label>
                            <Input id="phone1" v-model="form.phone1" placeholder="(11) 99999-9999"
                                :class="{ 'border-destructive': form.errors.phone1 }" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="contact1">Pessoa de Contato 1</Label>
                            <Input id="contact1" v-model="form.contact1" placeholder="Nome do responsável" />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone1">Telefone Secundário</Label>
                            <Input id="phone2" v-model="form.phone2" placeholder="(11) 3333-4444" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="contact2">Pessoa de Contato 2 / Departamento</Label>
                        <Input id="contact2" v-model="form.contact2" placeholder="Financeiro, Logística, etc." />
                    </div>

                    <!-- Endereço -->
                    <Card>
                        <CardHeader class="cursor-pointer" @click="addressOpen = !addressOpen">
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="text-lg">Endereço</CardTitle>
                                    <CardDescription>Localização do fornecedor</CardDescription>
                                </div>
                                <ChevronDown class="h-5 w-5 transition-transform" :class="{ 'rotate-180': addressOpen }" />
                            </div>
                        </CardHeader>
                        <CardContent v-show="addressOpen" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="address">Logradouro</Label>
                                <Input id="address" v-model="form.address" placeholder="Rua, Avenida..." />
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="number">Número</Label>
                                    <Input id="number" v-model="form.number" placeholder="123" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="district">Bairro</Label>
                                    <Input id="district" v-model="form.district" placeholder="Bairro" />
                                </div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-2">
                                    <Label for="city">Cidade</Label>
                                    <Input id="city" v-model="form.city" placeholder="Cidade" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="state">Estado</Label>
                                    <Input id="state" v-model="form.state" placeholder="UF" maxlength="2" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="zipcode">CEP</Label>
                                    <Input id="zipcode" v-model="form.zipcode" placeholder="00000-000" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="space-y-2">
                        <Label for="notes">Observações</Label>
                        <Textarea id="notes" v-model="form.notes" placeholder="Notas internas sobre o fornecedor" rows={3} />
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="suppliersIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Fornecedor' }}
                </Button>
            </div>
        </form>
    </div>
</template>