<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Cadastro de Transportadora',
        description: 'Cadastre sua transportadora na plataforma',
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    doc_type: 'CNPJ',
    document: '',
    data_nascimento: '',
    accept_terms: false,
});

function submit() {
    form.post('/register_carrier', {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Cadastro de Transportadora" />

    <div class="mb-4 text-center">
        <p class="text-sm text-muted-foreground">
            Cadastre sua transportadora para começar a operar na plataforma
        </p>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
            <!-- Nome da Empresa -->
            <div class="grid gap-2">
                <Label for="name">Nome da Transportadora</Label>
                <Input
                    id="name"
                    type="text"
                    name="name"
                    v-model="form.name"
                    required
                    autofocus
                    :tabindex="1"
                    placeholder="Transportadora Exemplo Ltda"
                />
                <InputError :message="form.errors.name" />
            </div>

            <!-- E-mail -->
            <div class="grid gap-2">
                <Label for="email">E-mail</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <!-- Senha -->
            <div class="grid gap-2">
                <Label for="password">Senha</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="form.password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                />
                <InputError :message="form.errors.password" />
            </div>

            <!-- Confirmar Senha -->
            <div class="grid gap-2">
                <Label for="password_confirmation">Confirmar Senha</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    v-model="form.password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    placeholder="Repita a senha"
                />
            </div>

            <!-- Tipo de Documento -->
            <div class="grid gap-2">
                <Label for="doc_type">Tipo de Documento</Label>
                <Select v-model="form.doc_type">
                    <SelectTrigger :tabindex="5">
                        <SelectValue placeholder="Selecione" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="CNPJ">CNPJ</SelectItem>
                        <SelectItem value="CPF">CPF</SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.doc_type" />
            </div>

            <!-- Documento -->
            <div class="grid gap-2">
                <Label for="document">
                    {{ form.doc_type === 'CNPJ' ? 'CNPJ' : 'CPF' }}
                </Label>
                <Input
                    id="document"
                    type="text"
                    name="document"
                    v-model="form.document"
                    required
                    :tabindex="6"
                    :placeholder="
                        form.doc_type === 'CNPJ'
                            ? '00.000.000/0000-00'
                            : '000.000.000-00'
                    "
                />
                <InputError :message="form.errors.document" />
            </div>

            <!-- Data de Nascimento (apenas para CPF) -->
            <div v-if="form.doc_type === 'CPF'" class="grid gap-2">
                <Label for="data_nascimento">Data de Nascimento</Label>
                <Input
                    id="data_nascimento"
                    type="date"
                    name="data_nascimento"
                    v-model="form.data_nascimento"
                    :tabindex="7"
                />
                <InputError :message="form.errors.data_nascimento" />
            </div>

            <!-- Aceite dos Termos -->
            <div class="flex items-start gap-2">
                <Checkbox
                    id="accept_terms"
                    name="accept_terms"
                    v-model:checked="form.accept_terms"
                    :tabindex="8"
                />
                <Label for="accept_terms" class="text-sm leading-relaxed">
                    Li e aceito os
                    <TextLink :href="'/legal/terms'" target="_blank">
                        Termos de Uso
                    </TextLink>
                    e a
                    <TextLink :href="'/legal/privacy'" target="_blank">
                        Política de Privacidade
                    </TextLink>
                </Label>
            </div>
            <InputError :message="form.errors.accept_terms" />

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="9"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" />
                Criar Conta de Transportadora
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Já tem uma conta?
            <TextLink :href="'/login_carrier'" :tabindex="10"
                >Faça login</TextLink
            >
        </div>
    </form>
</template>
