<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  basicScatterChart,
  dateTimeScatterChart,
  scatterImagesChart,
} from "./data";

/**
 * Scatter chart component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      basicScatterChart: basicScatterChart,
      dateTimeScatterChart: dateTimeScatterChart,
      scatterImagesChart: scatterImagesChart,
    };
  },
  computed: {
    ...layoutComputed,
    layoutTheme() {
      return this.$store ? this.$store.state.layout.layoutTheme : {} || {};
    }
  },
  methods: {
    handleUpdate() {
      setTimeout(() => {
        this.basicScatterChart = {
          ...this.basicScatterChart,
          chartOptions: {
            ...this.basicScatterChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning"]')
          }
        };
        this.dateTimeScatterChart = {
          ...this.dateTimeScatterChart,
          chartOptions: {
            ...this.dateTimeScatterChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-warning", "--vz-info"]')
          }
        };
        this.scatterImagesChart = {
          ...this.scatterImagesChart,
          chartOptions: {
            ...this.scatterImagesChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-danger"]')
          }
        };
      }, 200)
    }
  },
  mounted() {
    this.handleUpdate();
  },
  watch: {
    layoutTheme() {
      this.handleUpdate();
    },
  },
};
</script>

<template>
  <Layout>
    <PageHeader title="Scatter Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Basic Scatter Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="basicScatterChart.series"
              :options="basicScatterChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Scatter - Datetime Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="dateTimeScatterChart.series"
              :options="dateTimeScatterChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Scatter Images Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="scatterImagesChart.series"
              :options="scatterImagesChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
