<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, LayoutList, ListChecks } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard', {}, false),
        icon: LayoutGrid,
    },
    {
        title: 'All Tasks',
        href: route('tasks.index', {}, false),
        icon: LayoutList,
    },
];

const priorityItems: NavItem[] = [
    {
        title: 'High Priority',
        href: route('tasks.index', { priority: 'high' }, false),
    },
    {
        title: 'Medium Priority',
        href: route('tasks.index', { priority: 'medium' }, false),
    },
    {
        title: 'Low Priority',
        href: route('tasks.index', { priority: 'low' }, false),
    },
];

const dueDateItems: NavItem[] = [
    {
        title: 'Overdue',
        href: route('tasks.index', { due: 'overdue' }, false),
    },
    {
        title: 'Due Today',
        href: route('tasks.index', { due: 'today' }, false),
    },
    {
        title: 'Due This Week',
        href: route('tasks.index', { due: 'thisweek' }, false),
    },
    {
        title: 'Due This Month',
        href: route('tasks.index', { due: 'thismonth' }, false),
    },
];

const footerNavItems: NavItem[] = [

];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" groupLabel="Where to?" class="mb-2" />

            <NavMain :items="priorityItems" groupLabel="View Tasks by Priority" class="mb-2" />

            <NavMain :items="dueDateItems" groupLabel="View Tasks by Due Date" class="mb-2" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
