<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import { basicPolarareaChart, polarAreaMonochromeChart } from "./data";

/**
 * Polararea chart component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      basicPolarareaChart: basicPolarareaChart,
      polarAreaMonochromeChart: polarAreaMonochromeChart,
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
        this.basicPolarareaChart = {
          ...this.basicPolarareaChart,
          chartOptions: {
            ...this.basicPolarareaChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning","--vz-danger", "--vz-info", "--vz-success", "--vz-primary", "--vz-dark", "--vz-secondary"]')
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
    <PageHeader title="Polararea Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Basic-Polar Area Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="330" dir="ltr" :series="basicPolarareaChart.series"
              :options="basicPolarareaChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Polar-Area Monochrome</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="330" dir="ltr" :series="polarAreaMonochromeChart.series"
              :options="polarAreaMonochromeChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
