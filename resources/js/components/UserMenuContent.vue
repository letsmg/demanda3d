<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Banknote,
    KeyRound,
    LogOut,
    Palette,
    User as UserIcon,
} from 'lucide-vue-next';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const page = usePage<{
    auth: { user?: { access_level?: number; isCarrier?: boolean } };
}>();
const accessLevel = page.props.auth?.user?.access_level ?? 0;
const isCarrier = accessLevel === 5 || accessLevel === 6;
const isAdmin = accessLevel >= 10;
const isSeller1 = accessLevel === 1;
const bankHref = isAdmin ? '/admin/bank' : '/settings/bank';

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <!-- Carrier menu -->
        <template v-if="isCarrier">
            <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full cursor-pointer"
                    href="/carrier/profile"
                    prefetch
                >
                    <UserIcon class="mr-2 h-4 w-4" />
                    Perfil
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full cursor-pointer"
                    href="/carrier/bank"
                    prefetch
                >
                    <Banknote class="mr-2 h-4 w-4" />
                    Dados Bancários
                </Link>
            </DropdownMenuItem>
        </template>

        <!-- Staff menu -->
        <template v-else>
            <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full cursor-pointer"
                    :href="edit()"
                    prefetch
                >
                    <UserIcon class="mr-2 h-4 w-4" />
                    Perfil
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full cursor-pointer"
                    href="/settings/security"
                    prefetch
                >
                    <KeyRound class="mr-2 h-4 w-4" />
                    Senha
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem v-if="isAdmin || isSeller1" :as-child="true">
                <Link
                    class="block w-full cursor-pointer"
                    :href="bankHref"
                    prefetch
                >
                    <Banknote class="mr-2 h-4 w-4" />
                    Dados Bancários
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full cursor-pointer"
                    href="/settings/appearance"
                    prefetch
                >
                    <Palette class="mr-2 h-4 w-4" />
                    Aparência
                </Link>
            </DropdownMenuItem>
        </template>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Sair
        </Link>
    </DropdownMenuItem>
</template>
