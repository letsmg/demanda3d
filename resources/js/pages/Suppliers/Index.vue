<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{ suppliers: any }>();

const deleteSupplier = (id: number) => {
    if (confirm('Tem certeza que deseja excluir este fornecedor?')) {
        router.delete(`/suppliers/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Fornecedores" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Fornecedores
                </h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seus fornecedores de insumos
                </p>
            </div>
            <Button as-child>
                <Link href="/suppliers/create"
                    ><Plus class="mr-2 h-4 w-4" />Novo Fornecedor</Link
                >
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Todos os Fornecedores</CardTitle>
                <CardDescription
                    >{{
                        suppliers.meta?.total || suppliers.data?.length || 0
                    }}
                    fornecedores cadastrados</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Nome</th>
                                <th
                                    class="hidden px-4 py-3 font-medium md:table-cell"
                                >
                                    Documento
                                </th>
                                <th
                                    class="hidden px-4 py-3 font-medium lg:table-cell"
                                >
                                    Contato
                                </th>
                                <th
                                    class="hidden px-4 py-3 font-medium lg:table-cell"
                                >
                                    E-mail
                                </th>
                                <th
                                    class="hidden px-4 py-3 font-medium xl:table-cell"
                                >
                                    Cidade/UF
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="supplier in Array.isArray(suppliers)
                                    ? suppliers
                                    : suppliers.data"
                                :key="supplier.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ supplier.name }}
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-muted-foreground md:table-cell"
                                >
                                    {{ supplier.document || '-' }}
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-muted-foreground lg:table-cell"
                                >
                                    {{ supplier.contact || '-' }}
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-muted-foreground lg:table-cell"
                                >
                                    <a
                                        v-if="supplier.email"
                                        :href="'mailto:' + supplier.email"
                                        class="text-blue-600 hover:underline"
                                        >{{ supplier.email }}</a
                                    >
                                    <span v-else>-</span>
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-muted-foreground xl:table-cell"
                                >
                                    {{
                                        supplier.city
                                            ? supplier.city +
                                              '/' +
                                              supplier.state
                                            : '-'
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right whitespace-nowrap"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="`/suppliers/${supplier.id}/edit`"
                                            ><Pencil class="h-4 w-4"
                                        /></Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteSupplier(supplier.id)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !(
                                        Array.isArray(suppliers)
                                            ? suppliers
                                            : suppliers.data
                                    )?.length
                                "
                            >
                                <td
                                    colspan="6"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    Nenhum fornecedor encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
