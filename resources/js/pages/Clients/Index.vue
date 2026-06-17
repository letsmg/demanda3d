<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { create as clientsCreate, edit as clientsEdit } from '@/routes/clients';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Users,
    Plus,
    Search,
    Edit,
    Trash2,
    Phone,
    MapPin,
    ChevronLeft,
    ChevronRight,
} from '@lucide/vue';
import type { Client } from '@/types';

type PaginatedClients = {
    data: Client[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

const props = defineProps<{
    clients: PaginatedClients;
    filters?: { search?: string };
}>();

const searchQuery = ref(props.filters?.search || '');
const showDeleteDialog = ref(false);
const deletingClient = ref<Client | null>(null);
const searchTimeout = ref<ReturnType<typeof setTimeout>>();

const onSearchInput = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        router.get(
            '/clients',
            { search: searchQuery.value || undefined, page: 1 },
            { preserveState: true, replace: true },
        );
    }, 400);
};

const goToPage = (pageNumber: number) => {
    router.get(
        '/clients',
        { page: pageNumber, search: searchQuery.value || undefined },
        { preserveState: true, replace: true },
    );
};

const confirmDelete = (client: Client) => {
    deletingClient.value = client;
    showDeleteDialog.value = true;
};

const deleteForm = useForm({});

const executeDelete = () => {
    if (!deletingClient.value) return;
    deleteForm.delete(`/clients/${deletingClient.value.id}`, {
        preserveState: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingClient.value = null;
        },
    });
};

const formatDoc = (doc: string) => {
    if (!doc) return '-';
    if (doc.length === 14)
        return doc.replace(
            /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
            '$1.$2.$3/$4-$5',
        );
    if (doc.length === 11)
        return doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    return doc;
};
</script>

<template>
    <Head title="Clientes" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Clientes
                </h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seus clientes de impressão 3D ({{ clients.total }}
                    total)
                </p>
            </div>
            <Button as-child>
                <Link :href="clientsCreate()">
                    <Plus class="mr-2 h-4 w-4" />
                    Novo Cliente
                </Link>
            </Button>
        </div>

        <!-- Search -->
        <div class="relative">
            <Search
                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="searchQuery"
                placeholder="Buscar por nome, documento, cidade ou telefone..."
                class="pl-10"
                @input="onSearchInput"
            />
        </div>

        <!-- Empty State -->
        <div
            v-if="clients.data.length === 0"
            class="flex flex-col items-center justify-center py-16 text-center"
        >
            <Users class="mb-4 h-12 w-12 text-muted-foreground/50" />
            <h3 class="mb-2 text-lg font-semibold">
                Nenhum cliente encontrado
            </h3>
            <p class="mb-6 text-sm text-muted-foreground">
                {{
                    searchQuery
                        ? 'Nenhum cliente corresponde à sua busca.'
                        : 'Comece cadastrando seu primeiro cliente.'
                }}
            </p>
            <Button v-if="!searchQuery" as-child>
                <Link :href="clientsCreate()">
                    <Plus class="mr-2 h-4 w-4" />
                    Criar Cliente
                </Link>
            </Button>
        </div>

        <!-- Clients List -->
        <template v-else>
            <!-- Desktop Table -->
            <div class="hidden overflow-hidden rounded-xl border md:block">
                <table class="w-full">
                    <thead>
                        <tr
                            class="border-b bg-muted/50 text-left text-sm font-medium text-muted-foreground"
                        >
                            <th class="px-6 py-4">Nome / Documento</th>
                            <th class="px-6 py-4">Contato</th>
                            <th class="px-6 py-4">Localização</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="client in clients.data"
                            :key="client.id"
                            class="border-b transition-colors last:border-0 hover:bg-muted/30"
                        >
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ client.name }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDoc(client.doc) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-sm">
                                    <Phone
                                        class="h-3 w-3 text-muted-foreground"
                                    />
                                    {{ client.phone1 }}
                                </div>
                                <div
                                    v-if="client.contact1"
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    Contato: {{ client.contact1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-sm">
                                    <MapPin
                                        class="h-3 w-3 text-muted-foreground"
                                    />
                                    {{ client.city }}, {{ client.state }}
                                </div>
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
                                                clientsEdit({
                                                    client: client.id,
                                                })
                                            "
                                        >
                                            <Edit class="h-3 w-3" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-destructive hover:bg-destructive/10"
                                        @click="confirmDelete(client)"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="grid gap-3 md:hidden">
                <Card
                    v-for="client in clients.data"
                    :key="client.id"
                    class="border-border/50"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-base">{{
                                    client.name
                                }}</CardTitle>
                                <CardDescription>{{
                                    formatDoc(client.doc)
                                }}</CardDescription>
                            </div>
                            <Badge variant="secondary" class="shrink-0">{{
                                client.state
                            }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <Phone class="h-3.5 w-3.5" />
                                <span>{{ client.phone1 }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <MapPin class="h-3.5 w-3.5" />
                                <span
                                    >{{ client.city }}, {{ client.state }} -
                                    {{ client.zipcode }}</span
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
                                <Link
                                    :href="clientsEdit({ client: client.id })"
                                >
                                    <Edit class="mr-1 h-3 w-3" /> Editar
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="flex-1 text-destructive"
                                @click="confirmDelete(client)"
                            >
                                <Trash2 class="mr-1 h-3 w-3" /> Excluir
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div
                v-if="clients.last_page > 1"
                class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Mostrando {{ clients.from }} a {{ clients.to }} de
                    {{ clients.total }} clientes
                </p>
                <div class="flex items-center gap-1">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="clients.current_page === 1"
                        @click="goToPage(clients.current_page - 1)"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <span class="flex items-center px-4 text-sm">
                        Página {{ clients.current_page }} de
                        {{ clients.last_page }}
                    </span>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="clients.current_page === clients.last_page"
                        @click="goToPage(clients.current_page + 1)"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </template>
    </div>

    <!-- Delete Dialog -->
    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Excluir Cliente</DialogTitle>
                <DialogDescription>
                    Tem certeza que deseja excluir
                    <strong>{{ deletingClient?.name }}</strong
                    >? Esta ação não pode ser desfeita.
                </DialogDescription>
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
