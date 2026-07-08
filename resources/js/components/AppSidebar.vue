<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    BookOpen,
    FolderGit2,
    LayoutGrid,
    ShieldCheck,
    Truck,
    Users,
    Package,
    ShoppingBag,
    Ship,
    Wrench,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as clientsIndex } from '@/routes/clients';
import { index as inputsIndex } from '@/routes/inputs';
import { index as ordersIndex } from '@/routes/orders';
import { index as productsIndex } from '@/routes/products';
import { index as reportsIndex } from '@/routes/reports';
import { index as suppliersIndex } from '@/routes/suppliers';
import { index as carriersIndex } from '@/routes/carriers';
import { index as freightContractsIndex } from '@/routes/freight-contracts';
import { index as toolsIndex } from '@/routes/tools';
import type { NavItem } from '@/types';

const page = usePage<{ auth: { user?: { access_level?: number } } }>();
const isAdmin = (page.props.auth?.user?.access_level ?? 0) >= 10;

const mainNavItems: NavItem[] = [
    {
        title: 'Painel',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Clientes',
        href: clientsIndex(),
        icon: Users,
    },
    {
        title: 'Pedidos',
        href: ordersIndex(),
        icon: Package,
    },
    {
        title: 'Fornecedores',
        href: suppliersIndex(),
        icon: Truck,
    },
    {
        title: 'Insumos',
        href: inputsIndex(),
        icon: BookOpen,
    },
    {
        title: 'Transportadoras',
        href: carriersIndex(),
        icon: Ship,
    },
    {
        title: 'Contratos de Frete',
        href: freightContractsIndex(),
        icon: FolderGit2,
    },
    {
        title: 'Produtos',
        href: productsIndex(),
        icon: ShoppingBag,
    },
    // Visível apenas para Admin
    ...(isAdmin
        ? [{
              title: 'Ferramentas',
              href: toolsIndex(),
              icon: Wrench,
          }]
        : []),
    {
        title: 'Relatórios',
        href: reportsIndex(),
        icon: BarChart3,
    },
    // Visível apenas para Admin
    ...(isAdmin
        ? [{
              title: 'Vendedores',
              href: '/admin/users',
              icon: ShieldCheck,
          }]
        : []),
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repositório',
        href: 'https://github.com/letsmg/demanda3d',
        icon: FolderGit2,
    },
    {
        title: 'Documentação',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
