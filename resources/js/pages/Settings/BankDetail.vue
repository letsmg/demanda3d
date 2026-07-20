<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = defineProps<{
    tenant: any;
    bankDetail: any;
    document: string;
    legal_responsible_name: string | null;
    type?: string; // 'tenant' | 'carrier' | 'admin_readonly'
}>();

const isReadonly = props.type === 'admin_readonly';

const form = useForm({
    bank_name: props.bankDetail?.bank_name || '',
    routing_number: props.bankDetail?.routing_number || '',
    account_number: props.bankDetail?.account_number || '',
    bank_pix_key: props.bankDetail?.bank_pix_key || '',
    account_holder_name:
        props.bankDetail?.account_holder_name ||
        props.tenant.fantasy_name ||
        '',
    account_holder_doc: props.document || '',
    consented: !!props.bankDetail?.consented,
});

function submit() {
    form.post('/settings/bank', { preserveState: true });
}

function fillForm() {
    form.bank_name = 'Banco do Brasil';
    form.routing_number = '0001';
    form.account_number = '100001-1';
    form.bank_pix_key = props.document || '';
    form.account_holder_name = props.tenant.fantasy_name || '';
    form.account_holder_doc = props.document || '';
    form.consented = true;
}

function clearForm() {
    form.bank_name = '';
    form.routing_number = '';
    form.account_number = '';
    form.bank_pix_key = '';
    form.account_holder_name = '';
    form.account_holder_doc = '';
    form.consented = false;
}
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-amber-900">
                Dados Bancários
            </h1>
            <p v-if="isReadonly" class="mt-1 text-sm text-amber-600">
                Visualização administrativa (somente leitura) — os dados abaixo
                pertencem ao vendedor.
            </p>
            <p v-else class="mt-1 text-sm text-amber-600">
                Configure sua conta bancária para receber os repasses de vendas.
            </p>
        </div>

        <Card class="border-amber-500 bg-amber-50/80 shadow-sm">
            <CardContent class="pt-6">
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="bank_name" class="text-amber-800"
                                >Nome do Banco</Label
                            >
                            <Input
                                id="bank_name"
                                v-model="form.bank_name"
                                required
                                :tabindex="1"
                                :disabled="isReadonly"
                                class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                placeholder="Ex: Banco do Brasil"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label
                                    for="routing_number"
                                    class="text-amber-800"
                                    >Agência</Label
                                >
                                <Input
                                    id="routing_number"
                                    v-model="form.routing_number"
                                    required
                                    :tabindex="2"
                                    :disabled="isReadonly"
                                    class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                    placeholder="0001"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    for="account_number"
                                    class="text-amber-800"
                                    >Conta</Label
                                >
                                <Input
                                    id="account_number"
                                    v-model="form.account_number"
                                    required
                                    :tabindex="3"
                                    :disabled="isReadonly"
                                    class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                    placeholder="123456-7"
                                />
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="bank_pix_key" class="text-amber-800"
                                >Chave PIX (opcional)</Label
                            >
                            <Input
                                id="bank_pix_key"
                                v-model="form.bank_pix_key"
                                :tabindex="4"
                                :disabled="isReadonly"
                                class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                placeholder="CPF/CNPJ, e-mail ou telefone"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="account_holder_name"
                                class="text-amber-800"
                                >Titular da Conta (Responsável Legal)</Label
                            >
                            <Input
                                id="account_holder_name"
                                v-model="form.account_holder_name"
                                required
                                :tabindex="5"
                                :disabled="isReadonly"
                                class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                placeholder="Nome completo do titular"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="account_holder_doc"
                                class="text-amber-800"
                                >CPF/CNPJ do Titular</Label
                            >
                            <Input
                                id="account_holder_doc"
                                v-model="form.account_holder_doc"
                                required
                                :tabindex="6"
                                :disabled="isReadonly"
                                class="placeholder:text-amber-910 border-amber-900 bg-white text-amber-900 focus:border-amber-500"
                                placeholder="00.000.000/0001-00"
                            />
                            <p class="text-xs text-amber-500">
                                Deve ser o mesmo documento do responsável legal
                                cadastrado nesta loja.
                            </p>
                        </div>

                        <!-- Consentimento LGPD -->
                        <div class="rounded-md bg-amber-100 p-3">
                            <div class="flex items-start gap-2">
                                <Checkbox
                                    id="consented"
                                    v-model:checked="form.consented"
                                    :tabindex="7"
                                    :disabled="isReadonly"
                                    class="border-amber-900"
                                />
                                <Label
                                    for="consented"
                                    class="text-sm leading-relaxed text-amber-800"
                                >
                                    Estou ciente e dou consentimento para o
                                    processamento dos meus dados financeiros
                                    para fins de repasse de vendas, nos termos
                                    da LGPD. Entendo que o titular da conta
                                    bancária deve ser o mesmo responsável legal
                                    cadastrado nesta loja.
                                </Label>
                            </div>
                        </div>

                        <!-- Botões -->
                        <!-- Botões — ocultos no modo admin readonly -->
                        <div v-if="!isReadonly" class="flex gap-2">
                            <Button
                                type="submit"
                                class="hover:bg-amber-910 flex-1 bg-amber-500 font-semibold text-amber-950"
                                :tabindex="8"
                                :disabled="form.processing"
                            >
                                <Spinner v-if="form.processing" /> Salvar Dados
                                Bancários
                            </Button>
                            <Button
                                type="button"
                                variant="secondary"
                                @click="fillForm"
                                :tabindex="9"
                                class="border-amber-900 text-amber-700 hover:bg-amber-50"
                            >
                                Preencher
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="clearForm"
                                :tabindex="10"
                                class="border-amber-900 text-amber-700 hover:bg-amber-50"
                            >
                                Limpar
                            </Button>
                        </div>
                    </div>
                </form>
            </CardContent>
        </Card>

        <!-- Dados do responsável legal -->
        <Card
            v-if="legal_responsible_name"
            class="border-amber-200 bg-amber-50/50"
        >
            <CardHeader class="pb-2">
                <CardTitle class="text-sm text-amber-700"
                    >Responsável Legal</CardTitle
                >
                <CardDescription class="text-amber-600">
                    {{ legal_responsible_name }} — {{ document }}
                </CardDescription>
            </CardHeader>
        </Card>
    </div>
</template>
