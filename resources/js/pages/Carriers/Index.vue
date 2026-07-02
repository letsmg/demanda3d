<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{ carriers: any }>();

const deleteCarrier = (id: number) => {
    if (confirm('Tem certeza que deseja excluir esta transportadora?')) {
        router.delete(`/carriers/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Transportadoras" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Transportadoras</h1>
                <p class="text-sm text-muted-foreground">Gerencie suas transportadoras de frete</p>
            </div>
            <Button as-child><Link href="/carriers/create"><Plus class="mr-2 h-4 w-4" />Nova Transportadora</Link></Button>
        </div>
        <Card>
            <CardHeader>
                <CardTitle>Todas as Transportadoras</CardTitle>
                <CardDescription>{{ carriers.data?.length || 0 }} transportadoras cadastradas</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3 px-4 font-medium">Nome</th>
                                <th class="py-3 px-4 font-medium">Documento</th>
                                <th class="py-3 px-4 font-medium">Cidade/UF</th>
                                <th class="py-3 px-4 font-medium text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="carrier in carriers.data" :key="carrier.id" class="border-b hover:bg-muted/50">
                                <td class="py-3 px-4 font-medium">{{ carrier.name }}</td>
                                <td class="py-3 px-4 text-sm">{{ carrier.document }}</td>
                                <td class="py-3 px-4 text-sm">{{ carrier.city }}/{{ carrier.state }}</td>
                                <td class="py-3 px-4 text-right">
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="`/carriers/${carrier.id}/edit`"><Pencil class="h-4 w-4" /></Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteCarrier(carrier.id)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>