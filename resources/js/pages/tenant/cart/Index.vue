<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { TrashIcon, ShoppingCartIcon, ArrowRightIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/tenant/ShopLayout.vue';
import cart_routes from '@/routes/tenant/cart';
import shop_routes from '@/routes/tenant/shop';

interface CartItem {
    id: number;
    quantity: number;
    price: number;
    subtotal: number;
    product: {
        id: number;
        name: string;
        sku: string;
        image: string | null;
    };
}

interface Cart {
    items: CartItem[];
    total: number;
}

const props = defineProps<{ cart: Cart }>();

const quantities = ref<Record<number, number>>(
    Object.fromEntries(props.cart.items.map((i) => [i.id, i.quantity])),
);

function updateQty(item: CartItem) {
    const qty = quantities.value[item.id];

    if (qty < 1 || qty === item.quantity) {
        return;
    }

    router.patch(
        cart_routes.update.url(item.id),
        { quantity: qty },
        { preserveScroll: true },
    );
}

function removeItem(id: number) {
    router.delete(cart_routes.remove.url(id), { preserveScroll: true });
}

function fmt(value: number) {
    return value.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    });
}
</script>

<template>
    <Head title="My Cart" />

    <ShopLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-semibold">Shopping Cart</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ props.cart.items.length }}
                {{ props.cart.items.length === 1 ? 'item' : 'items' }} in your
                cart
            </p>
        </div>

        <!-- Empty state -->
        <div
            v-if="props.cart.items.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-20 text-center"
        >
            <ShoppingCartIcon class="mb-4 size-12 text-muted-foreground/40" />
            <p class="text-lg font-medium">Your cart is empty</p>
            <p class="mt-1 mb-6 text-sm text-muted-foreground">
                Browse our products and add something you like.
            </p>
            <Link :href="shop_routes.index()">
                <Button>Start Shopping</Button>
            </Link>
        </div>

        <!-- Cart content -->
        <div v-else class="grid gap-6 lg:grid-cols-3">
            <!-- Items -->
            <div class="lg:col-span-2">
                <div
                    class="overflow-hidden rounded-xl border border-border bg-card"
                >
                    <div
                        v-for="(item, idx) in props.cart.items"
                        :key="item.id"
                        :class="[
                            'flex items-center gap-4 p-4',
                            idx > 0 && 'border-t border-border',
                        ]"
                    >
                        <!-- Product image placeholder -->
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                        >
                            <ShoppingCartIcon class="size-6" />
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">
                                {{ item.product.name }}
                            </p>
                            <p
                                class="mt-0.5 font-mono text-xs text-muted-foreground"
                            >
                                {{ item.product.sku }}
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ fmt(item.price) }} each
                            </p>
                        </div>

                        <!-- Quantity -->
                        <div class="flex items-center gap-2">
                            <Input
                                v-model.number="quantities[item.id]"
                                type="number"
                                min="1"
                                max="100"
                                class="h-8 w-16 text-center"
                                @change="updateQty(item)"
                            />
                        </div>

                        <!-- Subtotal -->
                        <div class="w-20 text-right">
                            <p class="font-medium">{{ fmt(item.subtotal) }}</p>
                        </div>

                        <!-- Remove -->
                        <Button
                            variant="ghost"
                            size="icon"
                            class="shrink-0 text-muted-foreground hover:text-destructive"
                            @click="removeItem(item.id)"
                        >
                            <TrashIcon class="size-4" />
                        </Button>
                    </div>
                </div>

                <div class="mt-4">
                    <Link :href="shop_routes.index()">
                        <Button variant="ghost" size="sm" class="gap-1.5">
                            ← Continue Shopping
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Order summary -->
            <div>
                <div
                    class="sticky top-20 rounded-xl border border-border bg-card p-5"
                >
                    <h2 class="mb-4 text-base font-semibold">Order Summary</h2>

                    <div class="space-y-2 text-sm">
                        <div
                            v-for="item in props.cart.items"
                            :key="item.id"
                            class="flex justify-between text-muted-foreground"
                        >
                            <span class="truncate pr-2">
                                {{ item.product.name }}
                                <span class="text-xs"
                                    >×{{ item.quantity }}</span
                                >
                            </span>
                            <span class="shrink-0">{{
                                fmt(item.subtotal)
                            }}</span>
                        </div>
                    </div>

                    <div class="my-4 border-t border-border" />

                    <div class="flex justify-between font-semibold">
                        <span>Total</span>
                        <span>{{ fmt(props.cart.total) }}</span>
                    </div>

                    <Button class="mt-5 w-full gap-2">
                        Proceed to Checkout
                        <ArrowRightIcon class="size-4" />
                    </Button>

                    <p class="mt-3 text-center text-xs text-muted-foreground">
                        Taxes and shipping calculated at checkout
                    </p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
