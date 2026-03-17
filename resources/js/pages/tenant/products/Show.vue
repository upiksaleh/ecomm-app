<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import product_routes from '@/routes/tenant/products';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ product: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: product_routes.index() },
    { title: props.product.name, href: '' },
];

const formattedPrice = Number(props.product.price).toLocaleString('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
});
</script>

<template>
    <Head :title="`Product: ${props.product.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="m-4 max-w-2xl space-y-6 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Product Details</h1>
                <Link :href="product_routes.edit(props.product.id)">
                    <Button variant="secondary" size="sm">Edit</Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Name
                    </p>
                    <p class="text-sm font-medium">{{ props.product.name }}</p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        SKU
                    </p>
                    <p class="font-mono text-sm">{{ props.product.sku }}</p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Price
                    </p>
                    <p class="text-sm">{{ formattedPrice }}</p>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Quantity
                    </p>
                    <p class="text-sm">{{ props.product.quantity }}</p>
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
                            props.product.active
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                        ]"
                    >
                        {{ props.product.active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="space-y-1">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Created At
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{
                            new Date(props.product.created_at).toLocaleString()
                        }}
                    </p>
                </div>

                <div class="space-y-1 sm:col-span-2">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Description
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ props.product.description || '—' }}
                    </p>
                </div>
            </div>

            <div
                class="border-t border-sidebar-border/70 pt-4 dark:border-sidebar-border"
            >
                <Link :href="product_routes.index()">
                    <Button variant="secondary">← Back to Products</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
