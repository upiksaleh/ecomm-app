<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { computed } from 'vue';
import centralRoutes from '@/routes/central';
import tenantRoutes from '@/routes/tenant';

const page = usePage();

const logoutUrl = computed(() => {
    if (page.props.app_type === 'central') {
        return centralRoutes.logout.url();
    }

    return tenantRoutes.logout.url();
});
</script>
<template>
    <Link
        class="block w-full cursor-pointer"
        :href="logoutUrl"
        method="post"
        as="button"
        data-test="logout-button"
    >
        <LogOut class="mr-2 h-4 w-4" />
        Log out
    </Link>
</template>
