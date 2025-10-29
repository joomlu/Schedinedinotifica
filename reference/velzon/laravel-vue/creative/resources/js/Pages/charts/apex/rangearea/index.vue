<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { basicRangeAreaChart, comboChart } from "./data";

/**
 * Mixed chart component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      basicRangeAreaChart: JSON.parse(JSON.stringify(basicRangeAreaChart)),
      comboChart: JSON.parse(JSON.stringify(comboChart)),
    };
  },
  computed: {
    chartOptions() {
      return JSON.parse(JSON.stringify(this.basicRangeAreaChart.chartOptions)); // Create a deep copy
    },
    chartSeries() {
      return JSON.parse(JSON.stringify(this.basicRangeAreaChart.series)); // Create a deep copy
    },
    comboChartOptions() {
      return JSON.parse(JSON.stringify(this.comboChart.chartOptions)); // Create a deep copy of combo chart options
    },
    comboChartSeries() {
      return JSON.parse(JSON.stringify(this.comboChart.series)); // Create a deep copy of combo chart series
    }
  }
};
</script>

<template>
  <Layout>
    <PageHeader title="Range Area Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol xl="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Basic Range Area Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="chartSeries" :options="chartOptions">
            </apexchart>
          </BCardBody>
        </BCard>
      </BCol>


      <BCol xl="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Combo</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="comboChartSeries"
              :options="comboChartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

    </BRow>
  </Layout>
</template>