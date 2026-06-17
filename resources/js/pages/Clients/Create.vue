<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index as clientsIndex } from '@/routes/clients';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const form = useForm({
    name: '',
    doc: '',
    address: '',
    number: '',
    state: '',
    zipcode: '',
    city: '',
    phone1: '',
    phone2: '',
    contact1: '',
    contact2: '',
});

const submit = () => {
    form.post('/clients', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Criar Cliente" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="clientsIndex()">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Criar Cliente
                </h1>
                <p class="text-sm text-muted-foreground">
                    Cadastrar um novo cliente para serviços de impressão 3D
                </p>
            </div>
        </div>

        <!-- Error Alert -->
        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <!-- Form -->
        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Cliente</CardTitle>
                    <CardDescription
                        >Preencha os dados do cliente abaixo</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Name & Document -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Nome / Razão Social *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Nome do cliente"
                                :class="{
                                    'border-destructive': form.errors.name,
                                }"
                            />
                            <span
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                                >{{ form.errors.name }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="doc">CPF / CNPJ *</Label>
                            <Input
                                id="doc"
                                v-model="form.doc"
                                placeholder="00.000.000/0000-00"
                                :class="{
                                    'border-destructive': form.errors.doc,
                                }"
                            />
                            <span
                                v-if="form.errors.doc"
                                class="text-sm text-destructive"
                                >{{ form.errors.doc }}</span
                            >
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="address">Endereço *</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                placeholder="Rua, Avenida..."
                                :class="{
                                    'border-destructive': form.errors.address,
                                }"
                            />
                            <span
                                v-if="form.errors.address"
                                class="text-sm text-destructive"
                                >{{ form.errors.address }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="number">Número *</Label>
                            <Input
                                id="number"
                                v-model="form.number"
                                placeholder="123"
                                :class="{
                                    'border-destructive': form.errors.number,
                                }"
                            />
                            <span
                                v-if="form.errors.number"
                                class="text-sm text-destructive"
                                >{{ form.errors.number }}</span
                            >
                        </div>
                    </div>

                    <!-- City, State, Zipcode -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="city">Cidade *</Label>
                            <Input
                                id="city"
                                v-model="form.city"
                                placeholder="São Paulo"
                                :class="{
                                    'border-destructive': form.errors.city,
                                }"
                            />
                            <span
                                v-if="form.errors.city"
                                class="text-sm text-destructive"
                                >{{ form.errors.city }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="state">UF *</Label>
                            <Input
                                id="state"
                                v-model="form.state"
                                placeholder="SP"
                                maxlength="2"
                                :class="{
                                    'border-destructive': form.errors.state,
                                }"
                            />
                            <span
                                v-if="form.errors.state"
                                class="text-sm text-destructive"
                                >{{ form.errors.state }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="zipcode">CEP *</Label>
                            <Input
                                id="zipcode"
                                v-model="form.zipcode"
                                placeholder="00000-000"
                                :class="{
                                    'border-destructive': form.errors.zipcode,
                                }"
                            />
                            <span
                                v-if="form.errors.zipcode"
                                class="text-sm text-destructive"
                                >{{ form.errors.zipcode }}</span
                            >
                        </div>
                    </div>

                    <!-- Phones -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="phone1">Telefone 1 *</Label>
                            <Input
                                id="phone1"
                                v-model="form.phone1"
                                placeholder="(11) 99999-0000"
                                :class="{
                                    'border-destructive': form.errors.phone1,
                                }"
                            />
                            <span
                                v-if="form.errors.phone1"
                                class="text-sm text-destructive"
                                >{{ form.errors.phone1 }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="phone2">Telefone 2</Label>
                            <Input
                                id="phone2"
                                v-model="form.phone2"
                                placeholder="(11) 3333-0000"
                            />
                        </div>
                    </div>

                    <!-- Contacts -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="contact1">Contato 1</Label>
                            <Input
                                id="contact1"
                                v-model="form.contact1"
                                placeholder="Nome do contato principal"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="contact2">Contato 2</Label>
                            <Input
                                id="contact2"
                                v-model="form.contact2"
                                placeholder="Nome do contato secundário"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="clientsIndex()">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Cliente' }}
                </Button>
            </div>
        </form>
    </div>
</template>
