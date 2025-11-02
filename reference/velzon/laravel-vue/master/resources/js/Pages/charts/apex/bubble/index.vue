<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import { simpleBubbleChart, bubble3DChart } from "./data";

/**
 * Bubble Charts component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      simpleBubbleChart: simpleBubbleChart,
      bubble3DChart: bubble3DChart,
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
        this.simpleBubbleChart = {
          ...this.simpleBubbleChart,
          chartOptions: {
            ...this.simpleBubbleChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-info", "--vz-warning", "--vz-success"]')
          }
        };
        this.bubble3DChart = {
          ...this.bubble3DChart,
          chartOptions: {
            ...this.bubble3DChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger"]')
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
    <PageHeader title="Boxplot Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Basic Bubble Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="simpleBubbleChart.series"
              :options="simpleBubbleChart.chartOptions"></apexchart>
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
            <BCardTitle>3D Bubble Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="bubble3DChart.series"
              :options="bubble3DChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
