<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ref, watch } from 'vue';

const props = defineProps<{
    products: any;
}>();

const search = ref('');

watch(search, (value) => {
    router.get('/products', { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const deleteProduct = (id: number) => {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        router.delete(`/products/${id}`, {
            preserveScroll: true,
        });
    }
};

const formatPrice = (value: string | number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
};
</script>

<template>
    <Head title="Produtos" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Produtos</h1>
                <p class="text-sm text-muted-foreground">Gerencie seus produtos para a vitrine</p>
            </div>
            <Button as-child>
                <Link :href="`/products/create`">
                    <Plus class="mr-2 h-4 w-4" />
                    Novo Produto
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Todos os Produtos</CardTitle>
                <CardDescription>{{ products.meta?.total || products.data?.length || 0 }} produtos cadastrados</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="mb-4">
                    <Input
                        v-model="search"
                        placeholder="Buscar produtos..."
                        class="max-w-sm"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-3 px-4 font-medium">Nome</th>
                                <th class="py-3 px-4 font-medium">Preço</th>
                                <th class="py-3 px-4 font-medium">Desconto</th>
                                <th class="py-3 px-4 font-medium">Ativo</th>
                                <th class="py-3 px-4 font-medium text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products.data" :key="product.id" class="border-b hover:bg-muted/50">
                                <td class="py-3 px-4 font-medium">{{ product.name }}</td>
                                <td class="py-3 px-4">{{ formatPrice(product.price_sale) }}</td>
                                <td class="py-3 px-4">{{ product.discount_cash }}%</td>
                                <td class="py-3 px-4">
                                    <span :class="product.is_active ? 'text-green-600' : 'text-red-500'">
                                        {{ product.is_active ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="`/products/${product.id}/edit`">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteProduct(product.id)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="!products.data?.length">
                                <td colspan="5" class="py-8 text-center text-muted-foreground">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>