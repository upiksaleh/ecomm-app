<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/AppLayout.vue';
import tenant_routes from '@/routes/central/tenants';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tenants', href: tenant_routes.index() },
    { title: 'Create', href: '' },
];
</script>

<template>
    <Head title="Create Tenant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="m-4 max-w-2xl space-y-4 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <h1 class="text-xl font-semibold">Create Tenant</h1>

            <Form
                v-bind="tenant_routes.store.form()"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="id">
                        ID <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="id"
                        name="id"
                        placeholder="e.g. my-store"
                        required
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        Only lowercase letters, numbers, hyphens and underscores
                        are allowed.
                    </p>
                    <InputError :message="errors.id" />
                </div>

                <div>
                    <Label for="domain">
                        Domain <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="domain"
                        name="domain"
                        placeholder="e.g. my-store.localhost"
                        required
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        The domain used to access this tenant's storefront.
                    </p>
                    <InputError :message="errors.domain" />
                </div>

                <div class="flex gap-2 pt-3">
                    <Button type="submit" :disabled="processing">
                        Create Tenant
                    </Button>
                    <Link :href="tenant_routes.index()">
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
