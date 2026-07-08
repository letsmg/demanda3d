<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ShoppingBag } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import { dashboard, login, register } from '@/routes';

defineOptions({
    layout: WelcomeLayout,
});

const props = defineProps<{
    heroImages?: string[];
}>();

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

// Imagens do carrossel recebidas do WelcomeController
// Se não houver, usa fallback hardcoded
const heroImages = computed(() => {
    if (props.heroImages && props.heroImages.length > 0) {
        return props.heroImages;
    }
    // Fallback para quando acessado sem controller (ex: Route::inertia)
    return [
        '/storage/imgs/home/3.webp',
        '/storage/imgs/home/4.webp',
        '/storage/imgs/home/5.webp',
        '/storage/imgs/home/6.webp',
        '/storage/imgs/home/7.webp',
        '/storage/imgs/home/8.webp',
    ];
});

const currentImageIndex = ref(0);
let imageInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (heroImages.value.length > 0) {
        imageInterval = setInterval(() => {
            currentImageIndex.value =
                (currentImageIndex.value + 1) % heroImages.value.length;
        }, 4000);
    }
});

onUnmounted(() => {
    if (imageInterval) {
        clearInterval(imageInterval);
    }
});

const features = [
    {
        title: 'Gestão de Clientes',
        description:
            'Cadastre e gerencie seus clientes com informações completas de contato, documentos e histórico.',
        icon: '👥',
        color: 'bg-amber-50 dark:bg-amber-950/30',
        iconColor: 'text-amber-600',
    },
    {
        title: 'Controle de Pedidos',
        description:
            'Acompanhe pedidos de impressão 3D desde a criação até a entrega com preços e especificações.',
        icon: '📦',
        color: 'bg-amber-50 dark:bg-amber-950/30',
        iconColor: 'text-amber-600',
    },
    {
        title: 'Controle de Insumos',
        description:
            'Gerencie filamentos, custos de energia e materiais para cálculo preciso de custos.',
        icon: '🧵',
        color: 'bg-amber-50 dark:bg-amber-950/30',
        iconColor: 'text-amber-600',
    },
    {
        title: 'Acesso Seguro',
        description:
            'Controle de acesso baseado em funções com níveis administrativo, equipe e cliente.',
        icon: '🔒',
        color: 'bg-amber-50 dark:bg-amber-950/30',
        iconColor: 'text-amber-600',
    },
    {
        title: 'Dashboard em Tempo Real',
        description:
            'Analytics visuais e KPIs para monitorar o desempenho do seu negócio de impressão 3D.',
        icon: '📊',
        color: 'bg-amber-50 dark:bg-amber-950/30',
        iconColor: 'text-amber-600',
    },
    {
        title: 'Design Responsivo',
        description:
            'Interface totalmente responsiva otimizada para desktop e dispositivos móveis.',
        icon: '📱',
        color: 'bg-amber-50 dark:bg-amber-950/30',
        iconColor: 'text-amber-600',
    },
];
</script>

<template>
    <Head title="Bem-vindo">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <!-- Hero Section -->
    <section class="relative flex min-h-[600px] items-center overflow-hidden">
        <!-- Rotating background images with overlay -->
        <div
            v-for="(img, idx) in heroImages"
            :key="img"
            class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000"
            :style="{ backgroundImage: `url(${img})` }"
            :class="idx === currentImageIndex ? 'opacity-100' : 'opacity-0'"
        />
        <div class="absolute inset-0 bg-amber-950/80" />
        <div
            class="relative z-10 container mx-auto px-4 py-20 md:px-8 md:py-32"
        >
            <div class="mx-auto max-w-3xl text-center">
                <Badge
                    variant="secondary"
                    class="mb-4 border-amber-400/30 bg-amber-500/20 px-3 py-1 text-sm text-amber-200"
                >
                    🚀 Sistema de Gestão de Impressão 3D
                </Badge>
                <h1
                    class="mb-6 text-4xl font-bold tracking-tight text-white drop-shadow-lg md:text-6xl"
                >
                    Gerencie seu
                    <span
                        class="bg-gradient-to-r from-amber-300 to-amber-500 bg-clip-text text-transparent drop-shadow"
                    >
                        Negócio de Impressão 3D
                    </span>
                </h1>
                <p
                    class="mb-8 text-lg text-amber-100/90 drop-shadow md:text-xl"
                >
                    Solução completa para gerenciar clientes, pedidos e
                    materiais para sua manufatura de impressão 3D. Otimize seu
                    fluxo de trabalho do pedido à entrega.
                </p>
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <template v-if="auth?.user">
                        <Button
                            size="lg"
                            as-child
                            class="bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400"
                        >
                            <Link :href="dashboard.url()"
                                >Ir para o Painel</Link
                            >
                        </Button>
                    </template>
                    <template v-else>
                        <Button
                            size="lg"
                            as-child
                            class="bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400"
                        >
                            <Link :href="register.url()">Começar Agora</Link>
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            as-child
                            class="border-amber-400 text-amber-100 hover:bg-amber-800 hover:text-amber-50"
                        >
                            <Link :href="login.url()">Sou Parceiro</Link>
                        </Button>
                        <Button
                            size="lg"
                            variant="secondary"
                            as-child
                            class="bg-amber-700 text-amber-100 hover:bg-amber-600"
                        >
                            <Link :href="'/login_cli'">Sou Cliente</Link>
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            as-child
                            class="border-amber-300 text-amber-100 hover:bg-amber-800 hover:text-amber-50"
                        >
                            <Link :href="'/login_carrier'"
                                >Sou Transportadora</Link
                            >
                        </Button>
                    </template>
                </div>
                <!-- Image dots -->
                <div v-if="heroImages.length > 0" class="mt-8 flex justify-center gap-2">
                    <button
                        v-for="(img, idx) in heroImages"
                        :key="img"
                        class="h-2 w-2 rounded-full transition-all"
                        :class="
                            idx === currentImageIndex
                                ? 'w-6 bg-amber-400'
                                : 'bg-amber-600/50 hover:bg-amber-500/70'
                        "
                        @click="currentImageIndex = idx"
                    />
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section (only if logged in) -->
    <section v-if="stats" class="border-y border-amber-200 bg-amber-50/50">
        <div class="container mx-auto px-4 py-12 md:px-8">
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-amber-700">
                        {{ stats.clients_count }}
                    </p>
                    <p class="text-sm text-amber-600">Total de Clientes</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-amber-700">
                        {{ stats.orders_count }}
                    </p>
                    <p class="text-sm text-amber-600">Total de Pedidos</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-amber-700">
                        {{ stats.inputs_count }}
                    </p>
                    <p class="text-sm text-amber-600">Tipos de Insumos</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-amber-700">
                        R$
                        {{
                            Number(stats.monthly_revenue).toLocaleString(
                                'pt-BR',
                                { minimumFractionDigits: 2 },
                            )
                        }}
                    </p>
                    <p class="text-sm text-amber-600">Receita Mensal</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20">
        <div class="container mx-auto px-4 md:px-8">
            <div class="mb-12 text-center">
                <Button
                    variant="outline"
                    size="lg"
                    as-child
                    class="mb-8 border-amber-300 text-amber-700 hover:bg-amber-50"
                >
                    <Link href="/store">
                        <ShoppingBag class="mr-2 h-5 w-5" />
                        Ver Loja de Produtos
                    </Link>
                </Button>
                <h2 class="mb-4 text-3xl font-bold text-amber-900">
                    Tudo que Você Precisa
                </h2>
                <p class="text-lg text-amber-600">
                    Ferramentas completas para gerenciar seu negócio de
                    impressão 3D
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="feature in features"
                    :key="feature.title"
                    class="border-amber-200 transition-all hover:border-amber-400 hover:shadow-md"
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
                        <CardTitle class="mt-4 text-amber-900">{{
                            feature.title
                        }}</CardTitle>
                        <CardDescription class="text-amber-600">{{
                            feature.description
                        }}</CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-amber-950 py-16">
        <div class="container mx-auto px-4 text-center md:px-8">
            <h2 class="mb-4 text-3xl font-bold text-amber-100">
                Pronto para Começar?
            </h2>
            <p class="mb-8 text-lg text-amber-300/80">
                Junte-se a nós e otimize seu fluxo de trabalho de impressão 3D
            </p>
            <div
                class="flex flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <Button
                    size="lg"
                    variant="outline"
                    as-child
                    class="border-amber-400 text-amber-100 hover:bg-amber-800 hover:text-amber-50"
                >
                    <Link href="/store">
                        <ShoppingBag class="mr-2 h-5 w-5" />
                        Ver Loja de Produtos
                    </Link>
                </Button>
                <template v-if="auth?.user">
                    <Button
                        size="lg"
                        as-child
                        class="bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400"
                    >
                        <Link :href="dashboard.url()">Ir para o Painel</Link>
                    </Button>
                </template>
                <template v-else>
                    <Button
                        size="lg"
                        as-child
                        class="bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400"
                    >
                        <Link :href="login.url()">Sou Parceiro</Link>
                    </Button>
                    <Button
                        size="lg"
                        as-child
                        class="bg-amber-600 text-amber-50 hover:bg-amber-500"
                    >
                        <Link :href="'/login_cli'">Sou Cliente</Link>
                    </Button>
                </template>
            </div>
            <div class="mt-6 text-center">
                <p class="text-sm text-amber-400/60">
                    Já é cliente?
                    <Link
                        :href="'/login_cli'"
                        class="font-medium text-amber-300 underline underline-offset-4 hover:text-amber-200"
                    >
                        Acesse sua conta para comprar na loja
                    </Link>
                </p>
            </div>
        </div>
    </section>
</template>