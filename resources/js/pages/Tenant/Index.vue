<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { LayoutTemplate, Search, X } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({
    layout: WelcomeLayout,
});

const props = defineProps<{
    tenants: Array<{
        id: number;
        fantasy_name: string;
        company_name: string;
        fantasy_slug?: string;
        city?: string;
        state?: string;
        logo_url?: string;
        banner_url?: string;
        description?: string;
        rating_average?: string;
        rating_count?: number;
    }>;
    filters: {
        search?: string;
        sort?: string;
        sort_dir?: string;
        city?: string;
        state?: string;
    };
}>();

// ============================================================
// Search & filter state
// ============================================================
const searchTerm = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort || 'fantasy_name');
const sortOrder = ref(props.filters.sort_dir || 'asc');

let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch(searchTerm, (newVal: string) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    if (newVal.length >= 3) {
        searchTimer = setTimeout(() => applyTenantFilters(), 500);
    } else if (newVal.length === 0) {
        applyTenantFilters();
    }
});

function applyTenantFilters(): void {
    const params: Record<string, any> = {};

    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    params.sort = sortBy.value;
    params.sort_dir = sortOrder.value;

    router.get('/tenants', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearTenantFilters(): void {
    searchTerm.value = '';
    sortBy.value = 'fantasy_name';
    sortOrder.value = 'asc';
    applyTenantFilters();
}

const hasActiveFilters = computed(() => {
    return (
        searchTerm.value ||
        sortBy.value !== 'fantasy_name' ||
        sortOrder.value !== 'asc'
    );
});

const sortOptions = [
    { value: 'fantasy_name_asc', label: 'Nome A-Z' },
    { value: 'fantasy_name_desc', label: 'Nome Z-A' },
    { value: 'rating_average_desc', label: 'Melhor Avaliados' },
    { value: 'rating_average_asc', label: 'Pior Avaliados' },
    { value: 'created_at_desc', label: 'Mais Recentes' },
    { value: 'created_at_asc', label: 'Mais Antigos' },
];

function onSortOptionChange(value: string): void {
    const [field, dir] = value.split('_');
    sortBy.value = field;
    sortOrder.value = dir;
    applyTenantFilters();
}

function getCurrentSort(): string {
    return sortBy.value + '_' + sortOrder.value;
}
</script>

<template>
    <Head title="Impressoras 3D - Demanda3D">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-amber-50">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-amber-900">
                    Impressoras 3D
                </h1>
                <p class="mt-1 text-sm text-amber-600">
                    Encontre profissionais e empresas de impressão 3D
                </p>
            </div>

            <!-- Filtro de busca -->
            <div class="mb-8 space-y-4">
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-amber-910"
                    />
                    <Input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar por nome da empresa..."
                        class="w-full border-amber-900 bg-white! pr-10 pl-10 text-amber-900! placeholder:text-amber-800! focus:border-amber-500 focus:ring-amber-500"
                    />
                    <button
                        v-if="searchTerm"
                        class="absolute top-1/2 right-3 -translate-y-1/2 text-amber-910 hover:text-amber-600"
                        @click="searchTerm = ''; applyTenantFilters()"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <p
                    v-if="searchTerm.length > 0 && searchTerm.length < 3"
                    class="text-xs text-muted-foreground"
                >
                    Digite pelo menos 3 caracteres para buscar
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-amber-700">Ordenar:</label>
                        <Select
                            :model-value="getCurrentSort()"
                            @update:model-value="onSortOptionChange"
                        >
                            <SelectTrigger
                                class="w-44 border-amber-900 bg-white! text-amber-800 placeholder:text-amber-800!"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in sortOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        class="text-amber-600"
                        @click="clearTenantFilters"
                    >
                        <X class="mr-1 h-4 w-4" />Limpar filtros
                    </Button>
                </div>
            </div>

            <div v-if="tenants.length === 0" class="py-16 text-center">
                <LayoutTemplate class="mx-auto h-12 w-12 text-amber-900" />
                <h3 class="mt-2 text-sm font-semibold text-amber-800">
                    Nenhuma impressora encontrada
                </h3>
                <p class="mt-1 text-sm text-amber-600">
                    Tente ajustar os filtros ou buscar por outros termos.
                </p>
                <Button variant="outline" class="mt-4" @click="clearTenantFilters">
                    Limpar filtros
                </Button>
            </div>

            <div
                v-else
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <Card v-for="tenant in tenants" :key="tenant.id" class="flex flex-col overflow-hidden">
                    <div class="relative h-48 w-full bg-amber-100">
                        <img
                            v-if="tenant.logo_url"
                            :src="tenant.logo_url"
                            :alt="tenant.fantasy_name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center"
                        >
                            <LayoutTemplate class="h-12 w-12 text-amber-200" />
                        </div>
                    </div>
                    <CardHeader class="pb-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-base text-amber-900">
                                    {{ tenant.fantasy_name }}
                                </CardTitle>
                                <CardDescription
                                    v-if="tenant.company_name"
                                    class="text-xs text-amber-700"
                                >
                                    {{ tenant.company_name }}
                                </CardDescription>
                            </div>
                        </div>
                        <div v-if="tenant.city || tenant.state" class="mt-1 text-xs text-amber-800">
                            {{ [tenant.city, tenant.state].filter(Boolean).join(' - ') }}
                        </div>
                    </CardHeader>
                    <CardContent class="flex-1 pb-2">
                        <p
                            v-if="tenant.description"
                            class="line-clamp-2 text-xs text-amber-800"
                        >
                            {{ tenant.description }}
                        </p>
                        <div v-if="tenant.rating_count && tenant.rating_count > 0" class="mt-2 flex items-center gap-1">
                            <span class="text-xs font-medium text-amber-700">
                                ⭐ {{ tenant.rating_average }} ({{ tenant.rating_count }})
                            </span>
                        </div>
                        <div v-else class="mt-2 text-xs text-amber-800">
                            Sem avaliações
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>
    </div>
</template>