<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import UnloggedFooter from '@/components/UnloggedFooter.vue';
import UnloggedHeader from '@/components/UnloggedHeader.vue';
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
