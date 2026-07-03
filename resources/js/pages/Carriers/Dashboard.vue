<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        title: 'Painel da Transportadora',
        description: 'Gerencie seus fretes e contratos',
    },
});
</script>

<template>
    <Head title="Painel da Transportadora" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">
                Painel da Transportadora
            </h1>
            <p class="text-sm text-muted-foreground">
                Gerencie seus fretes, contratos e solicitações de vínculo com
                vendedores.
            </p>
        </div>

        <!-- Cards de resumo -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Fretes Ativos</p>
                <p class="text-2xl font-bold">0</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">
                    Solicitações Pendentes
                </p>
                <p class="text-2xl font-bold">0</p>
            </div>
            <div class="rounded-lg border p-4">
                <p class="text-sm text-muted-foreground">Avaliação Média</p>
                <p class="text-2xl font-bold">—</p>
            </div>
        </div>

        <!-- Seção de solicitações de vínculo -->
        <div class="rounded-lg border">
            <div class="border-b px-4 py-3">
                <h2 class="font-semibold">Solicitações de Vínculo</h2>
            </div>
            <div class="p-4">
                <p class="text-sm text-muted-foreground">
                    Nenhuma solicitação pendente no momento.
                </p>
            </div>
        </div>
    </div>
</template>
