<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ShoppingCartIcon, ArrowLeftIcon, PackageIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ShopLayout from '@/layouts/tenant/ShopLayout.vue';
import cart_routes from '@/routes/tenant/cart';
import customer_routes from '@/routes/tenant/customer';
import shop_routes from '@/routes/tenant/shop';

const props = defineProps<{ product: any }>();

const page = usePage();
const customer = computed(() => page.props.auth.customer);

const quantity = ref(1);
const adding = ref(false);

const formattedPrice = computed(() =>
    Number(props.product.price).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }),
);

function addToCart() {
    if (!customer.value) {
        router.visit(customer_routes.login());

        return;
    }

    adding.value = true;
    router.post(
        cart_routes.add.url(),
        { product_id: props.product.id, quantity: quantity.value },
        {
            preserveScroll: true,
            onFinish: () => {
                adding.value = false;
            },
        },
    );
}

function increment() {
    if (quantity.value < props.product.quantity) {
        quantity.value++;
    }
}

function decrement() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}
</script>

<template>
    <Head :title="product.name" />

    <ShopLayout>
        <!-- Back link -->
        <div class="mb-6">
            <Link
                :href="shop_routes.index()"
                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
            >
                <ArrowLeftIcon class="size-4" />
                Back to Shop
            </Link>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Product image placeholder -->
            <div
                class="flex aspect-square items-center justify-center rounded-xl border border-border bg-slate-50 dark:bg-slate-900"
            >
                <PackageIcon
                    class="size-24 text-slate-300 dark:text-slate-700"
                />
            </div>

            <!-- Product info -->
            <div class="flex flex-col gap-6">
                <div>
                    <p class="mb-1 font-mono text-xs text-muted-foreground">
                        SKU: {{ product.sku }}
                    </p>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ product.name }}
                    </h1>
                    <p class="mt-3 text-3xl font-semibold text-primary">
                        {{ formattedPrice }}
                    </p>
                </div>

                <!-- Description -->
                <p
                    v-if="product.description"
                    class="text-sm leading-relaxed text-muted-foreground"
                >
                    {{ product.description }}
                </p>

                <!-- Stock status -->
                <div class="flex items-center gap-2">
                    <span
                        v-if="product.quantity > 0"
                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-400"
                    >
                        In Stock ({{ product.quantity }} available)
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-600 dark:bg-red-900/40 dark:text-red-400"
                    >
                        Out of Stock
                    </span>
                </div>

                <!-- Quantity + Add to cart -->
                <div v-if="product.quantity > 0" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <Label for="quantity">Quantity</Label>
                        <div class="flex w-32 items-center">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                class="rounded-r-none"
                                :disabled="quantity <= 1"
                                @click="decrement"
                            >
                                −
                            </Button>
                            <Input
                                id="quantity"
                                v-model.number="quantity"
                                type="number"
                                min="1"
                                :max="product.quantity"
                                class="[appearance:textfield] rounded-none text-center [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                class="rounded-l-none"
                                :disabled="quantity >= product.quantity"
                                @click="increment"
                            >
                                +
                            </Button>
                        </div>
                    </div>

                    <Button
                        class="w-full gap-2 sm:w-auto"
                        size="lg"
                        :disabled="adding"
                        @click="addToCart"
                    >
                        <ShoppingCartIcon class="size-5" />
                        {{ customer ? 'Add to Cart' : 'Login to Add to Cart' }}
                    </Button>
                </div>

                <!-- Out of stock CTA -->
                <div v-else>
                    <Button variant="outline" disabled class="w-full sm:w-auto">
                        Out of Stock
                    </Button>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
