<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import FormTestHelper, {
    type TestField,
} from '@/components/FormTestHelper.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Spinner } from '@/components/ui/spinner';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { store } from '@/routes/register';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });

const acceptTerms = ref(true);
const acceptPrivacy = ref(true);

// ── Form de preenchimento rápido (opera sobre o DOM via FormTestHelper) ──
const quickForm = useForm({
    email: '',
    password: '',
    password_confirmation: '',
});

const testFields: TestField[] = [
    { key: 'email', value: 'vendedor@loja.com.br' },
    { key: 'password', value: 'Senha@123' },
    { key: 'password_confirmation', value: 'Senha@123' },
];

function handleFill() {
    quickForm.email = 'vendedor@loja.com.br';
    quickForm.password = 'Senha@123';
    quickForm.password_confirmation = 'Senha@123';
    // Aplica nos inputs reais do Form Fortify
    const emailInput = document.querySelector<HTMLInputElement>(
        'input[name="email"]',
    );
    const passwordInput = document.querySelector<HTMLInputElement>(
        'input[name="password"]',
    );
    const passwordConfInput = document.querySelector<HTMLInputElement>(
        'input[name="password_confirmation"]',
    );
    if (emailInput) emailInput.value = 'vendedor@loja.com.br';
    if (passwordInput) passwordInput.value = 'Senha@123';
    if (passwordConfInput) passwordConfInput.value = 'Senha@123';
}

function handleClear() {
    const emailInput = document.querySelector<HTMLInputElement>(
        'input[name="email"]',
    );
    const passwordInput = document.querySelector<HTMLInputElement>(
        'input[name="password"]',
    );
    const passwordConfInput = document.querySelector<HTMLInputElement>(
        'input[name="password_confirmation"]',
    );
    if (emailInput) emailInput.value = '';
    if (passwordInput) passwordInput.value = '';
    if (passwordConfInput) passwordConfInput.value = '';
}
</script>

<template>
    <Head title="Cadastro de Vendedor" />
    <div class="mx-auto flex w-full flex-col justify-center py-12 sm:w-[460px]">
        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardHeader class="pb-2 text-center">
                <CardTitle class="text-xl text-amber-900"
                    >Criar conta de vendedor</CardTitle
                >
                <CardDescription class="text-amber-600"
                    >Cadastre-se para começar a vender na
                    plataforma</CardDescription
                >
            </CardHeader>
            <CardContent>
                <FormTestHelper
                    :form="quickForm as any"
                    :fields="testFields"
                    label="Vendedor teste"
                    @fill="handleFill"
                    @clear="handleClear"
                />
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="mt-3 flex flex-col gap-6"
                >
                    <input
                        type="hidden"
                        name="accept_terms"
                        :value="acceptTerms ? '1' : '0'"
                    />
                    <input
                        type="hidden"
                        name="accept_privacy"
                        :value="acceptPrivacy ? '1' : '0'"
                    />
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="email" class="text-amber-800"
                                >E-mail *</Label
                            >
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                :tabindex="1"
                                class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                placeholder="Seu e-mail de acesso"
                            />
                            <InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password" class="text-amber-800"
                                >Senha *</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                v-model:show="showPassword"
                                required
                                :tabindex="2"
                                placeholder="Mínimo 8 caracteres"
                            />
                            <InputError :message="errors.password" />
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="password_confirmation"
                                class="text-amber-800"
                                >Confirmar Senha *</Label
                            >
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                v-model:show="showPassword"
                                required
                                :tabindex="3"
                                placeholder="Repita a senha"
                            />
                        </div>
                        <p class="text-xs text-amber-600">
                            Após o cadastro, você será direcionado para
                            completar os dados da sua loja e perfil.
                        </p>
                        <div class="space-y-2 rounded-md bg-amber-100 p-3">
                            <div class="flex items-start gap-2">
                                <Checkbox
                                    id="accept_terms_v"
                                    v-model:checked="acceptTerms"
                                    :tabindex="4"
                                    class="border-amber-900"
                                />
                                <Label
                                    for="accept_terms_v"
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
                                    id="accept_privacy_v"
                                    v-model:checked="acceptPrivacy"
                                    :tabindex="5"
                                    class="border-amber-900"
                                />
                                <Label
                                    for="accept_privacy_v"
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
                        <InputError :message="errors.accept_terms" />
                        <div class="flex gap-2">
                            <Button
                                type="submit"
                                class="hover:bg-amber-910 flex-1 bg-amber-500 font-semibold text-amber-950"
                                :tabindex="6"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" /> Criar Conta de
                                Vendedor
                            </Button>
                            <Button
                                type="reset"
                                variant="outline"
                                class="border-amber-900 text-amber-700 hover:bg-amber-50"
                                :tabindex="7"
                            >
                                Limpar
                            </Button>
                        </div>
                    </div>
                    <div class="text-center text-sm text-amber-600">
                        Já tem uma conta?
                        <a
                            href="/login"
                            class="font-medium text-amber-700 hover:text-amber-900"
                            >Faça login</a
                        >
                    </div>
                    <div class="space-y-1 text-center text-xs text-amber-600">
                        <div>
                            <Link
                                :href="'/register_cli'"
                                class="font-medium text-amber-700 hover:text-amber-900"
                                >Cadastre-se como Cliente</Link
                            >
                        </div>
                        <div>
                            <Link
                                :href="'/register_carrier'"
                                class="font-medium text-amber-700 hover:text-amber-900"
                                >Cadastre-se como Transportadora</Link
                            >
                        </div>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
