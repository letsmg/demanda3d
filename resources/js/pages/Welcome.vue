<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import { dashboard, login, register } from '@/routes';

defineOptions({
    layout: WelcomeLayout,
});

const page = usePage();
const auth = computed(() => page.props.auth);
const stats = computed(
    () =>
        page.props.stats as {
            clients_count: number;
            orders_count: number;
            inputs_count: number;
            monthly_revenue: number;
        } | null,
);

const features = [
    {
        title: 'Gestão de Clientes',
        description:
            'Cadastre e gerencie seus clientes com informações completas de contato, documentos e histórico.',
        icon: '👥',
        color: 'bg-blue-50 dark:bg-blue-950/30',
    },
    {
        title: 'Controle de Pedidos',
        description:
            'Acompanhe pedidos de impressão 3D desde a criação até a entrega com preços e especificações.',
        icon: '📦',
        color: 'bg-green-50 dark:bg-green-950/30',
    },
    {
        title: 'Controle de Insumos',
        description:
            'Gerencie filamentos, custos de energia e materiais para cálculo preciso de custos.',
        icon: '🧵',
        color: 'bg-purple-50 dark:bg-purple-950/30',
    },
    {
        title: 'Acesso Seguro',
        description:
            'Controle de acesso baseado em funções com níveis administrativo, equipe e cliente.',
        icon: '🔒',
        color: 'bg-amber-50 dark:bg-amber-950/30',
    },
    {
        title: 'Dashboard em Tempo Real',
        description:
            'Analytics visuais e KPIs para monitorar o desempenho do seu negócio de impressão 3D.',
        icon: '📊',
        color: 'bg-rose-50 dark:bg-rose-950/30',
    },
    {
        title: 'Design Responsivo',
        description:
            'Interface totalmente responsiva otimizada para desktop e dispositivos móveis.',
        icon: '📱',
        color: 'bg-cyan-50 dark:bg-cyan-950/30',
    },
];
</script>

<template>
    <Head title="Bem-vindo">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div
            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"
        />
        <div class="relative container mx-auto px-4 py-20 md:px-8 md:py-32">
            <div class="mx-auto max-w-3xl text-center">
                <Badge variant="secondary" class="mb-4 px-3 py-1 text-sm">
                    🚀 Sistema de Gestão de Impressão 3D
                </Badge>
                <h1 class="mb-6 text-4xl font-bold tracking-tight md:text-6xl">
                    Gerencie seu
                    <span
                        class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
                    >
                        Negócio de Impressão 3D
                    </span>
                </h1>
                <p class="mb-8 text-lg text-muted-foreground md:text-xl">
                    Solução completa para gerenciar clientes, pedidos e
                    materiais para sua manufatura de impressão 3D. Otimize seu
                    fluxo de trabalho do pedido à entrega.
                </p>
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <template v-if="auth?.user">
                        <Button size="lg" as-child>
                            <Link :href="dashboard.url()"
                                >Ir para o Painel</Link
                            >
                        </Button>
                    </template>
                    <template v-else>
                        <Button size="lg" as-child>
                            <Link :href="register.url()">Começar Agora</Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link :href="login.url()">Entrar</Link>
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section (only if logged in) -->
    <section v-if="stats" class="border-y border-border/40 bg-muted/30">
        <div class="container mx-auto px-4 py-12 md:px-8">
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">
                        {{ stats.clients_count }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Total de Clientes
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">
                        {{ stats.orders_count }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Total de Pedidos
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">
                        {{ stats.inputs_count }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Tipos de Insumos
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">
                        R$
                        {{
                            Number(stats.monthly_revenue).toLocaleString(
                                'pt-BR',
                                { minimumFractionDigits: 2 },
                            )
                        }}
                    </p>
                    <p class="text-sm text-muted-foreground">Receita Mensal</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20">
        <div class="container mx-auto px-4 md:px-8">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold">Tudo que Você Precisa</h2>
                <p class="text-lg text-muted-foreground">
                    Ferramentas completas para gerenciar seu negócio de
                    impressão 3D
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="feature in features"
                    :key="feature.title"
                    class="border-border/50 transition-all hover:border-primary/50 hover:shadow-md"
                >
                    <CardHeader>
                        <div
                            :class="[
                                'inline-flex h-12 w-12 items-center justify-center rounded-lg text-2xl',
                                feature.color,
                            ]"
                        >
                            {{ feature.icon }}
                        </div>
                        <CardTitle class="mt-4">{{ feature.title }}</CardTitle>
                        <CardDescription>{{
                            feature.description
                        }}</CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section
        class="bg-gradient-to-r from-blue-600 to-purple-600 py-16 text-white"
    >
        <div class="container mx-auto px-4 text-center md:px-8">
            <h2 class="mb-4 text-3xl font-bold">Pronto para Começar?</h2>
            <p class="mb-8 text-lg text-white/80">
                Junte-se a nós e otimize seu fluxo de trabalho de impressão 3D
            </p>
            <template v-if="auth?.user">
                <Button size="lg" variant="secondary" as-child>
                    <Link :href="dashboard.url()">Ir para o Painel</Link>
                </Button>
            </template>
            <template v-else>
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <Button size="lg" variant="secondary" as-child>
                        <Link :href="login.url()">Entrar</Link>
                    </Button>
                    <Button size="lg" variant="default" as-child>
                        <Link :href="register.url()">Criar Conta</Link>
                    </Button>
                </div>
            </template>
        </div>
    </section>
</template>
