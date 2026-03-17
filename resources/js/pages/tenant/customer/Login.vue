<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import customer_routes from '@/routes/tenant/customer';
import shop_routes from '@/routes/tenant/shop';
</script>

<template>
    <Head title="Customer Login" />

    <AuthLayout
        title="Sign in to your account"
        description="Enter your email and password to continue shopping"
    >
        <Form
            v-bind="customer_routes.login_store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="you@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox id="remember" name="remember" />
                    <Label for="remember">Remember me</Label>
                </div>

                <Button type="submit" class="w-full" :disabled="processing">
                    Sign in
                </Button>
            </div>

            <p class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <Link
                    :href="customer_routes.register()"
                    class="text-foreground underline underline-offset-4 hover:no-underline"
                >
                    Create one
                </Link>
            </p>

            <p class="text-center text-sm text-muted-foreground">
                <Link
                    :href="shop_routes.index()"
                    class="text-foreground underline underline-offset-4 hover:no-underline"
                >
                    ← Continue browsing
                </Link>
            </p>
        </Form>
    </AuthLayout>
</template>
