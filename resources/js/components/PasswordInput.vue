<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { HTMLAttributes } from 'vue';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        show?: boolean;
    }>(),
    {
        show: undefined,
    },
);

const emit = defineEmits<{
    'update:show': [value: boolean];
}>();

const internalShow = ref(false);

const showPassword = computed({
    get: () => (props.show !== undefined ? props.show : internalShow.value),
    set: (val: boolean) => {
        if (props.show !== undefined) {
            emit('update:show', val);
        } else {
            internalShow.value = val;
        }
    },
});

function toggle() {
    showPassword.value = !showPassword.value;
}
</script>

<template>
    <div class="relative">
        <Input
            :type="showPassword ? 'text' : 'password'"
            :class="cn('pr-10', props.class)"
            v-bind="$attrs"
        />
        <button
            type="button"
            @click="toggle"
            :class="
                cn(
                    'absolute inset-y-0 right-0 flex items-center rounded-r-md px-3 text-muted-foreground hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:outline-none',
                )
            "
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
            :tabindex="-1"
        >
            <EyeOff v-if="showPassword" class="size-4" />
            <Eye v-else class="size-4" />
        </button>
    </div>
</template>
