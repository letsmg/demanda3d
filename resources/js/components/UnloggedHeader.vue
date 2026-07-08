<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { welcome, login } from '@/routes';
import storeRoutes from '@/routes/store';

const navigation = [
    { name: 'Início', href: welcome.url() },
    { name: 'Loja', href: storeRoutes.index.url() },
];

const partnerLinks = [
    { name: 'Sou Vendedor', href: login.url(), highlight: true },
];

const clientLinks = [
    { name: 'Sou Cliente', href: '/login_cli', highlight: true },
];

const carrierLinks = [
    { name: 'Sou Transportadora', href: '/login_carrier', highlight: true },
];
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full border-b border-amber-700/30 bg-amber-950 shadow-md"
    >
        <div class="container mx-auto flex h-16 items-center px-4 md:px-8">
            <Link :href="welcome.url()" class="flex items-center gap-2">
                <AppLogoIcon class="size-8 fill-current text-white" />
                <span class="text-lg font-semibold text-white">Demanda 3D</span>
            </Link>

            <nav class="ml-auto flex items-center gap-2">
                <template v-for="item in navigation" :key="item.name">
                    <Link
                        :href="item.href"
                        class="text-sm font-medium text-amber-200 transition-colors hover:text-amber-50"
                    >
                        {{ item.name }}
                    </Link>
                </template>

                <div class="mx-2 h-5 w-px bg-amber-700/30"></div>

                <!-- Client links (Sou Cliente) -->
                <template v-for="item in clientLinks" :key="item.name">
                    <Button
                        v-if="item.highlight"
                        size="sm"
                        as-child
                        class="hidden bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400 sm:flex"
                    >
                        <Link :href="item.href">{{ item.name }}</Link>
                    </Button>
                </template>

                <!-- Carrier links (Sou Transportadora) -->
                <template v-for="item in carrierLinks" :key="item.name">
                    <Button
                        v-if="item.highlight"
                        size="sm"
                        as-child
                        class="hidden bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400 sm:flex"
                    >
                        <Link :href="item.href">{{ item.name }}</Link>
                    </Button>
                </template>

                <!-- Partner links (Sou Vendedor) -->
                <template v-for="item in partnerLinks" :key="item.name">
                    <Button
                        v-if="item.highlight"
                        size="sm"
                        as-child
                        class="hidden bg-amber-500 font-semibold text-amber-950 hover:bg-amber-400 sm:flex"
                    >
                        <Link :href="item.href">{{ item.name }}</Link>
                    </Button>
                </template>

                <!-- Mobile menu button -->
                <div class="sm:hidden">
                    <Link
                        :href="login.url()"
                        class="inline-flex items-center justify-center rounded-md p-2 text-amber-200 hover:bg-amber-800 hover:text-amber-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                            />
                        </svg>
                    </Link>
                </div>
            </nav>
        </div>
    </header>
</template>
