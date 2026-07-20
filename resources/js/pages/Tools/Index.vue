<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    Wrench,
    RefreshCw,
    FileText,
    CheckCircle,
    Image,
    Upload,
    Trash2,
    AlertCircle,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB

interface HeroImage {
    filename: string;
    url: string;
}

const props = defineProps<{
    sitemap: { exists: boolean; last_generated: string; product_count: number };
    heroImages: HeroImage[];
}>();

const page = usePage();
const flash = computed(
    () => page.props.flash as { success?: string; error?: string } | null,
);

const sitemapForm = useForm({});
const uploadForm = useForm({ image_name: '', images: [] as File[] });
const imageError = ref<string | null>(null);
const seoFormatError = ref<string | null>(null);
const uploading = ref(false);

const generateSitemap = () => {
    sitemapForm.post('/tools/sitemap', { preserveScroll: true });
};

const onFilesSelected = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;
    const files = Array.from(target.files);
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    for (const file of files) {
        if (!allowedTypes.includes(file.type)) {
            imageError.value = `Formato não aceito: ${file.name}`;
            target.value = '';
            return;
        }
        if (file.size > MAX_IMAGE_SIZE) {
            imageError.value = `${file.name} excede 2MB.`;
            target.value = '';
            return;
        }
    }
    imageError.value = null;
    uploadForm.images = files;
    submitUpload();
};

const sanitizeSeoName = (value: string) => {
    uploadForm.image_name = value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    seoFormatError.value = null;
};

const submitUpload = () => {
    if (!uploadForm.image_name.trim()) {
        seoFormatError.value = 'Informe um nome SEO para a imagem.';
        return;
    }
    if (uploadForm.images.length === 0) {
        imageError.value = 'Selecione pelo menos uma imagem.';
        return;
    }
    uploading.value = true;
    uploadForm.post('/tools/hero-images', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            uploadForm.image_name = '';
            uploadForm.images = [];
            uploading.value = false;
            seoFormatError.value = null;
            const input = document.getElementById(
                'hero-image-input',
            ) as HTMLInputElement;
            if (input) input.value = '';
        },
        onError: () => {
            uploading.value = false;
        },
    });
};

const deleteImage = (filename: string) => {
    if (!confirm(`Remover "${filename}" do carrossel?`)) return;
    // Usa POST com _method=DELETE para compatibilidade com todos os browsers
    const form = useForm({ filename, _method: 'DELETE' });
    form.post(`/tools/hero-images`, { preserveScroll: true });
};

const rebuildForm = useForm({});

const rebuildAllImages = () => {
    if (
        !confirm(
            'Isso irá APAGAR todas as imagens otimizadas e recriá-las a partir dos originais. Continuar?',
        )
    )
        return;
    rebuildForm.post('/tools/hero-images/rebuild', { preserveScroll: true });
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
        <Alert
            v-if="flash?.success"
            class="border-green-910 bg-green-50 text-green-800"
        >
            <CheckCircle class="h-4 w-4" /><AlertTitle>Sucesso</AlertTitle
            ><AlertDescription>{{ flash?.success }}</AlertDescription>
        </Alert>
        <Alert v-if="flash?.error" variant="destructive">
            <AlertCircle class="h-4 w-4" /><AlertTitle>Erro</AlertTitle
            ><AlertDescription>{{ flash?.error }}</AlertDescription>
        </Alert>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2"
                    ><Wrench class="h-5 w-5 text-amber-600" /> Ferramentas
                    disponíveis</CardTitle
                >
                <CardDescription
                    >Utilitários para administração do sistema</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-lg border p-3">
                        <FileText
                            class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                        />
                        <div>
                            <p class="text-sm font-medium">Gerar Sitemap</p>
                            <p class="text-xs text-muted-foreground">
                                Atualiza sitemap.xml com todas as páginas
                                públicas e produtos ativos
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border p-3">
                        <Image class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                        <div>
                            <p class="text-sm font-medium">Carrossel da Home</p>
                            <p class="text-xs text-muted-foreground">
                                Upload e gerenciamento das imagens de fundo da
                                página inicial
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <FileText class="h-6 w-6 text-amber-600" />
                        <div>
                            <CardTitle>Gerar Sitemap</CardTitle
                            ><CardDescription
                                >Atualiza o sitemap.xml</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2 rounded-lg bg-muted/50 p-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Status:</span
                            ><span
                                :class="
                                    sitemap.exists
                                        ? 'text-green-600'
                                        : 'text-red-500'
                                "
                                class="flex items-center gap-1"
                                ><CheckCircle class="h-4 w-4" />
                                {{
                                    sitemap.exists ? 'Gerado' : 'Não gerado'
                                }}</span
                            >
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Última geração:</span
                            ><span class="font-medium">{{
                                sitemap.last_generated
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Produtos no sitemap:</span
                            ><span class="font-medium">{{
                                sitemap.product_count
                            }}</span>
                        </div>
                    </div>
                    <Button
                        class="w-full"
                        :disabled="sitemapForm.processing"
                        @click="generateSitemap"
                        ><RefreshCw
                            class="mr-2 h-4 w-4"
                            :class="{ 'animate-spin': sitemapForm.processing }"
                        />{{
                            sitemapForm.processing
                                ? 'Gerando...'
                                : 'Gerar Sitemap Agora'
                        }}</Button
                    >
                    <p class="text-xs text-muted-foreground">
                        Configure uma cron:
                        <code>php artisan sitemap:generate</code>
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <Image class="h-6 w-6 text-amber-600" />
                        <div>
                            <CardTitle>Imagens do Carrossel</CardTitle
                            ><CardDescription
                                >Gerencie as imagens de fundo da página
                                inicial</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="image_name">Nome SEO da Imagem *</Label>
                        <p class="text-xs text-muted-foreground">
                            Use letras minúsculas, números e hífens. Ex:
                            impressao-3d-resina
                        </p>
                        <input
                            id="image_name"
                            v-model="uploadForm.image_name"
                            type="text"
                            placeholder="ex: impressao-3d-resina"
                            class="flex h-10 w-full rounded-md border border-input bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            :disabled="uploading || uploadForm.processing"
                            @input="
                                sanitizeSeoName(
                                    ($event.target as HTMLInputElement).value,
                                )
                            "
                        />
                        <span
                            v-if="
                                seoFormatError || uploadForm.errors.image_name
                            "
                            class="text-sm text-destructive"
                            >{{
                                seoFormatError || uploadForm.errors.image_name
                            }}</span
                        >
                    </div>
                    <div class="space-y-2">
                        <Label for="hero-image-input">Adicionar Imagens</Label>
                        <p class="text-xs text-muted-foreground">
                            Tamanho máximo: 2MB. Formatos: JPG, PNG, WEBP.
                        </p>
                        <input
                            id="hero-image-input"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            :disabled="uploading || uploadForm.processing"
                            class="flex h-10 w-full rounded-md border border-input bg-white px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            @change="onFilesSelected"
                        />
                        <span
                            v-if="imageError || uploadForm.errors.images"
                            class="text-sm text-destructive"
                            >{{ imageError || uploadForm.errors.images }}</span
                        >
                    </div>
                    <!-- Botão Recriar Todas -->
                    <div v-if="heroImages.length > 0" class="pt-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="rebuildForm.processing"
                            @click="rebuildAllImages"
                        >
                            <RefreshCw
                                class="mr-2 h-4 w-4"
                                :class="{
                                    'animate-spin': rebuildForm.processing,
                                }"
                            />
                            {{
                                rebuildForm.processing
                                    ? 'Recriando...'
                                    : 'Recriar Todas as Imagens'
                            }}
                        </Button>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Recria todas as imagens otimizadas a partir dos
                            originais com pipeline de otimização.
                        </p>
                    </div>

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
                                    @click="deleteImage(img.filename)"
                                >
                                    <Trash2 class="h-4 w-4 text-white" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        Nenhuma imagem no carrossel ainda.
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
