import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Task {
    id: number;
    title: string;
    description: string | null;
    is_completed: boolean;
    priority: 'none' | 'low' | 'medium' | 'high';
    user_id: number;
    due_date: date | null;
    completed_at: string;
    completed_ago: string | null;
    created_at: string;
    updated_at: string;
}

export interface PersonalToken {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
}
