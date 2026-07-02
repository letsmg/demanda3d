<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <footer class="border-t border-amber-700/30 bg-amber-950">
        <div class="container mx-auto px-4 py-8 md:px-8">
            <div
                class="flex flex-col items-center justify-between gap-4 md:flex-row"
            >
                <div
                    class="flex items-center gap-2 text-sm text-amber-300"
                >
                    <AppLogoIcon class="size-5 fill-current text-amber-400" />
                    <span
                        >&copy; {{ new Date().getFullYear() }} Demanda3D. All
                        rights reserved.</span
                    >
                </div>
                <p class="text-xs text-amber-400/70">
                    Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
                </p>

                <nav class="flex items-center gap-4 text-sm text-amber-300">
                    <a
                        :href="'/legal/terms'"
                        class="transition-colors hover:text-amber-100"
                    >
                        Termos de Uso
                    </a>
                    <a
                        :href="'/legal/privacy'"
                        class="transition-colors hover:text-amber-100"
                    >
                        Política de Privacidade
                    </a>
                    <span class="hidden sm:inline text-amber-400/70">|</span>
                    <a
                        href="mailto:contato@demanda3d.com.br"
                        class="transition-colors hover:text-amber-100"
                    >
                        contato@demanda3d.com.br
                    </a>
                    <span class="hidden sm:inline text-amber-400/70">3D Printing Solutions</span>
                </nav>
            </div>
        </div>
    </footer>
</template>
