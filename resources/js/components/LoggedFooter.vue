<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <footer class="border-t border-sidebar-border/80 bg-background">
        <div class="container mx-auto px-4 py-6 md:px-8">
            <div class="flex flex-col items-center justify-between gap-3 md:flex-row">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <AppLogoIcon class="size-4 fill-current" />
                    <span>&copy; {{ new Date().getFullYear() }} Demanda3D. All rights reserved.</span>
                </div>

                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                    <span class="hidden sm:inline">Sistema de Gerenciamento de Impressão 3D</span>
                    <span class="h-1 w-1 rounded-full bg-muted-foreground/30 hidden sm:inline-block"></span>
                    <span>v1.0.0</span>
                </div>
            </div>
        </div>
    </footer>
</template>