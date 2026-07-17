import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

type FilterParams = Record<string, string>;

interface StoreFilters {
    search?: string;
    min_price?: number;
    max_price?: number;
    sort?: string;
    sort_dir?: string;
    categories?: string;
}

function parseCategories(raw: string | undefined): string[] {
    if (!raw) {
        return [];
    }

    return raw.split(',').filter(Boolean);
}

export function useStoreFilters(props: { filters: StoreFilters }) {
    const searchTerm = ref(props.filters.search || '');
    const priceMin = ref(props.filters.min_price?.toString() || '');
    const priceMax = ref(props.filters.max_price?.toString() || '');
    const selectedCategories = ref<string[]>(parseCategories(props.filters.categories));
    const sortBy = ref(props.filters.sort || 'name');
    const sortOrder = ref(props.filters.sort_dir || 'asc');

    // ⚡ Sincroniza refs com props quando Inertia atualiza os filtros
    watch(
        () => props.filters,
        (newFilters) => {
            if (!newFilters) {
                return;
            }

            searchTerm.value = newFilters.search || '';
            priceMin.value = newFilters.min_price?.toString() || '';
            priceMax.value = newFilters.max_price?.toString() || '';
            selectedCategories.value = parseCategories(newFilters.categories);
            sortBy.value = newFilters.sort || 'name';
            sortOrder.value = newFilters.sort_dir || 'asc';
        },
        { deep: true },
    );

    // ── Autocomplete ────────────────────────────────────────
    const suggestions = ref<string[]>([]);
    const showSuggestions = ref(false);
    const highlightedIndex = ref(-1);
    let abortController: AbortController | null = null;

    async function fetchSuggestions(): Promise<void> {
        if (abortController) {
            abortController.abort();
        }

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
            if (e.key === 'Enter') {
                applyStoreFilters();
            }

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

        if (!target.closest('[data-search-area]')) {
            showSuggestions.value = false;
        }
    }

    onMounted(() => {
        window.addEventListener('click', onWindowClick);
    });

    onUnmounted(() => {
        window.removeEventListener('click', onWindowClick);
    });

    // ── Search ─────────────────────────────────────────────
    function clearSearch(): void {
        searchTerm.value = '';
        suggestions.value = [];
        showSuggestions.value = false;
        highlightedIndex.value = -1;
        applyStoreFilters();

        const input = document.querySelector<HTMLInputElement>('input[placeholder*="Buscar"]');

        input?.focus();
    }

    // ── Price ──────────────────────────────────────────────
    function onPriceBlur(): void {
        const min = parseFloat(priceMin.value);
        const max = parseFloat(priceMax.value);

        if (!isNaN(min) && !isNaN(max) && min > max) {
            const tmp = priceMin.value;

            priceMin.value = priceMax.value;
            priceMax.value = tmp;
        }

        applyStoreFilters();
    }

    function onPriceEnter(e: KeyboardEvent): void {
        if (e.key === 'Enter') {
            (e.target as HTMLInputElement).blur();
        }
    }

    // ── Categories ─────────────────────────────────────────
    function toggleCategory(catSlug: string): void {
        const idx = selectedCategories.value.indexOf(catSlug);

        if (idx >= 0) {
            selectedCategories.value.splice(idx, 1);
        } else {
            selectedCategories.value.push(catSlug);
        }

        applyStoreFilters();
    }

    // ── Sorting ────────────────────────────────────────────
    const sortOptions = [
        { value: 'name_asc', label: 'Nome A-Z' },
        { value: 'name_desc', label: 'Nome Z-A' },
        { value: 'sale_price_asc', label: 'Menor Preço' },
        { value: 'sale_price_desc', label: 'Maior Preço' },
        { value: 'created_at_desc', label: 'Mais Recentes' },
        { value: 'created_at_asc', label: 'Mais Antigos' },
    ];

    function handleSortChange(value: string | null): void {
        if (!value) {
            return;
        }

        const parts = value.split('_');
        const dir = parts.pop()!;
        const field = parts.join('_');

        sortBy.value = field;
        sortOrder.value = dir;
        applyStoreFilters();
    }

    function getCurrentSort(): string {
        return sortBy.value + '_' + sortOrder.value;
    }

    // ── Core ───────────────────────────────────────────────
    function applyStoreFilters(): void {
        const params: FilterParams = {};
        const trimmedSearch = searchTerm.value.trim();

        if (trimmedSearch.length >= 3) {
            params.search = trimmedSearch;
        }

        if (priceMin.value) {
            params.min_price = priceMin.value;
        }

        if (priceMax.value) {
            params.max_price = priceMax.value;
        }

        if (selectedCategories.value.length > 0) {
            params.categories = selectedCategories.value.join(',');
        }

        params.sort = sortBy.value;
        params.sort_dir = sortOrder.value;

        router.get('/store', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }

    function clearFiltersOnly(): void {
        priceMin.value = '';
        priceMax.value = '';
        selectedCategories.value = [];
        sortBy.value = 'name';
        sortOrder.value = 'asc';

        applyStoreFilters();
    }

    function clearAllFilters(): void {
        searchTerm.value = '';
        suggestions.value = [];
        showSuggestions.value = false;
        priceMin.value = '';
        priceMax.value = '';
        selectedCategories.value = [];
        sortBy.value = 'name';
        sortOrder.value = 'asc';

        router.get('/store', {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }

    // ── Computed ───────────────────────────────────────────
    const hasFilters = computed(() => {
        return (
            searchTerm.value.trim().length >= 3 ||
            priceMin.value ||
            priceMax.value ||
            selectedCategories.value.length > 0 ||
            sortBy.value !== 'name' ||
            sortOrder.value !== 'asc'
        );
    });

    return {
        // State
        searchTerm,
        priceMin,
        priceMax,
        selectedCategories,
        sortBy,
        sortOrder,
        // Autocomplete
        suggestions,
        showSuggestions,
        highlightedIndex,
        fetchSuggestions,
        selectSuggestion,
        onSearchInput,
        onSearchKeydown,
        // Search
        clearSearch,
        // Price
        onPriceBlur,
        onPriceEnter,
        // Categories
        toggleCategory,
        // Sorting
        sortOptions,
        handleSortChange,
        getCurrentSort,
        // Core
        applyStoreFilters,
        clearFiltersOnly,
        clearAllFilters,
        // Computed
        hasFilters,
    };
}