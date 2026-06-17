<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index as inputsIndex } from '@/routes/inputs';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { Input as InputType } from '@/types';

const props = defineProps<{
    input: InputType;
}>();

const form = useForm({
    filaments: props.input.filaments,
    energy: props.input.energy.toString(),
    dt_buy: props.input.dt_buy,
    cost_buy: props.input.cost_buy.toString(),
    purge: props.input.purge.toString(),
});

const submit = () => {
    form.put(`/inputs/${props.input.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Editar Insumo" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="inputsIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Editar Insumo</h1>
                <p class="text-sm text-muted-foreground">Editando: {{ props.input.filaments }}</p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Insumo</CardTitle>
                    <CardDescription>Edite os dados do material abaixo</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="filaments">Filamento / Material *</Label>
                        <Input id="filaments" v-model="form.filaments" placeholder="Ex: PLA 1.75mm" :class="{ 'border-destructive': form.errors.filaments }" />
                        <span v-if="form.errors.filaments" class="text-sm text-destructive">{{ form.errors.filaments }}</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="dt_buy">Data da Compra *</Label>
                            <Input id="dt_buy" type="date" v-model="form.dt_buy" :class="{ 'border-destructive': form.errors.dt_buy }" />
                            <span v-if="form.errors.dt_buy" class="text-sm text-destructive">{{ form.errors.dt_buy }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="cost_buy">Custo de Compra *</Label>
                            <Input id="cost_buy" type="number" step="0.01" v-model="form.cost_buy" placeholder="0.00" :class="{ 'border-destructive': form.errors.cost_buy }" />
                            <span v-if="form.errors.cost_buy" class="text-sm text-destructive">{{ form.errors.cost_buy }}</span>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="energy">Custo de Energia *</Label>
                            <Input id="energy" type="number" step="0.01" v-model="form.energy" placeholder="0.00" :class="{ 'border-destructive': form.errors.energy }" />
                            <span v-if="form.errors.energy" class="text-sm text-destructive">{{ form.errors.energy }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="purge">Purga (gramas) *</Label>
                            <Input id="purge" type="number" step="0.1" v-model="form.purge" placeholder="0.0" :class="{ 'border-destructive': form.errors.purge }" />
                            <span v-if="form.errors.purge" class="text-sm text-destructive">{{ form.errors.purge }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="inputsIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" /> {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                </Button>
            </div>
        </form>
    </div>
</template>
