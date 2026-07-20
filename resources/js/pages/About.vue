<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ShoppingBag,
    Store,
    Package,
    TrendingUp,
    Shield,
    Users,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });
</script>

<template>
    <Head title="Como Funciona — Demanda3D" />

    <div class="min-h-screen bg-amber-50">
        <!-- Hero -->
        <section class="bg-amber-950 py-16">
            <div class="container mx-auto px-4 text-center md:px-8">
                <h1 class="text-3xl font-bold text-amber-100 md:text-4xl">
                    Como Funciona o Demanda3D
                </h1>
                <p class="mt-4 text-lg text-amber-300/80">
                    Marketplace integrado com ERP para vendedores e clientes de
                    produtos personalizados
                </p>
            </div>
        </section>

        <!-- O que é -->
        <section class="py-16">
            <div class="container mx-auto max-w-4xl px-4 md:px-8">
                <h2 class="mb-6 text-2xl font-bold text-amber-900">
                    O que é o Demanda3D?
                </h2>
                <p class="mb-4 leading-relaxed text-amber-700">
                    O <strong class="text-amber-900">Demanda3D</strong> é um
                    <strong>marketplace integrado com ERP</strong> onde
                    múltiplos vendedores podem se cadastrar de forma
                    independente, gerenciar seus fornecedores, insumos,
                    produtos, pedidos e clientes — tudo em um único sistema.
                </p>
                <p class="leading-relaxed text-amber-700">
                    Embora o foco inicial seja impressão 3D, a plataforma aceita
                    <strong>qualquer tipo de produto</strong> permitido pela
                    legislação brasileira, desde que respeite rigorosamente os
                    <Link
                        href="/legal/terms"
                        class="text-amber-600 underline hover:text-amber-800"
                        >Termos de Uso</Link
                    >
                    e a
                    <Link
                        href="/legal/privacy"
                        class="text-amber-600 underline hover:text-amber-800"
                        >Política de Privacidade</Link
                    >.
                </p>
            </div>
        </section>

        <!-- Planos -->
        <section class="bg-white py-16">
            <div class="container mx-auto max-w-4xl px-4 md:px-8">
                <h2 class="mb-8 text-center text-2xl font-bold text-amber-900">
                    Planos e Preços
                </h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Plano Básico -->
                    <Card class="border-amber-300 bg-amber-50/50">
                        <CardHeader>
                            <CardTitle class="text-xl text-amber-900"
                                >Plano Básico</CardTitle
                            >
                            <CardDescription class="text-amber-600"
                                >Ideal para pequenos vendedores</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <p class="text-3xl font-bold text-amber-900">
                                R$ 100<small
                                    class="text-lg font-normal text-amber-600"
                                    >/mês</small
                                >
                            </p>
                            <ul class="space-y-2 text-sm text-amber-700">
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span> Até
                                    <strong>15 produtos</strong> cadastrados
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Gerenciamento de clientes e pedidos
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Cadastro de fornecedores
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Controle básico de insumos
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Vitrine na loja pública
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    <!-- Plano Avançado -->
                    <Card class="border-amber-400 bg-amber-50/80">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <CardTitle class="text-xl text-amber-900"
                                    >Plano Avançado</CardTitle
                                >
                                <span
                                    class="rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-amber-950"
                                    >Recomendado</span
                                >
                            </div>
                            <CardDescription class="text-amber-600"
                                >Para vendedores que precisam de
                                mais</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <p class="text-3xl font-bold text-amber-900">
                                R$ 200<small
                                    class="text-lg font-normal text-amber-600"
                                    >/mês</small
                                >
                            </p>
                            <ul class="space-y-2 text-sm text-amber-700">
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span> Até
                                    <strong>30 produtos</strong> cadastrados
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    <strong>Tudo do plano Básico</strong>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Gerenciamento completo de insumos
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Relatórios financeiros
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    Dashboard avançado com métricas
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>

                <div
                    class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-center"
                >
                    <p class="text-sm text-amber-700">
                        <strong>Importante:</strong> Os valores e condições dos
                        planos podem ser alterados a qualquer momento sem aviso
                        prévio. Quaisquer alterações passarão a valer apenas a
                        partir do mês seguinte ao comunicado.
                    </p>
                </div>
            </div>
        </section>

        <!-- Para quem é -->
        <section class="py-16">
            <div class="container mx-auto max-w-4xl px-4 md:px-8">
                <h2 class="mb-8 text-center text-2xl font-bold text-amber-900">
                    Para quem é o Demanda3D?
                </h2>
                <div class="grid gap-6 md:grid-cols-3">
                    <Card class="border-amber-200">
                        <CardHeader>
                            <Store class="mb-2 h-8 w-8 text-amber-600" />
                            <CardTitle class="text-lg text-amber-900"
                                >Vendedores</CardTitle
                            >
                            <CardDescription class="text-amber-600"
                                >Impressão 3D, artesanato, produtos
                                personalizados ou qualquer produto
                                legal</CardDescription
                            >
                        </CardHeader>
                    </Card>
                    <Card class="border-amber-200">
                        <CardHeader>
                            <ShoppingBag class="mb-2 h-8 w-8 text-amber-600" />
                            <CardTitle class="text-lg text-amber-900"
                                >Clientes</CardTitle
                            >
                            <CardDescription class="text-amber-600"
                                >Compre produtos exclusivos de múltiplos
                                vendedores em um só lugar</CardDescription
                            >
                        </CardHeader>
                    </Card>
                    <Card class="border-amber-200">
                        <CardHeader>
                            <TrendingUp class="mb-2 h-8 w-8 text-amber-600" />
                            <CardTitle class="text-lg text-amber-900"
                                >Empreendedores</CardTitle
                            >
                            <CardDescription class="text-amber-600"
                                >Gerencie seu negócio com ERP completo integrado
                                ao marketplace</CardDescription
                            >
                        </CardHeader>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-amber-950 py-16">
            <div class="container mx-auto px-4 text-center md:px-8">
                <h2 class="mb-4 text-3xl font-bold text-amber-100">
                    Pronto para começar?
                </h2>
                <p class="mb-8 text-lg text-amber-300/80">
                    Cadastre-se agora e comece a vender no maior marketplace de
                    produtos 3D do Brasil
                </p>
                <div class="flex justify-center gap-4">
                    <Button
                        size="lg"
                        as-child
                        class="bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400"
                    >
                        <Link href="/register">Criar Conta de Vendedor</Link>
                    </Button>
                    <Button
                        size="lg"
                        variant="outline"
                        as-child
                        class="border-amber-400 text-amber-100 hover:bg-amber-800"
                    >
                        <Link href="/store">Ver Loja</Link>
                    </Button>
                </div>
            </div>
        </section>
    </div>
</template>
