<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({
    layout: WelcomeLayout,
});

defineProps<{
    document: {
        type: string;
        title: string;
        content: string;
        version: number;
        published_at: string;
    };
}>();
</script>

<template>
    <Head :title="document.title" />

    <div class="min-h-screen bg-amber-50">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6">
                <Button variant="outline" size="sm" as-child>
                    <Link href="/">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Voltar
                    </Link>
                </Button>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm border border-amber-100 sm:p-8">
                <div class="mb-4 text-sm text-amber-500">
                    Versão {{ document.version }} — Atualizado em {{ document.published_at }}
                </div>
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div class="legal-content text-amber-900" v-html="document.content" />
            </div>
        </div>
    </div>
</template>

<style>
.legal-content {
    line-height: 1.8;
}
.legal-content h2 {
    font-size: 1.2rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 0.5rem;
    color: #92400e;
}
.legal-content ul {
    list-style: disc;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}
.legal-content li {
    margin-bottom: 0.3rem;
}
</style>