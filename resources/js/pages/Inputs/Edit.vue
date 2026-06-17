<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { Input as InputType } from '@/types';

const props = defineProps<{ input: InputType }>();

const form = ref({
    filaments: props.input.filaments,
    energy: String(props.input.energy),
    dt_buy: props.input.dt_buy,
    cost_buy: String(props.input.cost_buy),
    purge: String(props.input.purge),
});

const errors = ref<Record<string, string>>({});
const loading = ref(false);

const submit = async () => {
    loading.value = true;
    errors.value = {};
    try {
        const response = await fetch(`/api/inputs/${props.input.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                ...form.value,
                energy: parseFloat(form.value.energy),
                cost_buy: parseFloat(form.value.cost_buy),
                purge: parseFloat(form.value.purge),
            }),
        });
        if (!response.ok) {
            const data = await response.json();
            errors.value = data.errors || {};
            if (data.message && !data.errors) errors.value._general = data.message;
        } else {
            router.visit(route('inputs.index'));
        }
    } catch (error) {
        errors.value._general = 'An unexpected error occurred.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head :title="`Edit ${input.filaments}`" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="route('inputs.index')"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Edit Input</h1>
                <p class="text-sm text-muted-foreground">Updating: {{ input.filaments }}</p>
            </div>
        </div>

        <Alert v-if="errors._general" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{{ errors._general }}</AlertDescription>
        </Alert>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Input Details</CardTitle>
                    <CardDescription>Update the material/resource information</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="filaments">Filament / Material *</Label>
                        <Input id="filaments" v-model="form.filaments" :class="{ 'border-destructive': errors.filaments }" />
                        <span v-if="errors.filaments" class="text-sm text-destructive">{{ errors.filaments }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="dt_buy">Purchase Date *</Label>
                            <Input id="dt_buy" type="date" v-model="form.dt_buy" :class="{ 'border-destructive': errors.dt_buy }" />
                            <span v-if="errors.dt_buy" class="text-sm text-destructive">{{ errors.dt_buy }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="cost_buy">Purchase Cost (R$) *</Label>
                            <Input id="cost_buy" type="number" step="0.01" min="0" v-model="form.cost_buy" :class="{ 'border-destructive': errors.cost_buy }" />
                            <span v-if="errors.cost_buy" class="text-sm text-destructive">{{ errors.cost_buy }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="energy">Energy Cost (R$) *</Label>
                            <Input id="energy" type="number" step="0.01" min="0" v-model="form.energy" :class="{ 'border-destructive': errors.energy }" />
                            <span v-if="errors.energy" class="text-sm text-destructive">{{ errors.energy }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="purge">Purge (grams)</Label>
                            <Input id="purge" type="number" step="0.1" min="0" v-model="form.purge" :class="{ 'border-destructive': errors.purge }" />
                            <span v-if="errors.purge" class="text-sm text-destructive">{{ errors.purge }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="route('inputs.index')">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="loading">
                    <Save class="mr-2 h-4 w-4" />
                    {{ loading ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>