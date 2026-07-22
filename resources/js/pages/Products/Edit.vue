<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    AlertCircle,
    Save,
    X,
    GripVertical,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { ref, onMounted, nextTick } from 'vue';
import FormTestHelper from '@/components/FormTestHelper.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { Textarea } from '@/components/ui/textarea';
import { useTestData } from '@/composables/useTestData';
import { index as productsIndex } from '@/routes/products';
import type { Product } from '@/types';

const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_IMAGES = 5;
const SLOTS = Array.from({ length: MAX_IMAGES }, (_, i) => i);

interface ExistingImage {
    id: number;
    url: string;
    order: number;
}

interface NewPreview {
    file: File;
    url: string;
}

const { randomProductName, randomProductDescription, randomPrice } =
    useTestData();

const props = defineProps<{
    product: Product & {
        images?: { id: number; url: string; order: number }[];
    };
}>();

const imageError = ref<string | null>(null);
const existingImages = ref<ExistingImage[]>(
    (props.product.images || []).sort((a, b) => a.order - b.order),
);
const newPreviews = ref<NewPreview[]>([]);
const imagesToDelete = ref<number[]>([]);
const thumbnailContainer = ref<HTMLElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
let sortableInstance: Sortable | null = null;

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    sale_price: props.product.sale_price,
    is_active: props.product.is_active,
    images: [] as File[],
    images_order: existingImages.value.map((img) => img.id),
    images_delete: [] as number[],
});

// =========================================================================
// SortableJS — corrigido: destroy antes de init, acionado em cada mudança
// =========================================================================
function initSortable() {
    destroySortable();
    const total = existingImages.value.length + newPreviews.value.length;

    if (!thumbnailContainer.value || total < 2) {
        return;
    }

    sortableInstance = Sortable.create(thumbnailContainer.value, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-50',
        onEnd() {
            const newOrder = sortableInstance!.toArray();

            const reorderedExisting = newOrder
                .map((id) =>
                    existingImages.value.find(
                        (img) => `existing-${img.id}` === id,
                    ),
                )
                .filter(Boolean) as ExistingImage[];

            const reorderedNew = newOrder
                .map((id) => {
                    const match = id.match(/^new-(\d+)$/);

                    return match
                        ? newPreviews.value[parseInt(match[1], 10)]
                        : null;
                })
                .filter(Boolean) as NewPreview[];

            existingImages.value = reorderedExisting;
            newPreviews.value = reorderedNew;

            form.images_order = existingImages.value.map((img) => img.id);
            form.images = reorderedNew.map((p) => p.file);
        },
    });
}

function destroySortable() {
    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }
}

const totalThumbnails = () =>
    existingImages.value.length + newPreviews.value.length;

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
        if (f.key in form) {
            (form as any)[f.key] = f.value;
        }
    }
}

function handleClear() {
    form.reset();
    imageError.value = null;
    newPreviews.value = [];
    destroySortable();
}

// =========================================================================
// Image handling
// =========================================================================
function addImages(files: FileList) {
    if (totalThumbnails() + files.length > MAX_IMAGES) {
        imageError.value = `Máximo de ${MAX_IMAGES} imagens. Você já tem ${totalThumbnails()}.`;

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

        newPreviews.value.push({ file, url: URL.createObjectURL(file) });
    }

    imageError.value = null;
    form.images = newPreviews.value.map((p) => p.file);
    nextTick(() => initSortable());
}

function removeExistingImage(imageId: number) {
    existingImages.value = existingImages.value.filter(
        (img) => img.id !== imageId,
    );
    imagesToDelete.value.push(imageId);
    form.images_delete = [...imagesToDelete.value];
    form.images_order = existingImages.value.map((img) => img.id);
    nextTick(() => initSortable());
}

function removeNewImage(index: number) {
    URL.revokeObjectURL(newPreviews.value[index].url);
    newPreviews.value.splice(index, 1);
    form.images = newPreviews.value.map((p) => p.file);
    nextTick(() => initSortable());
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

function onContainerDrop(e: DragEvent) {
    e.preventDefault();

    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        addImages(e.dataTransfer.files);
    }
}

function onDragOver(e: DragEvent) {
    e.preventDefault();
}

onMounted(() => {
    if (totalThumbnails() > 1) {
        initSortable();
    }
});

const submit = () => {
    imageError.value = null;
    form.put(`/products/${props.product.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Editar Produto" />
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="productsIndex()"
                    ><ArrowLeft class="h-4 w-4"
                /></Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Editar Produto
                </h1>
                <p class="text-sm text-muted-foreground">
                    Editando: {{ product.name }}
                </p>
            </div>
        </div>

        <Alert v-if="Object.keys(form.errors).length > 0" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Erro de validação</AlertTitle>
            <AlertDescription>Verifique os campos abaixo.</AlertDescription>
        </Alert>

        <FormTestHelper
            :form="form"
            :fields="buildTestFields()"
            label="Editar Produto"
            @fill="handleFill"
            @clear="handleClear"
        />

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Informações do Produto</CardTitle>
                    <CardDescription
                        >Edite os dados do produto. Os campos SEO são gerados
                        automaticamente.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Nome *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Nome"
                                :class="{
                                    'border-destructive': form.errors.name,
                                }"
                            />
                            <span
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                                >{{ form.errors.name }}</span
                            >
                        </div>
                        <div class="space-y-2">
                            <Label for="sale_price">Preço de Venda *</Label>
                            <Input
                                id="sale_price"
                                v-model="form.sale_price"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                :class="{
                                    'border-destructive':
                                        form.errors.sale_price,
                                }"
                            />
                            <span
                                v-if="form.errors.sale_price"
                                class="text-sm text-destructive"
                                >{{ form.errors.sale_price }}</span
                            >
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Descrição</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Descrição"
                            rows="4"
                        />
                    </div>

                    <!-- Imagens do Produto -->
                    <div class="space-y-2">
                        <Label
                            >Imagens do Produto (máx. {{ MAX_IMAGES }})</Label
                        >
                        <p class="text-sm text-muted-foreground">
                            Arraste as miniaturas para reordenar. Arraste novas
                            imagens do seu computador para cá ou clique para
                            selecionar. Tamanho máximo: 2MB. Formatos: JPG, PNG,
                            WEBP.
                        </p>

                        <!-- Área de drop + miniaturas existentes + novas + slots vazios -->
                        <div
                            v-if="totalThumbnails() < MAX_IMAGES"
                            ref="thumbnailContainer"
                            class="flex flex-wrap gap-3 rounded-lg border-2 border-dashed border-muted-foreground/30 p-4 transition-colors hover:border-primary/50"
                            @dragover="onDragOver"
                            @drop="onContainerDrop"
                        >
                            <!-- Existing images -->
                            <div
                                v-for="img in existingImages"
                                :key="'existing-' + img.id"
                                :data-id="'existing-' + img.id"
                                class="group relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-border bg-muted"
                            >
                                <img
                                    :src="img.url"
                                    :alt="'Imagem ' + img.order"
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click.stop="removeExistingImage(img.id)"
                                    class="absolute top-0.5 right-0.5 rounded-full bg-destructive p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </button>
                                <div
                                    v-if="totalThumbnails() > 1"
                                    class="drag-handle absolute bottom-0.5 left-0.5 cursor-grab rounded bg-black/50 p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <GripVertical class="h-3 w-3" />
                                </div>
                            </div>

                            <!-- New previews -->
                            <div
                                v-for="(preview, index) in newPreviews"
                                :key="'new-' + index"
                                :data-id="'new-' + index"
                                class="group relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border-2 border-dashed border-primary/50 bg-muted"
                            >
                                <img
                                    :src="preview.url"
                                    alt="Nova imagem"
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click.stop="removeNewImage(index)"
                                    class="absolute top-0.5 right-0.5 rounded-full bg-destructive p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                                <div
                                    v-if="totalThumbnails() > 1"
                                    class="drag-handle absolute bottom-0.5 left-0.5 cursor-grab rounded bg-black/50 p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <GripVertical class="h-3 w-3" />
                                </div>
                            </div>

                            <!-- Slots vazios com "+" — clicáveis para abrir o file picker -->
                            <button
                                v-for="slot in SLOTS.filter(
                                    (_, i) => i >= totalThumbnails(),
                                )"
                                :key="'slot-' + slot"
                                type="button"
                                class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg border border-dashed border-muted-foreground/40 bg-muted/50 transition-colors hover:border-primary/40 hover:bg-primary/5"
                                @click="triggerFileInput"
                            >
                                <Plus
                                    class="h-6 w-6 text-muted-foreground/50"
                                />
                            </button>
                        </div>

                        <input
                            ref="fileInput"
                            id="images"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="hidden"
                            @change="onFileChange"
                        />
                        <span
                            v-if="imageError"
                            class="text-sm text-destructive"
                            >{{ imageError }}</span
                        >
                        <span
                            v-if="form.errors.images"
                            class="text-sm text-destructive"
                            >{{ form.errors.images }}</span
                        >
                    </div>

                    <div class="flex items-center gap-2">
                        <Label for="is_active">Produto ativo?</Label>
                        <input
                            id="is_active"
                            type="checkbox"
                            v-model="form.is_active"
                            class="h-4 w-4"
                        />
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex items-center justify-end gap-3">
                <Button variant="outline" as-child
                    ><Link :href="productsIndex()">Cancelar</Link></Button
                >
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />{{
                        form.processing ? 'Salvando...' : 'Salvar Alterações'
                    }}
                </Button>
            </div>
        </form>
    </div>
</template>
