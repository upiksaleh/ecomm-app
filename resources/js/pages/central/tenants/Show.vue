<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/AppLayout.vue';
import tenant_routes from '@/routes/central/tenants';
import type { BreadcrumbItem, ITenant } from '@/types';

const props = defineProps<{ tenant: ITenant }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tenants', href: tenant_routes.index() },
    { title: props.tenant.id, href: '' },
];
</script>

<template>
    <Head :title="`Tenant: ${props.tenant.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="m-4 max-w-2xl space-y-6 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Tenant Details</h1>
                <Link :href="tenant_routes.edit(props.tenant.id)">
                    <Button variant="secondary" size="sm">Edit</Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        ID
                    </p>
                    <p class="font-mono text-sm">{{ props.tenant.id }}</p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Domain(s)
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-if="
                                props.tenant.domains &&
                                props.tenant.domains.length
                            "
                        >
                            <span
                                v-for="domain in props.tenant.domains"
                                :key="domain.id"
                                class="inline-block rounded bg-slate-100 px-2 py-0.5 font-mono text-xs dark:bg-slate-800"
                            >
                                {{ domain.domain }}
                            </span>
                        </span>
                        <span v-else class="text-sm text-muted-foreground"
                            >—</span
                        >
                    </div>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Created At
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ new Date(props.tenant.created_at).toLocaleString() }}
                    </p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Updated At
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ new Date(props.tenant.updated_at).toLocaleString() }}
                    </p>
                </div>
            </div>

            <div
                class="border-t border-sidebar-border/70 pt-4 dark:border-sidebar-border"
            >
                <Link :href="tenant_routes.index()">
                    <Button variant="secondary">← Back to Tenants</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
