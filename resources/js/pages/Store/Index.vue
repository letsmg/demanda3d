<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useCart } from '@/composables/store/useCart';
import { useImageGallery } from '@/composables/store/useImageGallery';
import { useShareDialog } from '@/composables/store/useShareDialog';
import { useStoreFilters } from '@/composables/store/useStoreFilters';
import { useStoreProducts } from '@/composables/store/useStoreProducts';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';
import StoreCartBar from './components/StoreCartBar.vue';
import StoreFilterCard from './components/StoreFilterCard.vue';
import StoreImageGallery from './components/StoreImageGallery.vue';
import StoreProductGrid from './components/StoreProductGrid.vue';
import StoreShareDialog from './components/StoreShareDialog.vue';

defineOptions({
    layout: WelcomeLayout,
});

const props = defineProps<{
    products: any[];
    categories: Array<{ slug: string; name: string }>;
    filters: {
        search?: string;
        min_price?: number;
        max_price?: number;
        sort?: string;
        sort_dir?: string;
        categories?: string;
    };
}>();

const isMounted = ref(false);

// Composables
const cart = useCart();
const storeFilters = useStoreFilters(props);
const products = useStoreProducts(props, {
    searchTerm: storeFilters.searchTerm,
    priceMin: storeFilters.priceMin,
    priceMax: storeFilters.priceMax,
    selectedCategories: storeFilters.selectedCategories,
    sortBy: storeFilters.fieldSort,
    sortOrder: storeFilters.dirSort,
});
const gallery = useImageGallery();
const share = useShareDialog();

onMounted(() => {
    isMounted.value = true;
    if (cart.isAuthenticated()) {
        cart.fetchCartData();
    }
});

function formatPrice(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
}

function getImageUrl(product: any, index: number = 0): string | undefined {
    if (product.images && product.images.length > 0 && product.images[index]) {
        return product.images[index].url;
    }
    return undefined;
}
</script>

<template>
    <Head title="Loja - Demanda3D">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-brand-amberBg">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-amber-900">
                    Loja de Produtos
                </h1>
                <p class="mt-1 text-sm text-amber-700">
                    Produtos disponíveis para impressão 3D de todos os nossos vendedores
                </p>
            </div>

            <StoreFilterCard
                :search-term="storeFilters.searchTerm.value"
                :suggestions="storeFilters.suggestions.value"
                :show-suggestions="storeFilters.showSuggestions.value"
                :highlighted-index="storeFilters.highlightedIndex.value"
                :price-min="storeFilters.priceMin.value"
                :price-max="storeFilters.priceMax.value"
                :categories="props.categories"
                :selected-categories="storeFilters.selectedCategories.value"
                :sort-options="storeFilters.sortOptions"
                :current-sort="storeFilters.getCurrentSort()"
                :has-filters="storeFilters.hasFilters.value"
                
                @update:search-term="storeFilters.searchTerm.value = $event"
                @input-search="storeFilters.onSearchInput()"
                @keydown-search="storeFilters.onSearchKeydown($event)"
                @select-suggestion="storeFilters.selectSuggestion($event)"
                @clear-search="storeFilters.clearSearch()"
                @update:price-range="storeFilters.updatePriceRange($event)"
                @toggle-category="storeFilters.toggleCategory($event)"
                @select-all-categories="storeFilters.clearCategories()"
                @update:sort="storeFilters.handleSortChange($event)"
            />

            <!-- Grid de Produtos -->
            <StoreProductGrid
                :products="products.visibleProducts.value"
                :has-more="products.hasMore.value"
                :loading-more="products.loadingMore.value"
                :get-cart-qty="cart.getCartQty"
                :get-cart-item-id="cart.getCartItemId"
                :cart-loading="cart.cartLoading.value"
                :format-price="formatPrice"
                :get-image-url="getImageUrl"
                @open-gallery="gallery.openGallery"
                @open-share="share.openShare"
                @remove-from-cart="cart.removeFromCart"
                @add-to-cart="cart.addToCart"
                @load-more="products.loadMoreProducts"
                @clear-all="storeFilters.clearAllFilters()"
            />
        </main>

        <!-- Componentes de Suporte (Diálogo, Galeria, Carrinho) permanecem inalterados -->
        <StoreShareDialog
            :open="share.shareDialogOpen.value"
            :title="share.shareTitle.value"
            :url="share.shareUrl.value"
            :copied="share.shareCopied.value"
            @update:open="share.shareDialogOpen = $event"
            @copy-link="share.copyLink()"
            @share-whatsapp="share.shareWhatsApp()"
            @share-facebook="share.shareFacebook()"
            @share-twitter="share.shareTwitter()"
            @share-telegram="share.shareTelegram()"
        />

        <StoreImageGallery
            :product="gallery.selectedProduct.value"
            :current-image-index="gallery.currentImageIndex.value"
            :format-price="formatPrice"
            :get-cart-qty="cart.getCartQty"
            :get-cart-item-id="cart.getCartItemId"
            @update:open="gallery.closeGallery()"
            @prev-image="gallery.prevImage()"
            @next-image="gallery.nextImage()"
            @select-image="gallery.currentImageIndex = $event"
            @add-to-cart="cart.addToCart"
            @remove-from-cart="cart.removeFromCart"
            @close="gallery.closeGallery()"
        />

        <StoreCartBar
            :is-mounted="isMounted"
            :is-authenticated="cart.isAuthenticated()"
            :cart-count="cart.cartCount.value"
            :cart-total="formatPrice(cart.cartTotal.value)"
            @clear-cart="cart.clearCart()"
        />
    </div>
</template>