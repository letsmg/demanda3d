<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/storage/imgs/site/logo.jpg"
        alt="Demanda 3D"
        :class="className ?? 'h-8 w-8'"
        v-bind="$attrs"
    />
</template>
