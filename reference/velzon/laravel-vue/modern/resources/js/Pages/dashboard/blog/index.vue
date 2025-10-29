<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Visiters from "./visiters.vue";
// import Comments from "./comments.vue";

export default {
    components: {
        Layout,
        PageHeader,
        Visiters,
        // Comments
    }
}
</script>

<template>
    <Layout>
        <PageHeader title="Blog" pageTitle="Dashboards" />

        <BRow>
            <Visiters/>
            <!-- <Comments/> -->
        </BRow>

    </Layout>
</template>