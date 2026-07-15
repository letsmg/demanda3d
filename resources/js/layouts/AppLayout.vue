<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import ConsentBanner from '@/components/ConsentBanner.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <ConsentBanner />
    </AppLayout>
</template>
