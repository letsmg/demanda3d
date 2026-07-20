<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ArrowRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: () => import('@/layouts/WelcomeLayout.vue'),
});

defineProps<{
    session_id?: string;
}>();
</script>

<template>
    <Head title="Payment Successful - Demanda3D">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="min-h-screen bg-amber-50">
        <main class="mx-auto max-w-lg px-4 py-16 text-center">
            <CheckCircle2 class="mx-auto h-16 w-16 text-emerald-500" />
            <h1 class="mt-6 text-2xl font-bold text-amber-900">
                Payment Successful!
            </h1>
            <p class="mt-2 text-amber-600">
                Your order has been confirmed. Thank you for your purchase!
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link href="/cart">
                    <Button variant="outline">Back to Cart</Button>
                </Link>
                <Link href="/store">
                    <Button class="bg-amber-500 text-white hover:bg-amber-600">
                        <ArrowRight class="mr-2 h-4 w-4" />
                        Continue Shopping
                    </Button>
                </Link>
            </div>
        </main>
    </div>
</template>
