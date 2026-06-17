<script setup lang="ts">
import UnloggedHeader from '@/components/UnloggedHeader.vue';
import UnloggedFooter from '@/components/UnloggedFooter.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <UnloggedHeader />
        <main class="flex-1">
            <slot />
        </main>
        <UnloggedFooter />
        <Toaster />
    </div>
</template>