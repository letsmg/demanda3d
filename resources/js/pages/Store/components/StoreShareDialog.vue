<script setup lang="ts">
import { Copy, Link2, MessageCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    open: boolean;
    title: string;
    url: string;
    copied: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'copy-link': [];
    'share-whatsapp': [];
    'share-facebook': [];
    'share-twitter': [];
    'share-telegram': [];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Compartilhar Produto</DialogTitle>
                <DialogDescription>{{ title }}</DialogDescription>
            </DialogHeader>
            <div class="grid grid-cols-2 gap-3">
                <Button
                    variant="outline"
                    class="flex items-center justify-center gap-2"
                    @click="emit('copy-link')"
                >
                    <Copy v-if="!copied" class="h-4 w-4" />
                    <Link2 v-else class="h-4 w-4 text-green-600" />
                    {{ copied ? 'Copiado!' : 'Copiar Link' }}
                </Button>
                <Button
                    variant="outline"
                    class="flex items-center justify-center gap-2 bg-green-50 hover:bg-green-100"
                    @click="emit('share-whatsapp')"
                >
                    <MessageCircle class="h-4 w-4 text-green-600" /> WhatsApp
                </Button>
                <Button
                    variant="outline"
                    class="flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100"
                    @click="emit('share-facebook')"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#1877F2">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                        />
                    </svg>
                    Facebook
                </Button>
                <Button
                    variant="outline"
                    class="flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100"
                    @click="emit('share-twitter')"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#0F1419">
                        <path
                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                        />
                    </svg>
                    X
                </Button>
                <Button
                    variant="outline"
                    class="flex items-center justify-center gap-2 bg-sky-50 hover:bg-sky-100"
                    @click="emit('share-telegram')"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="#0088CC">
                        <path
                            d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.26.333-.54.333l.194-2.76 5.02-4.536c.223-.198-.048-.308-.346-.11l-6.2 3.9-2.67-.834c-.58-.182-.592-.58.122-.862l10.43-4.02c.483-.18.904.117.746.876z"
                        />
                    </svg>
                    Telegram
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
