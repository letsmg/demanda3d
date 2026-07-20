<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Ban,
    CheckCircle,
    ChevronDown,
    Filter,
    Pencil,
    PlusCircle,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    logs: {
        data: Array<{
            id: number;
            event: string;
            description: string | null;
            properties: Record<string, any> | null;
            causer_type: string | null;
            causer_id: number | null;
            causer: {
                id: number;
                display_name: string;
                access_level: { label: string } | null;
            } | null;
            subject_type: string | null;
            subject: Record<string, any> | null;
            tenant: { fantasy_name: string } | null;
            created_at: string;
            created_at_human: string;
        }>;
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        total: number;
    };
    filters: Record<string, string>;
    isAdmin: boolean;
}>();

// ── Estado dos filtros ────────────────────────────────────
const showFilters = ref(false);
const eventFilter = ref(props.filters.event || '');
const causerIdFilter = ref(props.filters.causer_id || '');
const subjectTypeFilter = ref(props.filters.subject_type || '');
const dateFromFilter = ref(props.filters.from || '');
const dateToFilter = ref(props.filters.to || '');
const loading = ref(false);

// ── Lista acumulada para "Load More" ──────────────────────
const allLogs = ref([...props.logs.data]);
const currentPage = ref(props.logs.current_page);
const hasMore = computed(() => currentPage.value < props.logs.last_page);

// ── Cores por tipo de ação ────────────────────────────────
function eventColor(event: string): string {
    const lower = event.toLowerCase();

    if (lower.includes('criou') || lower.includes('criado') || lower.includes('create')) {
        return 'border-l-green-500 bg-green-50';
    }

    if (lower.includes('excluiu') || lower.includes('bloqueou') || lower.includes('delete') || lower.includes('block')) {
        return 'border-l-red-500 bg-red-50';
    }

    if (lower.includes('atualizou') || lower.includes('editou') || lower.includes('update') || lower.includes('edit')) {
        return 'border-l-orange-500 bg-orange-50';
    }

    return 'border-l-blue-500 bg-blue-50';
}

function eventIcon(event: string) {
    const lower = event.toLowerCase();

    if (lower.includes('criou') || lower.includes('criado') || lower.includes('create')) {
        return 'text-green-600';
    }

    if (lower.includes('excluiu') || lower.includes('bloqueou') || lower.includes('delete') || lower.includes('block')) {
        return 'text-red-600';
    }

    if (lower.includes('atualizou') || lower.includes('editou') || lower.includes('update') || lower.includes('edit')) {
        return 'text-orange-600';
    }

    return 'text-blue-600';
}

// ── Label amigável para subject_type ──────────────────────
function subjectLabel(type: string): string {
    const map: Record<string, string> = {
        'App\\Models\\Product': 'Produto',
        'App\\Models\\Order': 'Pedido',
        'App\\Models\\User': 'Usuário',
        'App\\Models\\Tenant': 'Loja',
        'App\\Models\\Client': 'Cliente',
        'App\\Models\\Carrier': 'Transportadora',
        'App\\Models\\Input': 'Insumo',
        'App\\Models\\Supplier': 'Fornecedor',
        'App\\Models\\Coupon': 'Cupom',
    };

    return map[type] || type.split('\\').pop() || '—';
}

// ── Aplicar filtros ───────────────────────────────────────
function applyFilters() {
    loading.value = true;
    allLogs.value = [];
    currentPage.value = 1;

    const params: Record<string, string> = {};

    if (eventFilter.value) {
        params.event = eventFilter.value;
    }

    if (causerIdFilter.value) {
        params.causer_id = causerIdFilter.value;
    }

    if (subjectTypeFilter.value) {
        params.subject_type = subjectTypeFilter.value;
    }

    if (dateFromFilter.value) {
        params.from = dateFromFilter.value;
    }

    if (dateToFilter.value) {
        params.to = dateToFilter.value;
    }

    router.get('/audit-logs', params, {
        preserveState: true,
        replace: true,
        only: ['logs', 'filters'],
        onSuccess: () => {
            loading.value = false;
        },
    });
}

// ── Limpar filtros ────────────────────────────────────────
function clearFilters() {
    eventFilter.value = '';
    causerIdFilter.value = '';
    subjectTypeFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';

    router.get('/audit-logs', {}, {
        preserveState: true,
        replace: true,
        only: ['logs', 'filters'],
    });
}

// ── Load More (carrega próxima página) ────────────────────
function loadMore() {
    if (!hasMore.value || loading.value) {
        return;
    }

    loading.value = true;

    const nextPage = currentPage.value + 1;
    const params: Record<string, string> = { page: String(nextPage) };

    if (eventFilter.value) {
        params.event = eventFilter.value;
    }

    if (causerIdFilter.value) {
        params.causer_id = causerIdFilter.value;
    }

    if (subjectTypeFilter.value) {
        params.subject_type = subjectTypeFilter.value;
    }

    if (dateFromFilter.value) {
        params.from = dateFromFilter.value;
    }

    if (dateToFilter.value) {
        params.to = dateToFilter.value;
    }

    router.get('/audit-logs', params, {
        preserveState: true,
        preserveScroll: true,
        only: ['logs'],
        onSuccess: (page: any) => {
            allLogs.value = [...allLogs.value, ...page.props.logs.data];
            currentPage.value = page.props.logs.current_page;
            loading.value = false;
        },
    });
}

// ── Formatação de data ────────────────────────────────────
function formatDate(dateStr: string): string {
    if (!dateStr) {
        return '—';
    }

    const d = new Date(dateStr);

    return d.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Logs de Auditoria" />

    <div class="p-6 max-w-5xl mx-auto">
        <!-- Cabeçalho -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Logs de Auditoria</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ isAdmin ? 'Visualizando todos os registros do sistema.' : 'Visualizando apenas registros da sua loja.' }}
                    <span class="text-gray-400">({{ props.logs.total }} registros)</span>
                </p>
            </div>

            <Button
                variant="outline"
                size="sm"
                @click="showFilters = !showFilters"
                :class="{ 'border-indigo-500 text-indigo-600': showFilters }"
            >
                <Filter class="w-4 h-4 mr-2" />
                Filtros
                <ChevronDown class="w-4 h-4 ml-1" :class="{ 'rotate-180': showFilters }" />
            </Button>
        </div>

        <!-- Barra de Filtros -->
        <Card v-if="showFilters" class="mb-6">
            <CardContent class="pt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tipo de Ação -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de Ação
                        </label>
                        <select
                            v-model="eventFilter"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Todos</option>
                            <option value="Criou Produto">Criou Produto</option>
                            <option value="Atualizou Produto">Atualizou Produto</option>
                            <option value="Excluiu Produto">Excluiu Produto</option>
                            <option value="Bloqueou Usuário">Bloqueou Usuário</option>
                            <option value="Desbloqueou Usuário">Desbloqueou Usuário</option>
                            <option value="Atualizou Pedido">Atualizou Pedido</option>
                            <option value="Cancelou Pedido">Cancelou Pedido</option>
                        </select>
                    </div>

                    <!-- Recurso Afetado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Recurso
                        </label>
                        <select
                            v-model="subjectTypeFilter"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Todos</option>
                            <option value="App\Models\Product">Produto</option>
                            <option value="App\Models\Order">Pedido</option>
                            <option value="App\Models\User">Usuário</option>
                            <option value="App\Models\Tenant">Loja</option>
                            <option value="App\Models\Client">Cliente</option>
                            <option value="App\Models\Carrier">Transportadora</option>
                            <option value="App\Models\Input">Insumo</option>
                            <option value="App\Models\Supplier">Fornecedor</option>
                            <option value="App\Models\Coupon">Cupom</option>
                        </select>
                    </div>

                    <!-- ID do Usuário -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ID do Usuário
                        </label>
                        <Input
                            v-model="causerIdFilter"
                            type="number"
                            placeholder="Ex: 42"
                        />
                    </div>

                    <!-- Data Início -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Data Início
                        </label>
                        <Input
                            v-model="dateFromFilter"
                            type="date"
                        />
                    </div>

                    <!-- Data Fim -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Data Fim
                        </label>
                        <Input
                            v-model="dateToFilter"
                            type="date"
                        />
                    </div>

                    <!-- Botões -->
                    <div class="flex items-end gap-2">
                        <Button
                            @click="applyFilters"
                            size="sm"
                            class="w-full"
                        >
                            <Search class="w-4 h-4 mr-2" />
                            Filtrar
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            <X class="w-4 h-4 mr-1" />
                            Limpar
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Lista de Logs -->
        <div class="space-y-3">
            <div
                v-for="log in allLogs"
                :key="log.id"
                class="flex items-start gap-4 p-4 border-l-4 rounded-r-lg shadow-sm bg-white"
                :class="eventColor(log.event)"
            >
                <!-- Ícone -->
                <div class="flex-shrink-0 mt-1" :class="eventIcon(log.event)">
                    <PlusCircle v-if="/criou|criado|create/i.test(log.event)" class="w-5 h-5" />
                    <Pencil v-else-if="/atualizou|editou|update|edit/i.test(log.event)" class="w-5 h-5" />
                    <Trash2 v-else-if="/excluiu|delete/i.test(log.event)" class="w-5 h-5" />
                    <Ban v-else-if="/bloqueou|block/i.test(log.event)" class="w-5 h-5" />
                    <CheckCircle v-else-if="/desbloqueou|unblock/i.test(log.event)" class="w-5 h-5" />
                    <Filter v-else class="w-5 h-5" />
                </div>

                <!-- Conteúdo -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-gray-900 text-sm">
                            {{ log.causer?.display_name || 'Sistema' }}
                        </span>
                        <span
                            v-if="log.causer?.access_level?.label"
                            class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                        >
                            {{ log.causer.access_level.label }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-700 mt-1 leading-relaxed">
                        {{ log.description || log.event }}
                    </p>

                    <!-- Metadados -->
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                        <span>{{ formatDate(log.created_at) }}</span>
                        <span v-if="log.subject_type">
                            · {{ subjectLabel(log.subject_type) }}
                        </span>
                        <span v-if="isAdmin && log.tenant">
                            · {{ log.tenant.fantasy_name }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estado vazio -->
            <div
                v-if="allLogs.length === 0 && !loading"
                class="text-center py-12 text-gray-400"
            >
                <Filter class="w-12 h-12 mx-auto mb-3 opacity-30" />
                <p class="text-sm">Nenhum log de auditoria encontrado.</p>
            </div>
        </div>

        <!-- Load More Button -->
        <div
            v-if="hasMore"
            class="flex justify-center mt-6"
        >
            <Button
                variant="outline"
                @click="loadMore"
                :disabled="loading"
                class="px-8"
            >
                <span v-if="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                        <circle
                            class="opacity-25"
                            cx="12" cy="12" r="10"
                            stroke="currentColor"
                            stroke-width="4"
                            fill="none"
                        />
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                        />
                    </svg>
                    Carregando...
                </span>
                <span v-else>
                    Mostrar Mais
                </span>
            </Button>
        </div>

        <!-- Indicador de fim da lista -->
        <div
            v-if="!hasMore && allLogs.length > 0"
            class="text-center mt-6 text-xs text-gray-400"
        >
            — Fim dos registros —
        </div>
    </div>
</template>