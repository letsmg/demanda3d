<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import UnloggedFooter from '@/components/UnloggedFooter.vue';
import UnloggedHeader from '@/components/UnloggedHeader.vue';
import ClientHeader from '@/components/ClientHeader.vue';

const page = usePage();
const authClient = computed(() => (page.props as any).auth_client);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <ClientHeader v-if="authClient?.user" :client="authClient.user" />
        <UnloggedHeader v-else />
        <main class="flex-1">
            <slot />
        </main>
        <UnloggedFooter />
        <Toaster />
    </div>
</template>