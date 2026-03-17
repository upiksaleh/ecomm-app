<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ShoppingCartIcon,
    UserIcon,
    LogOutIcon,
    StoreIcon,
    HomeIcon,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import tenant_routes from '@/routes/tenant';

const page = usePage();

const customer = computed(() => page.props.auth.customer);
const cartCount = computed(() => page.props.cart_count ?? 0);
const tenant = computed(() => page.props.tenant);
const storeName = computed(
    () => tenant.value?.data?.name ?? tenant.value?.id ?? 'Store',
);

function logout() {
    router.post(tenant_routes.customer.logout.url());
}
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Navbar -->
        <header
            class="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div
                class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4"
            >
                <!-- Brand / Home Index -->
                <Link
                    :href="tenant_routes.home()"
                    class="flex items-center gap-2 font-semibold text-foreground hover:opacity-80"
                >
                    <HomeIcon class="size-5" />
                    <span>Home</span>
                </Link>

                <!-- Shop Index -->
                <Link
                    :href="tenant_routes.shop.index()"
                    class="flex items-center gap-2 font-semibold text-foreground hover:opacity-80"
                >
                    <StoreIcon class="size-5" />
                    <span>{{ storeName }}</span>
                </Link>

                <!-- Right side -->
                <div class="flex items-center gap-2">
                    <!-- Cart -->
                    <Link :href="tenant_routes.cart.index()">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="relative gap-1.5"
                        >
                            <ShoppingCartIcon class="size-4" />
                            <span class="hidden sm:inline">Cart</span>
                            <span
                                v-if="cartCount > 0"
                                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground"
                            >
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                        </Button>
                    </Link>

                    <!-- Authenticated customer -->
                    <template v-if="customer">
                        <span
                            class="hidden items-center gap-1.5 text-sm text-muted-foreground sm:flex"
                        >
                            <UserIcon class="size-3.5" />
                            {{ customer.name }}
                        </span>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="gap-1.5"
                            @click="logout"
                        >
                            <LogOutIcon class="size-4" />
                            <span class="hidden sm:inline">Logout</span>
                        </Button>
                    </template>

                    <!-- Guest -->
                    <template v-else>
                        <Link :href="tenant_routes.customer.login()">
                            <Button variant="ghost" size="sm">Login</Button>
                        </Link>
                        <Link :href="tenant_routes.customer.register()">
                            <Button size="sm">Register</Button>
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="mx-auto max-w-6xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
