<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import FormTestHelper, {
    type TestField,
} from '@/components/FormTestHelper.vue';
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

const props = defineProps<{
    client: any;
}>();

const form = useForm({
    display_name: props.client.display_name || '',
    email: props.client.email || '',
});

const testFields: TestField[] = [
    { key: 'display_name', value: 'Tech3D Soluções Ltda' },
    { key: 'email', value: 'tech3d@demanda3d.com' },
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
    form.put('/perfil', { preserveScroll: true });
}
</script>

<template>
    <Head title="Meu Perfil" />

    <h1 class="mb-6 text-2xl font-bold tracking-tight text-amber-900">
        Meu Perfil
    </h1>

    <Card>
        <CardHeader>
            <CardTitle>Informações Pessoais</CardTitle>
            <CardDescription>Atualize seus dados de cadastro</CardDescription>
        </CardHeader>
        <form @submit.prevent="submit">
            <CardContent class="space-y-4">
                <FormTestHelper
                    :form="form"
                    :fields="testFields"
                    label="Perfil"
                    @fill="handleFill"
                    @clear="handleClear"
                />

                <div class="space-y-2">
                    <Label for="display_name">Nome / Empresa</Label>
                    <Input
                        id="display_name"
                        v-model="form.display_name"
                        required
                        placeholder="Seu nome ou empresa"
                    />
                    <span
                        v-if="form.errors.display_name"
                        class="text-sm text-destructive"
                        >{{ form.errors.display_name }}</span
                    >
                </div>
                <div class="space-y-2">
                    <Label for="email">E-mail</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        placeholder="seu@email.com"
                    />
                    <span
                        v-if="form.errors.email"
                        class="text-sm text-destructive"
                        >{{ form.errors.email }}</span
                    >
                </div>
            </CardContent>
            <CardFooter class="border-t px-6 py-4">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    <Save class="mr-2 h-4 w-4" />
                    Salvar alterações
                </Button>
            </CardFooter>
        </form>
    </Card>
</template>
