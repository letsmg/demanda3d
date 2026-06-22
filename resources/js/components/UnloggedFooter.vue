<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <footer class="border-t border-border/40 bg-background">
        <div class="container mx-auto px-4 py-8 md:px-8">
            <div
                class="flex flex-col items-center justify-between gap-4 md:flex-row"
            >
                <div
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <AppLogoIcon class="size-5 fill-current" />
                    <span
                        >&copy; {{ new Date().getFullYear() }} Demanda3D. All
                        rights reserved.</span
                    >
                </div>
                <p class="text-xs text-muted-foreground">
                    Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
                </p>

                <div
                    class="flex items-center gap-6 text-sm text-muted-foreground"
                >
                    <a
                        href="mailto:contato@demanda3d.com.br"
                        class="transition-colors hover:text-foreground"
                    >
                        contato@demanda3d.com.br
                    </a>
                    <span class="hidden sm:inline">3D Printing Solutions</span>
                </div>
            </div>
        </div>
    </footer>
</template>
