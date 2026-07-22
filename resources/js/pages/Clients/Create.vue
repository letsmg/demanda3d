<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Lock, Save } from 'lucide-vue-next';
import { ref, watch, nextTick } from 'vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import type { TestField } from '@/components/FormTestHelper.vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useCep } from '@/composables/useCep';
import {
    applyDocMask,
    detectDocType,
    isValidCpf,
    isValidCnpj,
    generateValidCpf as genCpf,
} from '@/composables/useDocumentValidation';
import { index as clientsIndex } from '@/routes/clients';

const { loadingCep, isStateLocked, fetchCep, resetCep } = useCep();

const form = useForm({
    first_name: '',
    last_name: '',
    display_name: '',
    doc_type: 'CPF',
    doc: '',
    address: '',
    number: '',
    state: '',
    state_id: null as number | null,
    zipcode: '',
    city: '',
    phone1: '',
    phone2: '',
    contact1: '',
    contact2: '',
});

const firstNames = [
    'João',
    'Maria',
    'Pedro',
    'Ana',
    'Carlos',
    'Juliana',
    'Rafael',
    'Beatriz',
    'Lucas',
    'Camila',
    'Gabriel',
    'Fernanda',
    'Marcos',
    'Patrícia',
    'Felipe',
    'Larissa',
    'Bruno',
    'Amanda',
    'Daniel',
    'Natália',
];

const lastNames = [
    'Silva',
    'Santos',
    'Oliveira',
    'Costa',
    'Pereira',
    'Rodrigues',
    'Almeida',
    'Nascimento',
    'Lima',
    'Araújo',
    'Barbosa',
    'Cardoso',
    'Dias',
    'Ferreira',
    'Gomes',
    'Moreira',
];

function rnd<T>(arr: T[]): T {
    return arr[Math.floor(Math.random() * arr.length)];
}

const testFields: TestField[] = [
    { key: 'first_name', value: 'João' },
    { key: 'last_name', value: 'Silva Santos' },
    { key: 'display_name', value: 'João Silva Santos' },
    { key: 'doc_type', value: 'CPF' },
    { key: 'doc', value: genCpf() },
    { key: 'address', value: 'Rua das Flores' },
    { key: 'number', value: '123' },
    { key: 'zipcode', value: '01310-100' },
    { key: 'city', value: 'São Paulo' },
    { key: 'phone1', value: '(11) 99999-0000' },
    { key: 'phone2', value: '(11) 3333-0000' },
    { key: 'contact1', value: 'Maria Souza' },
    { key: 'contact2', value: 'Pedro Oliveira' },
];

function handleFill() {
    const firstName = rnd(firstNames);
    const lastName = rnd(lastNames);
    form.first_name = firstName;
    form.last_name = lastName;
    form.display_name = `${firstName} ${lastName}`;
    form.doc_type = 'CPF';
    form.doc = applyDocMask(genCpf(), 'CPF');
    form.address = 'Rua das Flores';
    form.number = String(Math.floor(Math.random() * 9000) + 100);
    form.zipcode = '01310-100';
    form.state = 'SP';
    form.city = 'São Paulo';
    form.phone1 =
        '(11) 9' +
        Math.floor(Math.random() * 9000 + 1000) +
        '-' +
        Math.floor(Math.random() * 9000 + 1000);
    form.phone2 = '(11) 3333-0000';
    form.contact1 = rnd(firstNames) + ' ' + rnd(lastNames);
    form.contact2 = rnd(firstNames) + ' ' + rnd(lastNames);
    docError.value = '';
}

function handleClear(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = '';
        }
    }

    resetCep();
    docError.value = '';
}

// ── CEP autocomplete ─────────────────────────────
async function onCepBlur() {
    const digits = (form.zipcode || '').replace(/\D/g, '');

    if (digits.length < 8) {
        return;
    }

    const data = await fetchCep(form.zipcode);

    if (data.state_id) {
        form.state_id = data.state_id;
        form.state = data.uf || '';
    }
}

// ── Tipo de documento ────────────────────────────
const docType = ref(form.doc_type);

watch(docType, () => {
    form.doc_type = docType.value;
    form.doc = '';
    docError.value = '';
    typeMismatchWarning.value = '';
});

watch(
    () => form.doc,
    (val: string) => {
        if (!val) {
            return;
        }

        const masked = applyDocMask(val, form.doc_type as 'CPF' | 'CNPJ');

        if (masked !== val) {
            nextTick(() => {
                form.doc = masked;
            });
        }
    },
);

// ── Validação ─────────────────────────────────────
const docError = ref('');
const typeMismatchWarning = ref('');

function validateDoc(): boolean {
    const clean = (form.doc || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
    typeMismatchWarning.value = '';

    if (!clean) {
        docError.value = 'O documento é obrigatório.';

        return false;
    }

    const actual = detectDocType(clean);
    const selected = form.doc_type;

    if (actual !== selected) {
        typeMismatchWarning.value = `Você digitou um ${actual}, mas o tipo selecionado é ${selected}. Altere o tipo para ${actual} ou corrija o documento.`;

        return false;
    }

    if (selected === 'CPF') {
        if (clean.length !== 11) {
            docError.value = 'CPF deve ter 11 dígitos.';

            return false;
        }

        if (!isValidCpf(clean)) {
            docError.value = 'CPF inválido.';

            return false;
        }
    } else {
        if (clean.length !== 14) {
            docError.value = 'CNPJ deve ter 14 caracteres.';

            return false;
        }

        if (!isValidCnpj(clean)) {
            docError.value = 'CNPJ inválido.';

            return false;
        }
    }

    docError.value = '';

    return true;
}

const submit = () => {
    docError.value = '';
    typeMismatchWarning.value = '';

    if (!validateDoc()) {
        return;
    }

    form.post('/clients', { preserveScroll: true });
};
</script>

<template>
    <Head title="Criar Cliente" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child
                ><Link :href="clientsIndex()"
                    ><ArrowLeft class="h-4 w-4" /></Link
            ></Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Criar Cliente
                </h1>
                <p class="text-sm text-muted-foreground">
                    Cadastrar um novo cliente
                </p>
            </div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"
            ><AlertCircle class="h-4 w-4" /><AlertTitle
                >Erro de validação</AlertTitle
            ><AlertDescription
                >Verifique os campos abaixo.</AlertDescription
            ></Alert
        >
        <FormTestHelper
            :form="form"
            :fields="testFields"
            label="Cliente teste"
            @fill="handleFill"
            @clear="handleClear"
        />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader
                    ><CardTitle>Informações do Cliente</CardTitle
                    ><CardDescription
                        >Preencha os dados abaixo</CardDescription
                    ></CardHeader
                >
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="first_name">Nome *</Label
                            ><Input
                                id="first_name"
                                v-model="form.first_name"
                                placeholder="Primeiro nome"
                                :class="{
                                    'border-destructive':
                                        form.errors.first_name,
                                }"
                            /><span
                                v-if="form.errors.first_name"
                                class="text-sm text-destructive"
                                >{{ form.errors.first_name }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="last_name">Sobrenome *</Label
                            ><Input
                                id="last_name"
                                v-model="form.last_name"
                                placeholder="Sobrenome"
                                :class="{
                                    'border-destructive': form.errors.last_name,
                                }"
                            /><span
                                v-if="form.errors.last_name"
                                class="text-sm text-destructive"
                                >{{ form.errors.last_name }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="display_name">Nome de Exibição</Label
                            ><Input
                                id="display_name"
                                v-model="form.display_name"
                                placeholder="Opcional"
                            />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="doc_type">Tipo *</Label>
                            <Select v-model="docType">
                                <SelectTrigger id="doc_type"
                                    ><SelectValue placeholder="Selecione"
                                /></SelectTrigger>
                                <SelectContent
                                    ><SelectItem value="CPF">CPF</SelectItem
                                    ><SelectItem value="CNPJ"
                                        >CNPJ</SelectItem
                                    ></SelectContent
                                >
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="doc">Documento *</Label>
                            <Input
                                id="doc"
                                v-model="form.doc"
                                maxlength="18"
                                :placeholder="
                                    form.doc_type === 'CPF'
                                        ? '000.000.000-00'
                                        : '00.000.000/0000-00'
                                "
                                :class="{
                                    'border-destructive':
                                        form.errors.doc || docError,
                                }"
                            />
                            <span
                                v-if="form.errors.doc"
                                class="text-sm text-destructive"
                                >{{ form.errors.doc }}</span
                            >
                            <span
                                v-if="docError"
                                class="text-sm text-destructive"
                                >{{ docError }}</span
                            >
                        </div>
                    </div>
                    <Alert
                        v-if="typeMismatchWarning"
                        variant="default"
                        class="border-amber-900 bg-amber-50 text-amber-900"
                    >
                        <AlertCircle class="h-4 w-4" />
                        <AlertTitle>Atenção</AlertTitle>
                        <AlertDescription>{{
                            typeMismatchWarning
                        }}</AlertDescription>
                    </Alert>

                    <!-- ── ENDEREÇO: CEP PRIMEIRO ── -->
                    <div class="rounded-lg border p-4">
                        <p
                            class="mb-4 text-sm font-medium text-muted-foreground"
                        >
                            Endereço
                        </p>

                        <!-- CEP (primeiro campo) -->
                        <div class="mb-4">
                            <Label for="zipcode" class="mb-1 block"
                                >CEP *
                                <span class="text-xs text-muted-foreground"
                                    >(digite primeiro)</span
                                ></Label
                            >
                            <div class="flex gap-2">
                                <Input
                                    id="zipcode"
                                    v-model="form.zipcode"
                                    placeholder="00000-000"
                                    maxlength="9"
                                    :class="{
                                        'border-destructive':
                                            form.errors.zipcode,
                                        'flex-1': true,
                                    }"
                                    :disabled="false"
                                    @blur="onCepBlur"
                                />
                                <span
                                    v-if="loadingCep"
                                    class="mt-2 text-xs text-muted-foreground"
                                    >🔍 Buscando...</span
                                >
                            </div>
                            <span
                                v-if="form.errors.zipcode"
                                class="text-sm text-destructive"
                                >{{ form.errors.zipcode }}</span
                            >
                        </div>

                        <!-- Estado (bloqueado após CEP preenchido) -->
                        <div class="mb-4">
                            <Label for="state">UF *</Label>
                            <div class="relative">
                                <Input
                                    id="state"
                                    v-model="form.state"
                                    placeholder="SP"
                                    maxlength="2"
                                    :disabled="isStateLocked"
                                    :class="{
                                        'border-destructive': form.errors.state,
                                        'bg-muted': isStateLocked,
                                    }"
                                />
                                <Lock
                                    v-if="isStateLocked"
                                    class="absolute top-2.5 right-3 h-4 w-4 text-muted-foreground"
                                />
                            </div>
                            <span
                                v-if="form.errors.state"
                                class="text-sm text-destructive"
                                >{{ form.errors.state }}</span
                            >
                            <p
                                v-if="isStateLocked"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Estado preenchido automaticamente pelo CEP. Para
                                alterar, corrija o CEP.
                            </p>
                        </div>

                        <!-- Cidade, Endereço e Número (só habilitados após CEP válido) -->
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="city">Cidade *</Label
                                ><Input
                                    id="city"
                                    v-model="form.city"
                                    placeholder="São Paulo"
                                    :disabled="!isStateLocked"
                                    :class="{
                                        'border-destructive': form.errors.city,
                                        'bg-muted opacity-50': !isStateLocked,
                                    }"
                                /><span
                                    v-if="form.errors.city"
                                    class="text-sm text-destructive"
                                    >{{ form.errors.city }}</span
                                >
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <Label for="address">Endereço *</Label
                                ><Input
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Rua, Avenida..."
                                    :disabled="!isStateLocked"
                                    :class="{
                                        'border-destructive':
                                            form.errors.address,
                                        'bg-muted opacity-50': !isStateLocked,
                                    }"
                                /><span
                                    v-if="form.errors.address"
                                    class="text-sm text-destructive"
                                    >{{ form.errors.address }}</span
                                >
                            </div>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="number">Número *</Label
                                ><Input
                                    id="number"
                                    v-model="form.number"
                                    placeholder="123"
                                    :disabled="!isStateLocked"
                                    :class="{
                                        'border-destructive':
                                            form.errors.number,
                                        'bg-muted opacity-50': !isStateLocked,
                                    }"
                                /><span
                                    v-if="form.errors.number"
                                    class="text-sm text-destructive"
                                    >{{ form.errors.number }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- ── TELEFONES ── -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="phone1">Telefone 1 *</Label
                            ><Input
                                id="phone1"
                                v-model="form.phone1"
                                placeholder="(11) 99999-0000"
                                :class="{
                                    'border-destructive': form.errors.phone1,
                                }"
                            /><span
                                v-if="form.errors.phone1"
                                class="text-sm text-destructive"
                                >{{ form.errors.phone1 }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="phone2">Telefone 2</Label
                            ><Input
                                id="phone2"
                                v-model="form.phone2"
                                placeholder="(11) 3333-0000"
                            />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="contact1">Contato 1</Label
                            ><Input
                                id="contact1"
                                v-model="form.contact1"
                                placeholder="Nome do contato"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="contact2">Contato 2</Label
                            ><Input
                                id="contact2"
                                v-model="form.contact2"
                                placeholder="Nome do contato"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child
                    ><Link :href="clientsIndex()">Cancelar</Link></Button
                >
                <Button type="submit" :disabled="form.processing"
                    ><Save class="mr-2 h-4 w-4" />{{
                        form.processing ? 'Salvando...' : 'Salvar Cliente'
                    }}</Button
                >
            </div>
        </form>
    </div>
</template>
