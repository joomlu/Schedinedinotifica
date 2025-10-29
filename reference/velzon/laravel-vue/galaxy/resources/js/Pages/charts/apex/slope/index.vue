<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

import { basicSlopeChart, multiSlopeChart } from "./data";

export default {
    components: {
        Layout,
        PageHeader,
    },
    data() {
        return {
            basicSlopeChart: basicSlopeChart,
            multiSlopeChart: multiSlopeChart,
        };
    },
};
</script>

<template>
    <Layout>
        <PageHeader title="Slope Charts" pageTitle="Apexcharts" />
        <BRow>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>
                        <BCardTitle class="mb-0">Basic Chart</BCardTitle>
                    </BCardHeader>
                    <BCardBody>
                        <apexchart id="basic_slope_chart" class="apex-charts" dir="ltr" :series="basicSlopeChart.series" :options="basicSlopeChart.chartOptions"></apexchart>
                    </BCardBody>
                </BCard>
            </BCol>
            <BCol lg="6">
                <BCard no-body>
                    <BCardHeader>
                        <BCardTitle class="mb-0">Multi Chart</BCardTitle>
                    </BCardHeader>
                    <BCardBody>
                        <apexchart id="multi_group_chart" class="apex-charts" dir="ltr" :series="multiSlopeChart.series" :options="multiSlopeChart.chartOptions"></apexchart>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>