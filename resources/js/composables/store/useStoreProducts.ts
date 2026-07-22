import { ref, watch } from 'vue';

interface ProductList {
    products: any; // Aceita array ou objeto (paginator)
}

interface FilterState {
    searchTerm: { value: string };
    priceMin: { value: string };
    priceMax: { value: string };
    selectedCategories: { value: string[] };
    sortBy: { value: string };
    sortOrder: { value: string };
}

// Função auxiliar para extrair a lista de produtos corretamente
const extractProducts = (data: any): any[] => {
    if (Array.isArray(data)) {
return data;
}

    if (data && typeof data === 'object' && Array.isArray(data.data)) {
return data.data;
}

    return [];
};

export function useStoreProducts(props: ProductList, filters?: FilterState) {
    // Inicialização robusta
    const productsList = extractProducts(props.products);

    const visibleProducts = ref<any[]>(productsList);
    const hasMore = ref(productsList.length >= 24); // Ajustado para 24
    const currentPage = ref(1);
    const loadingMore = ref(false);

    watch(
        () => props.products,
        (newList) => {
            const items = extractProducts(newList);
            visibleProducts.value = [...items];
            // Se a página retornou 24 itens (ou o total esperado), possivelmente há mais
            hasMore.value = items.length >= 24;
            currentPage.value = 1;
        },
        { deep: true },
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
                if (filters.searchTerm.value.trim().length >= 3) {
qs.set('search', filters.searchTerm.value.trim());
}

                if (filters.priceMin.value) {
qs.set('min_price', filters.priceMin.value);
}

                if (filters.priceMax.value) {
qs.set('max_price', filters.priceMax.value);
}

                if (filters.selectedCategories.value.length > 0) {
qs.set(
                        'categories',
                        filters.selectedCategories.value.join(','),
                    );
}

                qs.set('sort', filters.sortBy.value);
                qs.set('sort_dir', filters.sortOrder.value);
            }

            const url = '/api/store/products?' + qs.toString();
            const res = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (res.ok) {
                const json = await res.json();
                // json.data é o padrão de resposta do Laravel Resource/Paginator
                const newItems = json.data || [];

                visibleProducts.value.push(...newItems);
                hasMore.value = newItems.length > 0; // Se veio algo, assume que pode ter mais
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
