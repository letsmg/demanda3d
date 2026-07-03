<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Wrench, RefreshCw, FileText, CheckCircle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    sitemap: {
        exists: boolean;
        last_generated: string;
        product_count: number;
    };
}>();

const form = useForm({});

const generateSitemap = () => {
    form.post('/tools/sitemap', { preserveScroll: true });
};
</script>

<template>
    <Head title="Ferramentas" />
    <div class="space-y-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                Ferramentas
            </h1>
            <p class="text-sm text-muted-foreground">
                Utilitários administrativos do sistema
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Sitemap Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <FileText class="h-6 w-6 text-amber-600" />
                        <div>
                            <CardTitle>Gerar Sitemap</CardTitle>
                            <CardDescription
                                >Atualiza o sitemap.xml com todas as páginas
                                públicas</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2 rounded-lg bg-muted/50 p-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Status:</span>
                            <span
                                :class="
                                    sitemap.exists
                                        ? 'text-green-600'
                                        : 'text-red-500'
                                "
                                class="flex items-center gap-1"
                            >
                                <CheckCircle class="h-4 w-4" />
                                {{ sitemap.exists ? 'Gerado' : 'Não gerado' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Última geração:</span
                            >
                            <span class="font-medium">{{
                                sitemap.last_generated
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Produtos no sitemap:</span
                            >
                            <span class="font-medium">{{
                                sitemap.product_count
                            }}</span>
                        </div>
                    </div>

                    <Button
                        class="w-full"
                        :disabled="form.processing"
                        @click="generateSitemap"
                    >
                        <RefreshCw
                            class="mr-2 h-4 w-4"
                            :class="{ 'animate-spin': form.processing }"
                        />
                        {{
                            form.processing
                                ? 'Gerando...'
                                : 'Gerar Sitemap Agora'
                        }}
                    </Button>

                    <p class="text-xs text-muted-foreground">
                        O sitemap é gerado com: página inicial, loja, todos os
                        produtos ativos e documentos legais. Para automação,
                        configure uma cron:
                        <code>php artisan sitemap:generate</code>
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
