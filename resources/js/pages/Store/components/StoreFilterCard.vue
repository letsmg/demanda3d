<script setup lang="ts">
import { Search, SlidersHorizontal, RotateCw, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Category {
    slug: string;
    name: string;
}

interface SortOption {
    value: string;
    label: string;
}

defineProps<{
    // Search
    searchTerm: string;
    suggestions: string[];
    showSuggestions: boolean;
    highlightedIndex: number;
    // Price
    priceMin: string;
    priceMax: string;
    // Categories
    categories: Category[];
    selectedCategories: string[];
    // Sort
    sortOptions: SortOption[];
    currentSort: string;
    // Flags
    hasFilters: boolean;
}>();

const emit = defineEmits<{
    'update:search-term': [value: string];
    'input-search': [];
    'keydown-search': [e: KeyboardEvent];
    'select-suggestion': [text: string];
    'clear-search': [];
    'update:price-min': [value: string];
    'update:price-max': [value: string];
    'blur-price': [];
    'keyup-price': [e: KeyboardEvent];
    'toggle-category': [slug: string];
    'select-all-categories': [];
    'update:sort': [value: string | null];
    'apply-filters': [];
    'clear-filters': [];
}>();

function onSearchInput(e: Event): void {
    emit('update:search-term', (e.target as HTMLInputElement).value);
    emit('input-search');
}

function onPriceMinInput(e: Event): void {
    emit('update:price-min', (e.target as HTMLInputElement).value);
}

function onPriceMaxInput(e: Event): void {
    emit('update:price-max', (e.target as HTMLInputElement).value);
}
</script>

<template>
    <!-- ═══════ HEADER: Busca + Ordenação lado a lado ═══════ -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row" data-search-area>
        <div class="relative flex-1">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-amber-700"
            />
            <input
                :value="searchTerm"
                type="text"
                placeholder="Buscar produtos por nome ou descrição..."
                class="store-amber-input w-full rounded-md border border-brand-amberInputBorder px-10 py-2 text-brand-amberDark placeholder:text-brand-amberPrimary/60 focus:border-amber-500 focus:ring-amber-500 focus:outline-none"
                @input="onSearchInput"
                @keydown="emit('keydown-search', $event)"
            />
            <button
                v-if="searchTerm"
                type="button"
                class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-0.5 text-amber-700 hover:text-amber-900"
                @click="emit('clear-search')"
                title="Limpar pesquisa"
            >
                <X class="h-4 w-4" />
            </button>

            <!-- Autocomplete dropdown -->
            <div
                v-if="showSuggestions && suggestions.length > 0"
                class="absolute z-50 mt-1 w-full rounded-lg border border-amber-200 bg-white shadow-lg"
            >
                <div
                    v-for="(s, idx) in suggestions"
                    :key="idx"
                    class="flex cursor-pointer items-center gap-2 px-4 py-2.5 text-sm transition"
                    :class="idx === highlightedIndex ? 'bg-amber-100 text-amber-900' : 'text-gray-700 hover:bg-amber-50'"
                    @mousedown.prevent="emit('select-suggestion', s)"
                >
                    <Search class="h-3.5 w-3.5 text-amber-500" />
                    {{ s }}
                </div>
            </div>
        </div>

        <!-- Ordenação lado a lado -->
        <Select
            :model-value="currentSort"
            @update:model-value="emit('update:sort', $event)"
        >
            <SelectTrigger class="w-44 border-brand-amberInputBorder text-brand-amberDark">
                <SelectValue placeholder="Nome A-Z" />
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

    <!-- ═══════ CARD DE FILTROS (Dark Amber) ═══════ -->
    <Card class="mb-8 border-amber-700 bg-amber-600 text-white shadow-md">
        <CardContent class="space-y-4 pt-5">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Preço -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-amber-50">Preço:</label>
                    <input
                        :value="priceMin"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="Mín"
                        class="store-amber-input w-24 rounded-md border border-brand-amberInputBorder px-2 py-2 text-brand-amberDark placeholder:text-brand-amberPrimary/60 focus:outline-none"
                        @input="onPriceMinInput"
                        @blur="emit('blur-price')"
                        @keyup="emit('keyup-price', $event)"
                    />
                    <span class="text-brand-amberPrimary">-</span>
                    <input
                        :value="priceMax"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="Máx"
                        class="store-amber-input w-24 rounded-md border border-brand-amberInputBorder px-2 py-2 text-brand-amberDark placeholder:text-brand-amberPrimary/60 focus:outline-none"
                        @input="onPriceMaxInput"
                        @blur="emit('blur-price')"
                        @keyup="emit('keyup-price', $event)"
                    />
                </div>

                <!-- Ações -->
                <Button
                    type="button"
                    size="sm"
                    class="flex items-center gap-1.5 bg-amber-800 text-white hover:bg-amber-900"
                    @click="emit('apply-filters')"
                >
                    <SlidersHorizontal class="h-4 w-4" />
                    Aplicar Filtros
                </Button>

                <Button
                    v-if="hasFilters"
                    type="button"
                    variant="outline"
                    size="sm"
                    class="border-amber-200 text-amber-50 hover:bg-amber-700 hover:text-white"
                    @click="emit('clear-filters')"
                >
                    <RotateCw class="mr-1 h-4 w-4" />Limpar Filtros
                </Button>
            </div>

            <!-- Categorias multi-seleção -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-amber-50">Categorias:</label>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Botão Todas -->
                    <button
                        type="button"
                        class="rounded-full border px-3 py-1 text-xs font-medium transition select-none"
                        :class="selectedCategories.length === 0
                            ? 'border-amber-50 bg-amber-800 text-white shadow-sm'
                            : 'border-amber-300 bg-transparent text-amber-50 hover:border-amber-100 hover:bg-amber-700'"
                        @click="$emit('select-all-categories')"
                    >
                        Todas
                    </button>

                    <!-- Cápsulas das Categorias -->
                    <div
                        v-for="cat in categories"
                        :key="cat.slug"
                        class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition select-none shadow-sm"
                        :class="selectedCategories.includes(cat.slug)
                            ? 'border-amber-50 bg-amber-800 text-white'
                            : 'border-amber-300 bg-transparent text-amber-50 hover:border-amber-100 hover:bg-amber-700'"
                        @click="$emit('toggle-category', cat.slug)"
                    >
                        <!-- 
                        Input nativo: o Tailwind Forms adiciona o 'v' branco automaticamente 
                        usando a cor definida em 'text-amber-600' para o fundo do quadrado.
                        -->
                        <input
                            type="checkbox"
                            :checked="selectedCategories.includes(cat.slug)"
                            class="h-3.5 w-3.5 rounded border-amber-400 bg-transparent text-amber-600 focus:ring-0 focus:ring-offset-0 pointer-events-none transition-colors checked:border-amber-50"
                        />
                        <span>{{ cat.name }}</span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>