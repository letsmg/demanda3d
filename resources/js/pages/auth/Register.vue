<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { store } from '@/routes/register';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineProps<{ passwordRules: string }>();
defineOptions({ layout: WelcomeLayout });

const acceptTerms = ref(true);
const acceptPrivacy = ref(true);
</script>

<template>
    <Head title="Cadastro de Vendedor" />
    <div class="mx-auto flex w-full flex-col justify-center py-12 sm:w-[460px]">
        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardHeader class="text-center pb-2">
                <CardTitle class="text-xl text-amber-900">Criar conta de vendedor</CardTitle>
                <CardDescription class="text-amber-600">Preencha os dados abaixo para criar sua conta de vendedor</CardDescription>
            </CardHeader>
            <CardContent>
                <Form v-bind="store.form()" :reset-on-success="['password', 'password_confirmation']" v-slot="{ errors, processing }" class="flex flex-col gap-6">
                    <input type="hidden" name="accept_terms" :value="acceptTerms ? '1' : '0'" />
                    <input type="hidden" name="accept_privacy" :value="acceptPrivacy ? '1' : '0'" />
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="display_name" class="text-amber-800">Nome de exibição</Label>
                            <Input id="display_name" type="text" name="display_name" :tabindex="1"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                placeholder="Nome da sua loja" required />
                            <InputError :message="errors.display_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email" class="text-amber-800">E-mail</Label>
                            <Input id="email" type="email" name="email" required :tabindex="2"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                placeholder="email@exemplo.com" />
                            <InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password" class="text-amber-800">Senha</Label>
                            <PasswordInput id="password" name="password" required :tabindex="3" placeholder="Mínimo 8 caracteres" />
                            <InputError :message="errors.password" />
                            <p class="text-xs text-amber-500">{{ passwordRules }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="password_confirmation" class="text-amber-800">Confirmar Senha</Label>
                            <PasswordInput id="password_confirmation" name="password_confirmation" required :tabindex="4" placeholder="Repita a senha" />
                        </div>
                        <div class="rounded-md bg-amber-100 p-3 flex items-start gap-2">
                            <Checkbox id="accept_terms_v" v-model:checked="acceptTerms" :tabindex="5" class="border-amber-900" />
                            <Label for="accept_terms_v" class="text-sm leading-relaxed text-amber-800">
                                Li e aceito os <a href="/legal/terms" target="_blank" class="font-medium text-amber-700 hover:text-amber-900">Termos de Uso</a>
                                e a <a href="/legal/privacy" target="_blank" class="font-medium text-amber-700 hover:text-amber-900">Política de Privacidade</a>
                            </Label>
                        </div>
                        <InputError :message="errors.accept_terms" />
                        <Button type="submit" class="mt-2 w-full bg-amber-500 font-semibold text-amber-950 hover:bg-amber-910" :tabindex="6" :disabled="processing">
                            <Spinner v-if="processing" /> Criar Conta de Vendedor
                        </Button>
                    </div>
                    <div class="text-center text-sm text-amber-600">
                        Já tem uma conta? <a href="/login" class="font-medium text-amber-700 hover:text-amber-900">Faça login</a>
                    </div>
                    <div class="space-y-1 text-center text-xs text-amber-600">
                        <div><Link :href="'/register_cli'" class="font-medium text-amber-700 hover:text-amber-900">Cadastre-se como Cliente</Link></div>
                        <div><Link :href="'/register_carrier'" class="font-medium text-amber-700 hover:text-amber-900">Cadastre-se como Transportadora</Link></div>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>