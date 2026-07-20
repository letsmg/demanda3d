<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ShoppingBag, Menu, X } from 'lucide-vue-next';
import { setCartCount } from '@/stores/cartStore';

const props = defineProps<{
    client: any;
}>();

const mobileMenuOpen = ref(false);

function getCsrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta as HTMLMetaElement).content : '';
}

const cartCount = ref(0);

async function fetchCartCount() {
    try {
        const res = await fetch('/cart/items', {
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        });
        if (res.ok) {
            const data = await res.json();
            cartCount.value = data.count || 0;
            setCartCount(data.count || 0);
        }
    } catch {
        // ignore
    }
}

onMounted(() => {
    fetchCartCount();
});

const initials = computed(() => {
    if (!props.client?.display_name) return '?';
    const parts = props.client.display_name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return props.client.display_name.substring(0, 2).toUpperCase();
});

function logout() {
    router.post('/logout_cli');
}
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b border-amber-700/30 bg-amber-950 shadow-md">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <Link href="/store" class="flex items-center gap-2 text-xl font-bold text-white">
                    🏪 Demanda3D
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center gap-4">
                    <Link href="/store" class="text-sm font-medium text-amber-200 hover:text-white transition">
                        Loja
                    </Link>
                    <Link href="/perfil" class="text-sm font-medium text-amber-200 hover:text-white transition">
                        Meu Perfil
                    </Link>
                    <Link href="/perfil/enderecos" class="text-sm font-medium text-amber-200 hover:text-white transition">
                        Meus Endereços
                    </Link>
                    <Link href="/perfil/pedidos" class="text-sm font-medium text-amber-200 hover:text-white transition">
                        Meus Pedidos
                    </Link>

                    <!-- Cart -->
                    <Link href="/cart" class="relative inline-flex items-center justify-center rounded-md p-2 text-amber-200 hover:bg-amber-800 hover:text-amber-100 transition">
                        <ShoppingBag class="h-5 w-5" />
                        <span v-if="cartCount > 0" class="absolute -top-1 -right-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[11px] font-bold text-white shadow">
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>

                    <!-- User avatar + logout -->
                    <div class="flex items-center gap-2 ml-4 pl-4 border-l border-amber-700">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-600 text-xs font-medium text-white">
                            {{ initials }}
                        </div>
                        <span class="text-sm text-amber-200 max-w-[120px] truncate">{{ client.display_name || 'Cliente' }}</span>
                        <button @click="logout" class="text-xs text-amber-300 hover:text-amber-100 ml-2 transition">
                            Sair
                        </button>
                    </div>
                </nav>

                <!-- Mobile hamburger -->
                <div class="flex items-center gap-2 md:hidden">
                    <Link href="/cart" class="relative inline-flex items-center justify-center rounded-md p-2 text-amber-200 hover:bg-amber-800 transition">
                        <ShoppingBag class="h-5 w-5" />
                        <span v-if="cartCount > 0" class="absolute -top-1 -right-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[11px] font-bold text-white shadow">
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-amber-200 hover:bg-amber-800 rounded-md transition">
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-2">
                <Link href="/store" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm text-amber-200 hover:bg-amber-800 rounded-md transition">
                    Loja
                </Link>
                <Link href="/perfil" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm text-amber-200 hover:bg-amber-800 rounded-md transition">
                    Meu Perfil
                </Link>
                <Link href="/perfil/enderecos" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm text-amber-200 hover:bg-amber-800 rounded-md transition">
                    Meus Endereços
                </Link>
                <Link href="/perfil/pedidos" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm text-amber-200 hover:bg-amber-800 rounded-md transition">
                    Meus Pedidos
                </Link>
                <div class="flex items-center gap-2 px-3 py-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-600 text-xs font-medium text-white">
                        {{ initials }}
                    </div>
                    <span class="text-sm text-amber-200">{{ client.display_name || 'Cliente' }}</span>
                </div>
                <button @click="logout" class="block w-full text-left px-3 py-2 text-sm text-amber-300 hover:bg-amber-800 rounded-md transition">
                    Sair
                </button>
            </div>
        </div>
    </header>
</template>