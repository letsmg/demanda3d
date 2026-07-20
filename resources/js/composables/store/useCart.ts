import { ref } from 'vue';
import { setCartCount } from '@/stores/cartStore';

function csrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? (meta as HTMLMetaElement).content : '';
}

export function useCart() {
    const cartItems = ref<any[]>([]);
    const cartTotal = ref(0);
    const cartCount = ref(0);
    const cartLoading = ref(false);

    function isAuthenticated(): boolean {
        const page = (window as any).__inertia_page?.props;

        return !!(page?.auth_client?.user);
    }

    async function fetchCartData(): Promise<void> {
        if (!isAuthenticated()) {
            return;
        }

        try {
            const res = await fetch('/cart/items', { credentials: 'include' });

            if (res.ok) {
                const data = await res.json();

                cartItems.value = data.items || [];
                cartTotal.value = data.total || 0;
                cartCount.value = data.count || 0;
                setCartCount(data.count || 0);
            }
        } catch {
            // ignore (401 for unauthenticated visitors is expected)
        }
    }

    async function addToCart(productId: number): Promise<void> {
        if (!isAuthenticated()) {
            window.location.href = '/login_cli';

            return;
        }

        cartLoading.value = true;

        try {
            const res = await fetch('/cart', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 }),
            });

            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = '/login_cli';
                }

                return;
            }

            const contentType = res.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                return;
            }

            const data = await res.json();

            cartItems.value = data.items || [];
            cartTotal.value = data.total || 0;
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        } catch {
            // Erro de rede ou JSON inválido — ignora silenciosamente
        } finally {
            cartLoading.value = false;
        }
    }

    async function removeFromCart(cartItemId: number): Promise<void> {
        const item = cartItems.value.find((i) => i.id === cartItemId);

        if (!item) {
            return;
        }

        if (item.quantity <= 1) {
            await removeCartItem(cartItemId);

            return;
        }

        try {
            const res = await fetch('/cart/' + cartItemId, {
                method: 'PUT',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ quantity: item.quantity - 1 }),
            });

            if (res.ok) {
                const data = await res.json();

                cartItems.value = data.items || [];
                cartTotal.value = data.total || 0;
                cartCount.value = data.count || 0;
                setCartCount(data.count || 0);
            }
        } catch {
            // ignore
        }
    }

    async function removeCartItem(cartItemId: number): Promise<void> {
        try {
            const res = await fetch('/cart/' + cartItemId, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'X-CSRF-TOKEN': csrfToken() },
            });

            if (res.ok) {
                const data = await res.json();

                cartItems.value = data.items || [];
                cartTotal.value = data.total || 0;
                cartCount.value = data.count || 0;
                setCartCount(data.count || 0);
            }
        } catch {
            // ignore
        }
    }

    async function clearCart(): Promise<void> {
        try {
            const res = await fetch('/cart/clear', {
                method: 'POST',
                credentials: 'include',
                headers: { 'X-CSRF-TOKEN': csrfToken() },
            });

            if (res.ok) {
                cartItems.value = [];
                cartTotal.value = 0;
                cartCount.value = 0;
                setCartCount(0);
            }
        } catch {
            // ignore
        }
    }

    function getCartQty(productId: number): number {
        const item = cartItems.value.find((i) => i.product_id === productId);

        return item ? item.quantity : 0;
    }

    function getCartItemId(productId: number): number | null {
        const item = cartItems.value.find((i) => i.product_id === productId);

        return item ? item.id : null;
    }

    return {
        cartItems,
        cartTotal,
        cartCount,
        cartLoading,
        isAuthenticated,
        fetchCartData,
        addToCart,
        removeFromCart,
        removeCartItem,
        clearCart,
        getCartQty,
        getCartItemId,
    };
}