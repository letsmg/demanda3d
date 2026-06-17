<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { Client } from '@/types';

const props = defineProps<{
    client: Client;
}>();

const form = ref({ ...props.client });
const errors = ref<Record<string, string>>({});
const loading = ref(false);

const submit = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const response = await fetch(`/api/clients/${form.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value),
        });

        if (!response.ok) {
            const data = await response.json();
            errors.value = data.errors || {};
            if (data.message && !data.errors) {
                errors.value._general = data.message;
            }
        } else {
            router.visit(route('clients.index'));
        }
    } catch (error) {
        console.error('Error:', error);
        errors.value._general = 'An unexpected error occurred. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head :title="`Edit ${client.name}`" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="route('clients.index')">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Edit Client</h1>
                <p class="text-sm text-muted-foreground">
                    Updating: {{ client.name }}
                </p>
            </div>
        </div>

        <!-- Error Alert -->
        <Alert v-if="errors._general" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{{ errors._general }}</AlertDescription>
        </Alert>

        <!-- Form -->
        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Client Information</CardTitle>
                    <CardDescription>Update the client's details</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Full Name / Company Name *</Label>
                            <Input id="name" v-model="form.name" :class="{ 'border-destructive': errors.name }" />
                            <span v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="doc">CPF / CNPJ *</Label>
                            <Input id="doc" v-model="form.doc" :class="{ 'border-destructive': errors.doc }" />
                            <span v-if="errors.doc" class="text-sm text-destructive">{{ errors.doc }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="address">Address *</Label>
                            <Input id="address" v-model="form.address" :class="{ 'border-destructive': errors.address }" />
                            <span v-if="errors.address" class="text-sm text-destructive">{{ errors.address }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="number">Number *</Label>
                            <Input id="number" v-model="form.number" :class="{ 'border-destructive': errors.number }" />
                            <span v-if="errors.number" class="text-sm text-destructive">{{ errors.number }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="city">City *</Label>
                            <Input id="city" v-model="form.city" :class="{ 'border-destructive': errors.city }" />
                            <span v-if="errors.city" class="text-sm text-destructive">{{ errors.city }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="state">State *</Label>
                            <Input id="state" v-model="form.state" maxlength="2" :class="{ 'border-destructive': errors.state }" />
                            <span v-if="errors.state" class="text-sm text-destructive">{{ errors.state }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="zipcode">Zipcode *</Label>
                            <Input id="zipcode" v-model="form.zipcode" :class="{ 'border-destructive': errors.zipcode }" />
                            <span v-if="errors.zipcode" class="text-sm text-destructive">{{ errors.zipcode }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="phone1">Phone 1 *</Label>
                            <Input id="phone1" v-model="form.phone1" :class="{ 'border-destructive': errors.phone1 }" />
                            <span v-if="errors.phone1" class="text-sm text-destructive">{{ errors.phone1 }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="phone2">Phone 2</Label>
                            <Input id="phone2" v-model="form.phone2" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="contact1">Contact 1</Label>
                            <Input id="contact1" v-model="form.contact1" />
                        </div>
                        <div class="space-y-2">
                            <Label for="contact2">Contact 2</Label>
                            <Input id="contact2" v-model="form.contact2" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child>
                    <Link :href="route('clients.index')">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="loading">
                    <Save class="mr-2 h-4 w-4" />
                    {{ loading ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>