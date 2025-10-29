<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  mixedLineChart,
  yAxisChart,
  lineAreaChart,
  lineColumnAreaChart,
} from "./data";

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
      mixedLineChart: mixedLineChart,
      yAxisChart: yAxisChart,
      lineAreaChart: lineAreaChart,
      lineColumnAreaChart: lineColumnAreaChart,
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
        this.mixedLineChart = {
          ...this.mixedLineChart,
          chartOptions: {
            ...this.mixedLineChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success"]')
          }
        };
        this.yAxisChart = {
          ...this.yAxisChart,
          chartOptions: {
            ...this.yAxisChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-info", "--vz-success"]')
          }
        };
        this.lineAreaChart = {
          ...this.lineAreaChart,
          chartOptions: {
            ...this.lineAreaChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success"]')
          }
        };
        this.lineColumnAreaChart = {
          ...this.lineColumnAreaChart,
          chartOptions: {
            ...this.lineColumnAreaChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-danger"]')
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
}
</script>

<template>
  <Layout>
    <PageHeader title="Mixed Charts" pageTitle="Apexcharts" />

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Line & Column Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="mixedLineChart.series"
              :options="mixedLineChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="justify-content-between d-flex align-items-center">
            <BCardTitle>Multiple Y-Axis Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="yAxisChart.series"
              :options="yAxisChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Line & Area Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="lineAreaChart.series"
              :options="lineAreaChart.chartOptions"></apexchart>
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
            <BCardTitle>Line, Column & Area Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="lineColumnAreaChart.series"
              :options="lineColumnAreaChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>