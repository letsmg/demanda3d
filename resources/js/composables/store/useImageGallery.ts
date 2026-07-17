import { ref } from 'vue';

export function useImageGallery() {
    const selectedProduct = ref<any>(null);
    const currentImageIndex = ref(0);

    function openGallery(product: any, index: number = 0): void {
        selectedProduct.value = product;
        currentImageIndex.value = index;
    }

    function closeGallery(): void {
        selectedProduct.value = null;
        currentImageIndex.value = 0;
    }

    function prevImage(): void {
        if (!selectedProduct.value?.images?.length) {
            return;
        }

        currentImageIndex.value =
            (currentImageIndex.value - 1 + selectedProduct.value.images.length) %
            selectedProduct.value.images.length;
    }

    function nextImage(): void {
        if (!selectedProduct.value?.images?.length) {
            return;
        }

        currentImageIndex.value =
            (currentImageIndex.value + 1) % selectedProduct.value.images.length;
    }

    return {
        selectedProduct,
        currentImageIndex,
        openGallery,
        closeGallery,
        prevImage,
        nextImage,
    };
}