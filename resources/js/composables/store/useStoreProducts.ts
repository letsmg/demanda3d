import { ref, watch } from 'vue';

interface ProductList {
    products: any[];
}

interface FilterState {
    searchTerm: { value: string };
    priceMin: { value: string };
    priceMax: { value: string };
    selectedCategories: { value: string[] };
    sortBy: { value: string };
    sortOrder: { value: string };
}

export function useStoreProducts(props: ProductList, filters?: FilterState) {
    const visibleProducts = ref<any[]>(Array.isArray(props.products) ? [...props.products] : []);
    const hasMore = ref(Array.isArray(props.products) ? props.products.length >= 8 : false);
    const currentPage = ref(1);
    const loadingMore = ref(false);

    watch(
        () => props.products,
        (newList) => {
            if (!newList || !Array.isArray(newList)) {
                return;
            }

            visibleProducts.value = [...newList];
            hasMore.value = newList.length >= 8;
            currentPage.value = 1;
        },
        { immediate: true },
    );

    async function loadMoreProducts(): Promise<void> {
        if (loadingMore.value || !hasMore.value) {
            return;
        }

        loadingMore.value = true;

        const nextPage = currentPage.value + 1;

        try {
            const qs = new URLSearchParams();

            qs.set('page', String(nextPage));

            if (filters) {
                const trimmedSearch = filters.searchTerm.value.trim();

                if (trimmedSearch.length >= 3) {
                    qs.set('search', trimmedSearch);
                }

                if (filters.priceMin.value) {
                    qs.set('min_price', filters.priceMin.value);
                }

                if (filters.priceMax.value) {
                    qs.set('max_price', filters.priceMax.value);
                }

                if (filters.selectedCategories.value.length > 0) {
                    qs.set('categories', filters.selectedCategories.value.join(','));
                }

                qs.set('sort', filters.sortBy.value);
                qs.set('sort_dir', filters.sortOrder.value);
            }

            const url = '/api/store/products?' + qs.toString();
            const res = await fetch(url, { headers: { Accept: 'application/json' } });

            if (res.ok) {
                const json = await res.json();

                visibleProducts.value.push(...json.data);
                hasMore.value = json.has_more;
                currentPage.value = nextPage;
            }
        } finally {
            loadingMore.value = false;
        }
    }

    return {
        visibleProducts,
        hasMore,
        currentPage,
        loadingMore,
        loadMoreProducts,
    };
}