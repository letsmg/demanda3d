<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import ClientHeader from '@/components/ClientHeader.vue';
import FormTestHelper, {
    type TestField,
} from '@/components/FormTestHelper.vue';

const props = defineProps<{
    client: any;
}>();

const form = useForm({
    address: props.client.address || '',
    number: props.client.number || '',
    state: props.client.state || '',
    zipcode: props.client.zipcode || '',
    city: props.client.city || '',
});

const testFields: TestField[] = [
    { key: 'address', value: 'Rua Augusta' },
    { key: 'number', value: '1500' },
    { key: 'city', value: 'São Paulo' },
    { key: 'state', value: 'SP' },
    { key: 'zipcode', value: '01310-100' },
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
    form.put('/perfil/enderecos', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Meus Endereços" />

    <div class="min-h-screen bg-amber-50">
        <ClientHeader :client="client" />

        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-bold tracking-tight text-amber-900">
                Meus Endereços
            </h1>

            <Card>
                <CardHeader>
                    <CardTitle>Endereço de Entrega</CardTitle>
                    <CardDescription
                        >Defina seu endereço principal para receber
                        pedidos</CardDescription
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

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="space-y-2 sm:col-span-2">
                                <Label for="address">Endereço</Label>
                                <Input
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Rua, Avenida..."
                                />
                                <span
                                    v-if="form.errors.address"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.address }}
                                </span>
                            </div>
                            <div class="space-y-2">
                                <Label for="number">Número</Label>
                                <Input
                                    id="number"
                                    v-model="form.number"
                                    placeholder="Nº"
                                />
                                <span
                                    v-if="form.errors.number"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.number }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="city">Cidade</Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    placeholder="São Paulo"
                                />
                                <span
                                    v-if="form.errors.city"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.city }}
                                </span>
                            </div>
                            <div class="space-y-2">
                                <Label for="state">UF</Label>
                                <Input
                                    id="state"
                                    v-model="form.state"
                                    placeholder="SP"
                                    maxlength="2"
                                />
                                <span
                                    v-if="form.errors.state"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.state }}
                                </span>
                            </div>
                            <div class="space-y-2">
                                <Label for="zipcode">CEP</Label>
                                <Input
                                    id="zipcode"
                                    v-model="form.zipcode"
                                    placeholder="00000-000"
                                />
                                <span
                                    v-if="form.errors.zipcode"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.zipcode }}
                                </span>
                            </div>
                        </div>
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
        </main>
    </div>
</template>
