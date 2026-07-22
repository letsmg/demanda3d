<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Banknote, Check, X, Eye, AlertTriangle } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface BankDetailDecrypted {
    bank_name: string;
    routing_number: string | null;
    account_number: string | null;
    bank_pix_key: string | null;
    account_holder_name: string;
    account_holder_doc: string | null;
    consented: boolean;
    consented_at: string | null;
}

interface TenantData {
    id: number;
    fantasy_name: string;
    fantasy_slug: string;
    company_name: string | null;
    legal_responsible_name: string | null;
    document: string | null;
    active: boolean;
    owner_email: string | null;
    bank_detail: BankDetailDecrypted | null;
    has_bank: boolean;
}

defineProps<{
    tenants: TenantData[];
}>();

function maskDoc(doc: string): string {
    if (doc.length === 14) {
        return doc.replace(
            /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/,
            '$1.$2.$3/$4-$5',
        );
    }

    return doc;
}

function formatDate(date: string | null): string {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Dados Bancários — Vendedores" />

    <div class="space-y-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                Dados Bancários
            </h1>
            <p class="text-sm text-muted-foreground">
                Visualização administrativa dos dados bancários de todos os
                vendedores ativos.
            </p>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Todos os Vendedores</CardTitle>
                <CardDescription>
                    {{ tenants.length }} vendedor(es) ativo(s) cadastrado(s).
                    Dados bancários descriptografados apenas para contas com
                    consentimento ativo.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 font-medium">Loja</th>
                                <th class="px-4 py-3 font-medium">
                                    Responsável Legal
                                </th>
                                <th class="px-4 py-3 font-medium">Banco</th>
                                <th class="px-4 py-3 font-medium">
                                    Consentimento
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tenant in tenants"
                                :key="tenant.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        {{ tenant.fantasy_name }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{
                                            tenant.company_name ||
                                            'Razão social não disponível'
                                        }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{
                                            tenant.document
                                                ? maskDoc(tenant.document)
                                                : '—'
                                        }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    {{ tenant.legal_responsible_name || '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <template
                                        v-if="
                                            tenant.has_bank &&
                                            tenant.bank_detail
                                        "
                                    >
                                        <div class="flex items-center gap-1.5">
                                            <Banknote
                                                class="h-4 w-4 text-green-600"
                                            />
                                            <span>{{
                                                tenant.bank_detail.bank_name
                                            }}</span>
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            Ag:
                                            {{
                                                tenant.bank_detail
                                                    .routing_number || '—'
                                            }}
                                            | CC:
                                            {{
                                                tenant.bank_detail
                                                    .account_number || '—'
                                            }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div
                                            class="flex items-center gap-1.5 text-muted-foreground"
                                        >
                                            <AlertTriangle class="h-4 w-4" />
                                            <span class="italic"
                                                >Não cadastrado</span
                                            >
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <template
                                        v-if="tenant.bank_detail?.consented"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-green-600"
                                        >
                                            <Check class="h-4 w-4" />
                                            <span>Sim</span>
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                formatDate(
                                                    tenant.bank_detail
                                                        .consented_at,
                                                )
                                            }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div
                                            class="flex items-center gap-1.5 text-muted-foreground"
                                        >
                                            <X class="h-4 w-4" />
                                            <span>{{
                                                tenant.has_bank
                                                    ? 'Não consentido'
                                                    : '—'
                                            }}</span>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/admin/bank/${tenant.id}/edit`"
                                        class="inline-flex items-center gap-1 text-sm text-primary hover:underline"
                                    >
                                        <Eye class="h-4 w-4" />
                                        Visualizar
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="tenants.length === 0">
                                <td
                                    colspan="5"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    Nenhum vendedor ativo encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
