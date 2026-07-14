<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });

const form = useForm({
    name: '', email: '', password: '', password_confirmation: '',
    doc_type: 'CNPJ', document: '', data_nascimento: '', accept_terms: false, accept_privacy: false,
});

function submit() { form.post('/register_carrier', { preserveState: true }); }
</script>

<template>
    <Head title="Cadastro de Transportadora" />
    <div class="mx-auto flex w-full flex-col justify-center py-12 sm:w-[460px]">
        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardHeader class="text-center pb-2">
                <CardTitle class="text-xl text-amber-900">Cadastro de Transportadora</CardTitle>
                <CardDescription class="text-amber-600">Cadastre sua transportadora para começar a operar na plataforma</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name" class="text-amber-800">Nome da Transportadora</Label>
                            <Input id="name" type="text" v-model="form.name" required autofocus :tabindex="1"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                placeholder="Transportadora Exemplo Ltda" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email" class="text-amber-800">E-mail</Label>
                            <Input id="email" type="email" v-model="form.email" required :tabindex="2"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                placeholder="email@exemplo.com" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password" class="text-amber-800">Senha</Label>
                            <PasswordInput id="password" v-model="form.password" required :tabindex="3" placeholder="Mínimo 8 caracteres" />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password_confirmation" class="text-amber-800">Confirmar Senha</Label>
                            <PasswordInput id="password_confirmation" v-model="form.password_confirmation" required :tabindex="4" placeholder="Repita a senha" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="doc_type" class="text-amber-800">Tipo de Documento</Label>
                            <Select v-model="form.doc_type">
                                <SelectTrigger :tabindex="5" class="border-amber-900 bg-white text-amber-800"><SelectValue placeholder="Selecione" /></SelectTrigger>
                                <SelectContent><SelectItem value="CNPJ">CNPJ</SelectItem><SelectItem value="CPF">CPF</SelectItem></SelectContent>
                            </Select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="document" class="text-amber-800">{{ form.doc_type === 'CNPJ' ? 'CNPJ' : 'CPF' }}</Label>
                            <Input id="document" type="text" v-model="form.document" required :tabindex="6"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                :placeholder="form.doc_type === 'CNPJ' ? '00.000.000/0000-00' : '000.000.000-00'" />
                            <InputError :message="form.errors.document" />
                        </div>
                        <div v-if="form.doc_type === 'CPF'" class="grid gap-2">
                            <Label for="data_nascimento" class="text-amber-800">Data de Nascimento</Label>
                            <Input id="data_nascimento" type="date" v-model="form.data_nascimento" :tabindex="7"
                                class="border-amber-900 bg-white text-amber-900" />
                        </div>
                        <div class="rounded-md bg-amber-100 p-3 space-y-2">
                            <div class="flex items-start gap-2">
                                <Checkbox id="accept_terms" v-model:checked="form.accept_terms" :tabindex="8" class="border-amber-900" />
                                <Label for="accept_terms" class="text-sm leading-relaxed text-amber-800">
                                    Li e aceito os <a href="/legal/terms" target="_blank" class="font-medium text-amber-700 hover:text-amber-900">Termos de Uso</a>
                                </Label>
                            </div>
                            <div class="flex items-start gap-2">
                                <Checkbox id="accept_privacy" v-model:checked="form.accept_privacy" :tabindex="9" class="border-amber-900" />
                                <Label for="accept_privacy" class="text-sm leading-relaxed text-amber-800">
                                    Li e aceito a <a href="/legal/privacy" target="_blank" class="font-medium text-amber-700 hover:text-amber-900">Política de Privacidade</a>
                                </Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.accept_terms" />
                        <div class="flex gap-2">
                            <Button type="submit" class="flex-1 bg-amber-500 font-semibold text-amber-950 hover:bg-amber-910" :tabindex="10" :disabled="form.processing">
                                <Spinner v-if="form.processing" /> Criar Conta de Transportadora
                            </Button>
                            <Button type="reset" variant="outline" class="border-amber-900 text-amber-700 hover:bg-amber-50" :tabindex="11">
                                Limpar
                            </Button>
                        </div>
                    </div>
                    <div class="text-center text-sm text-amber-600">
                        Já tem uma conta? <a href="/login_carrier" class="font-medium text-amber-700 hover:text-amber-900">Faça login</a>
                    </div>
                    <div class="space-y-1 text-center text-xs text-amber-600">
                        <div><Link :href="'/register'" class="font-medium text-amber-700 hover:text-amber-900">Cadastre-se como Vendedor</Link></div>
                        <div><Link :href="'/register_cli'" class="font-medium text-amber-700 hover:text-amber-900">Cadastre-se como Cliente</Link></div>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>