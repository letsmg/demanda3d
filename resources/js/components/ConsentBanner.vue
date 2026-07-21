<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const page = usePage();

const legal = computed(() => page.props.legalConsent as any);

const title = computed(() => {
    const doc = legal.value;

    if (!doc) {
return 'Documentos Legais atualizados';
}

    return doc.document_title || 'Documentos Legais atualizados';
});

const graceDays = computed(() => legal.value?.grace_days ?? 7);

const visible = computed(() => {
    const doc = legal.value;

    return (
        doc?.needs_acceptance &&
        doc?.show_banner === true &&
        !doc?.is_grace_expired
    );
});

function handleAccept() {
    router.post(
        '/consent/accept',
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}

function handleDismiss() {
    router.post(
        '/consent/dismiss',
        {},
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="visible"
            class="fixed right-0 bottom-0 left-0 z-50 mx-auto max-w-2xl p-4"
        >
            <div
                class="rounded-lg border border-amber-500 bg-amber-50 p-4 shadow-lg"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 shrink-0">
                        <AlertTriangle class="h-5 w-5 text-amber-600" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-amber-900">
                            {{ title }}
                        </p>
                        <p class="mt-1 text-xs text-amber-700">
                            Nossos Termos de Uso e Pol&iacute;tica de
                            Privacidade foram atualizados. Voc&ecirc; tem
                            <strong>{{ graceDays }} dias</strong> para
                            aceit&aacute;-los. Caso n&atilde;o aceite, seus
                            produtos ser&atilde;o ocultados da loja.
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <Button
                                size="sm"
                                class="bg-amber-600 text-white hover:bg-amber-700"
                                @click="handleAccept"
                            >
                                Aceitar Termos e Privacidade
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                class="text-amber-700 hover:text-amber-900"
                                @click="handleDismiss"
                            >
                                Lembrar depois
                            </Button>
                            <a
                                href="/legal/terms"
                                target="_blank"
                                class="text-xs text-amber-600 underline hover:text-amber-800"
                            >
                                Ler Termos de Uso
                            </a>
                            <a
                                href="/legal/privacy"
                                target="_blank"
                                class="text-xs text-amber-600 underline hover:text-amber-800"
                            >
                                Ler Pol&iacute;tica de Privacidade
                            </a>
                        </div>
                    </div>
                    <button
                        class="shrink-0 text-amber-400 hover:text-amber-600"
                        @click="handleDismiss"
                    >
                        &times;
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
