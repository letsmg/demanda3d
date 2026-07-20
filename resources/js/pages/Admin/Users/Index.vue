<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Shield,
    UserCog,
    UserCheck,
    Pencil,
    KeyRound,
    X,
} from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: '/dashboard' },
            { title: 'Vendedores', href: '/admin/users' },
        ],
    },
});

interface UserItem {
    id: number;
    display_name: string;
    email: string;
    access_level: number;
    access_label: string;
    is_active: boolean;
    created_at: string;
}

const { users } = defineProps<{
    users: UserItem[];
}>();

// Editing state
const editingUser = ref<UserItem | null>(null);
const showEditModal = ref(false);

const editForm = useForm({
    display_name: '',
    email: '',
    access_level: 0,
});

// Reset password state
const resetPasswordUserId = ref<number | null>(null);
const resetPasswordValue = ref<string | null>(null);
const showResetModal = ref(false);

function openEditModal(user: UserItem) {
    editingUser.value = user;
    editForm.display_name = user.display_name;
    editForm.email = user.email;
    editForm.access_level = user.access_level;
    showEditModal.value = true;
}

function closeEditModal() {
    showEditModal.value = false;
    editingUser.value = null;
    editForm.reset();
}

function submitEdit() {
    if (!editingUser.value) return;
    editForm.put(`/admin/users/${editingUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            toast.success('Vendedor atualizado com sucesso.');
        },
    });
}

function toggleUser(user: UserItem) {
    // Admin não pode ser bloqueado
    if (user.access_level >= 10) {
        toast.error('Não é possível bloquear um Administrador.');
        return;
    }

    const form = useForm({});
    form.patch(`/admin/users/${user.id}/toggle`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(
                `Vendedor ${user.is_active ? 'bloqueado' : 'ativado'} com sucesso.`,
            );
        },
    });
}

function resetPassword(user: UserItem) {
    const form = useForm({});
    form.post(`/admin/users/${user.id}/reset-password`, {
        preserveScroll: true,
        onSuccess: (response: any) => {
            if (response?.props?.reset_password) {
                resetPasswordUserId.value = user.id;
                resetPasswordValue.value = response.props.reset_password;
                showResetModal.value = true;
            }
        },
    });
}

function closeResetModal() {
    showResetModal.value = false;
    resetPasswordUserId.value = null;
    resetPasswordValue.value = null;
}

function copyPassword() {
    if (resetPasswordValue.value) {
        navigator.clipboard.writeText(resetPasswordValue.value);
        toast.success('Senha copiada!');
    }
}

function accessBadge(level: number) {
    if (level >= 10)
        return { variant: 'default' as const, label: 'Admin', icon: Shield };
    if (level >= 1)
        return {
            variant: 'secondary' as const,
            label: 'Gestor',
            icon: UserCog,
        };
    return {
        variant: 'outline' as const,
        label: 'Operacional',
        icon: UserCheck,
    };
}

const filteredUsers = computed(() => {
    // Mostra todos: staff + admins
    return users.filter((u) => u.access_level <= 10);
});
</script>

<template>
    <Head title="Vendedores Cadastrados" />

    <div class="space-y-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                Vendedores Cadastrados
            </h1>
            <p class="text-sm text-muted-foreground">
                Gerencie todos os vendedores e administradores da plataforma.
                Ative, bloqueie, edite dados ou resete senhas.
            </p>
        </div>

        <Card>
            <CardHeader>
                <CardTitle
                    >{{ filteredUsers.length }} usuário(s)
                    encontrado(s)</CardTitle
                >
                <CardDescription
                    >Ordenados por nível de acesso (Admin → Gestor →
                    Operacional)</CardDescription
                >
            </CardHeader>
            <CardContent>
                <div
                    v-if="filteredUsers.length === 0"
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    Nenhum vendedor cadastrado.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="border-b text-left text-sm font-medium text-muted-foreground"
                            >
                                <th class="pr-4 pb-3">Nome</th>
                                <th class="pr-4 pb-3">E-mail</th>
                                <th class="pr-4 pb-3">Nível</th>
                                <th class="pr-4 pb-3 text-center">Ativo</th>
                                <th class="pb-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="border-b last:border-0"
                            >
                                <td class="py-3 pr-4 text-sm font-medium">
                                    {{ user.display_name }}
                                    <span
                                        v-if="!user.is_active"
                                        class="ml-1 text-xs text-muted-foreground"
                                        >(bloqueado)</span
                                    >
                                </td>
                                <td
                                    class="py-3 pr-4 text-sm text-muted-foreground"
                                >
                                    {{ user.email }}
                                </td>
                                <td class="py-3 pr-4">
                                    <Badge
                                        :variant="
                                            accessBadge(user.access_level)
                                                .variant
                                        "
                                        class="gap-1 px-2 py-0.5 text-xs"
                                    >
                                        <component
                                            :is="
                                                accessBadge(user.access_level)
                                                    .icon
                                            "
                                            class="h-3 w-3"
                                        />
                                        {{
                                            accessBadge(user.access_level).label
                                        }}
                                    </Badge>
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    <button
                                        type="button"
                                        class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer items-center rounded-full transition-colors"
                                        :class="
                                            user.is_active
                                                ? 'bg-primary'
                                                : 'bg-muted-foreground/30'
                                        "
                                        :disabled="user.access_level >= 10"
                                        @click="toggleUser(user)"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform"
                                            :class="
                                                user.is_active
                                                    ? 'translate-x-[22px]'
                                                    : 'translate-x-[2px]'
                                            "
                                        />
                                    </button>
                                </td>
                                <td class="py-3 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="openEditModal(user)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="resetPassword(user)"
                                        >
                                            <KeyRound class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Modal de Edição -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="w-full max-w-md rounded-lg border bg-background p-6 shadow-lg"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Editar Vendedor</h2>
                    <Button variant="ghost" size="icon" @click="closeEditModal"
                        ><X class="h-4 w-4"
                    /></Button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="edit_name">Nome de exibição</Label>
                        <Input
                            id="edit_name"
                            v-model="editForm.display_name"
                            placeholder="Nome"
                            required
                        />
                        <span
                            v-if="editForm.errors.display_name"
                            class="text-xs text-destructive"
                            >{{ editForm.errors.display_name }}</span
                        >
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_email">E-mail</Label>
                        <Input
                            id="edit_email"
                            v-model="editForm.email"
                            type="email"
                            placeholder="E-mail"
                            required
                        />
                        <span
                            v-if="editForm.errors.email"
                            class="text-xs text-destructive"
                            >{{ editForm.errors.email }}</span
                        >
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_level">Nível de Acesso</Label>
                        <Select
                            :model-value="String(editForm.access_level)"
                            @update:model-value="
                                (v) => {
                                    editForm.access_level = Number(v);
                                }
                            "
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione o nível" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="0">Operacional</SelectItem>
                                <SelectItem value="1">Gestor</SelectItem>
                                <SelectItem value="10">Admin</SelectItem>
                            </SelectContent>
                        </Select>
                        <span
                            v-if="editForm.errors.access_level"
                            class="text-xs text-destructive"
                            >{{ editForm.errors.access_level }}</span
                        >
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <Button
                            variant="outline"
                            type="button"
                            @click="closeEditModal"
                            >Cancelar</Button
                        >
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Salvando...' : 'Salvar' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Senha Resetada -->
        <div
            v-if="showResetModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="w-full max-w-sm rounded-lg border bg-background p-6 shadow-lg"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">
                        <KeyRound class="mr-1 inline h-5 w-5" />
                        Senha Resetada
                    </h2>
                    <Button variant="ghost" size="icon" @click="closeResetModal"
                        ><X class="h-4 w-4"
                    /></Button>
                </div>
                <p class="mb-3 text-sm text-muted-foreground">
                    A senha do vendedor foi alterada com sucesso. Copie a nova
                    senha abaixo e compartilhe com o usuário.
                    <strong>Esta senha será exibida apenas uma vez.</strong>
                </p>
                <div
                    class="flex items-center gap-2 rounded-md border bg-muted p-3"
                >
                    <code class="flex-1 text-sm break-all select-all">{{
                        resetPasswordValue
                    }}</code>
                    <Button
                        variant="outline"
                        size="sm"
                        class="shrink-0"
                        @click="copyPassword"
                        >Copiar</Button
                    >
                </div>
                <div class="mt-4 flex justify-end">
                    <Button variant="outline" @click="closeResetModal"
                        >Fechar</Button
                    >
                </div>
            </div>
        </div>
    </div>
</template>
