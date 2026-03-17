<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { TrashIcon, EyeIcon, PencilIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import AppLayout from '@/layouts/central/AppLayout.vue';
import tenant_routes from '@/routes/central/tenants';
import type { BreadcrumbItem, ITenant } from '@/types';

interface PaginatedTenants {
    data: ITenant[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{ tenants: PaginatedTenants }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tenants', href: '' }];

function deleteTenant(id: string) {
    if (
        confirm(
            'Delete this tenant and its database? This action cannot be undone.',
        )
    ) {
        router.delete(tenant_routes.destroy.url(id));
    }
}
</script>

<template>
    <Head title="Tenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-4 flex items-center justify-between">
            <Link :href="tenant_routes.create()">
                <Button>Create Tenant</Button>
            </Link>
        </div>

        <div
            class="m-4 overflow-hidden rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-slate-50 text-xs text-slate-500 uppercase dark:bg-slate-900 dark:text-slate-300"
                >
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Domain</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="tenant in props.tenants.data"
                        :key="tenant.id"
                        class="border-t border-slate-200 dark:border-slate-800"
                    >
                        <td
                            class="px-4 py-2 font-mono text-xs text-muted-foreground"
                        >
                            {{ tenant.id }}
                        </td>
                        <td class="px-4 py-2">
                            <span
                                v-if="tenant.domains && tenant.domains.length"
                                class="inline-flex flex-wrap gap-1"
                            >
                                <span
                                    v-for="domain in tenant.domains"
                                    :key="domain.id"
                                    class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs dark:bg-slate-800"
                                >
                                    {{ domain.domain }}
                                </span>
                            </span>
                            <span v-else class="text-muted-foreground">—</span>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">
                            {{
                                new Date(tenant.created_at).toLocaleDateString()
                            }}
                        </td>
                        <td class="space-x-2 px-4 py-2">
                            <ButtonGroup size="sm">
                                <Link
                                    :as="Button"
                                    size="sm"
                                    aria-label="Show"
                                    title="Show"
                                    :href="tenant_routes.show(tenant.id)"
                                >
                                    <EyeIcon />
                                </Link>
                                <Link
                                    :as="Button"
                                    variant="secondary"
                                    size="sm"
                                    aria-label="Edit"
                                    title="Edit"
                                    :href="tenant_routes.edit(tenant.id)"
                                >
                                    <PencilIcon />
                                </Link>
                                <Link
                                    :as="Button"
                                    variant="destructive"
                                    size="sm"
                                    aria-label="Delete"
                                    title="Delete"
                                    @click="deleteTenant(tenant.id)"
                                >
                                    <TrashIcon />
                                </Link>
                            </ButtonGroup>
                        </td>
                    </tr>
                    <tr v-if="props.tenants.data.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-10 text-center text-muted-foreground"
                        >
                            No tenants yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="m-4 flex items-center gap-2">
            <Pagination
                :items-per-page="15"
                :total="props.tenants.last_page"
                :default-page="props.tenants.current_page"
            >
                <PaginationContent>
                    <Link
                        v-if="props.tenants.prev_page_url"
                        :href="props.tenants.prev_page_url"
                        class="mr-2"
                    >
                        <PaginationPrevious />
                    </Link>
                    <span class="text-sm text-muted-foreground">
                        {{ props.tenants.current_page }} /
                        {{ props.tenants.last_page }}
                    </span>
                    <Link
                        v-if="props.tenants.next_page_url"
                        :href="props.tenants.next_page_url"
                        class="ml-2"
                    >
                        <PaginationNext />
                    </Link>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
