<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    TrashIcon,
    EyeIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    SearchIcon,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import { Input } from '@/components/ui/input';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import customers_routes from '@/routes/tenant/customers';
import type { BreadcrumbItem } from '@/types';

interface Customer {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Filters {
    search?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
}

const props = defineProps<{
    customers: PaginatedResponse<Customer>;
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Customers', href: '' }];

const searchQuery = ref(props.filters.search || '');

const debouncedSearch = useDebounceFn((value: string) => {
    router.get(
        customers_routes.index.url(),
        {
            search: value || undefined,
            sort: props.filters.sort,
            direction: props.filters.direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}, 300);

watch(searchQuery, (newValue) => {
    debouncedSearch(newValue);
});

function sortBy(column: string) {
    const currentSort = props.filters.sort;
    const currentDirection = props.filters.direction;

    let newDirection: 'asc' | 'desc' = 'asc';

    if (currentSort === column) {
        newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    }

    router.get(
        customers_routes.index.url(),
        {
            search: props.filters.search,
            sort: column,
            direction: newDirection,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function deleteCustomer(id: number, name: string) {
    if (confirm(`Delete customer "${name}"? This action cannot be undone.`)) {
        router.delete(customers_routes.destroy.url(id));
    }
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getCustomerStatus(customer: Customer): string {
    return customer.email_verified_at ? 'Verified' : 'Unverified';
}

function isSorted(column: string): boolean {
    return props.filters.sort === column;
}

function getSortIcon(column: string) {
    if (!isSorted(column)) {
return null;
}

    return props.filters.direction === 'asc' ? ArrowUpIcon : ArrowDownIcon;
}
</script>

<template>
    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <div class="relative max-w-md">
                <SearchIcon
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by name or email..."
                    class="pl-9"
                />
            </div>
        </div>

        <div
            class="m-4 overflow-hidden rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-slate-50 text-xs text-slate-500 uppercase dark:bg-slate-900 dark:text-slate-300"
                >
                    <tr>
                        <th class="px-4 py-3">
                            <button
                                @click="sortBy('name')"
                                class="flex items-center gap-1 hover:text-slate-700 dark:hover:text-slate-100"
                            >
                                Name
                                <component
                                    :is="getSortIcon('name')"
                                    v-if="getSortIcon('name')"
                                    class="h-3 w-3"
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3">
                            <button
                                @click="sortBy('email')"
                                class="flex items-center gap-1 hover:text-slate-700 dark:hover:text-slate-100"
                            >
                                Email
                                <component
                                    :is="getSortIcon('email')"
                                    v-if="getSortIcon('email')"
                                    class="h-3 w-3"
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3">
                            <button
                                @click="sortBy('created_at')"
                                class="flex items-center gap-1 hover:text-slate-700 dark:hover:text-slate-100"
                            >
                                Registered
                                <component
                                    :is="getSortIcon('created_at')"
                                    v-if="getSortIcon('created_at')"
                                    class="h-3 w-3"
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="customer in customers.data"
                        :key="customer.id"
                        class="border-t border-slate-200 dark:border-slate-800"
                    >
                        <td class="px-4 py-2 font-medium">
                            {{ customer.name }}
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">
                            {{ customer.email }}
                        </td>
                        <td class="px-4 py-2">
                            {{ formatDate(customer.created_at) }}
                        </td>
                        <td class="px-4 py-2">
                            <span
                                :class="[
                                    'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                                    customer.email_verified_at
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                                ]"
                            >
                                {{ getCustomerStatus(customer) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <ButtonGroup size="sm">
                                <Link
                                    :as="Button"
                                    size="sm"
                                    aria-label="View Details"
                                    title="View Details"
                                    :href="
                                        customers_routes.show.url(customer.id)
                                    "
                                >
                                    <EyeIcon />
                                </Link>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    aria-label="Delete"
                                    title="Delete"
                                    @click="
                                        deleteCustomer(
                                            customer.id,
                                            customer.name,
                                        )
                                    "
                                >
                                    <TrashIcon />
                                </Button>
                            </ButtonGroup>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-10 text-center text-muted-foreground"
                        >
                            <template v-if="filters.search">
                                No customers found matching "{{
                                    filters.search
                                }}".
                            </template>
                            <template v-else> No customers yet. </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="m-4 flex items-center gap-2">
            <Pagination
                :items-per-page="15"
                :total="customers.last_page"
                :default-page="customers.current_page"
            >
                <PaginationContent>
                    <Link
                        v-if="customers.prev_page_url"
                        :href="customers.prev_page_url"
                        class="mr-2"
                    >
                        <PaginationPrevious />
                    </Link>
                    <span class="text-sm text-muted-foreground">
                        {{ customers.current_page }} /
                        {{ customers.last_page }}
                    </span>
                    <Link
                        v-if="customers.next_page_url"
                        :href="customers.next_page_url"
                        class="ml-2"
                    >
                        <PaginationNext />
                    </Link>
                </PaginationContent>
            </Pagination>
            <span class="text-sm text-muted-foreground">
                Total: {{ customers.total }} customers
            </span>
        </div>
    </AppLayout>
</template>
