<script setup lang="ts">
import { computed } from 'vue';
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

const props = defineProps<{
    client: any;
    cartCount?: number;
}>();

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
    <header class="sticky top-0 z-50 w-full border-b border-border/40 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <Link href="/store" class="flex items-center gap-2">
                    <AppLogo />
                </Link>

                <nav class="flex items-center gap-3">
                    <!-- Cart badge -->
                    <div v-if="cartCount && cartCount > 0" class="relative">
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700">
                            <ShoppingBag class="h-4 w-4" />
                            {{ cartCount }}
                        </span>
                    </div>

                    <!-- User menu -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="relative h-9 gap-2 px-2">
                                <Avatar class="h-8 w-8">
                                    <AvatarFallback class="bg-primary/10 text-primary text-xs font-medium">
                                        {{ initials }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="hidden text-sm font-medium sm:inline-block max-w-[150px] truncate">
                                    {{ client.display_name || 'Cliente' }}
                                </span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-56" align="end">
                            <DropdownMenuLabel class="font-normal">
                                <div class="flex flex-col space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ client.display_name }}</p>
                                    <p class="text-xs leading-none text-muted-foreground">{{ client.email }}</p>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/perfil" class="cursor-pointer">
                                    <User class="mr-2 h-4 w-4" />
                                    <span>Meu Perfil</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link href="/perfil/enderecos" class="cursor-pointer">
                                    <MapPin class="mr-2 h-4 w-4" />
                                    <span>Meus Endereços</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="logout" class="cursor-pointer text-red-600 focus:text-red-600">
                                <LogOut class="mr-2 h-4 w-4" />
                                <span>Sair</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </nav>
            </div>
        </div>
    </header>
</template>