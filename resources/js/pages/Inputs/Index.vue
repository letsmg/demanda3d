<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Box,
    Plus,
    Edit,
    Trash2,
    DollarSign,
    Gauge,
    Search,
} from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { create as inputsCreate, edit as inputsEdit } from '@/routes/inputs';
import type { Input as InputType } from '@/types';

type PaginatedInputs = {
    data: InputType[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
};

const { inputs } = defineProps<{
    inputs: PaginatedInputs;
}>();

const searchQuery = ref('');
const showDeleteDialog = ref(false);
const deletingInput = ref<InputType | null>(null);
const deleteForm = useForm({});
let searchTimeout: ReturnType<typeof setTimeout>;

const onSearchInput = () => {
    clearTimeout(searchTimeout);

    if (searchQuery.value.length === 0) {
        router.get('/inputs', {}, { preserveState: true, replace: true });
        return;
    }

    if (searchQuery.value.length < 3) {
        return;
    }

    searchTimeout = setTimeout(() => {
        router.get(
            '/inputs',
            { search: searchQuery.value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);
};

const goToPage = (pageNumber: number) => {
    router.get(
        '/inputs',
        { page: pageNumber, search: searchQuery.value || undefined },
        { preserveState: true, replace: true },
    );
};

const confirmDelete = (input: InputType) => {
    deletingInput.value = input;
    showDeleteDialog.value = true;
};

const executeDelete = () => {
    if (!deletingInput.value) {
        return;
    }

    deleteForm.delete(`/inputs/${deletingInput.value.id}`, {
        preserveState: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingInput.value = null;
        },
    });
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);

const formatDate = (dateStr: string) =>
    new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');
</script>

<template>
    <Head title="Insumos" />
    <div class="space-y-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Insumos
                </h1>
                <p class="text-sm text-muted-foreground">
                    Gerenciar filamentos, materiais e recursos ({{
                        inputs.total
                    }}
                    total)
                </p>
            </div>
            <Button as-child>
                <Link :href="inputsCreate()"
                    ><Plus class="mr-2 h-4 w-4" /> Novo Insumo</Link
                >
            </Button>
        </div>

        <div class="relative">
            <Search
                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="searchQuery"
                placeholder="Buscar por filamento (mín. 3 letras)..."
                class="pl-10"
                @input="onSearchInput"
            />
        </div>

        <div
            v-if="inputs.data.length === 0"
            class="flex flex-col items-center justify-center py-16 text-center"
        >
            <Box class="mb-4 h-12 w-12 text-muted-foreground/50" />
            <h3 class="mb-2 text-lg font-semibold">Nenhum insumo encontrado</h3>
            <p class="mb-6 text-sm text-muted-foreground">
                Registre seu primeiro filamento ou material.
            </p>
            <Button as-child
                ><Link :href="inputsCreate()"
                    ><Plus class="mr-2 h-4 w-4" /> Criar Insumo</Link
                ></Button
            >
        </div>

        <template v-else>
            <div class="hidden overflow-hidden rounded-xl border md:block">
                <table class="w-full">
                    <thead>
                        <tr
                            class="border-b bg-muted/50 text-left text-sm font-medium text-muted-foreground"
                        >
                            <th class="px-6 py-4">Filamento</th>
                            <th class="px-6 py-4">Marca</th>
                            <th class="px-6 py-4">Data Compra</th>
                            <th class="px-6 py-4">Custo</th>
                            <th class="px-6 py-4">Qtd (g)</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="input in inputs.data"
                            :key="input.id"
                            class="border-b transition-colors last:border-0 hover:bg-muted/30"
                        >
                            <td class="px-6 py-4 font-medium">
                                {{ input.description }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ input.brand }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ formatDate(input.purchase_date) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ formatCurrency(Number(input.cost_value)) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ input.quantity }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                inputsEdit({ input: input.id })
                                            "
                                            ><Edit class="h-3 w-3"
                                        /></Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-destructive hover:bg-destructive/10"
                                        @click="confirmDelete(input)"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 md:hidden">
                <Card
                    v-for="input in inputs.data"
                    :key="input.id"
                    class="border-border/50"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <CardTitle class="text-base">{{
                                input.description
                            }}</CardTitle>
                            <Badge variant="secondary" class="shrink-0">{{
                                formatDate(input.purchase_date)
                            }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 font-medium">
                                <DollarSign
                                    class="h-3.5 w-3.5 text-green-600"
                                />
                                <span>{{
                                    formatCurrency(Number(input.cost_value))
                                }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <Gauge class="h-3.5 w-3.5" />
                                <span
                                    >Qtd: {{ input.quantity }}g | Frete:
                                    {{
                                        formatCurrency(
                                            Number(input.shipping_cost),
                                        )
                                    }}</span
                                >
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="flex-1"
                                as-child
                            >
                                <Link :href="inputsEdit({ input: input.id })"
                                    ><Edit class="mr-1 h-3 w-3" /> Editar</Link
                                >
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="flex-1 text-destructive"
                                @click="confirmDelete(input)"
                            >
                                <Trash2 class="mr-1 h-3 w-3" /> Excluir
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="inputs.last_page > 1"
                class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Mostrando {{ inputs.from }} a {{ inputs.to }} de
                    {{ inputs.total }}
                </p>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="inputs.current_page === 1"
                        @click="goToPage(inputs.current_page - 1)"
                        >Anterior</Button
                    >
                    <span class="flex items-center px-4 text-sm"
                        >Página {{ inputs.current_page }} de
                        {{ inputs.last_page }}</span
                    >
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="inputs.current_page === inputs.last_page"
                        @click="goToPage(inputs.current_page + 1)"
                        >Próxima</Button
                    >
                </div>
            </div>
        </template>
    </div>

    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Excluir Insumo</DialogTitle>
                <DialogDescription
                    >Tem certeza que deseja excluir
                    <strong>{{ deletingInput?.description }}</strong
                    >? Esta ação não pode ser desfeita.</DialogDescription
                >
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false"
                    >Cancelar</Button
                >
                <Button variant="destructive" @click="executeDelete"
                    >Excluir</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
