<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import ConsentBanner from '@/components/ConsentBanner.vue';
import UnloggedFooter from '@/components/UnloggedFooter.vue';
import UnloggedHeader from '@/components/UnloggedHeader.vue';
import ClientHeader from '@/components/ClientHeader.vue';

const page = usePage();
const authClient = computed(() => (page.props as any).auth_client?.user);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-amber-50">
        <ClientHeader v-if="authClient" :client="authClient" />
        <UnloggedHeader v-else />
        <main class="flex-1">
            <slot />
        </main>
        <UnloggedFooter />
        <ConsentBanner />
        <Toaster />
    </div>
</template>
