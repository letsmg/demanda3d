<script setup lang="ts">
import { ref, watch } from 'vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as clientsIndex } from '@/routes/clients';
import type { Client } from '@/types';
import FormTestHelper, { type TestField } from '@/components/FormTestHelper.vue';

const props = defineProps<{
    client: Client;
}>();

const form = useForm({
    first_name: props.client.first_name,
    last_name: props.client.last_name,
    display_name: props.client.display_name || '',
    doc_type: props.client.doc_type || 'CPF',
    doc: props.client.doc,
    address: props.client.address,
    number: props.client.number,
    state: props.client.state,
    zipcode: props.client.zipcode,
    city: props.client.city,
    phone1: props.client.phone1,
    phone2: props.client.phone2,
    contact1: props.client.contact1 || '',
    contact2: props.client.contact2 || '',
});

function generateValidCpf(): string {
    const digits: number[] = [];
    for (let i = 0; i < 9; i++) {
        digits.push(Math.floor(Math.random() * 10));
    }

    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += digits[i] * (10 - i);
    }
    const d1 = sum % 11;
    digits.push(d1 < 2 ? 0 : 11 - d1);

    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += digits[i] * (11 - i);
    }
    const d2 = sum % 11;
    digits.push(d2 < 2 ? 0 : 11 - d2);

    const raw = digits.join('');
    return `${raw.slice(0, 3)}.${raw.slice(3, 6)}.${raw.slice(6, 9)}-${raw.slice(9, 11)}`;
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

function handleFill(fields: TestField[]) {
    // Generate a fresh CPF each time "Preencher Teste" is clicked
    const freshCpf = generateValidCpf();

    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = f.key === 'doc' ? freshCpf : f.value;
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

const docType = ref(form.doc_type);

const applyDocMask = (value: string, type: string): string => {
    const digits = value.replace(/\D/g, '');
    if (type === 'CPF') {
        return digits
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .slice(0, 14);
    } else {
        return digits
            .replace(/(\d{2})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1/$2')
            .replace(/(\d{4})(\d{1,2})$/, '$1-$2')
            .slice(0, 18);
    }
};

watch(docType, (newType) => {
    form.doc_type = newType;
    form.doc = applyDocMask(form.doc, newType);
});

const onDocInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    form.doc = applyDocMask(target.value, form.doc_type);
};

const submit = () => {
    form.put(`/clients/${props.client.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Cliente" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="clientsIndex()">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Editar Cliente
                </h1>
                <p class="text-sm text-muted-foreground">
                    Editando: {{ props.client.first_name }} {{ props.client.last_name }}
                </p>
            </div>
        </div>

        <!-- Error Alert -->
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <!-- Test Helper -->
        <FormTestHelper :form="form" :fields="testFields" label="Editar Cliente" @fill="handleFill" @clear="handleClear" />

        <!-- Form -->
        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Cliente</CardTitle>
                    <CardDescription
                        >Edite os dados do cliente abaixo</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Name fields -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="first_name">Nome *</Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                placeholder="Primeiro nome"
                                :class="{
                                    'border-destructive': form.errors.first_name,
                                }"
                            />
                            <span
                                v-if="form.errors.first_name"
                                class="text-sm text-destructive"
                                >{{ form.errors.first_name }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="last_name">Sobrenome *</Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                placeholder="Sobrenome"
                                :class="{
                                    'border-destructive': form.errors.last_name,
                                }"
                            />
                            <span
                                v-if="form.errors.last_name"
                                class="text-sm text-destructive"
                                >{{ form.errors.last_name }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="display_name">Nome de Exibição</Label>
                            <Input
                                id="display_name"
                                v-model="form.display_name"
                                placeholder="Nome para exibição (opcional)"
                            />
                        </div>
                    </div>

                    <!-- Document -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="doc_type">Tipo de Documento *</Label>
                            <Select v-model="docType">
                                <SelectTrigger id="doc_type">
                                    <SelectValue placeholder="Selecione o tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="CPF">CPF</SelectItem>
                                    <SelectItem value="CNPJ">CNPJ</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="doc">Documento *</Label>
                            <Input
                                id="doc"
                                :value="form.doc"
                                @input="onDocInput"
                                :placeholder="form.doc_type === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00'"
                                maxlength="18"
                                :class="{
                                    'border-destructive': form.errors.doc,
                                }"
                            />
                            <span
                                v-if="form.errors.doc"
                                class="text-sm text-destructive"
                                >{{ form.errors.doc }}</span
                            >
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="address">Endereço *</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                placeholder="Rua, Avenida..."
                                :class="{
                                    'border-destructive': form.errors.address,
                                }"
                            />
                            <span
                                v-if="form.errors.address"
                                class="text-sm text-destructive"
                                >{{ form.errors.address }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="number">Número *</Label>
                            <Input
                                id="number"
                                v-model="form.number"
                                placeholder="123"
                                :class="{
                                    'border-destructive': form.errors.number,
                                }"
                            />
                            <span
                                v-if="form.errors.number"
                                class="text-sm text-destructive"
                                >{{ form.errors.number }}</span
                            >
                        </div>
                    </div>

                    <!-- City, State, Zipcode -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="city">Cidade *</Label>
                            <Input
                                id="city"
                                v-model="form.city"
                                placeholder="São Paulo"
                                :class="{
                                    'border-destructive': form.errors.city,
                                }"
                            />
                            <span
                                v-if="form.errors.city"
                                class="text-sm text-destructive"
                                >{{ form.errors.city }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="state">UF *</Label>
                            <Input
                                id="state"
                                v-model="form.state"
                                placeholder="SP"
                                maxlength="2"
                                :class="{
                                    'border-destructive': form.errors.state,
                                }"
                            />
                            <span
                                v-if="form.errors.state"
                                class="text-sm text-destructive"
                                >{{ form.errors.state }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="zipcode">CEP *</Label>
                            <Input
                                id="zipcode"
                                v-model="form.zipcode"
                                placeholder="00000-000"
                                :class="{
                                    'border-destructive': form.errors.zipcode,
                                }"
                            />
                            <span
                                v-if="form.errors.zipcode"
                                class="text-sm text-destructive"
                                >{{ form.errors.zipcode }}</span
                            >
                        </div>
                    </div>

                    <!-- Phones -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="phone1">Telefone 1 *</Label>
                            <Input
                                id="phone1"
                                v-model="form.phone1"
                                placeholder="(11) 99999-0000"
                                :class="{
                                    'border-destructive': form.errors.phone1,
                                }"
                            />
                            <span
                                v-if="form.errors.phone1"
                                class="text-sm text-destructive"
                                >{{ form.errors.phone1 }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="phone2">Telefone 2</Label>
                            <Input
                                id="phone2"
                                v-model="form.phone2"
                                placeholder="(11) 3333-0000"
                            />
                        </div>
                    </div>

                    <!-- Contacts -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="contact1">Contato 1</Label>
                            <Input
                                id="contact1"
                                v-model="form.contact1"
                                placeholder="Nome do contato principal"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="contact2">Contato 2</Label>
                            <Input
                                id="contact2"
                                v-model="form.contact2"
                                placeholder="Nome do contato secundário"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="clientsIndex()">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                </Button>
            </div>
        </form>
    </div>
</template>