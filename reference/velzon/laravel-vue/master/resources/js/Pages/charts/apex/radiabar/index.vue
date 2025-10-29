<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  basicRadialBarChart,
  multiRadialbarChart,
  circleAngleChart,
  gradientRadialbarChart,
  strokedRadialbarChart,
  semiRadialbarChart,
} from "./data";

/**
 * Radaialbar chart component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      basicRadialBarChart: basicRadialBarChart,
      multiRadialbarChart: multiRadialbarChart,
      circleAngleChart: circleAngleChart,
      gradientRadialbarChart: gradientRadialbarChart,
      strokedRadialbarChart: strokedRadialbarChart,
      semiRadialbarChart: semiRadialbarChart,
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
        this.basicRadialBarChart = {
          ...this.basicRadialBarChart,
          chartOptions: {
            ...this.basicRadialBarChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.multiRadialbarChart = {
          ...this.multiRadialbarChart,
          chartOptions: {
            ...this.multiRadialbarChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger"]')
          }
        };
        this.circleAngleChart = {
          ...this.circleAngleChart,
          chartOptions: {
            ...this.circleAngleChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]')
          }
        };
        this.gradientRadialbarChart = {
          ...this.gradientRadialbarChart,
          chartOptions: {
            ...this.gradientRadialbarChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.strokedRadialbarChart = {
          ...this.strokedRadialbarChart,
          chartOptions: {
            ...this.strokedRadialbarChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.semiRadialbarChart = {
          ...this.semiRadialbarChart,
          chartOptions: {
            ...this.semiRadialbarChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary"]')
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
    <PageHeader title="Radialbar Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Simple Radialbar Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="basicRadialBarChart.series"
              :options="basicRadialBarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Multiple Radialbar</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <div id="multiple_radialbar" class="apex-charts" dir="ltr"></div>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="multiRadialbarChart.series"
              :options="multiRadialbarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Circle Chart - Custom Angle</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="circleAngleChart.series"
              :options="circleAngleChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Gradient Circle Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="gradientRadialbarChart.series"
              :options="gradientRadialbarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow class="mb-4">
      <BCol lg="6">
        <BCard no-body class="h-100 mb-xl-0">
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Stroked Circular Gauge</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="strokedRadialbarChart.series"
              :options="strokedRadialbarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body class="h-100 mb-xl-0">
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Semi Circular Gauge</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="semiRadialbarChart.series"
              :options="semiRadialbarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
