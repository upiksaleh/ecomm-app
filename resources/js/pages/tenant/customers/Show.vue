<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { TrashIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
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

interface CartStatus {
    hasActiveCart: boolean;
    itemCount: number;
}

const props = defineProps<{
    customer: Customer;
    cartStatus: CartStatus;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customers', href: customers_routes.index.url() },
    { title: props.customer.name, href: '' },
];

function deleteCustomer() {
    if (
        confirm(
            `Delete customer "${props.customer.name}"? This action cannot be undone and will also delete their cart and cart items.`,
        )
    ) {
        router.delete(customers_routes.destroy.url(props.customer.id));
    }
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getVerificationStatus(): string {
    return props.customer.email_verified_at ? 'Verified' : 'Unverified';
}
</script>

<template>
    <Head :title="`Customer: ${customer.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="m-4 max-w-2xl space-y-6 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Customer Details</h1>
                <Button variant="destructive" size="sm" @click="deleteCustomer">
                    <TrashIcon class="mr-2 h-4 w-4" />
                    Delete Customer
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Name
                    </p>
                    <p class="text-sm font-medium">{{ customer.name }}</p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Email
                    </p>
                    <p class="text-sm">{{ customer.email }}</p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Status
                    </p>
                    <span
                        :class="[
                            'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                            customer.email_verified_at
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                        ]"
                    >
                        {{ getVerificationStatus() }}
                    </span>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Email Verified At
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{
                            customer.email_verified_at
                                ? formatDate(customer.email_verified_at)
                                : '—'
                        }}
                    </p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Registered At
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatDate(customer.created_at) }}
                    </p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Last Updated
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatDate(customer.updated_at) }}
                    </p>
                </div>
            </div>

            <div
                class="border-t border-sidebar-border/70 pt-4 dark:border-sidebar-border"
            >
                <h2 class="mb-3 text-sm font-semibold">Cart Status</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Active Cart
                        </p>
                        <span
                            :class="[
                                'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                                cartStatus.hasActiveCart
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'
                                    : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                            ]"
                        >
                            {{ cartStatus.hasActiveCart ? 'Yes' : 'No' }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Items in Cart
                        </p>
                        <p class="text-sm">{{ cartStatus.itemCount }}</p>
                    </div>
                </div>
            </div>

            <div
                class="border-t border-sidebar-border/70 pt-4 dark:border-sidebar-border"
            >
                <Link :href="customers_routes.index.url()">
                    <Button variant="secondary">← Back to Customers</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
