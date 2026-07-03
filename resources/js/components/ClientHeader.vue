<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { User, MapPin, LogOut, ShoppingBag } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cartCount, setCartCount } from '@/stores/cartStore';

const props = defineProps<{
    client: any;
}>();

function getCsrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta as HTMLMetaElement).content : '';
}

async function fetchCartCount() {
    try {
        const res = await fetch('/cart/items', {
            credentials: 'include',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        });
        if (res.ok) {
            const data = await res.json();
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
    if (!props.client?.display_name) {
        return '?';
    }
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
    <header
        class="sticky top-0 z-50 w-full border-b border-amber-700/30 bg-amber-950 shadow-md"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <Link href="/store" class="flex items-center gap-2">
                    <AppLogo class="brightness-0 invert" />
                </Link>

                <nav class="flex items-center gap-3">
                    <!-- Cart icon with badge -->
                    <Link
                        href="/cart"
                        class="relative inline-flex items-center justify-center rounded-md p-2 text-amber-200 transition hover:bg-amber-800 hover:text-amber-100"
                    >
                        <ShoppingBag class="h-5 w-5" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[11px] font-bold text-white shadow"
                        >
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>

                    <!-- User menu -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                class="relative h-9 gap-2 px-2 text-amber-100 hover:bg-amber-800 hover:text-amber-50"
                            >
                                <Avatar class="h-8 w-8">
                                    <AvatarFallback
                                        class="bg-amber-600 text-xs font-medium text-white"
                                    >
                                        {{ initials }}
                                    </AvatarFallback>
                                </Avatar>
                                <span
                                    class="hidden max-w-[150px] truncate text-sm font-medium sm:inline-block"
                                >
                                    {{ client.display_name || 'Cliente' }}
                                </span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            class="w-56 border-amber-200 bg-white shadow-lg"
                            align="end"
                        >
                            <DropdownMenuLabel class="font-normal">
                                <div class="flex flex-col space-y-1">
                                    <p
                                        class="text-sm leading-none font-medium text-amber-900"
                                    >
                                        {{ client.display_name }}
                                    </p>
                                    <p
                                        class="text-xs leading-none text-amber-500"
                                    >
                                        {{ client.email }}
                                    </p>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator class="bg-amber-100" />
                            <DropdownMenuItem
                                as-child
                                class="text-amber-800 focus:bg-amber-50 focus:text-amber-900"
                            >
                                <Link href="/perfil" class="cursor-pointer">
                                    <User class="mr-2 h-4 w-4" />
                                    <span>Meu Perfil</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                as-child
                                class="text-amber-800 focus:bg-amber-50 focus:text-amber-900"
                            >
                                <Link
                                    href="/perfil/enderecos"
                                    class="cursor-pointer"
                                >
                                    <MapPin class="mr-2 h-4 w-4" />
                                    <span>Meus Endereços</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator class="bg-amber-100" />
                            <DropdownMenuItem
                                @click="logout"
                                class="cursor-pointer text-rose-600 focus:bg-rose-50 focus:text-rose-700"
                            >
                                <LogOut class="mr-2 h-4 w-4" />
                                <span>Logout</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </nav>
            </div>
        </div>
    </header>
</template>
