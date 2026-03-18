<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '',
    },
];

defineProps<{
    productsTotal: number;
    productsActive: number;
    productsInactive: number;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center p-6 bg-white dark:bg-zinc-900 shadow-sm"
                >
                    <dt class="text-sm font-medium text-muted-foreground truncate dark:text-zinc-400">Total Products</dt>
                    <dd class="mt-1 text-4xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ productsTotal }}</dd>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center p-6 bg-white dark:bg-zinc-900 shadow-sm"
                >
                    <dt class="text-sm font-medium text-muted-foreground truncate dark:text-zinc-400">Active Products</dt>
                    <dd class="mt-1 text-4xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ productsActive }}</dd>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-center justify-center p-6 bg-white dark:bg-zinc-900 shadow-sm"
                >
                    <dt class="text-sm font-medium text-muted-foreground truncate dark:text-zinc-400">Inactive Products</dt>
                    <dd class="mt-1 text-4xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ productsInactive }}</dd>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
