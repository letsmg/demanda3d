<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Save } from 'lucide-vue-next';
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
import {
    applyDocMask,
    detectDocType,
    isValidCpf,
    isValidCnpj,
    generateValidCpf as genCpf,
} from '@/composables/useDocumentValidation';
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
    state_id: null as number | null,
    city: props.client.city || '',
    data_nascimento: props.client.data_nascimento || '',
    phone1: props.client.phone1 || '',
    phone2: props.client.phone2 || '',
    contact1: props.client.contact1 || '',
    contact2: props.client.contact2 || '',
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
    { key: 'first_name', value: 'Maria' },
    { key: 'last_name', value: 'Oliveira Costa' },
    { key: 'display_name', value: 'Maria Oliveira Costa' },
    { key: 'doc_type', value: 'CPF' },
    { key: 'doc', value: genCpf() },
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
    form.doc = applyDocMask(genCpf(), 'CPF');
    form.address = 'Avenida Paulista';
    form.number = String(Math.floor(Math.random() * 9000) + 100);
    form.state = 'SP';
    form.zipcode = '01310-100';
    form.city = 'São Paulo';
    form.phone1 =
        '(11) 9' +
        Math.floor(Math.random() * 9000 + 1000) +
        '-' +
        Math.floor(Math.random() * 9000 + 1000);
    form.phone2 = '(11) 3333-4444';
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

    form.put(`/clients/${props.client.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Editar Cliente" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child
                ><Link :href="clientsIndex()"
                    ><ArrowLeft class="h-4 w-4" /></Link
            ></Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Editar Cliente
                </h1>
                <p class="text-sm text-muted-foreground">
                    Editando: {{ props.client.first_name }}
                    {{ props.client.last_name }}
                </p>
            </div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"
            ><AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle
            ><AlertDescription
                >Verifique os campos abaixo.</AlertDescription
            ></Alert
        >
        <FormTestHelper
            :form="form"
            :fields="testFields"
            label="Editar Cliente"
            @fill="handleFill"
            @clear="handleClear"
        />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader
                    ><CardTitle>Informações do Cliente</CardTitle
                    ><CardDescription
                        >Edite os dados abaixo</CardDescription
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
                    <AddressCepBlock
                        :zipcode="form.zipcode"
                        :state="form.state"
                        :city="form.city"
                        :address="form.address"
                        :number="form.number"
                        :zipcode-error="form.errors.zipcode"
                        :state-error="form.errors.state"
                        :city-error="form.errors.city"
                        :address-error="form.errors.address"
                        :number-error="form.errors.number"
                        @update:zipcode="form.zipcode = $event"
                        @update:state="form.state = $event"
                        @update:state-id="form.state_id = $event"
                        @update:city="form.city = $event"
                        @update:address="form.address = $event"
                        @update:number="form.number = $event"
                    />
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
                        form.processing ? 'Salvando...' : 'Salvar Alterações'
                    }}</Button
                >
            </div>
        </form>
    </div>
</template>
