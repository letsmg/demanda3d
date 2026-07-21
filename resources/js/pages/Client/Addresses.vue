<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import AddressCepBlock from '@/components/AddressCepBlock.vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import type {TestField} from '@/components/FormTestHelper.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    client: any;
}>();

const form = useForm({
    address: props.client.address || '',
    number: props.client.number || '',
    state: props.client.state || '',
    zipcode: props.client.zipcode || '',
    state_id: null as number | null,
    city: props.client.city || '',
});

const testFields: TestField[] = [
    { key: 'zipcode', value: '01310-100' },
    { key: 'address', value: 'Avenida Paulista' },
    { key: 'number', value: '1000' },
    { key: 'city', value: 'São Paulo' },
];

function handleFill(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = f.value;
        }
    }
}

function handleClear(fields: TestField[]) {
    for (const f of fields) {
        if (f.key in form) {
            (form as any)[f.key] = '';
        }
    }
}

function submit() {
    form.put('/perfil/enderecos', { preserveScroll: true });
}
</script>

<template>
    <Head title="Meus Endereços" />

    <h1 class="mb-6 text-2xl font-bold tracking-tight text-amber-900">
        Meus Endereços
    </h1>

    <Card>
        <CardHeader>
            <CardTitle>Endereço Principal</CardTitle>
            <CardDescription
                >Atualize seu endereço de entrega. Digite o CEP primeiro para
                preencher o estado automaticamente.</CardDescription
            >
        </CardHeader>
        <form @submit.prevent="submit">
            <CardContent class="space-y-4">
                <FormTestHelper
                    :form="form"
                    :fields="testFields"
                    label="Endereço"
                    @fill="handleFill"
                    @clear="handleClear"
                />

                <AddressCepBlock
                    :zipcode="form.zipcode"
                    :state="form.state"
                    :city="form.city"
                    :address="form.address"
                    :number="form.number"
                    :zipcode-error="form.errors.zipcode"
                    :state-error="form.errors.state"
                    :city-error="form.errors.city"
                    :address-error="form.errors.address"
                    :number-error="form.errors.number"
                    @update:zipcode="form.zipcode = $event"
                    @update:state="form.state = $event"
                    @update:state-id="form.state_id = $event"
                    @update:city="form.city = $event"
                    @update:address="form.address = $event"
                    @update:number="form.number = $event"
                />
            </CardContent>
            <CardFooter class="border-t px-6 py-4">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    <Save class="mr-2 h-4 w-4" />
                    Salvar endereço
                </Button>
            </CardFooter>
        </form>
    </Card>
</template>
