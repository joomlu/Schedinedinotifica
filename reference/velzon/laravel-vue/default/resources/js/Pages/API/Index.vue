<script setup>
import { Head } from "@inertiajs/vue3"
import Layout from "@/Layouts/main.vue";
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';

defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});
</script>

<template>
    <Layout>
        <Head title="API Tokens" />
        <BRow class="justify-content-center">
            <BCol lg="6">
                <ApiTokenManager :tokens="tokens" :available-permissions="availablePermissions" :default-permissions="defaultPermissions" />
            </BCol>
        </BRow>
    </Layout>
</template>
