<script setup lang="ts">
import { Head, Link, Form } from '@inertiajs/vue3';
import AppLayout from '@/layouts/tenant/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import type { BreadcrumbItem } from '@/types';
import product_routes from '@/routes/tenant/products';
import { TrashIcon, EyeIcon, PencilIcon } from 'lucide-vue-next';
import { ButtonGroup } from '@/components/ui/button-group';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
const props = defineProps<{ products: any }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Products', href: '' }];
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
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">SKU</th>
                        <th class="px-4 py-2">Price</th>
                        <th class="px-4 py-2">Quantity</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in props.products.data"
                        :key="product.id"
                        class="border-t border-slate-200 dark:border-slate-800"
                    >
                        <td class="px-4 py-2">{{ product.name }}</td>
                        <td class="px-4 py-2">{{ product.sku }}</td>
                        <td class="px-4 py-2">{{ product.price }}</td>
                        <td class="px-4 py-2">{{ product.quantity }}</td>
                        <td class="px-4 py-2">
                            {{ product.active ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="space-x-2 px-4 py-2">
                            <ButtonGroup class="" size="sm">
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
                                    aria-label="Show"
                                    title="Show"
                                    :href="product_routes.edit(product.id)"
                                >
                                    <PencilIcon />
                                </Link>

                                <Form
                                    v-bind="
                                        product_routes.destroy.form(product.id)
                                    "
                                    class="inline"
                                    v-slot="{ processing }"
                                >
                                    <Button
                                        type="submit"
                                        :disabled="processing"
                                        variant="destructive"
                                        size="sm"
                                        aria-label="Delete"
                                        title="Delete"
                                    >
                                        <TrashIcon />
                                    </Button>
                                </Form>
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
        <div class="flex flex-col gap-6">
            <Pagination
                v-slot="{ page }"
                :items-per-page="10"
                :total="props.products.last_page"
                :default-page="2"
            >
                <PaginationContent v-slot="{ items }">
                    <Link
                        v-if="props.products.prev_page_url"
                        :href="props.products.prev_page_url"
                        class="mr-2"
                        ><PaginationPrevious
                    /></Link>
                    <span
                        >{{ props.products.current_page }} /
                        {{ props.products.last_page }}</span
                    >
                    <Link
                        v-if="props.products.next_page_url"
                        :href="props.products.next_page_url"
                        class="ml-2"
                        ><PaginationNext
                    /></Link>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
