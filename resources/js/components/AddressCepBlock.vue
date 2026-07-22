<script setup lang="ts">
import { Lock } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCep } from '@/composables/useCep';

const props = defineProps<{
    zipcode: string;
    state: string;
    city: string;
    address: string;
    number: string;
    zipcodeError?: string;
    stateError?: string;
    cityError?: string;
    addressError?: string;
    numberError?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:zipcode': [value: string];
    'update:state': [value: string];
    'update:stateId': [value: number | null];
    'update:city': [value: string];
    'update:address': [value: string];
    'update:number': [value: string];
}>();

const { loadingCep, isStateLocked, fetchCep, resetCep } = useCep();

const fieldsEnabled = computed(() => isStateLocked.value && !props.disabled);

async function onCepBlur() {
    const digits = (props.zipcode || '').replace(/\D/g, '');

    if (digits.length < 8) {
        return;
    }

    const data = await fetchCep(props.zipcode);

    if (data.state_id) {
        emit('update:stateId', data.state_id);
        emit('update:state', data.uf || '');
    }
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <p class="mb-4 text-sm font-medium text-muted-foreground">Endereço</p>

        <!-- CEP (primeiro campo) -->
        <div class="mb-4">
            <Label for="zipcode" class="mb-1 block">
                CEP *
                <span class="text-xs text-muted-foreground"
                    >(digite primeiro)</span
                >
            </Label>
            <div class="flex gap-2">
                <Input
                    id="zipcode"
                    :model-value="zipcode"
                    placeholder="00000-000"
                    maxlength="9"
                    :class="{
                        'border-destructive': zipcodeError,
                        'flex-1': true,
                    }"
                    :disabled="disabled"
                    @blur="onCepBlur"
                    @update:model-value="
                        emit('update:zipcode', $event as string)
                    "
                />
                <span
                    v-if="loadingCep"
                    class="mt-2 text-xs text-muted-foreground"
                    >🔍 Buscando...</span
                >
            </div>
            <span v-if="zipcodeError" class="text-sm text-destructive">{{
                zipcodeError
            }}</span>
        </div>

        <!-- Estado (bloqueado após CEP preenchido) -->
        <div class="mb-4">
            <Label for="state">UF *</Label>
            <div class="relative">
                <Input
                    id="state"
                    :model-value="state"
                    placeholder="SP"
                    maxlength="2"
                    :disabled="isStateLocked || disabled"
                    :class="{
                        'border-destructive': stateError,
                        'bg-muted': isStateLocked,
                    }"
                    @update:model-value="emit('update:state', $event as string)"
                />
                <Lock
                    v-if="isStateLocked"
                    class="absolute top-2.5 right-3 h-4 w-4 text-muted-foreground"
                />
            </div>
            <span v-if="stateError" class="text-sm text-destructive">{{
                stateError
            }}</span>
            <p v-if="isStateLocked" class="mt-1 text-xs text-muted-foreground">
                Estado preenchido automaticamente pelo CEP. Para alterar,
                corrija o CEP.
            </p>
        </div>

        <!-- Cidade, Endereço, Número -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-2">
                <Label for="city">Cidade *</Label>
                <Input
                    id="city"
                    :model-value="city"
                    placeholder="São Paulo"
                    :disabled="!fieldsEnabled"
                    :class="{
                        'border-destructive': cityError,
                        'bg-muted opacity-50': !fieldsEnabled,
                    }"
                    @update:model-value="emit('update:city', $event as string)"
                />
                <span v-if="cityError" class="text-sm text-destructive">{{
                    cityError
                }}</span>
            </div>
            <div class="space-y-2 sm:col-span-2">
                <Label for="address">Endereço *</Label>
                <Input
                    id="address"
                    :model-value="address"
                    placeholder="Rua, Avenida..."
                    :disabled="!fieldsEnabled"
                    :class="{
                        'border-destructive': addressError,
                        'bg-muted opacity-50': !fieldsEnabled,
                    }"
                    @update:model-value="
                        emit('update:address', $event as string)
                    "
                />
                <span v-if="addressError" class="text-sm text-destructive">{{
                    addressError
                }}</span>
            </div>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div class="space-y-2">
                <Label for="number">Número *</Label>
                <Input
                    id="number"
                    :model-value="number"
                    placeholder="123"
                    :disabled="!fieldsEnabled"
                    :class="{
                        'border-destructive': numberError,
                        'bg-muted opacity-50': !fieldsEnabled,
                    }"
                    @update:model-value="
                        emit('update:number', $event as string)
                    "
                />
                <span v-if="numberError" class="text-sm text-destructive">{{
                    numberError
                }}</span>
            </div>
        </div>
    </div>
</template>
