<script setup lang="ts">
import { Head, Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import customer_routes from '@/routes/tenant/customer';
</script>

<template>
    <Head title="Create Account" />

    <AuthLayout
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Form
            v-bind="customer_routes.register_store.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
                    <Input
                        id="name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="John Doe"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        placeholder="Repeat your password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="5"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Create Account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <Link
                    :href="customer_routes.login()"
                    class="text-foreground underline underline-offset-4 hover:no-underline"
                    :tabindex="6"
                >
                    Log in
                </Link>
            </div>
        </Form>
    </AuthLayout>
</template>
