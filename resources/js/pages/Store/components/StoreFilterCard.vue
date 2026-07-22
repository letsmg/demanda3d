<script setup lang="ts">
import { Search, RotateCw, X } from 'lucide-vue-next';
import { computed } from 'vue';
import VueSlider from 'vue-slider-component';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import 'vue-slider-component/dist-css/vue-slider-component.css';

interface Category {
    slug: string;
    name: string;
}

interface SortOption {
    value: string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        searchTerm: string;
        suggestions: string[];
        showSuggestions: boolean;
        highlightedIndex: number;
        priceMin: number;
        priceMax: number;
        categories: Category[];
        selectedCategories: string[];
        sortOptions: SortOption[];
        currentSort: string;
        hasFilters: boolean;
    }>(),
    {
        selectedCategories: () => [],
        categories: () => [],
        suggestions: () => [],
        sortOptions: () => [],
    },
);

const emit = defineEmits<{
    'update:search-term': [value: string];
    'input-search': [];
    'keydown-search': [e: KeyboardEvent];
    'select-suggestion': [text: string];
    'clear-search': [];
    'update:price-range': [values: [number, number]];
    'toggle-category': [slug: string];
    'select-all-categories': [];
    'update:sort': [value: string | null];
}>();

function onSearchInput(e: Event): void {
    emit('update:search-term', (e.target as HTMLInputElement).value);
    emit('input-search');
}

// Valor vinculado do slider como array [min, max]
const sliderValue = computed<[number, number]>(() => [
    props.priceMin,
    props.priceMax,
]);

function onSliderChange(values: [number, number]): void {
    emit('update:price-range', values);
}

function sliderTooltipFormatter(val: number): string {
    return `R$ ${val}`;
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
                    :class="
                        idx === highlightedIndex
                            ? 'bg-amber-100 text-amber-900'
                            : 'text-gray-700 hover:bg-amber-50'
                    "
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
            <SelectTrigger
                class="w-44 border-brand-amberInputBorder text-brand-amberDark"
            >
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
        <CardContent class="">
            <!-- Range Slider de Preço + Limpar Filtros lado a lado -->
            <div>
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-amber-50"
                        >Faixa de Preço:</label
                    >
                    <span class="text-xs font-medium text-amber-200">
                        R$ {{ sliderValue[0] }} — R$ {{ sliderValue[1] }}
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Slider — metade da largura -->
                    <div :class="hasFilters ? 'w-1/2' : 'w-full'">
                        <VueSlider
                            :model-value="sliderValue"
                            :min="0"
                            :max="1500"
                            :interval="10"
                            :tooltip-formatter="sliderTooltipFormatter"
                            :dot-size="22"
                            :height="8"
                            :rail-style="{
                                backgroundColor: 'rgba(255,255,255,0.2)',
                                borderRadius: '4px',
                            }"
                            :process-style="{
                                backgroundColor: '#f59e0b',
                                borderRadius: '4px',
                            }"
                            :tooltip-style="{
                                backgroundColor: '#1c1917',
                                borderColor: '#1c1917',
                                color: '#fef3c7',
                                fontSize: '12px',
                                fontWeight: '600',
                            }"
                            :dot-style="{
                                backgroundColor: '#ffffff',
                                borderColor: '#451a03',
                                borderWidth: '3px',
                                boxShadow:
                                    '0 2px 6px rgba(0,0,0,0.4), 0 0 0 2px rgba(245,158,11,0.4)',
                            }"
                            @change="onSliderChange"
                        />
                    </div>
                    <!-- Limpar Filtros ao lado direito do slider -->
                    <Button
                        v-if="hasFilters"
                        type="button"
                        variant="outline"
                        size="sm"
                        class="shrink-0 border-amber-200 text-amber-50 hover:bg-amber-700 hover:text-white"
                        @click="
                            emit('select-all-categories');
                            emit('update:price-range', [0, 1500]);
                        "
                    >
                        <RotateCw class="mr-1 h-4 w-4" />Limpar Filtros
                    </Button>
                </div>
            </div>

            <!-- Categorias multi-seleção -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-amber-50"
                    >Categorias:</label
                >
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-full border px-3 py-1 text-xs font-medium transition select-none"
                        :class="
                            selectedCategories.length === 0
                                ? 'border-amber-50 bg-amber-800 text-white shadow-sm'
                                : 'border-amber-300 bg-transparent text-amber-50 hover:border-amber-100 hover:bg-amber-700'
                        "
                        @click="emit('select-all-categories')"
                    >
                        Todas
                    </button>
                    <div
                        v-for="cat in categories"
                        :key="cat.slug"
                        class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium shadow-sm transition select-none"
                        :class="
                            selectedCategories.includes(cat.slug)
                                ? 'border-amber-50 bg-amber-800 text-white'
                                : 'border-amber-300 bg-transparent text-amber-50 hover:border-amber-100 hover:bg-amber-700'
                        "
                        @click="emit('toggle-category', cat.slug)"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedCategories.includes(cat.slug)"
                            class="pointer-events-none h-3.5 w-3.5 rounded border-amber-400 bg-transparent text-amber-600 transition-colors checked:border-amber-50 focus:ring-0 focus:ring-offset-0"
                        />
                        <span>{{ cat.name }}</span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
