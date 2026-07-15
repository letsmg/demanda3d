<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });
const form = useForm({ email: '', password: '', remember: false });

interface TestCarrierAccount { label: string; email: string; }
const testAccounts: TestCarrierAccount[] = [
    { label: 'Transportadora Rápida (Carrier 1)', email: 'transp1@teste.com' },
    { label: 'Transportadora Veloz (Carrier 2)', email: 'transp2@teste.com' },
];
function fillTestCredentials(acc: TestCarrierAccount) { form.email = acc.email; form.password = 'Mudar@123'; }

function submit() { form.post('/login_carrier', { preserveState: true }); }
</script>

<template>
    <Head title="Entrar como Transportadora" />
    <div class="mx-auto flex w-full flex-col justify-center py-12 sm:w-[440px]">
        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardHeader class="text-center pb-2">
                <CardTitle class="text-xl text-amber-900">Entrar como Transportadora</CardTitle>
                <CardDescription class="text-amber-600">Acesse sua conta de transportadora para gerenciar fretes</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="rounded-lg border border-dashed border-amber-500/50 bg-amber-100/50 p-3">
                    <p class="mb-2 text-center text-xs font-medium text-amber-700">🧪 Contas de teste (senha: <strong>Mudar@123</strong>)</p>
                    <div class="flex flex-col gap-1.5">
                        <button v-for="acc in testAccounts" :key="acc.email" type="button"
                            class="flex items-center justify-between rounded-md px-3 py-1.5 text-xs text-amber-800 transition-colors hover:bg-amber-200/50"
                            @click="fillTestCredentials(acc)">
                            <span class="font-medium">{{ acc.label }}</span>
                            <span class="text-amber-600">{{ acc.email }}</span>
                        </button>
                    </div>
                </div>
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="email" class="text-amber-800">E-mail</Label>
                            <Input id="email" type="email" v-model="form.email" required autofocus :tabindex="1"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                placeholder="email@exemplo.com" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between"><Label for="password" class="text-amber-800">Senha</Label></div>
                            <PasswordInput id="password" v-model="form.password" required :tabindex="2" placeholder="Senha" />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3 text-amber-700">
                                <Checkbox id="remember" v-model:checked="form.remember" :tabindex="3" class="border-amber-900" /><span>Lembrar de mim</span>
                            </Label>
                        </div>
                        <Button type="submit" class="mt-2 w-full bg-amber-500 font-semibold text-amber-950 hover:bg-amber-910" :tabindex="4" :disabled="form.processing">
                            <Spinner v-if="form.processing" /> Entrar como Transportadora
                        </Button>
                    </div>
                    <div class="text-center text-sm text-amber-600">
                        Não tem uma conta? <a href="/register_carrier" class="font-medium text-amber-700 hover:text-amber-900">Cadastre-se</a>
                    </div>
                    <div class="space-y-1 text-center text-xs text-amber-600">
                        <div><Link :href="'/login'" class="font-medium text-amber-700 hover:text-amber-900">Acesse como Vendedor</Link></div>
                        <div><Link :href="'/login_cli'" class="font-medium text-amber-700 hover:text-amber-900">Acesse como Cliente</Link></div>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>