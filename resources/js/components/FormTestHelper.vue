<script setup lang="ts">
import { Beaker, Eraser } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

export interface TestField {
    key: string;
    value: string | number;
}

const props = defineProps<{
    form: Record<string, any>;
    fields: TestField[];
    label?: string;
}>();

const emit = defineEmits<{
    fill: [fields: TestField[]];
    clear: [fields: TestField[]];
}>();

function fillForm() {
    emit('fill', props.fields);
}

function clearForm() {
    emit('clear', props.fields);
}
</script>

<template>
    <div
        class="flex items-center gap-2 rounded-lg border border-dashed border-primary/30 bg-primary/5 p-3"
    >
        <span class="text-xs font-medium text-muted-foreground">
            🧪 {{ label || 'Testes rápidos' }}
        </span>
        <div class="ml-auto flex items-center gap-1.5">
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="h-7 text-xs"
                @click="clearForm"
            >
                <Eraser class="mr-1 h-3 w-3" />
                Limpar
            </Button>
            <Button
                type="button"
                variant="secondary"
                size="sm"
                class="h-7 text-xs"
                @click="fillForm"
            >
                <Beaker class="mr-1 h-3 w-3" />
                Preencher Teste
            </Button>
        </div>
    </div>
</template>
