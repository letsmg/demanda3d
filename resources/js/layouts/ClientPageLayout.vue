<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const clientUser = computed(() => (page.props as any).auth_client?.user);
</script>

<template>
    <div class="min-h-screen bg-amber-50">
        <!-- Client-specific header with cart and profile menu only -->
        <header
            class="sticky top-0 z-50 w-full border-b border-amber-700/30 bg-amber-950 shadow-md"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="/store" class="flex items-center gap-2">
                        <svg
                            class="h-8 w-8 text-amber-910"
                            viewBox="0 0 32 32"
                            fill="currentColor"
                        >
                            <path
                                d="M16 2L2 9l14 7 14-7L16 2zM2 23l14 7 14-7M2 16l14 7 14-7"
                                stroke="currentColor"
                                stroke-width="2"
                                fill="none"
                            />
                        </svg>
                        <span class="text-lg font-bold text-white"
                            >Demanda3D</span
                        >
                    </a>

                    <nav class="flex items-center gap-3">
                        <!-- Cart icon -->
                        <a
                            href="/cart"
                            class="relative inline-flex items-center justify-center rounded-md p-2 text-amber-200 transition hover:bg-amber-800 hover:text-amber-100"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"
                                />
                            </svg>
                        </a>

                        <!-- User menu -->
                        <div class="relative" v-if="clientUser">
                            <div class="flex items-center gap-2 text-amber-100">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-600 text-xs font-medium text-white"
                                >
                                    {{
                                        (clientUser.display_name || '?')
                                            .substring(0, 2)
                                            .toUpperCase()
                                    }}
                                </div>
                                <span
                                    class="hidden max-w-[150px] truncate text-sm font-medium sm:inline-block"
                                >
                                    {{ clientUser.display_name || 'Cliente' }}
                                </span>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Client navigation tabs -->
        <nav class="border-b border-amber-200 bg-white shadow-sm">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 overflow-x-auto">
                    <a
                        href="/perfil"
                        class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-amber-600 hover:border-amber-900 hover:text-amber-700"
                    >
                        Meu Perfil
                    </a>
                    <a
                        href="/perfil/enderecos"
                        class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-amber-600 hover:border-amber-900 hover:text-amber-700"
                    >
                        Meus Endereços
                    </a>
                    <a
                        href="/perfil/pedidos"
                        class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-amber-600 hover:border-amber-900 hover:text-amber-700"
                    >
                        Meus Pedidos
                    </a>
                    <a
                        href="/store"
                        class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-amber-600 hover:border-amber-900 hover:text-amber-700"
                    >
                        Store
                    </a>
                    <form
                        method="POST"
                        action="/logout_cli"
                        class="ml-auto flex items-center"
                    >
                        <input
                            type="hidden"
                            name="_token"
                            :value="(page.props as any).csrf_token"
                        />
                        <button
                            type="submit"
                            class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-rose-500 hover:border-rose-300 hover:text-rose-600"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Page content -->
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
