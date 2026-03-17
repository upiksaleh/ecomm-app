<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { TrashIcon, EyeIcon, PencilIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import product_routes from '@/routes/tenant/products';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ products: any }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Products', href: '' }];

function deleteProduct(id: number) {
    if (confirm('Delete this product? This action cannot be undone.')) {
        router.delete(product_routes.destroy.url(id));
    }
}
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-4 flex items-center justify-between">
            <Link :href="product_routes.create()">
                <Button>Create Product</Button>
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
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in props.products.data"
                        :key="product.id"
                        class="border-t border-slate-200 dark:border-slate-800"
                    >
                        <td class="px-4 py-2 font-medium">
                            {{ product.name }}
                        </td>
                        <td
                            class="px-4 py-2 font-mono text-xs text-muted-foreground"
                        >
                            {{ product.sku }}
                        </td>
                        <td class="px-4 py-2">
                            {{
                                Number(product.price).toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    minimumFractionDigits: 2,
                                })
                            }}
                        </td>
                        <td class="px-4 py-2">
                            {{ product.quantity }}
                        </td>
                        <td class="px-4 py-2">
                            <span
                                :class="[
                                    'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                                    product.active
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                                ]"
                            >
                                {{ product.active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <ButtonGroup size="sm">
                                <Link
                                    :as="Button"
                                    size="sm"
                                    aria-label="Show"
                                    title="Show"
                                    :href="product_routes.show(product.id)"
                                >
                                    <EyeIcon />
                                </Link>
                                <Link
                                    :as="Button"
                                    variant="secondary"
                                    size="sm"
                                    aria-label="Edit"
                                    title="Edit"
                                    :href="product_routes.edit(product.id)"
                                >
                                    <PencilIcon />
                                </Link>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    aria-label="Delete"
                                    title="Delete"
                                    @click="deleteProduct(product.id)"
                                >
                                    <TrashIcon />
                                </Button>
                            </ButtonGroup>
                        </td>
                    </tr>
                    <tr v-if="props.products.data.length === 0">
                        <td
                            colspan="6"
                            class="px-4 py-10 text-center text-muted-foreground"
                        >
                            No products yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="m-4 flex items-center gap-2">
            <Pagination
                :items-per-page="15"
                :total="props.products.last_page"
                :default-page="props.products.current_page"
            >
                <PaginationContent>
                    <Link
                        v-if="props.products.prev_page_url"
                        :href="props.products.prev_page_url"
                        class="mr-2"
                    >
                        <PaginationPrevious />
                    </Link>
                    <span class="text-sm text-muted-foreground">
                        {{ props.products.current_page }} /
                        {{ props.products.last_page }}
                    </span>
                    <Link
                        v-if="props.products.next_page_url"
                        :href="props.products.next_page_url"
                        class="ml-2"
                    >
                        <PaginationNext />
                    </Link>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
