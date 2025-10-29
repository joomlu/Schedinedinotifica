<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  basicRadarChart,
  radarMultiseriesChart,
  polygonRadarChart,
} from "./data";

/**
 * Radar chart component
 */
export default {
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      basicRadarChart: basicRadarChart,
      radarMultiseriesChart: radarMultiseriesChart,
      polygonRadarChart: polygonRadarChart,
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
        this.basicRadarChart = {
          ...this.basicRadarChart,
          chartOptions: {
            ...this.basicRadarChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.radarMultiseriesChart = {
          ...this.radarMultiseriesChart,
          chartOptions: {
            ...this.radarMultiseriesChart.chartOptions,
            colors: getChartColorsArray('["--vz-danger", "--vz-success", "--vz-primary"]')
          }
        };
        this.polygonRadarChart = {
          ...this.polygonRadarChart,
          chartOptions: {
            ...this.polygonRadarChart.chartOptions,
            colors: getChartColorsArray('["--vz-info"]')
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
    <PageHeader title="Radar Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Basic Radar Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="basicRadarChart.series"
              :options="basicRadarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Radar Chart - Multiple series</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="radarMultiseriesChart.series"
              :options="radarMultiseriesChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Radar Chart - Polygon Fill</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="polygonRadarChart.series"
              :options="polygonRadarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
