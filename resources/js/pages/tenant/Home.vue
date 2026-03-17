<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ShoppingBagIcon,
    ShieldCheckIcon,
    TruckIcon,
    HeadphonesIcon,
    ArrowRightIcon,
    SparklesIcon,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/tenant/ShopLayout.vue';
import customer_routes from '@/routes/tenant/customer';
import shop_routes from '@/routes/tenant/shop';

import type { ITenant } from '@/types';

const props = defineProps<{ tenant: ITenant | null }>();

const page = usePage();
const customer = computed(() => page.props.auth.customer);
const storeName = computed(
    () => props.tenant?.data?.name ?? props.tenant?.id ?? 'Our Store',
);

const features = [
    {
        icon: ShoppingBagIcon,
        title: 'Wide Selection',
        description:
            'Browse hundreds of carefully curated products across all categories.',
    },
    {
        icon: TruckIcon,
        title: 'Fast Delivery',
        description:
            'Get your orders delivered quickly and reliably to your doorstep.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Secure Shopping',
        description:
            'Your payments and personal data are always safe and protected.',
    },
    {
        icon: HeadphonesIcon,
        title: '24/7 Support',
        description:
            'Our friendly support team is available around the clock to help you.',
    },
];
</script>

<template>
    <Head title="Welcome" />

    <ShopLayout>
        <!-- Hero -->
        <section
            class="relative -mx-4 -mt-8 overflow-hidden px-4 py-20 sm:py-28"
        >
            <!-- Background gradient -->
            <div
                class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-background to-primary/10 dark:from-primary/10 dark:via-background dark:to-primary/5"
            />
            <!-- Decorative blobs -->
            <div
                class="pointer-events-none absolute -top-24 -right-24 -z-10 h-72 w-72 rounded-full bg-primary/10 blur-3xl dark:bg-primary/5"
            />
            <div
                class="pointer-events-none absolute -bottom-24 -left-24 -z-10 h-72 w-72 rounded-full bg-primary/10 blur-3xl dark:bg-primary/5"
            />

            <div class="mx-auto max-w-3xl text-center">
                <!-- Badge -->
                <div
                    class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-sm font-medium text-primary"
                >
                    <SparklesIcon class="size-3.5" />
                    Welcome to {{ storeName }}
                </div>

                <!-- Headline -->
                <h1
                    class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl"
                >
                    Discover Products
                    <span
                        class="mt-1 block bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent"
                    >
                        You'll Love
                    </span>
                </h1>

                <p
                    class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-muted-foreground"
                >
                    Shop the latest collection with confidence. Quality
                    products, unbeatable prices, and a seamless shopping
                    experience — all in one place.
                </p>

                <!-- CTAs -->
                <div
                    class="mt-10 flex flex-wrap items-center justify-center gap-4"
                >
                    <Link :href="shop_routes.index()">
                        <Button size="lg" class="gap-2 px-8">
                            <ShoppingBagIcon class="size-5" />
                            Shop Now
                        </Button>
                    </Link>

                    <template v-if="!customer">
                        <Link :href="customer_routes.register()">
                            <Button
                                size="lg"
                                variant="outline"
                                class="gap-2 px-8"
                            >
                                Create Account
                                <ArrowRightIcon class="size-4" />
                            </Button>
                        </Link>
                    </template>

                    <template v-else>
                        <p class="text-sm text-muted-foreground">
                            Welcome back,
                            <span class="font-medium text-foreground">{{
                                customer.name
                            }}</span
                            >!
                        </p>
                    </template>
                </div>
            </div>
        </section>

        <!-- Stats bar -->
        <section
            class="my-12 rounded-2xl border border-border bg-card px-6 py-6"
        >
            <div
                class="grid grid-cols-2 divide-x divide-border sm:grid-cols-4 [&>*]:px-6 [&>*:first-child]:pl-0 [&>*:last-child]:border-r-0"
            >
                <div class="text-center">
                    <p class="text-2xl font-bold text-foreground">10K+</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Happy Customers
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-foreground">500+</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">Products</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-foreground">99%</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Satisfaction Rate
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-foreground">24/7</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">Support</p>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="my-16">
            <div class="mb-10 text-center">
                <h2
                    class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    Why Shop With Us?
                </h2>
                <p class="mt-3 text-muted-foreground">
                    Everything you need for a great shopping experience.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="group rounded-2xl border border-border bg-card p-6 transition-shadow hover:shadow-md"
                >
                    <div
                        class="mb-4 inline-flex rounded-xl bg-primary/10 p-3 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                    >
                        <component :is="feature.icon" class="size-5" />
                    </div>
                    <h3 class="mb-2 font-semibold text-foreground">
                        {{ feature.title }}
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Bottom CTA banner -->
        <section
            class="my-8 overflow-hidden rounded-2xl bg-primary px-8 py-12 text-center text-primary-foreground"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-10"
                style="
                    background-image:
                        radial-gradient(
                            circle at 20% 50%,
                            white 1px,
                            transparent 1px
                        ),
                        radial-gradient(
                            circle at 80% 80%,
                            white 1px,
                            transparent 1px
                        );
                    background-size: 30px 30px;
                "
            />
            <h2 class="text-2xl font-bold sm:text-3xl">
                Ready to Start Shopping?
            </h2>
            <p class="mx-auto mt-3 max-w-md text-primary-foreground/80">
                Join thousands of happy customers and find your next favourite
                product today.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <Link :href="shop_routes.index()">
                    <Button
                        size="lg"
                        variant="secondary"
                        class="gap-2 px-8 font-semibold"
                    >
                        <ShoppingBagIcon class="size-5" />
                        Browse Products
                    </Button>
                </Link>
                <Link v-if="!customer" :href="customer_routes.register()">
                    <Button
                        size="lg"
                        variant="outline"
                        class="gap-2 border-primary-foreground/40 bg-transparent px-8 text-primary-foreground hover:bg-primary-foreground/10 hover:text-primary-foreground"
                    >
                        Sign Up Free
                        <ArrowRightIcon class="size-4" />
                    </Button>
                </Link>
            </div>
        </section>

        <!-- Admin link (subtle, at the bottom) -->
        <div class="mt-6 text-center">
            <a
                href="/login"
                class="text-xs text-muted-foreground/50 underline underline-offset-4 transition-colors hover:text-muted-foreground"
            >
                Tenant Admin Login
            </a>
        </div>
    </ShopLayout>
</template>
