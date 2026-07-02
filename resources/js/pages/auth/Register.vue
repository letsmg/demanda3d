<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Criar conta de vendedor',
        description: 'Preencha os dados abaixo para criar sua conta de vendedor',
    },
});

const acceptTerms = ref(true);
const acceptPrivacy = ref(true);
</script>

<template>
    <Head title="Cadastro de Vendedor" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <!-- Hidden inputs for consent (Fortify expects them as form fields) -->
        <input type="hidden" name="accept_terms" :value="acceptTerms ? '1' : '0'" />
        <input type="hidden" name="accept_privacy" :value="acceptPrivacy ? '1' : '0'" />

        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">Nome</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Seu nome completo"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">E-mail</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="email@exemplo.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Senha</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Senha"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirmar senha</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Repita a senha"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <!-- Consentimento legal obrigatório -->
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-3">
                <p class="text-sm font-medium text-amber-900">Termos e Condições</p>

                <div class="flex items-start gap-2">
                    <Checkbox
                        id="accept_terms_vis"
                        v-model:checked="acceptTerms"
                        :tabindex="5"
                    />
                    <div class="grid gap-1">
                        <Label for="accept_terms_vis" class="text-sm font-normal cursor-pointer">
                            Li e aceito os
                            <TextLink :href="'/legal/terms'" class="underline underline-offset-4">
                                Termos de Uso
                            </TextLink>
                        </Label>
                    </div>
                </div>

                <div class="flex items-start gap-2">
                    <Checkbox
                        id="accept_privacy_vis"
                        v-model:checked="acceptPrivacy"
                        :tabindex="6"
                    />
                    <div class="grid gap-1">
                        <Label for="accept_privacy_vis" class="text-sm font-normal cursor-pointer">
                            Li e aceito a
                            <TextLink :href="'/legal/privacy'" class="underline underline-offset-4">
                                Política de Privacidade
                            </TextLink>
                        </Label>
                    </div>
                </div>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                :tabindex="7"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                Criar conta
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Já tem uma conta?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="8"
            >
                Faça login
            </TextLink>
        </div>
    </Form>
</template>