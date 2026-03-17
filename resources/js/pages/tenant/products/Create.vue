<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import type { BreadcrumbItem } from '@/types';
import product_routes from '@/routes/tenant/products';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: '/products' },
    { title: 'Create', href: '' },
];
</script>

<template>
    <Head title="Create Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="max-w-3xl space-y-4 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <h1 class="text-xl font-semibold">Create Product</h1>

            <Form
                v-bind="product_routes.store.form()"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="name">Name</Label>
                    <Input id="name" name="name" required />
                    <InputError :message="errors.name" />
                </div>

                <div>
                    <Label for="sku">SKU</Label>
                    <Input id="sku" name="sku" required />
                    <InputError :message="errors.sku" />
                </div>

                <div>
                    <Label for="description">Description</Label>
                    <Input id="description" name="description" />
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
                    />
                    <InputError :message="errors.quantity" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox id="active" name="active" :default-value="true" />
                    <Label for="active">Active</Label>
                </div>

                <div class="flex gap-2 pt-3">
                    <Button type="submit" :disabled="processing">Save</Button>
                    <Link :href="product_routes.index()">
                        <Button variant="secondary">Cancel</Button>
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
