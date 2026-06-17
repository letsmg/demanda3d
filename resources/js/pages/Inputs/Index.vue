<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Input as InputUI } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Box, Plus, Search, Edit, Trash2, Calendar, DollarSign, Gauge } from '@lucide/vue';
import type { Input } from '@/types';

type PaginatedData = {
    data: Input[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
};

const inputs = ref<PaginatedData | null>(null);
const loading = ref(true);
const page = ref(1);

const showDeleteDialog = ref(false);
const deletingInput = ref<Input | null>(null);
const deleting = ref(false);

const fetchInputs = async (pageNumber: number = 1) => {
    loading.value = true;
    try {
        const params = new URLSearchParams({ page: pageNumber.toString(), per_page: '10' });
        const response = await fetch(`/api/inputs?${params}`);
        const data = await response.json();
        inputs.value = data;
    } catch (error) {
        console.error('Error fetching inputs:', error);
    } finally {
        loading.value = false;
    }
};

const confirmDelete = (input: Input) => {
    deletingInput.value = input;
    showDeleteDialog.value = true;
};

const executeDelete = async () => {
    if (!deletingInput.value) return;
    deleting.value = true;
    try {
        await fetch(`/api/inputs/${deletingInput.value.id}`, { method: 'DELETE' });
        showDeleteDialog.value = false;
        deletingInput.value = null;
        fetchInputs(page.value);
    } catch (error) {
        console.error('Error deleting input:', error);
    } finally {
        deleting.value = false;
    }
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

const formatDate = (dateStr: string) =>
    new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');

onMounted(() => fetchInputs());
</script>

<template>
    <Head title="Inputs" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Inputs</h1>
                <p class="text-sm text-muted-foreground">Manage filaments, materials and resources</p>
            </div>
            <Button as-child>
                <Link :href="route('inputs.create')">
                    <Plus class="mr-2 h-4 w-4" /> New Input
                </Link>
            </Button>
        </div>

        <template v-if="loading && !inputs">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Skeleton v-for="i in 6" :key="i" class="h-40 rounded-xl" />
            </div>
        </template>

        <div v-else-if="inputs && inputs.data.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <Box class="mb-4 h-12 w-12 text-muted-foreground/50" />
            <h3 class="mb-2 text-lg font-semibold">No inputs found</h3>
            <p class="mb-6 text-sm text-muted-foreground">Register your first filament or material input.</p>
            <Button as-child>
                <Link :href="route('inputs.create')"><Plus class="mr-2 h-4 w-4" /> Create Input</Link>
            </Button>
        </div>

        <template v-else-if="inputs">
            <div class="hidden overflow-hidden rounded-xl border md:block">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-left text-sm font-medium text-muted-foreground">
                            <th class="px-6 py-4">Filament</th>
                            <th class="px-6 py-4">Purchase Date</th>
                            <th class="px-6 py-4">Cost</th>
                            <th class="px-6 py-4">Energy</th>
                            <th class="px-6 py-4">Purge</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="input in inputs.data" :key="input.id" class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ input.filaments }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatDate(input.dt_buy) }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ formatCurrency(Number(input.cost_buy)) }}</td>
                            <td class="px-6 py-4 text-sm">{{ formatCurrency(Number(input.energy)) }}</td>
                            <td class="px-6 py-4 text-sm">{{ Number(input.purge).toFixed(1) }}g</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="route('inputs.edit', { input: input.id })"><Edit class="h-3 w-3" /></Link>
                                    </Button>
                                    <Button variant="outline" size="sm" class="text-destructive hover:bg-destructive/10" @click="confirmDelete(input)">
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 md:hidden">
                <Card v-for="input in inputs.data" :key="input.id" class="border-border/50">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <CardTitle class="text-base">{{ input.filaments }}</CardTitle>
                            <Badge variant="secondary" class="shrink-0">{{ formatDate(input.dt_buy) }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 font-medium">
                                <DollarSign class="h-3.5 w-3.5 text-green-600" />
                                <span>{{ formatCurrency(Number(input.cost_buy)) }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <Gauge class="h-3.5 w-3.5" />
                                <span>Energy: {{ formatCurrency(Number(input.energy)) }} | Purge: {{ Number(input.purge).toFixed(1) }}g</span>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Button variant="outline" size="sm" class="flex-1" as-child>
                                <Link :href="route('inputs.edit', { input: input.id })"><Edit class="mr-1 h-3 w-3" /> Edit</Link>
                            </Button>
                            <Button variant="outline" size="sm" class="flex-1 text-destructive hover:bg-destructive/10" @click="confirmDelete(input)">
                                <Trash2 class="mr-1 h-3 w-3" /> Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="inputs.last_page > 1" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                <p class="text-sm text-muted-foreground">Showing {{ inputs.from }} to {{ inputs.to }} of {{ inputs.total }}</p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="inputs.current_page === 1" @click="page--; fetchInputs(page)">Previous</Button>
                    <span class="flex items-center px-4 text-sm">Page {{ inputs.current_page }} of {{ inputs.last_page }}</span>
                    <Button variant="outline" size="sm" :disabled="inputs.current_page === inputs.last_page" @click="page++; fetchInputs(page)">Next</Button>
                </div>
            </div>
        </template>
    </div>

    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Input</DialogTitle>
                <DialogDescription>Are you sure you want to delete <strong>{{ deletingInput?.filaments }}</strong>? This action cannot be undone.</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false" :disabled="deleting">Cancel</Button>
                <Button variant="destructive" @click="executeDelete" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>