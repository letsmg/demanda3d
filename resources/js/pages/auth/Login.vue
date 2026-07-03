<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Entrar na sua conta',
        description: 'Digite seu email e senha abaixo para entrar',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

interface TestAccount {
    label: string;
    email: string;
    role: string;
}

const email = ref('');
const password = ref('');

const testAccounts: TestAccount[] = [
    { label: 'Admin Master', email: 'admin@demanda3d.com', role: 'Admin' },
    {
        label: 'Tech3D Soluções',
        email: 'tech3d@demanda3d.com.br',
        role: 'Partner',
    },
    { label: 'Maker Lab 3D', email: 'maker@demanda3d.com.br', role: 'Partner' },
    {
        label: 'Prototype Fast',
        email: 'prototype@demanda3d.com.br',
        role: 'Partner',
    },
];

function fillTestCredentials(acc: TestAccount) {
    email.value = acc.email;
    password.value = 'Mudar@123';
}
</script>

<template>
    <Head title="Entrar" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <!-- Test accounts -->
    <div
        class="mb-6 rounded-lg border border-dashed border-primary/30 bg-primary/5 p-3"
    >
        <p class="mb-2 text-center text-xs font-medium text-muted-foreground">
            🧪 Contas de teste (senha: <strong>Mudar@123</strong>)
        </p>
        <div class="flex flex-col gap-1.5">
            <button
                v-for="acc in testAccounts"
                :key="acc.email"
                type="button"
                class="flex items-center justify-between rounded-md px-3 py-1.5 text-xs transition-colors hover:bg-primary/10"
                @click="fillTestCredentials(acc)"
            >
                <span class="font-medium">{{ acc.label }}</span>
                <span class="text-muted-foreground">{{ acc.email }}</span>
                <span
                    class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                    >{{ acc.role }}</span
                >
            </button>
        </div>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">E-mail</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    v-model="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Senha</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        Esqueceu sua senha?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Senha"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Lembrar de mim</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Entrar
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Não tem uma conta?
            <TextLink :href="register()" :tabindex="5">Cadastre-se</TextLink>
        </div>
    </Form>
</template>
