import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface StoreFilters {
    search?: string;
    min_price?: number | string;
    max_price?: number | string;
    sort?: string;
    sort_dir?: string;
    categories?: string;
}

interface FilterParams {
    search?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
    sort_dir?: string;
    categories?: string;
}

// ── Função de Segurança: Garante que nunca enviaremos uma função ao backend ──
function forceString(val: any, fallback: string): string {
    if (val === undefined || val === null || typeof val === 'function') return fallback;
    const str = String(val);
    if (str.includes('[native code]')) return fallback;
    return str;
}

function parseCategories(raw: string | undefined): string[] {
    if (!raw || typeof raw === 'function') return [];
    return String(raw).split(',').filter(Boolean);
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

export function useStoreFilters(props: { filters: StoreFilters }) {
    // ── State (Sanitizado) ──────────────────────────────
    const searchTerm = ref(forceString(props.filters.search, ''));
    const priceMin = ref(Number(props.filters.min_price) || 0);
    const priceMax = ref(Number(props.filters.max_price) || 1500);
    const selectedCategories = ref<string[]>(parseCategories(props.filters.categories));
    
    // RENOMEADO: Usando nomes impossíveis de confundir com métodos nativos
    const fieldSort = ref(forceString(props.filters.sort, 'name'));
    const dirSort = ref(forceString(props.filters.sort_dir, 'asc'));

    // ── Watcher ──────────────────────────────────────────
    watch(
        () => props.filters,
        (newFilters) => {
            if (!newFilters) return;
            searchTerm.value = forceString(newFilters.search, '');
            priceMin.value = Number(newFilters.min_price) || 0;
            priceMax.value = Number(newFilters.max_price) || 1500;
            selectedCategories.value = parseCategories(newFilters.categories);
            
            fieldSort.value = forceString(newFilters.sort, 'name');
            dirSort.value = forceString(newFilters.sort_dir, 'asc');
        },
        { deep: true }
    );

    // ── Core: Aplicação de Filtros ─────────────────────────
    function applyStoreFilters(): void {
        const params: FilterParams = {};
        const trimmedSearch = searchTerm.value.trim();

        if (trimmedSearch.length >= 3) params.search = trimmedSearch;
        if (priceMin.value > 0) params.min_price = String(priceMin.value);
        if (priceMax.value < 1500) params.max_price = String(priceMax.value);
        if (selectedCategories.value.length > 0) params.categories = selectedCategories.value.join(',');
        
        // Forçando o envio dos nomes que o Backend espera, mas vindos de refs seguras
        params.sort = forceString(fieldSort.value, 'name');
        params.sort_dir = forceString(dirSort.value, 'asc');

        console.log('ENVIANDO PARA BACKEND (LIMPO):', params);

        router.get('/store', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }

    // ── Limpeza ────────────────────────────────────────────
    function clearCategories(): void {
        selectedCategories.value = [];
        applyStoreFilters();
    }

    function clearSearch(): void {
        searchTerm.value = '';
        suggestions.value = [];
        showSuggestions.value = false;
        highlightedIndex.value = -1;
        applyStoreFilters();
    }

    function clearAllFilters(): void {
        searchTerm.value = '';
        priceMin.value = 0;
        priceMax.value = 1500;
        selectedCategories.value = [];
        fieldSort.value = 'name';
        dirSort.value = 'asc';
        applyStoreFilters();
    }

    // ── Autocomplete ──────────────────────────────────────
    const suggestions = ref<string[]>([]);
    const showSuggestions = ref(false);
    const highlightedIndex = ref(-1);
    let abortController: AbortController | null = null;

    async function fetchSuggestions(): Promise<void> {
        if (abortController) abortController.abort();
        const val = searchTerm.value.trim();
        if (val.length < 1) {
            suggestions.value = [];
            showSuggestions.value = false;
            return;
        }

        abortController = new AbortController();
        try {
            const res = await fetch(`/api/search/suggestions?q=${encodeURIComponent(val)}`, {
                signal: abortController.signal,
                headers: { Accept: 'application/json' },
            });
            if (res.ok) {
                const json = await res.json();
                suggestions.value = json.suggestions || [];
                showSuggestions.value = suggestions.value.length > 0;
                highlightedIndex.value = -1;
            }
        } catch (e: any) {
            if (e.name !== 'AbortError') {
                suggestions.value = [];
                showSuggestions.value = false;
            }
        }
    }

    function selectSuggestion(text: string): void {
        searchTerm.value = text;
        showSuggestions.value = false;
        highlightedIndex.value = -1;
        applyStoreFilters();
    }

    function onSearchInput(): void {
        fetchSuggestions();
    }

    function onSearchKeydown(e: KeyboardEvent): void {
        if (!showSuggestions.value || suggestions.value.length === 0) {
            if (e.key === 'Enter') applyStoreFilters();
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex.value = Math.min(highlightedIndex.value + 1, suggestions.value.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex.value >= 0 && highlightedIndex.value < suggestions.value.length) {
                selectSuggestion(suggestions.value[highlightedIndex.value]);
            } else {
                showSuggestions.value = false;
                applyStoreFilters();
            }
        } else if (e.key === 'Escape') {
            showSuggestions.value = false;
        }
    }

    function onWindowClick(e: MouseEvent): void {
        const target = e.target as HTMLElement;
        if (!target.closest('[data-search-area]')) showSuggestions.value = false;
    }

    onMounted(() => window.addEventListener('click', onWindowClick));
    onUnmounted(() => window.removeEventListener('click', onWindowClick));

    // ── Price Slider ───────────────────────────────────────
    function updatePriceRange(values: [number, number]): void {
        priceMin.value = values[0];
        priceMax.value = values[1];
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            applyStoreFilters();
        }, 500);
    }

    // ── Categories / Sorting ───────────────────────────────
    function toggleCategory(catSlug: string): void {
        const idx = selectedCategories.value.indexOf(catSlug);
        if (idx >= 0) selectedCategories.value.splice(idx, 1);
        else selectedCategories.value.push(catSlug);
        applyStoreFilters();
    }

    const sortOptions = [
        { value: 'name_asc', label: 'Nome A-Z' },
        { value: 'name_desc', label: 'Nome Z-A' },
        { value: 'sale_price_asc', label: 'Menor Preço' },
        { value: 'sale_price_desc', label: 'Maior Preço' },
        { value: 'created_at_desc', label: 'Mais Recentes' },
        { value: 'created_at_asc', label: 'Mais Antigos' },
    ];

    function handleSortChange(value: string | null): void {
        if (!value || typeof value !== 'string') return;
        
        const parts = value.split('_');
        const dir = parts.pop()!;
        const field = parts.join('_');
        
        fieldSort.value = field;
        dirSort.value = dir;
        applyStoreFilters();
    }

    function getCurrentSort(): string {
        return fieldSort.value + '_' + dirSort.value;
    }

    const hasFilters = computed(() => {
        return (
            searchTerm.value.trim().length >= 3 ||
            priceMin.value > 0 ||
            priceMax.value < 1500 ||
            selectedCategories.value.length > 0 ||
            fieldSort.value !== 'name' ||
            dirSort.value !== 'asc'
        );
    });

    return {
        searchTerm,
        priceMin,
        priceMax,
        selectedCategories,
        fieldSort,
        dirSort,
        suggestions,
        showSuggestions,
        highlightedIndex,
        sortOptions,
        hasFilters,
        fetchSuggestions,
        selectSuggestion,
        onSearchInput,
        onSearchKeydown,
        clearSearch,
        updatePriceRange,
        toggleCategory,
        clearCategories,
        handleSortChange,
        getCurrentSort,
        applyStoreFilters,
        clearAllFilters,
    };
}