<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ShoppingCartIcon, PackageIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/tenant/ShopLayout.vue';
import cart_routes from '@/routes/tenant/cart';
import customer_routes from '@/routes/tenant/customer';
import shop_routes from '@/routes/tenant/shop';

const props = defineProps<{ products: any }>();

const page = usePage();
const customer = computed(() => page.props.auth.customer);

function addToCart(productId: number) {
    router.post(
        cart_routes.add.url(),
        { product_id: productId, quantity: 1 },
        { preserveScroll: true },
    );
}

function formattedPrice(price: number | string) {
    return Number(price).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    });
}
</script>

<template>
    <Head title="Shop" />

    <ShopLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">All Products</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ props.products.total }} product{{
                    props.products.total !== 1 ? 's' : ''
                }}
                available
            </p>
        </div>

        <!-- Empty state -->
        <div
            v-if="props.products.data.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-20 text-center"
        >
            <PackageIcon class="mb-4 size-12 text-muted-foreground/40" />
            <p class="text-sm font-medium text-muted-foreground">
                No products available yet.
            </p>
        </div>

        <!-- Product grid -->
        <div
            v-else
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <div
                v-for="product in props.products.data"
                :key="product.id"
                class="group flex flex-col overflow-hidden rounded-xl border border-border bg-card transition-shadow hover:shadow-md"
            >
                <!-- Image placeholder -->
                <Link :href="shop_routes.show(product.id)" class="block">
                    <div
                        class="flex aspect-square items-center justify-center bg-slate-100 dark:bg-slate-800"
                    >
                        <PackageIcon
                            class="size-16 text-slate-300 dark:text-slate-600"
                        />
                    </div>
                </Link>

                <!-- Info -->
                <div class="flex flex-1 flex-col gap-3 p-4">
                    <div class="flex-1">
                        <Link :href="shop_routes.show(product.id)">
                            <h2
                                class="line-clamp-2 text-sm leading-snug font-medium text-foreground hover:underline"
                            >
                                {{ product.name }}
                            </h2>
                        </Link>
                        <p
                            class="mt-0.5 font-mono text-xs text-muted-foreground"
                        >
                            {{ product.sku }}
                        </p>
                        <p
                            v-if="product.description"
                            class="mt-2 line-clamp-2 text-xs text-muted-foreground"
                        >
                            {{ product.description }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-base font-semibold text-foreground">
                            {{ formattedPrice(product.price) }}
                        </span>
                        <span
                            v-if="product.quantity === 0"
                            class="text-xs text-destructive"
                        >
                            Out of stock
                        </span>
                    </div>

                    <!-- Add to cart / Login prompt -->
                    <Button
                        v-if="customer"
                        size="sm"
                        class="w-full gap-1.5"
                        :disabled="product.quantity === 0"
                        @click="addToCart(product.id)"
                    >
                        <ShoppingCartIcon class="size-3.5" />
                        {{
                            product.quantity === 0
                                ? 'Out of Stock'
                                : 'Add to Cart'
                        }}
                    </Button>

                    <Link v-else :href="customer_routes.login()" class="w-full">
                        <Button variant="outline" size="sm" class="w-full">
                            Login to Shop
                        </Button>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="props.products.last_page > 1"
            class="mt-10 flex items-center justify-center gap-2"
        >
            <Link
                v-if="props.products.prev_page_url"
                :href="props.products.prev_page_url"
            >
                <Button variant="outline" size="sm">← Previous</Button>
            </Link>
            <span class="text-sm text-muted-foreground">
                Page {{ props.products.current_page }} of
                {{ props.products.last_page }}
            </span>
            <Link
                v-if="props.products.next_page_url"
                :href="props.products.next_page_url"
            >
                <Button variant="outline" size="sm">Next →</Button>
            </Link>
        </div>
    </ShopLayout>
</template>
