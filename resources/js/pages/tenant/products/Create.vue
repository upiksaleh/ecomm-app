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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: product_routes.index() },
    { title: 'Create', href: '' },
];
</script>

<template>
    <Head title="Create Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="m-4 max-w-2xl space-y-4 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <h1 class="text-xl font-semibold">Create Product</h1>

            <Form
                v-bind="product_routes.store.form()"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="name">
                        Name <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        name="name"
                        placeholder="e.g. Wireless Headphone"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>

                <div>
                    <Label for="sku">
                        SKU <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="sku"
                        name="sku"
                        placeholder="e.g. WH-1000XM5"
                        required
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        Stock Keeping Unit — must be unique across all products.
                    </p>
                    <InputError :message="errors.sku" />
                </div>

                <div>
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        name="description"
                        placeholder="Short product description (optional)"
                    />
                    <InputError :message="errors.description" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="price">
                            Price <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="price"
                            name="price"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0"
                            required
                        />
                        <InputError :message="errors.price" />
                    </div>

                    <div>
                        <Label for="quantity">
                            Quantity <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="quantity"
                            name="quantity"
                            type="number"
                            min="0"
                            placeholder="0"
                            required
                        />
                        <InputError :message="errors.quantity" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox id="active" name="active" :default-value="true" />
                    <Label for="active">Active</Label>
                    <InputError :message="errors.active" />
                </div>

                <div
                    class="flex gap-2 border-t border-sidebar-border/70 pt-4 dark:border-sidebar-border"
                >
                    <Button type="submit" :disabled="processing">
                        Create Product
                    </Button>
                    <Link :href="product_routes.index()">
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
