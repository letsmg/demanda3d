<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import FormTestHelper, { type TestField } from '@/components/FormTestHelper.vue';
import { Checkbox } from '@/components/ui/checkbox';

defineOptions({
    layout: {
        title: 'Criar conta de cliente',
        description: 'Cadastre-se para comprar na loja',
    },
});

const form = useForm({
    display_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    accept_terms: true,
    accept_privacy: true,
});

const testFields: TestField[] = [
    { key: 'display_name', value: 'Tech3D Soluções Ltda' },
    { key: 'email', value: 'tech3d@demanda3d.com' },
    { key: 'password', value: 'password' },
    { key: 'password_confirmation', value: 'password' },
];

function handleFill(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = f.value;
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

function submit() {
    form.post('/register_cli', {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Cadastro de Cliente" />

    <div class="mb-4 text-center">
        <p class="text-sm text-muted-foreground">
            Crie sua conta para acessar a loja e fazer pedidos
        </p>
    </div>

    <form
        @submit.prevent="submit"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <FormTestHelper :form="form" :fields="testFields" label="Cadastro" @fill="handleFill" @clear="handleClear" />

            <div class="grid gap-2">
                <Label for="display_name">Nome / Empresa</Label>
                <Input
                    id="display_name"
                    type="text"
                    v-model="form.display_name"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    placeholder="Seu nome ou nome da empresa"
                />
                <InputError :message="form.errors.display_name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">E-mail</Label>
                <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Senha</Label>
                <PasswordInput
                    id="password"
                    v-model="form.password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirmar senha</Label>
                <PasswordInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    placeholder="Repita a senha"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <!-- Consentimento legal obrigatório -->
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-3">
                <p class="text-sm font-medium text-amber-900">Termos e Condições</p>

                <div class="flex items-start gap-2">
                    <Checkbox
                        id="accept_terms"
                        v-model:checked="form.accept_terms"
                        :tabindex="5"
                    />
                    <div class="grid gap-1">
                        <Label for="accept_terms" class="text-sm font-normal cursor-pointer">
                            Li e aceito os
                            <TextLink :href="'/legal/terms'" class="underline underline-offset-4">
                                Termos de Uso
                            </TextLink>
                        </Label>
                        <InputError :message="form.errors.accept_terms" />
                    </div>
                </div>

                <div class="flex items-start gap-2">
                    <Checkbox
                        id="accept_privacy"
                        v-model:checked="form.accept_privacy"
                        :tabindex="6"
                    />
                    <div class="grid gap-1">
                        <Label for="accept_privacy" class="text-sm font-normal cursor-pointer">
                            Li e aceito a
                            <TextLink :href="'/legal/privacy'" class="underline underline-offset-4">
                                Política de Privacidade
                            </TextLink>
                        </Label>
                        <InputError :message="form.errors.accept_privacy" />
                    </div>
                </div>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                :tabindex="7"
                :disabled="form.processing"
                data-test="register-client-button"
            >
                <Spinner v-if="form.processing" />
                Criar conta
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Já tem uma conta?
            <TextLink
                :href="'/login_cli'"
                class="underline underline-offset-4"
                :tabindex="8"
            >
                Faça login
            </TextLink>
        </div>
    </form>
</template>