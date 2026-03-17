<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import product_routes from '@/routes/tenant/products';
import type { BreadcrumbItem } from '@/types';

defineProps<{ product: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: product_routes.index() },
    { title: 'Edit', href: '' },
];
</script>

<template>
    <Head title="Edit Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="max-w-3xl space-y-4 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <h1 class="text-xl font-semibold">Edit Product</h1>
            <Form
                v-bind="product_routes.update.form(product.id)"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        :default-value="product.name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div>
                    <Label for="sku">SKU</Label>
                    <Input
                        id="sku"
                        name="sku"
                        required
                        :default-value="product.sku"
                    />
                    <InputError :message="errors.sku" />
                </div>

                <div>
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        name="description"
                        :default-value="product.description"
                    />
                    <InputError :message="errors.description" />
                </div>

                <div>
                    <Label for="price">Price</Label>
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        step="0.01"
                        required
                        :default-value="product.price"
                    />
                    <InputError :message="errors.price" />
                </div>

                <div>
                    <Label for="quantity">Quantity</Label>
                    <Input
                        id="quantity"
                        name="quantity"
                        type="number"
                        min="0"
                        required
                        :default-value="product.quantity"
                    />
                    <InputError :message="errors.quantity" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="active"
                        name="active"
                        :default-value="product.active"
                    />
                    <Label for="active">Active</Label>
                    <InputError :message="errors.active" />
                </div>

                <div class="flex gap-2 pt-3">
                    <Button type="submit" :disabled="processing">Update</Button>
                    <Link :href="product_routes.index()">
                        <Button variant="secondary">Cancel</Button>
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
