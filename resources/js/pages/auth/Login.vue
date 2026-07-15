<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { request } from '@/routes/password';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });
defineProps<{ status?: string; canResetPassword: boolean }>();

interface TestAccount { label: string; email: string; }

const email = ref('');
const password = ref('');
const testAccounts: TestAccount[] = [
    { label: 'Admin Geral (Admin 1)', email: 'admin@teste.com' },
    { label: 'Admin Suporte (Admin 2)', email: 'admin2@teste.com' },
    { label: 'Loja 1 Admin (Seller 1)', email: 'loja1adm@teste.com' },
    { label: 'Loja 1 Padrão (Seller 2)', email: 'loja1padrao@teste.com' },
    { label: 'Loja 2 Admin (Seller 1)', email: 'loja2adm@teste.com' },
    { label: 'Loja 2 Padrão (Seller 2)', email: 'loja2padrao@teste.com' },
    { label: 'Loja 3 Admin (Seller 1)', email: 'loja3adm@teste.com' },
    { label: 'Loja 3 Padrão (Seller 2)', email: 'loja3padrao@teste.com' },
    { label: 'Loja 4 Admin (Seller 1)', email: 'loja4adm@teste.com' },
    { label: 'Loja 4 Padrão (Seller 2)', email: 'loja4padrao@teste.com' },
    { label: 'Loja 5 Admin (Seller 1)', email: 'loja5adm@teste.com' },
    { label: 'Loja 5 Padrão (Seller 2)', email: 'loja5padrao@teste.com' },
];
function fillTestCredentials(acc: TestAccount) { email.value = acc.email; password.value = 'Mudar@123'; }
</script>

<template>
    <Head title="Entrar como Vendedor" />
    <div class="mx-auto flex w-full flex-col justify-center py-12 sm:w-[440px]">
        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardHeader class="text-center pb-2">
                <CardTitle class="text-xl text-amber-900">Entrar como Vendedor</CardTitle>
                <CardDescription class="text-amber-600">Digite seu email e senha abaixo para entrar</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="rounded-lg border border-dashed border-amber-900/50 bg-amber-100/50 p-3">
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
                <form method="POST" action="/login" class="flex flex-col gap-6">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="email" class="text-amber-800">E-mail</Label>
                            <Input id="email" type="email" name="email" v-model="email" required autofocus :tabindex="1"
                                class="border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500 focus:ring-amber-500"
                                placeholder="email@exemplo.com" />
                            <InputError :message="$page.props.errors?.email" />
                        </div>
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-amber-800">Senha</Label>
                                <a :href="request()" class="text-sm text-amber-600 hover:text-amber-800">Esqueceu sua senha?</a>
                            </div>
                            <PasswordInput id="password" name="password" v-model="password" required :tabindex="2" placeholder="Senha" />
                            <InputError :message="$page.props.errors?.password" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3 text-amber-700">
                                <Checkbox id="remember" name="remember" :tabindex="3" class="border-amber-900" />
                                <span>Lembrar de mim</span>
                            </Label>
                        </div>
                        <Button type="submit" class="mt-2 w-full bg-amber-500 font-semibold text-amber-950 hover:bg-amber-910" :tabindex="4">
                            Entrar como Vendedor
                        </Button>
                    </div>
                    <div class="text-center text-sm text-amber-600">
                        Não tem uma conta? <a href="/register" class="font-medium text-amber-700 hover:text-amber-900">Cadastre-se</a>
                    </div>
                    <div class="space-y-1 text-center text-xs text-amber-600">
                        <div><Link :href="'/login_cli'" class="font-medium text-amber-700 hover:text-amber-900">Acesse como Cliente</Link></div>
                        <div><Link :href="'/login_carrier'" class="font-medium text-amber-700 hover:text-amber-900">Acesse como Transportadora</Link></div>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>