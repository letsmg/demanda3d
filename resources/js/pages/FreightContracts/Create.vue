<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Save } from 'lucide-vue-next';
import FormTestHelper from '@/components/FormTestHelper.vue';
import type {TestField} from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

defineProps<{ carriers: { id: number; name: string }[] }>();

const form = useForm({
    carrier_id: '',
    pickup_location: '',
    delivery_location: '',
    cargo_description: '',
    pickup_date: '',
    estimated_delivery_date: '',
    freight_value: '',
    freight_paid: false,
    status: 'pending',
    notes: '',
});

const testFields: TestField[] = [
    { key: 'pickup_location', value: 'Av. Paulista, 1000, São Paulo, SP' },
    { key: 'delivery_location', value: 'Rua das Palmeiras, 500, Campinas, SP' },
    { key: 'cargo_description', value: 'Caixas com filamentos PLA (10kg)' },
    { key: 'freight_value', value: '350.00' },
];

function handleFill() {
    form.pickup_location =
        'Av. Paulista, ' +
        Math.floor(Math.random() * 9000 + 100) +
        ', São Paulo, SP';
    form.delivery_location =
        'Rua das Palmeiras, ' +
        Math.floor(Math.random() * 900 + 100) +
        ', Campinas, SP';
    form.cargo_description =
        'Caixas com filamentos PLA (' +
        Math.floor(Math.random() * 20 + 5) +
        'kg)';
    form.pickup_date = new Date().toISOString().split('T')[0];
    const est = new Date();
    est.setDate(est.getDate() + Math.floor(Math.random() * 7 + 3));
    form.estimated_delivery_date = est.toISOString().split('T')[0];
    form.freight_value = String((Math.random() * 800 + 150).toFixed(2));
    form.freight_paid = false;
    form.status = 'pending';
}

function handleClear() {
    form.reset();
}

const submit = () => {
    form.post('/freight-contracts', { preserveScroll: true });
};
</script>

<template>
    <Head title="Novo Contrato de Frete" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child
                ><Link href="/freight-contracts"
                    ><ArrowLeft class="h-4 w-4" /></Link
            ></Button>
            <div>
                <h1 class="text-2xl font-bold">Novo Contrato de Frete</h1>
                <p class="text-sm text-muted-foreground">
                    Registrar frete contratado
                </p>
            </div>
        </div>
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive"
            ><AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle
            ><AlertDescription>Verifique os campos.</AlertDescription></Alert
        >
        <FormTestHelper
            :form="form"
            :fields="testFields"
            label="Contrato teste"
            @fill="handleFill"
            @clear="handleClear"
        />
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>Dados do Frete</CardTitle></CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="carrier_id">Transportadora *</Label>
                        <Select v-model="form.carrier_id"
                            ><SelectTrigger
                                ><SelectValue
                                    placeholder="Selecione" /></SelectTrigger
                            ><SelectContent
                                ><SelectItem
                                    v-for="c in carriers"
                                    :key="c.id"
                                    :value="String(c.id)"
                                    >{{ c.name }}</SelectItem
                                ></SelectContent
                            ></Select
                        >
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="pickup_location"
                                >Local de Coleta *</Label
                            ><Input
                                id="pickup_location"
                                v-model="form.pickup_location"
                                maxlength="500"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="delivery_location"
                                >Local de Entrega *</Label
                            ><Input
                                id="delivery_location"
                                v-model="form.delivery_location"
                                maxlength="500"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="cargo_description"
                            >Descrição da Carga *</Label
                        ><Input
                            id="cargo_description"
                            v-model="form.cargo_description"
                            maxlength="500"
                        />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="pickup_date">Data Coleta *</Label
                            ><Input
                                id="pickup_date"
                                type="date"
                                v-model="form.pickup_date"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="estimated_delivery_date"
                                >Entrega Estimada *</Label
                            ><Input
                                id="estimated_delivery_date"
                                type="date"
                                v-model="form.estimated_delivery_date"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="freight_value">Valor Frete *</Label
                            ><Input
                                id="freight_value"
                                type="number"
                                step="0.01"
                                v-model="form.freight_value"
                            />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="freight_paid">Frete Pago</Label
                            ><Select v-model="form.freight_paid"
                                ><SelectTrigger><SelectValue /></SelectTrigger
                                ><SelectContent
                                    ><SelectItem :value="true">Sim</SelectItem
                                    ><SelectItem :value="false"
                                        >Não</SelectItem
                                    ></SelectContent
                                ></Select
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="status">Status</Label
                            ><Select v-model="form.status"
                                ><SelectTrigger><SelectValue /></SelectTrigger
                                ><SelectContent
                                    ><SelectItem value="pending"
                                        >Pendente</SelectItem
                                    ><SelectItem value="in_transit"
                                        >Em trânsito</SelectItem
                                    ><SelectItem value="delivered"
                                        >Entregue</SelectItem
                                    ><SelectItem value="cancelled"
                                        >Cancelado</SelectItem
                                    ></SelectContent
                                ></Select
                            >
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="notes">Observações</Label
                        ><Textarea id="notes" v-model="form.notes" rows="3" />
                    </div>
                </CardContent>
            </Card>
            <div class="mt-6 flex justify-end gap-3">
                <Button variant="outline" as-child
                    ><Link href="/freight-contracts">Cancelar</Link></Button
                >
                <Button type="submit" :disabled="form.processing"
                    ><Save class="mr-2 h-4 w-4" />{{
                        form.processing ? 'Salvando...' : 'Salvar'
                    }}</Button
                >
            </div>
        </form>
    </div>
</template>
