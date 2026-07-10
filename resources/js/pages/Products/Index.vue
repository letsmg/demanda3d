<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Search } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';

defineProps<{
    products: any;
}>();

const search = ref('');
let searchTimeout: ReturnType<typeof setTimeout>;

const doSearch = (value: string) => {
    if (value.length >= 3) {
        router.get(
            '/products',
            { search: value },
            {
                preserveState: true,
                replace: true,
                only: ['products'],
            },
        );
    } else if (value.length === 0) {
        router.get(
            '/products',
            {},
            {
                preserveState: true,
                replace: true,
                only: ['products'],
            },
        );
    }
};

const onSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        doSearch(search.value);
    }, 300);
};

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
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Produtos
                </h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seus produtos para a vitrine
                </p>
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
                <CardDescription
                    >{{
                        products.meta?.total || products.data?.length || 0
                    }}
                    produtos cadastrados</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="relative mb-4 max-w-sm">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Buscar produtos (mín. 3 letras)..."
                        class="pl-10"
                        @input="onSearchInput"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Nome</th>
                                <th class="px-4 py-3 font-medium">Preço</th>
                                <th class="px-4 py-3 font-medium">Ativo</th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in Array.isArray(products)
                                    ? products
                                    : products.data"
                                :key="product.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ product.name }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatPrice(product.sale_price) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="
                                            product.is_active
                                                ? 'text-green-600'
                                                : 'text-red-500'
                                        "
                                    >
                                        {{ product.is_active ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="`/products/${product.id}/edit`"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteProduct(product.id)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !(
                                        Array.isArray(products)
                                            ? products
                                            : products.data
                                    )?.length
                                "
                            >
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-muted-foreground"
                                >
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
