<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    carrier: {
        id: number;
        fantasy_name: string;
        company_name: string;
        document_type: string;
        document: string;
        phone: string;
        address: string;
        email: string;
        website_url: string | null;
        coverageRanges: {
            id: number;
            title: string;
            cep_start: string;
            cep_end: string;
        }[];
        rating_average: number;
        rating_count: number;
    };
}>();

const form: any = useForm({
    fantasy_name: props.carrier.fantasy_name,
    website_url: props.carrier.website_url ?? '',
    phone: props.carrier.phone ?? '',
    address: props.carrier.address ?? '',
});

function submit() {
    form.put('/carrier/profile');
}
</script>

<template>
    <Head title="Perfil da Transportadora" />
    <div class="mx-auto max-w-2xl space-y-6 p-6">
        <h1 class="text-xl font-bold">Editar Perfil</h1>

        <Card>
            <CardHeader><CardTitle>Dados Públicos</CardTitle></CardHeader>
            <CardContent>
                <p class="mb-4 text-sm text-amber-700">
                    ⚠️ Os dados cadastrais exibidos aqui são públicos para todos
                    os vendedores da plataforma, de acordo com nossa Política de
                    Privacidade e Termos de Uso.
                </p>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label>Nome Fantasia</Label>
                        <Input v-model="form.fantasy_name" required />
                    </div>
                    <div>
                        <Label>Razão Social</Label>
                        <Input
                            :value="carrier.company_name"
                            disabled
                            class="bg-gray-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            A razão social é exibida apenas para vendedores com
                            acordo ativo.
                        </p>
                    </div>
                    <div>
                        <Label
                            >Documento ({{
                                carrier.document_type?.toUpperCase()
                            }})</Label
                        >
                        <Input
                            :value="carrier.document"
                            disabled
                            class="bg-gray-100"
                        />
                    </div>
                    <div>
                        <Label>Website</Label>
                        <Input
                            v-model="form.website_url"
                            placeholder="https://sua-transportadora.com.br"
                        />
                    </div>
                    <div>
                        <Label>Telefone</Label>
                        <Input
                            v-model="form.phone"
                            placeholder="(11) 99999-0000"
                        />
                    </div>
                    <div>
                        <Label>Endereço</Label>
                        <Input
                            v-model="form.address"
                            placeholder="Av. Paulista, 1000 — São Paulo, SP"
                        />
                    </div>
                    <div>
                        <Label>E-mail (login)</Label>
                        <Input
                            :value="carrier.email"
                            disabled
                            class="bg-gray-100"
                        />
                    </div>
                    <Button type="submit" :disabled="form.processing"
                        >Salvar Alterações</Button
                    >
                </form>
            </CardContent>
        </Card>

        <!-- Cobertura -->
        <Card>
            <CardHeader
                ><CardTitle
                    >Faixas de CEP — Cobertura de Entrega</CardTitle
                ></CardHeader
            >
            <CardContent>
                <div
                    v-if="carrier.coverageRanges.length === 0"
                    class="text-sm text-gray-500"
                >
                    Nenhuma faixa de cobertura cadastrada.
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-600">
                            <th class="py-1">Região</th>
                            <th>CEP Início</th>
                            <th>CEP Fim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="r in carrier.coverageRanges"
                            :key="r.id"
                            class="border-b"
                        >
                            <td class="py-1">{{ r.title }}</td>
                            <td>{{ r.cep_start }}</td>
                            <td>{{ r.cep_end }}</td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>
