<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { boxChart, boxplotScatterChart } from "./data";

/**
 * Boxplot Charts component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      boxChart: JSON.parse(JSON.stringify(boxChart)),
      boxplotScatterChart: JSON.parse(JSON.stringify(boxplotScatterChart)),
    };
  },
  computed: {
    boxChartOptions() {
      return JSON.parse(JSON.stringify(this.boxChart.chartOptions)); // Create a deep copy
    },
    boxChartSeries() {
      return JSON.parse(JSON.stringify(this.boxChart.series)); // Create a deep copy
    },
    boxplotScatterChartOptions() {
      return JSON.parse(JSON.stringify(this.boxplotScatterChart.chartOptions)); // Create a deep copy of combo chart options
    },
    boxplotScatterChartSeries() {
      return JSON.parse(JSON.stringify(this.boxplotScatterChart.series)); // Create a deep copy of combo chart series
    }
  },
};
</script>

<template>
  <Layout>
    <PageHeader title="Boxplot Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Basic Box Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="boxChartSeries"
              :options="boxChartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Boxplot with Scatter Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="boxplotScatterChartSeries"
              :options="boxplotScatterChartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>