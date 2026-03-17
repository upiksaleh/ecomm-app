<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import product_routes from '@/routes/tenant/products';
const props = defineProps<{ product: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: '/products' },
    { title: props.product.name, href: '' },
];
</script>

<template>
    <Head :title="`Product: ${props.product.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <h1 class="text-xl font-semibold">{{ props.product.name }}</h1>
            <p class="text-sm text-muted-foreground">
                SKU: {{ props.product.sku }}
            </p>
            <p class="mt-4">
                {{ props.product.description || 'No description' }}
            </p>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>Price: {{ props.product.price }}</div>
                <div>Quantity: {{ props.product.quantity }}</div>
                <div>
                    Status: {{ props.product.active ? 'Active' : 'Inactive' }}
                </div>
                <div>
                    Created:
                    {{ new Date(props.product.created_at).toLocaleString() }}
                </div>
            </div>
            <div class="mt-4">
                <Link :href="product_routes.index()">
                    <Button as="a" variant="secondary">Back</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
