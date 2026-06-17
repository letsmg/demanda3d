<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { create as clientsCreate, edit as clientsEdit } from '@/routes/clients';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Users, Plus, Search, Edit, Trash2, Phone, MapPin, ChevronLeft, ChevronRight } from '@lucide/vue';
import type { Client } from '@/types';

type PaginatedData = {
    data: Client[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

const clients = ref<PaginatedData | null>(null);
const loading = ref(true);
const searchQuery = ref('');
const page = ref(1);

// Delete dialog
const showDeleteDialog = ref(false);
const deletingClient = ref<Client | null>(null);
const deleting = ref(false);

const fetchClients = async (pageNumber: number = 1, search: string = '') => {
    loading.value = true;
    try {
        const params = new URLSearchParams({ page: pageNumber.toString(), per_page: '10' });
        if (search) params.append('search', search);
        const response = await fetch(`/api/clients?${params}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) {
            clients.value = { data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
            return;
        }
        const data = await response.json();
        clients.value = data;
    } catch (error) {
        console.error('Error fetching clients:', error);
        clients.value = { data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
    } finally {
        loading.value = false;
    }
};

const confirmDelete = (client: Client) => {
    deletingClient.value = client;
    showDeleteDialog.value = true;
};

const executeDelete = async () => {
    if (!deletingClient.value) return;
    deleting.value = true;
    try {
        await fetch(`/api/clients/${deletingClient.value.id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        showDeleteDialog.value = false;
        deletingClient.value = null;
        fetchClients(page.value, searchQuery.value);
    } catch (error) {
        console.error('Error deleting client:', error);
    } finally {
        deleting.value = false;
    }
};

const goToPage = (pageNumber: number) => {
    page.value = pageNumber;
    fetchClients(pageNumber, searchQuery.value);
};

const formatDoc = (doc: string) => {
    if (!doc) return '-';
    if (doc.length === 14) return doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    if (doc.length === 11) return doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    return doc;
};

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
const onSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        page.value = 1;
        fetchClients(1, searchQuery.value);
    }, 400);
};

const paginationRange = computed(() => {
    if (!clients.value) return [];
    const range: (number | '...')[] = [];
    const total = clients.value.last_page;
    const current = clients.value.current_page;
    
    if (total <= 7) {
        for (let i = 1; i <= total; i++) range.push(i);
    } else {
        range.push(1);
        if (current > 3) range.push('...');
        for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
            range.push(i);
        }
        if (current < total - 2) range.push('...');
        range.push(total);
    }
    return range;
});

onMounted(() => {
    fetchClients();
});
</script>

<template>
    <Head title="Clients" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Clientes</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seus clientes de impressão 3D
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
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
                v-model="searchQuery"
                placeholder="Buscar por nome, documento, cidade ou telefone..."
                class="pl-10"
                @input="onSearchInput"
            />
        </div>

        <!-- Loading State -->
        <template v-if="loading && !clients">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Skeleton v-for="i in 6" :key="i" class="h-40 rounded-xl" />
            </div>
        </template>

        <!-- Empty State -->
        <div v-else-if="clients && clients.data.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <Users class="mb-4 h-12 w-12 text-muted-foreground/50" />
            <h3 class="mb-2 text-lg font-semibold">Nenhum cliente encontrado</h3>
            <p class="mb-6 text-sm text-muted-foreground">
                {{ searchQuery ? 'Nenhum cliente corresponde à sua busca.' : 'Comece cadastrando seu primeiro cliente.' }}
            </p>
            <Button v-if="!searchQuery" as-child>
                <Link :href="clientsCreate()">
                    <Plus class="mr-2 h-4 w-4" />
                    Criar Cliente
                </Link>
            </Button>
        </div>

        <!-- Clients Grid -->
        <template v-else-if="clients">
            <!-- Desktop Table -->
            <div class="hidden overflow-hidden rounded-xl border md:block">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-left text-sm font-medium text-muted-foreground">
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
                            class="border-b last:border-0 hover:bg-muted/30 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ client.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ formatDoc(client.doc) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-sm">
                                    <Phone class="h-3 w-3 text-muted-foreground" />
                                    {{ client.phone1 }}
                                </div>
                                <div v-if="client.contact1" class="text-xs text-muted-foreground mt-0.5">
                                    Contato: {{ client.contact1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-sm">
                                    <MapPin class="h-3 w-3 text-muted-foreground" />
                                    {{ client.city }}, {{ client.state }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="clientsEdit({ client: client.id })">
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
                                <CardTitle class="text-base">{{ client.name }}</CardTitle>
                                <CardDescription>{{ formatDoc(client.doc) }}</CardDescription>
                            </div>
                            <Badge variant="secondary" class="shrink-0">
                                {{ client.state }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <Phone class="h-3.5 w-3.5" />
                                <span>{{ client.phone1 }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <MapPin class="h-3.5 w-3.5" />
                                <span>{{ client.city }}, {{ client.state }} - {{ client.zipcode }}</span>
                            </div>
                            <div v-if="client.contact1" class="text-xs text-muted-foreground">
                                Contact: {{ client.contact1 }}
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="flex-1"
                                as-child
                            >
                                <Link :href="clientsEdit({ client: client.id })">
                                    <Edit class="mr-1 h-3 w-3" />
                                    Edit
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="flex-1 text-destructive hover:bg-destructive/10"
                                @click="confirmDelete(client)"
                            >
                                <Trash2 class="mr-1 h-3 w-3" />
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div v-if="clients.last_page > 1" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} clients
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
                    <template v-for="p in paginationRange" :key="p">
                        <span v-if="p === '...'" class="px-2 text-muted-foreground">...</span>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            :class="p === clients.current_page ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                            @click="goToPage(p as number)"
                        >
                            {{ p }}
                        </Button>
                    </template>
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

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Excluir Cliente</DialogTitle>
                <DialogDescription>
                    Tem certeza que deseja excluir <strong>{{ deletingClient?.name }}</strong>?
                    Esta ação não pode ser desfeita. Todos os pedidos associados a este cliente também serão excluídos.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="showDeleteDialog = false"
                    :disabled="deleting"
                >
                    Cancelar
                </Button>
                <Button
                    variant="destructive"
                    @click="executeDelete"
                    :disabled="deleting"
                >
                    {{ deleting ? 'Excluindo...' : 'Excluir' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>