<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ClientHeader from '@/components/ClientHeader.vue';

const page = usePage();
const clientUser = computed(() => (page.props as any).auth_client?.user);
</script>

<template>
    <div class="min-h-screen bg-amber-50">
        <ClientHeader v-if="clientUser" :client="clientUser" />

        <!-- Page content -->
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>