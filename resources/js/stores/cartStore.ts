import { ref } from 'vue';

export const cartCount = ref(0);

export function setCartCount(count: number): void {
    cartCount.value = count;
}

export function incrementCartCount(by = 1): void {
    cartCount.value += by;
}

export function decrementCartCount(by = 1): void {
    cartCount.value = Math.max(0, cartCount.value - by);
}