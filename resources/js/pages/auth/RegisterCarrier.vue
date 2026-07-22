<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);
import FormTestHelper from '@/components/FormTestHelper.vue';
import type {TestField} from '@/components/FormTestHelper.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    accept_terms: true,
    accept_privacy: true,
});

// ── testFields para FormTestHelper ───────────────
const testFields: TestField[] = [
    { key: 'email', value: 'contato@transportadora.com.br' },
    { key: 'password', value: 'Senha@123' },
    { key: 'password_confirmation', value: 'Senha@123' },
];

function handleFill() {
    form.email = 'contato@transportadora.com.br';
    form.password = 'Senha@123';
    form.password_confirmation = 'Senha@123';
}

function handleClear() {
    form.reset();
}

function submit() {
    form.post('/register_carrier', { preserveState: true });
}
</script>

<template>
    <Head title="Cadastro de Transportadora" />
    <div class="mx-auto flex w-full flex-col justify-center py-12 sm:w-[460px]">
        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardHeader class="pb-2 text-center">
                <CardTitle class="text-xl text-amber-900"
                    >Cadastro de Transportadora</CardTitle
                >
                <CardDescription class="text-amber-600"
                    >Cadastre sua transportadora para começar a operar na
                    plataforma</CardDescription
                >
            </CardHeader>
            <CardContent>
                <FormTestHelper
                    :form="form as any"
                    :fields="testFields"
                    label="Transportadora teste"
                    @fill="handleFill"
                    @clear="handleClear"
                />
                <form @submit.prevent="submit" class="mt-3 flex flex-col gap-6">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="email" class="text-amber-800"
                                >E-mail *</Label
                            >
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                :tabindex="1"
                                class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                placeholder="Seu e-mail de acesso"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password" class="text-amber-800"
                                >Senha *</Label
                            >
                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                v-model:show="showPassword"
                                required
                                :tabindex="2"
                                placeholder="Mínimo 8 caracteres"
                            />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="password_confirmation"
                                class="text-amber-800"
                                >Confirmar Senha *</Label
                            >
                            <PasswordInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                v-model:show="showPassword"
                                required
                                :tabindex="3"
                                placeholder="Repita a senha"
                            />
                        </div>
                        <p class="text-xs text-amber-600">
                            Após o cadastro, você será direcionado para
                            completar os dados da sua transportadora.
                        </p>
                        <div class="space-y-2 rounded-md bg-amber-100 p-3">
                            <div class="flex items-start gap-2">
                                <Checkbox
                                    id="accept_terms"
                                    v-model:checked="form.accept_terms"
                                    :tabindex="4"
                                    class="border-amber-900"
                                />
                                <Label
                                    for="accept_terms"
                                    class="text-sm leading-relaxed text-amber-800"
                                >
                                    Li e aceito os
                                    <a
                                        href="/legal/terms"
                                        target="_blank"
                                        class="font-medium text-amber-700 hover:text-amber-900"
                                        >Termos de Uso</a
                                    >
                                </Label>
                            </div>
                            <div class="flex items-start gap-2">
                                <Checkbox
                                    id="accept_privacy"
                                    v-model:checked="form.accept_privacy"
                                    :tabindex="5"
                                    class="border-amber-900"
                                />
                                <Label
                                    for="accept_privacy"
                                    class="text-sm leading-relaxed text-amber-800"
                                >
                                    Li e aceito a
                                    <a
                                        href="/legal/privacy"
                                        target="_blank"
                                        class="font-medium text-amber-700 hover:text-amber-900"
                                        >Política de Privacidade</a
                                    >
                                </Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.accept_terms" />
                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                class="hover:bg-amber-910 flex-1 bg-amber-500 font-semibold text-amber-950"
                                :tabindex="5"
                                :disabled="form.processing"
                            >
                                <Spinner v-if="form.processing" /> Criar Conta
                                de Transportadora
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="border-amber-900 text-amber-700 hover:bg-amber-50"
                                :tabindex="6"
                                @click="form.reset()"
                            >
                                Limpar
                            </Button>
                        </div>
                    </div>
                    <div class="text-center text-sm text-amber-600">
                        Já tem uma conta?
                        <a
                            href="/login_carrier"
                            class="font-medium text-amber-700 hover:text-amber-900"
                            >Faça login</a
                        >
                    </div>
                    <div class="space-y-1 text-center text-xs text-amber-600">
                        <div>
                            <Link
                                :href="'/register'"
                                class="font-medium text-amber-700 hover:text-amber-900"
                                >Cadastre-se como Vendedor</Link
                            >
                        </div>
                        <div>
                            <Link
                                :href="'/register_cli'"
                                class="font-medium text-amber-700 hover:text-amber-900"
                                >Cadastre-se como Cliente</Link
                            >
                        </div>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
