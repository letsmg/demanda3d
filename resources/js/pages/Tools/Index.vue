<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Wrench, RefreshCw, FileText, CheckCircle, Image, Upload, Trash2, AlertCircle } from '@lucide/vue';
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
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB

interface HeroImage {
    filename: string;
    url: string;
}

const props = defineProps<{
    sitemap: {
        exists: boolean;
        last_generated: string;
        product_count: number;
    };
    heroImages: HeroImage[];
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string } | null);

const sitemapForm = useForm({});
const uploadForm = useForm({
    images: [] as File[],
});
const deleteForm = useForm({
    filename: '',
});

const imageError = ref<string | null>(null);
const uploading = ref(false);

const generateSitemap = () => {
    sitemapForm.post('/tools/sitemap', { preserveScroll: true });
};

const onFilesSelected = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;

    const files = Array.from(target.files);
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    // Valida cada arquivo
    for (const file of files) {
        if (!allowedTypes.includes(file.type)) {
            imageError.value = `Formato não aceito: ${file.name}. Use JPG, PNG ou WEBP.`;
            target.value = '';
            return;
        }
        if (file.size > MAX_IMAGE_SIZE) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            imageError.value = `${file.name} tem ${sizeMB}MB. O limite é 2MB por imagem.`;
            target.value = '';
            return;
        }
    }

    imageError.value = null;
    uploadForm.images = files;

    // Dispara o upload automaticamente
    submitUpload();
};

const submitUpload = () => {
    if (uploadForm.images.length === 0) {
        imageError.value = 'Selecione pelo menos uma imagem.';
        return;
    }

    uploading.value = true;

    uploadForm.post('/tools/hero-images', {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.images = [];
            uploading.value = false;
            const input = document.getElementById('hero-image-input') as HTMLInputElement;
            if (input) input.value = '';
        },
        onError: () => {
            uploading.value = false;
        },
    });
};

const deleteImage = (filename: string) => {
    if (!confirm(`Remover "${filename}" do carrossel?`)) return;

    deleteForm.filename = filename;
    deleteForm.delete('/tools/hero-images', {
        preserveScroll: true,
        onSuccess: () => {
            deleteForm.filename = '';
        },
    });
};
</script>

<template>
    <Head title="Ferramentas" />
    <div class="space-y-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                Ferramentas
            </h1>
            <p class="text-sm text-muted-foreground">
                Utilitários administrativos do sistema
            </p>
        </div>

        <!-- Flash messages -->
        <Alert v-if="flash?.success" class="border-green-910 bg-green-50 text-green-800">
            <CheckCircle class="h-4 w-4" />
            <AlertTitle>Sucesso</AlertTitle>
            <AlertDescription>{{ flash?.success }}</AlertDescription>
        </Alert>
        <Alert v-if="flash?.error" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro</AlertTitle>
            <AlertDescription>{{ flash?.error }}</AlertDescription>
        </Alert>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Sitemap Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <FileText class="h-6 w-6 text-amber-600" />
                        <div>
                            <CardTitle>Gerar Sitemap</CardTitle>
                            <CardDescription
                                >Atualiza o sitemap.xml com todas as páginas
                                públicas</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2 rounded-lg bg-muted/50 p-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Status:</span>
                            <span
                                :class="
                                    sitemap.exists
                                        ? 'text-green-600'
                                        : 'text-red-500'
                                "
                                class="flex items-center gap-1"
                            >
                                <CheckCircle class="h-4 w-4" />
                                {{ sitemap.exists ? 'Gerado' : 'Não gerado' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Última geração:</span
                            >
                            <span class="font-medium">{{
                                sitemap.last_generated
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Produtos no sitemap:</span
                            >
                            <span class="font-medium">{{
                                sitemap.product_count
                            }}</span>
                        </div>
                    </div>

                    <Button
                        class="w-full"
                        :disabled="sitemapForm.processing"
                        @click="generateSitemap"
                    >
                        <RefreshCw
                            class="mr-2 h-4 w-4"
                            :class="{ 'animate-spin': sitemapForm.processing }"
                        />
                        {{
                            sitemapForm.processing
                                ? 'Gerando...'
                                : 'Gerar Sitemap Agora'
                        }}
                    </Button>

                    <p class="text-xs text-muted-foreground">
                        O sitemap é gerado com: página inicial, loja, todos os
                        produtos ativos e documentos legais. Para automação,
                        configure uma cron:
                        <code>php artisan sitemap:generate</code>
                    </p>
                </CardContent>
            </Card>

            <!-- Hero Images Card — Admin Only -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <Image class="h-6 w-6 text-amber-600" />
                        <div>
                            <CardTitle>Imagens do Carrossel</CardTitle>
                            <CardDescription
                                >Gerencie as imagens de fundo da página
                                inicial</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Upload -->
                    <div class="space-y-2">
                        <Label for="hero-image-input">Adicionar Imagens</Label>
                        <p class="text-xs text-muted-foreground">
                            Tamanho máximo: 2MB por imagem. Formatos: JPG, PNG, WEBP.
                            As imagens serão otimizadas automaticamente.
                        </p>
                        <Input
                            id="hero-image-input"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            :disabled="uploading || uploadForm.processing"
                            @input="onFilesSelected"
                        />
                        <span
                            v-if="imageError || uploadForm.errors.images"
                            class="text-sm text-destructive"
                        >
                            {{ imageError || uploadForm.errors.images }}
                        </span>
                    </div>

                    <!-- Current images -->
                    <div v-if="heroImages.length > 0" class="space-y-2">
                        <Label>Imagens atuais ({{ heroImages.length }})</Label>
                        <div class="grid grid-cols-4 gap-2">
                            <div
                                v-for="img in heroImages"
                                :key="img.filename"
                                class="group relative overflow-hidden rounded-md border"
                            >
                                <img
                                    :src="img.url"
                                    :alt="img.filename"
                                    class="h-16 w-full object-cover"
                                />
                                <button
                                    class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
                                    :disabled="deleteForm.processing"
                                    @click="deleteImage(img.filename)"
                                >
                                    <Trash2 class="h-4 w-4 text-white" />
                                </button>
                            </div>
                        </div>

                        <!-- Preview panel -->
                        <div v-if="heroImages.length > 0" class="mt-3 rounded-lg bg-muted/50 p-3">
                            <p class="mb-2 text-xs font-medium text-muted-foreground">
                                Preview do carrossel
                            </p>
                            <div class="flex gap-1 overflow-x-auto pb-1">
                                <div
                                    v-for="img in heroImages"
                                    :key="'p-' + img.filename"
                                    class="flex-shrink-0"
                                >
                                    <img
                                        :src="img.url"
                                        :alt="img.filename"
                                        class="h-24 w-36 rounded object-cover"
                                        :title="img.filename"
                                    />
                                    <p class="mt-1 text-center text-[10px] text-muted-foreground truncate w-36">
                                        {{ img.filename }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p v-else class="text-sm text-muted-foreground">
                        Nenhuma imagem no carrossel ainda. Use o upload acima
                        para adicionar (nomes: 3d-1.webp, 3d-2.webp, ...).
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>