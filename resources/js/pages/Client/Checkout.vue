<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import {
    CreditCard,
    MapPin,
    Ticket,
    Truck,
    ArrowLeft,
    ArrowRight,
    Check,
    Loader2,
    AlertTriangle,
    ChevronDown,
    ChevronUp,
    Copy,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    client: any;
    addresses: Array<{
        id: number;
        address: string;
        number: string;
        city: string;
        state: string;
        zipcode: string;
    }>;
    carriers: Array<{
        id: number;
        name: string;
        freight_cost: number | null;
    }>;
    total: number;
    count: number;
}>();

const step = ref(1);
const selectedAddressId = ref<number | null>(null);
const newAddress = ref(false);
const couponCode = ref('');
const couponMessage = ref('');
const couponLoading = ref(false);
const couponData = ref<{
    valid?: boolean;
    discount_amount?: number;
    discounted_total?: number;
    applicable_items?: number;
    total_items?: number;
    error?: string;
} | null>(null);
const selectedCarrierId = ref<number | null>(null);
const selectedPaymentMethod = ref('card');

const newAddressForm = useForm({
    address: '',
    number: '',
    state: '',
    zipcode: '',
    city: '',
});

// ── DEV-only flags ──
const page = usePage();
const isDev = computed(() => {
    return (page.props as any).isDev === true || import.meta.env.DEV;
});

// ── Card inputs (DEV mode: manual card entry) ──
const cardNumber = ref('');
const cardExpiry = ref('');
const cardCvc = ref('');
const cardFieldReadonly = computed(() => isDev.value);

// ── Stripe test cards data ──
interface TestCard {
    category: string;
    brand: string;
    number: string;
    cvc: string;
    behavior: string;
}
const stripeTestCards: TestCard[] = [
    { category: 'Sucesso', brand: 'Visa', number: '4242424242424242', cvc: '123', behavior: 'Aprova com sucesso imediato' },
    { category: 'Sucesso', brand: 'Mastercard', number: '5555555555554444', cvc: '123', behavior: 'Aprova com sucesso imediato' },
    { category: 'Autenticação', brand: 'Visa (3D Secure)', number: '4000000000003020', cvc: '123', behavior: 'Dispara simulação de autenticação 3D' },
    { category: 'Recusa', brand: 'Visa (Geral)', number: '4000000000000002', cvc: '123', behavior: 'Simula Cartão Recusado pelo banco' },
    { category: 'Recusa', brand: 'Visa (Sem Saldo)', number: '4000000000000051', cvc: '123', behavior: 'Simula Saldo Insuficiente' },
    { category: 'Recusa', brand: 'Visa (Expirado)', number: '4000000000000003', cvc: '123', behavior: 'Simula Cartão Vencido' },
    { category: 'Recusa', brand: 'Visa (Fraude)', number: '4000000000000004', cvc: '123', behavior: 'Simula Bloqueio por Suspeita de Fraude' },
];

// ── Test cards helper panel toggle ──
const showTestCards = ref(false);

// ── Strict Stripe test card validation set ──
const STRIPE_TEST_CARD_NUMBERS = new Set(stripeTestCards.map((c) => c.number));

function fillTestCard(card: TestCard) {
    cardNumber.value = card.number;
    cardCvc.value = card.cvc;
    // Data de expiração futura: dezembro de 2028
    cardExpiry.value = '12/28';
    showTestCards.value = false;
}

function copyCardNumber(number: string) {
    navigator.clipboard.writeText(number).then(() => {
        // Feedback visual breve
    }).catch(() => { /* ignore */ });
}

const canAdvanceStep2 = computed(() => {
    return selectedCarrierId.value !== null && props.carriers.length > 0;
});

async function validateCoupon() {
    couponMessage.value = '';
    couponData.value = null;

    if (!couponCode.value.trim()) return;

    couponLoading.value = true;

    try {
        const res = await fetch('/api/coupons/validate', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({ code: couponCode.value.trim() }),
        });
        const data = await res.json();
        couponData.value = data;

        if (data.error) {
            couponMessage.value = data.error;
        } else {
            couponMessage.value = `Cupom ${data.code} aplicado! Desconto: R$ ${Number(data.discount_amount).toFixed(2)}`;
        }
    } catch {
        couponMessage.value = 'Erro ao validar cupom.';
    }

    couponLoading.value = false;
}

function submitCheckout() {
    if (!selectedAddressId.value && !newAddress.value) {
        alert('Selecione ou cadastre um endereço de entrega.');
        return;
    }

    if (!selectedCarrierId.value) {
        alert('Selecione uma transportadora.');
        return;
    }

    // ── DEV mode: validar cartão de teste Stripe ──
    if (isDev.value && selectedPaymentMethod.value === 'card') {
        const rawCard = cardNumber.value.replace(/\s+/g, '');

        if (!STRIPE_TEST_CARD_NUMBERS.has(rawCard)) {
            alert('⚠️ Cartão não autorizado para o ambiente de desenvolvimento! Utilize apenas cartões de teste da Stripe no painel "Stripe Test Cards Helper".');
            return;
        }
    }

    router.post(
        '/checkout',
        {
            address_id: selectedAddressId.value,
            coupon_code: couponCode.value || null,
            carrier_id: selectedCarrierId.value,
            payment_method: selectedPaymentMethod.value,
        },
        {
            onSuccess: (response: any) => {
                const url = response?.props?.stripe_url;

                if (url) {
                    window.location.href = url;
                }
            },
        },
    );
}

// ── Clean formatting mask for card number (groups of 4) ──
function formatCardNumber(event: Event) {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 16);
    const parts = value.match(/.{1,4}/g);

    input.value = parts ? parts.join(' ') : value;
    cardNumber.value = input.value;
}

function formatCardExpiry(event: Event) {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 4);

    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }

    input.value = value;
    cardExpiry.value = value;
}

onMounted(() => {
    // Garantir que o input de cartão esteja como readonly no DEV
    if (isDev.value && cardFieldReadonly.value) {
        cardNumber.value = '';
        cardExpiry.value = '';
        cardCvc.value = '';
    }
});
</script>

<template>
    <Head title="Finalizar Compra" />

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="mb-2 text-2xl font-bold tracking-tight text-amber-900">
            Finalizar Compra
        </h1>

        <!-- ════════════════════════════════════════════════ -->
        <!-- BANNER DE ALERTA — Ambiente de Testes (DEV)      -->
        <!-- ════════════════════════════════════════════════ -->
        <div
            v-if="isDev"
            class="mb-6 flex items-start gap-3 rounded-lg border-2 border-red-600 bg-red-700 px-5 py-4 text-white shadow-lg"
        >
            <AlertTriangle class="mt-0.5 h-6 w-6 flex-shrink-0 text-yellow-300" />
            <div>
                <p class="text-lg font-extrabold text-yellow-300">
                    ⚠️ ATENÇÃO: AMBIENTE DE TESTES
                </p>
                <p class="mt-1 text-sm text-red-100">
                    Este site está operando atualmente em fase de homologação e testes.
                    Nenhuma transação financeira real está sendo processada e nenhuma
                    cobrança será efetuada em seu cartão.
                </p>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════ -->
        <!-- STRIPE TEST CARDS HELPER (apenas DEV)           -->
        <!-- ════════════════════════════════════════════════ -->
        <div
            v-if="isDev && selectedPaymentMethod === 'card'"
            class="mb-6 rounded-lg border-2 border-dashed border-amber-400 bg-amber-50 p-4"
        >
            <button
                type="button"
                class="flex w-full items-center justify-between text-left"
                @click="showTestCards = !showTestCards"
            >
                <span class="flex items-center gap-2 text-sm font-bold text-amber-800">
                    <CreditCard class="h-4 w-4" />
                    Stripe Test Cards Helper
                </span>
                <ChevronDown v-if="!showTestCards" class="h-4 w-4 text-amber-600" />
                <ChevronUp v-else class="h-4 w-4 text-amber-600" />
            </button>

            <div v-if="showTestCards" class="mt-3 space-y-2">
                <p class="text-xs text-amber-600">
                    Clique em um cartão para preencher automaticamente os campos
                    de pagamento abaixo. Todos os cartões listados são cartões de teste
                    oficiais da Stripe.
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-amber-200 text-left text-amber-700">
                                <th class="pb-1 pr-2 font-semibold">Categoria</th>
                                <th class="pb-1 pr-2 font-semibold">Bandeira</th>
                                <th class="pb-1 pr-2 font-semibold">Número</th>
                                <th class="pb-1 pr-2 font-semibold">CVC</th>
                                <th class="pb-1 font-semibold">Comportamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="card in stripeTestCards"
                                :key="card.number"
                                class="cursor-pointer border-b border-amber-100 transition-colors hover:bg-amber-100"
                                @click="fillTestCard(card)"
                            >
                                <td class="py-1.5 pr-2">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold',
                                            card.category === 'Sucesso'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : card.category === 'Autenticação'
                                                  ? 'bg-blue-100 text-blue-700'
                                                  : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ card.category }}
                                    </span>
                                </td>
                                <td class="py-1.5 pr-2 font-medium text-amber-900">
                                    {{ card.brand }}
                                </td>
                                <td class="py-1.5 pr-2">
                                    <span class="flex items-center gap-1">
                                        <code class="rounded bg-white px-1.5 py-0.5 text-[11px] text-amber-900">
                                            {{ card.number }}
                                        </code>
                                        <button
                                            type="button"
                                            class="text-amber-400 hover:text-amber-600"
                                            title="Copiar número"
                                            @click.stop="copyCardNumber(card.number)"
                                        >
                                            <Copy class="h-3 w-3" />
                                        </button>
                                    </span>
                                </td>
                                <td class="py-1.5 pr-2 text-center">
                                    <code class="rounded bg-white px-1.5 py-0.5 text-[11px] text-amber-900">
                                        {{ card.cvc }}
                                    </code>
                                </td>
                                <td class="py-1.5 text-amber-600">{{ card.behavior }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Steps indicator (clicável para voltar) -->
        <div class="mb-8 flex items-center gap-2">
            <template v-for="s in 3" :key="s">
                <button
                    type="button"
                    class="flex items-center gap-2"
                    :class="step >= s ? 'cursor-pointer' : 'cursor-not-allowed'"
                    :disabled="step <= s"
                    @click="step = s"
                >
                    <div
                        :class="[
                            'flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-bold transition-colors',
                            step >= s
                                ? 'border-amber-600 bg-amber-600 text-white hover:bg-amber-700'
                                : 'border-amber-900 text-amber-910',
                        ]"
                    >
                        <Check v-if="step > s" class="h-4 w-4" />
                        <span v-else>{{ s }}</span>
                    </div>
                    <span class="hidden text-sm text-amber-600 sm:inline">{{
                        s === 1
                            ? 'Endereço'
                            : s === 2
                              ? 'Cupom & Frete'
                              : 'Pagamento'
                    }}</span>
                </button>
                <div v-if="s < 3" class="h-0.5 w-8 bg-amber-200" />
            </template>
        </div>

        <!-- STEP 1 -->
        <Card v-if="step === 1">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <MapPin class="h-5 w-5" />
                    Endereço de Entrega
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="addresses.length > 0" class="space-y-3">
                    <p class="text-sm text-muted-foreground">
                        Selecione um endereço salvo:
                    </p>
                    <div
                        v-for="addr in addresses"
                        :key="addr.id"
                        class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 hover:border-amber-910"
                        :class="{
                            'border-amber-500 bg-amber-50':
                                selectedAddressId === addr.id,
                        }"
                        @click="
                            selectedAddressId = addr.id;
                            newAddress = false;
                        "
                    >
                        <div class="mt-0.5">
                            <div
                                :class="[
                                    'flex h-5 w-5 items-center justify-center rounded-full border-2',
                                    selectedAddressId === addr.id
                                        ? 'border-amber-600 bg-amber-600'
                                        : 'border-gray-300',
                                ]"
                            >
                                <Check
                                    v-if="selectedAddressId === addr.id"
                                    class="h-3 w-3 text-white"
                                />
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-amber-900">
                                {{ addr.address }}, {{ addr.number }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ addr.city }} - {{ addr.state }} — CEP:
                                {{ addr.zipcode }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            v-model="newAddress"
                            class="h-4 w-4 rounded border-amber-900"
                        />
                        <span class="text-sm text-amber-700">
                            Cadastrar novo endereço
                        </span>
                    </div>
                </div>

                <div
                    v-if="addresses.length === 0 || newAddress"
                    class="space-y-3 rounded-lg border p-4"
                >
                    <p class="text-sm font-medium">Novo Endereço</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <Label for="new_zipcode">CEP *</Label>
                            <Input
                                id="new_zipcode"
                                v-model="newAddressForm.zipcode"
                                placeholder="00000-000"
                                maxlength="9"
                            />
                        </div>
                        <div>
                            <Label for="new_state">UF *</Label>
                            <Input
                                id="new_state"
                                v-model="newAddressForm.state"
                                placeholder="SP"
                                maxlength="2"
                            />
                        </div>
                        <div>
                            <Label for="new_city">Cidade *</Label>
                            <Input
                                id="new_city"
                                v-model="newAddressForm.city"
                                placeholder="São Paulo"
                            />
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <Label for="new_address">Endereço *</Label>
                            <Input
                                id="new_address"
                                v-model="newAddressForm.address"
                                placeholder="Rua, Avenida..."
                            />
                        </div>
                        <div>
                            <Label for="new_number">Número *</Label>
                            <Input
                                id="new_number"
                                v-model="newAddressForm.number"
                                placeholder="123"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <Button
                        @click="step = 2"
                        :disabled="!selectedAddressId && !newAddress"
                        class="bg-amber-600 hover:bg-amber-700"
                    >
                        Próximo <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 2 -->
        <Card v-if="step === 2">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Ticket class="h-5 w-5" />
                    Cupom e Transportadora
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Cupom -->
                <div>
                    <Label for="coupon">Cupom de Desconto</Label>
                    <div class="flex gap-2">
                        <Input
                            id="coupon"
                            v-model="couponCode"
                            placeholder="Digite o código"
                            class="flex-1"
                        />
                        <Button
                            variant="outline"
                            @click="validateCoupon"
                            :disabled="couponLoading"
                        >
                            <Loader2
                                v-if="couponLoading"
                                class="mr-1 h-4 w-4 animate-spin"
                            />
                            Aplicar
                        </Button>
                    </div>
                    <p
                        v-if="couponMessage"
                        :class="[
                            'mt-1 text-sm',
                            couponData?.error
                                ? 'text-rose-500'
                                : 'text-emerald-600',
                        ]"
                    >
                        {{ couponMessage }}
                    </p>
                </div>

                <!-- Transportadora -->
                <div>
                    <p class="mb-2 text-sm font-medium">
                        Selecione a transportadora:
                    </p>
                    <div
                        v-if="carriers.length === 0"
                        class="rounded-lg border border-amber-900 bg-amber-50 p-4 text-center text-sm text-amber-700"
                    >
                        ⚠️ Nenhuma transportadora disponível. O vendedor ainda
                        não vinculou transportadoras aos produtos.
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="carrier in carriers"
                            :key="carrier.id"
                            class="flex cursor-pointer items-center justify-between rounded-lg border p-3 hover:border-amber-910"
                            :class="{
                                'border-amber-500 bg-amber-50':
                                    selectedCarrierId === carrier.id,
                            }"
                            @click="selectedCarrierId = carrier.id"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        'flex h-5 w-5 items-center justify-center rounded-full border-2',
                                        selectedCarrierId === carrier.id
                                            ? 'border-amber-600 bg-amber-600'
                                            : 'border-gray-300',
                                    ]"
                                >
                                    <Check
                                        v-if="selectedCarrierId === carrier.id"
                                        class="h-3 w-3 text-white"
                                    />
                                </div>
                                <Truck class="h-5 w-5 text-amber-600" />
                                <span class="font-medium text-amber-900">
                                    {{ carrier.name }}
                                </span>
                            </div>
                            <span
                                v-if="carrier.freight_cost"
                                class="text-sm font-bold text-amber-700"
                            >
                                R$ {{ Number(carrier.freight_cost).toFixed(2) }}
                            </span>
                            <span v-else class="text-sm text-muted-foreground">
                                Frete grátis
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Resumo com desconto -->
                <div class="rounded-lg bg-amber-50 p-4">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal ({{ count }} itens)</span>
                        <span>
                            R$ {{ Number(total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <div
                        v-if="couponData?.discount_amount"
                        class="mt-1 flex justify-between text-sm text-emerald-600"
                    >
                        <span>Desconto ({{ couponCode }})</span>
                        <span>
                            - R$ {{ Number(couponData.discount_amount).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <div
                        v-if="couponData?.discounted_total"
                        class="mt-2 flex justify-between border-t border-amber-200 pt-2 text-lg font-bold text-emerald-700"
                    >
                        <span>Total com Desconto</span>
                        <span>
                            R$ {{ Number(couponData.discounted_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <Button variant="outline" @click="step = 1">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Voltar
                    </Button>
                    <Button
                        @click="step = 3"
                        :disabled="!canAdvanceStep2"
                        class="bg-amber-600 hover:bg-amber-700"
                    >
                        Próximo <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 3 -->
        <Card v-if="step === 3">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <CreditCard class="h-5 w-5" /> Forma de Pagamento
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Payment method selection -->
                <div class="space-y-3">
                    <div
                        v-for="method in [
                            { id: 'card', label: 'Cartão de Crédito (Visa/Master)', icon: '💳', note: 'Dados do cartão processados via Stripe — nunca armazenados.' },
                            { id: 'boleto', label: 'Boleto Bancário', icon: '📄' },
                            { id: 'pix', label: 'Pix', icon: '⚡' },
                        ]"
                        :key="method.id"
                        class="cursor-pointer rounded-lg border p-4 hover:border-amber-910"
                        :class="{
                            'border-amber-500 bg-amber-50':
                                selectedPaymentMethod === method.id,
                        }"
                        @click="selectedPaymentMethod = method.id"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ method.icon }}</span>
                            <div class="flex-1">
                                <p class="font-medium text-amber-900">
                                    {{ method.label }}
                                </p>
                                <p
                                    v-if="'note' in method && method.note"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ method.note }}
                                </p>
                            </div>
                            <div
                                :class="[
                                    'flex h-5 w-5 items-center justify-center rounded-full border-2',
                                    selectedPaymentMethod === method.id
                                        ? 'border-amber-600 bg-amber-600'
                                        : 'border-gray-300',
                                ]"
                            >
                                <Check
                                    v-if="selectedPaymentMethod === method.id"
                                    class="h-3 w-3 text-white"
                                />
                            </div>
                        </div>

                        <!-- Card input fields (only when 'card' selected + DEV mode) -->
                        <div
                            v-if="isDev && method.id === 'card' && selectedPaymentMethod === 'card'"
                            class="mt-4 space-y-3 border-t border-amber-200 pt-4"
                        >
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <Label for="card-number">Número do Cartão</Label>
                                    <Input
                                        id="card-number"
                                        v-model="cardNumber"
                                        placeholder="4242 4242 4242 4242"
                                        maxlength="19"
                                        :readonly="cardFieldReadonly"
                                        class="font-mono border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                        @input="formatCardNumber"
                                    />
                                    <p
                                        v-if="cardFieldReadonly"
                                        class="mt-1 text-xs text-amber-500"
                                    >
                                        ⚠️ Bloqueado no DEV — use o Stripe Test Cards Helper acima
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <Label for="card-expiry">Validade</Label>
                                        <Input
                                            id="card-expiry"
                                            v-model="cardExpiry"
                                            placeholder="MM/AA"
                                            maxlength="5"
                                            :readonly="cardFieldReadonly"
                                            class="font-mono border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                            @input="formatCardExpiry"
                                        />
                                    </div>
                                    <div>
                                        <Label for="card-cvc">CVC</Label>
                                        <Input
                                            id="card-cvc"
                                            v-model="cardCvc"
                                            placeholder="123"
                                            maxlength="4"
                                            :readonly="cardFieldReadonly"
                                            class="font-mono border-amber-900 bg-white text-amber-900 placeholder:text-amber-910 focus:border-amber-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order summary -->
                <div class="rounded-lg border p-4">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal ({{ count }} itens)</span>
                        <span>
                            R$ {{ Number(total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <div
                        v-if="couponData?.discount_amount"
                        class="mt-1 flex justify-between text-sm text-emerald-600"
                    >
                        <span>Desconto</span>
                        <span>
                            - R$ {{ Number(couponData.discount_amount).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <div
                        class="mt-2 flex justify-between border-t pt-2 text-lg font-bold"
                    >
                        <span>Total</span>
                        <span class="text-amber-900">
                            R$ {{ (couponData?.discounted_total || total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Limite por produto: R$ 500,00 | Limite total: R$ 1.500,00
                    </p>
                </div>

                <div class="flex justify-between pt-4">
                    <Button variant="outline" @click="step = 2">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Voltar
                    </Button>
                    <Button
                        @click="submitCheckout"
                        class="bg-emerald-600 font-bold text-white hover:bg-emerald-700"
                        size="lg"
                    >
                        <CreditCard class="mr-2 h-5 w-5" />
                        Finalizar Compra
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>