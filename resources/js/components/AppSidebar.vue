<script setup lang="ts">
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
import { adminDashboard, agentDashboard, adminTours, agentTours, adminAgentsList } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();

const auth = computed(() => page.props.auth);

const role = computed(() => auth.value.user.role);

let mainNavItems: NavItem[] = [];

if (role.value === 'admin') {
    mainNavItems = [
        {
            title: 'Dashboard',
            href: adminDashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Tours',
            href: adminTours(),
            icon: LayoutGrid,
        },
        {
            title: 'Agents',
            href: adminAgentsList(),
            icon: Users,
        },
    ];
}else{
    mainNavItems = [
        {
            title: 'Dashboard',
            href: agentDashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Tours',
            href: agentTours(),
            icon: LayoutGrid,
        },
    ];
}

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="mainNavItems[0].href">
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
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
