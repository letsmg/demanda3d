<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Sortable from 'sortablejs';
import { ArrowLeft, AlertCircle, Save, X, GripVertical, Plus } from '@lucide/vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTestData } from '@/composables/useTestData';
import { index as productsIndex } from '@/routes/products';

const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_IMAGES = 5;
const SLOTS = Array.from({ length: MAX_IMAGES }, (_, i) => i);

const { randomProductName, randomProductDescription, randomPrice } = useTestData();

const imageError = ref<string | null>(null);
const previewImages = ref<{ file: File; url: string }[]>([]);
const thumbnailContainer = ref<HTMLElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
let sortableInstance: Sortable | null = null;

const form = useForm({
    name: '',
    description: '',
    sale_price: '',
    is_active: true,
    images: [] as File[],
});

// =========================================================================
// SortableJS — inicializado e destruído manualmente, não via watcher
// =========================================================================
function initSortable() {
    destroySortable();
    if (!thumbnailContainer.value || previewImages.value.length < 2) return;

    sortableInstance = Sortable.create(thumbnailContainer.value, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-50',
        onEnd() {
            const newOrder = sortableInstance!.toArray();
            const reordered = newOrder
                .map((id) => {
                    const idx = parseInt(id.replace('preview-', ''), 10);
                    return previewImages.value[idx];
                })
                .filter(Boolean);

            previewImages.value = reordered;
            form.images = reordered.map((p) => p.file);
        },
    });
}

function destroySortable() {
    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }
}

// =========================================================================
// Test data
// =========================================================================
function buildTestFields() {
    return [
        { key: 'name', value: randomProductName() },
        { key: 'description', value: randomProductDescription() },
        { key: 'sale_price', value: randomPrice() },
    ];
}

function handleFill() {
    const fresh = buildTestFields();
    for (const f of fresh) {
        if (f.key in form) (form as any)[f.key] = f.value;
    }
}

function handleClear() {
    form.reset();
    imageError.value = null;
    previewImages.value = [];
    destroySortable();
}

// =========================================================================
// Image handling
// =========================================================================
function addImages(files: FileList) {
    if (previewImages.value.length + files.length > MAX_IMAGES) {
        imageError.value = `Máximo de ${MAX_IMAGES} imagens. Você já tem ${previewImages.value.length}.`;
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        if (!ALLOWED_TYPES.includes(file.type)) {
            imageError.value = 'Formato não aceito. Use JPG, PNG ou WEBP.';
            continue;
        }

        if (file.size > MAX_IMAGE_SIZE) {
            imageError.value = `"${file.name}" excede 2MB.`;
            continue;
        }

        previewImages.value.push({ file, url: URL.createObjectURL(file) });
    }

    imageError.value = null;
    syncFormImages();
    nextTick(() => initSortable());
}

function removeImage(index: number) {
    URL.revokeObjectURL(previewImages.value[index].url);
    previewImages.value.splice(index, 1);
    syncFormImages();
    nextTick(() => initSortable());
}

function syncFormImages() {
    form.images = previewImages.value.map((p) => p.file);
}

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        addImages(target.files);
        target.value = '';
    }
}

function triggerFileInput() {
    fileInput.value?.click();
}

/**
 * Drag-and-drop de arquivos do sistema de arquivos diretamente no container.
 */
function onContainerDrop(e: DragEvent) {
    e.preventDefault();
    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        addImages(e.dataTransfer.files);
    }
}

function onDragOver(e: DragEvent) {
    e.preventDefault();
}

onMounted(() => {});

const submit = () => {
    imageError.value = null;
    form.post('/products', { preserveScroll: true });
};
</script>

<template>
    <Head title="Criar Produto" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="productsIndex()"><ArrowLeft class="h-4 w-4" /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Criar Produto</h1>
                <p class="text-sm text-muted-foreground">Adicionar um novo produto à vitrine</p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <FormTestHelper :form="form" :fields="buildTestFields()" label="Produto teste" @fill="handleFill" @clear="handleClear" />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Produto</CardTitle>
                    <CardDescription>Preencha os dados do produto. Os campos SEO (meta tags, schema markup, GTM) são gerados automaticamente a partir destes dados.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Nome *</Label>
                            <Input id="name" v-model="form.name" placeholder="Nome do produto" :class="{ 'border-destructive': form.errors.name }" />
                            <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                        </div>
                        <div class="space-y-2">
                            <Label for="sale_price">Preço de Venda *</Label>
                            <Input id="sale_price" v-model="form.sale_price" type="number" step="0.01" placeholder="0.00" :class="{ 'border-destructive': form.errors.sale_price }" />
                            <span v-if="form.errors.sale_price" class="text-sm text-destructive">{{ form.errors.sale_price }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Descrição</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Descrição do produto" rows="4" />
                    </div>

                    <!-- Imagens do Produto -->
                    <div class="space-y-2">
                        <Label>Imagens do Produto (máx. {{ MAX_IMAGES }})</Label>
                        <p class="text-sm text-muted-foreground">
                            Arraste e solte imagens aqui ou clique para selecionar. Também pode arrastar as miniaturas para reordenar.
                            Tamanho máximo: 2MB. Formatos: JPG, PNG, WEBP.
                        </p>

                        <!-- Área de drop + miniaturas -->
                        <div
                            ref="thumbnailContainer"
                            class="flex flex-wrap gap-3 rounded-lg border-2 border-dashed border-muted-foreground/30 p-4 transition-colors hover:border-primary/50"
                            @dragover="onDragOver"
                            @drop="onContainerDrop"
                            @click="triggerFileInput"
                        >
                            <!-- Miniaturas das imagens selecionadas -->
                            <div
                                v-for="(preview, index) in previewImages"
                                :key="preview.url"
                                :data-id="'preview-' + index"
                                class="group relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-border bg-muted"
                            >
                                <img :src="preview.url" alt="Preview" class="h-full w-full object-cover" />
                                <button
                                    type="button"
                                    @click.stop="removeImage(index)"
                                    class="absolute right-0.5 top-0.5 rounded-full bg-destructive p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                                <div
                                    v-if="previewImages.length > 1"
                                    class="drag-handle absolute bottom-0.5 left-0.5 cursor-grab rounded bg-black/50 p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <GripVertical class="h-3 w-3" />
                                </div>
                            </div>

                            <!-- Slots vazios com "+" -->
                            <div
                                v-for="slot in SLOTS.filter((_, i) => i >= previewImages.length)"
                                :key="'slot-' + slot"
                                class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg border border-dashed border-muted-foreground/40 bg-muted/50 transition-colors hover:border-primary/40 hover:bg-primary/5"
                            >
                                <Plus class="h-6 w-6 text-muted-foreground/50" />
                            </div>
                        </div>

                        <Input
                            ref="fileInput"
                            id="images"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="hidden"
                            @input="onFileChange"
                        />
                        <span v-if="imageError" class="text-sm text-destructive">{{ imageError }}</span>
                        <span v-if="form.errors.images" class="text-sm text-destructive">{{ form.errors.images }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <Label for="is_active">Produto ativo na vitrine?</Label>
                        <input id="is_active" type="checkbox" v-model="form.is_active" class="h-4 w-4" />
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child><Link :href="productsIndex()">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Salvando...' : 'Salvar Produto' }}
                </Button>
            </div>
        </form>
    </div>
</template>