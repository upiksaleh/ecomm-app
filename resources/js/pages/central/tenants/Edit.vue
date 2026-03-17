<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/AppLayout.vue';
import tenant_routes from '@/routes/central/tenants';
import type { BreadcrumbItem } from '@/types';
import type { ITenant } from '@/types';

const props = defineProps<{ tenant: ITenant }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tenants', href: tenant_routes.index() },
    { title: props.tenant.id, href: tenant_routes.show(props.tenant.id) },
    { title: 'Edit', href: '' },
];
</script>

<template>
    <Head :title="`Edit Tenant: ${props.tenant.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="m-4 max-w-2xl space-y-4 rounded-lg border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <div>
                <h1 class="text-xl font-semibold">Edit Tenant</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    ID: <span class="font-mono">{{ props.tenant.id }}</span>
                </p>
            </div>

            <Form
                v-bind="tenant_routes.update.form(props.tenant.id)"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="domain">
                        Domain <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="domain"
                        name="domain"
                        :default-value="props.tenant.domains?.[0]?.domain ?? ''"
                        placeholder="e.g. my-store.localhost"
                        required
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        Changing the domain will replace the existing one.
                    </p>
                    <InputError :message="errors.domain" />
                </div>

                <div class="flex gap-2 pt-3">
                    <Button type="submit" :disabled="processing">Update</Button>
                    <Link :href="tenant_routes.show(props.tenant.id)">
                        <Button type="button" variant="secondary"
                            >Cancel</Button
                        >
                    </Link>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
