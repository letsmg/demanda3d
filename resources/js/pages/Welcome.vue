<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import { dashboard, login, register } from '@/routes';

defineOptions({
    layout: WelcomeLayout,
});

const page = usePage();
const auth = computed(() => page.props.auth);
const stats = computed(() => page.props.stats as {
    clients_count: number;
    orders_count: number;
    inputs_count: number;
    monthly_revenue: number;
} | null);

const features = [
    {
        title: 'Client Management',
        description: 'Register and manage your clients with complete contact information, documents, and history.',
        icon: '👥',
        color: 'bg-blue-50 dark:bg-blue-950/30',
    },
    {
        title: 'Order Tracking',
        description: 'Track 3D printing orders from creation to delivery with pricing and detailed specifications.',
        icon: '📦',
        color: 'bg-green-50 dark:bg-green-950/30',
    },
    {
        title: 'Input Control',
        description: 'Manage filaments, energy costs, and materials inventory for precise cost calculation.',
        icon: '🧵',
        color: 'bg-purple-50 dark:bg-purple-950/30',
    },
    {
        title: 'Secure Access',
        description: 'Role-based access control with admin, staff, and customer levels for data security.',
        icon: '🔒',
        color: 'bg-amber-50 dark:bg-amber-950/30',
    },
    {
        title: 'Real-time Dashboard',
        description: 'Visual analytics and KPIs to monitor your 3D printing business performance.',
        icon: '📊',
        color: 'bg-rose-50 dark:bg-rose-950/30',
    },
    {
        title: 'Responsive Design',
        description: 'Fully responsive interface optimized for desktop and mobile use.',
        icon: '📱',
        color: 'bg-cyan-50 dark:bg-cyan-950/30',
    },
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10" />
        <div class="container relative mx-auto px-4 py-20 md:px-8 md:py-32">
            <div class="mx-auto max-w-3xl text-center">
                <Badge variant="secondary" class="mb-4 px-3 py-1 text-sm">
                    🚀 3D Printing Management System
                </Badge>
                <h1 class="mb-6 text-4xl font-bold tracking-tight md:text-6xl">
                    Manage Your
                    <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        3D Printing
                    </span>
                    Business
                </h1>
                <p class="mb-8 text-lg text-muted-foreground md:text-xl">
                    Complete solution for managing clients, orders, and materials for your 3D printing manufacturing. Streamline your workflow from order to delivery.
                </p>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <template v-if="auth?.user">
                        <Button size="lg" as-child>
                            <Link :href="dashboard.url()">Go to Dashboard</Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button size="lg" as-child>
                            <Link :href="register.url()">Get Started</Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link :href="login.url()">Sign In</Link>
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
                    <p class="text-3xl font-bold text-primary">{{ stats.clients_count }}</p>
                    <p class="text-sm text-muted-foreground">Total Clients</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">{{ stats.orders_count }}</p>
                    <p class="text-sm text-muted-foreground">Total Orders</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">{{ stats.inputs_count }}</p>
                    <p class="text-sm text-muted-foreground">Input Types</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-primary">R$ {{ Number(stats.monthly_revenue).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</p>
                    <p class="text-sm text-muted-foreground">Monthly Revenue</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20">
        <div class="container mx-auto px-4 md:px-8">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold">Everything You Need</h2>
                <p class="text-lg text-muted-foreground">Complete tools to manage your 3D printing manufacturing business</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="feature in features"
                    :key="feature.title"
                    class="border-border/50 transition-all hover:border-primary/50 hover:shadow-md"
                >
                    <CardHeader>
                        <div :class="['inline-flex h-12 w-12 items-center justify-center rounded-lg text-2xl', feature.color]">
                            {{ feature.icon }}
                        </div>
                        <CardTitle class="mt-4">{{ feature.title }}</CardTitle>
                        <CardDescription>{{ feature.description }}</CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 py-16 text-white">
        <div class="container mx-auto px-4 text-center md:px-8">
            <h2 class="mb-4 text-3xl font-bold">Ready to Get Started?</h2>
            <p class="mb-8 text-lg text-white/80">Join us and streamline your 3D printing manufacturing workflow</p>
            <template v-if="auth?.user">
                <Button size="lg" variant="secondary" as-child>
                    <Link :href="dashboard.url()">Go to Dashboard</Link>
                </Button>
            </template>
            <template v-else>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Button size="lg" variant="secondary" as-child>
                        <Link :href="login.url()">Sign In</Link>
                    </Button>
                    <Button size="lg" variant="default" as-child>
                        <Link :href="register.url()">Create Account</Link>
                    </Button>
                </div>
            </template>
        </div>
    </section>
</template>