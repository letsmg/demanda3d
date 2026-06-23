<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Entrar como Cliente',
        description: 'Acesse sua conta de cliente para comprar na loja',
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

interface TestClientAccount {
    label: string;
    email: string;
}

const testAccounts: TestClientAccount[] = [
    { label: 'Tech3D Soluções Ltda', email: 'tech3d@demanda3d.com' },
    { label: 'Prototipagem Rápida S.A.', email: 'prototipagem@demanda3d.com' },
    { label: 'Indústria Criativa Maker', email: 'industria@demanda3d.com' },
];

function fillTestCredentials(acc: TestClientAccount) {
    form.email = acc.email;
    form.password = 'password';
}

function submit() {
    form.post('/login_cli', {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Entrar como Cliente" />

    <div class="mb-4 text-center">
        <p class="text-sm text-muted-foreground">
            Acesse sua conta para comprar produtos na loja
        </p>
    </div>

    <!-- Test client accounts -->
    <div class="mb-6 rounded-lg border border-dashed border-primary/30 bg-primary/5 p-3">
        <p class="mb-2 text-center text-xs font-medium text-muted-foreground">
            🧪 Contas de teste (senha: <strong>password</strong>)
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
            </button>
        </div>
    </div>

    <form
        @submit.prevent="submit"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">E-mail</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Senha</Label>
                    <TextLink
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
                    v-model="form.password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Senha"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" v-model:checked="form.remember" :tabindex="3" />
                    <span>Lembrar de mim</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="form.processing"
                data-test="login-client-button"
            >
                <Spinner v-if="form.processing" />
                Entrar como Cliente
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Não tem uma conta?
            <TextLink :href="'/register_cli'" :tabindex="5">Cadastre-se</TextLink>
        </div>

        <div class="text-center text-xs text-muted-foreground">
            <span>É um parceiro?</span>
            <Link :href="'/login'" class="ml-1 font-medium text-primary hover:underline">
                Acesse como Parceiro
            </Link>
        </div>
    </form>
</template>