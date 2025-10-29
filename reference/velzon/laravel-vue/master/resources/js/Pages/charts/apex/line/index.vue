<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  basicLineChart,
  zoomableChart,
  lineDatalabelChart,
  lineAnnotation,
  syncingAreaChart,
  brushLineChart,
  brushAreaChart,
  steplineChart,
  gradientLineChart,
  realtimeLineChart,
  dashedLineChart,
  missingDataChart,
} from "./data";

/**
 * Line chart component
 */
export default {
  data() {
    return {
      basicLineChart: basicLineChart,
      zoomableChart: zoomableChart,
      lineDatalabelChart: lineDatalabelChart,
      lineAnnotation: lineAnnotation,
      syncingAreaChart: syncingAreaChart,
      brushLineChart: brushLineChart,
      brushAreaChart: brushAreaChart,
      steplineChart: steplineChart,
      gradientLineChart: gradientLineChart,
      realtimeLineChart: realtimeLineChart,
      dashedLineChart: dashedLineChart,
      missingDataChart: missingDataChart,
    };
  },
  components: {
    Layout,
    PageHeader,
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
        this.basicLineChart = {
          ...this.basicLineChart,
          chartOptions: {
            ...this.basicLineChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary"]')
          }
        };
        this.zoomableChart = {
          ...this.zoomableChart,
          chartOptions: {
            ...this.zoomableChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.lineDatalabelChart = {
          ...this.lineDatalabelChart,
          chartOptions: {
            ...this.lineDatalabelChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success"]')
          }
        };
        this.dashedLineChart = {
          ...this.dashedLineChart,
          chartOptions: {
            ...this.dashedLineChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-danger", "--vz-success"]')
          }
        };
        this.lineAnnotation = {
          ...this.lineAnnotation,
          chartOptions: {
            ...this.lineAnnotation.chartOptions,
            colors: getChartColorsArray('["--vz-primary"]')
          }
        };
        this.syncingAreaChart = {
          ...this.syncingAreaChart,
          chartOptions: {
            ...this.syncingAreaChart.chartOptions,
            colors: getChartColorsArray('["--vz-danger"]')
          }
        };
        this.brushLineChart = {
          ...this.brushLineChart,
          chartOptions: {
            ...this.brushLineChart.chartOptions,
            colors: getChartColorsArray('["--vz-danger"]')
          }
        };
        this.brushAreaChart = {
          ...this.brushAreaChart,
          chartOptions: {
            ...this.brushAreaChart.chartOptions,
            colors: getChartColorsArray('["--vz-info"]')
          }
        };
        this.steplineChart = {
          ...this.steplineChart,
          chartOptions: {
            ...this.steplineChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.gradientLineChart = {
          ...this.gradientLineChart,
          chartOptions: {
            ...this.gradientLineChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.missingDataChart = {
          ...this.missingDataChart,
          chartOptions: {
            ...this.missingDataChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-danger", "--vz-success"]')
          }
        };
      }, 200)
    },
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
    <PageHeader title="Line Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Basic Line Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="basicLineChart.series"
              :options="basicLineChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Zoomable Timeseries</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="zoomableChart.series"
              :options="zoomableChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Line with Data Labels</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="lineDatalabelChart.series"
              :options="lineDatalabelChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Dashed Line</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="dashedLineChart.series"
              :options="dashedLineChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Line with Annotations</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="lineAnnotation.series"
              :options="lineAnnotation.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Brush Charts</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <div>
              <apexchart class="apex-charts" height="230" dir="ltr" :series="brushLineChart.series"
                :options="brushLineChart.chartOptions"></apexchart>

              <apexchart class="apex-charts" height="130" dir="ltr" :series="brushAreaChart.series"
                :options="brushAreaChart.chartOptions"></apexchart>
            </div>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Stepline Charts</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="steplineChart.series"
              :options="steplineChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Gradient Charts</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="gradientLineChart.series"
              :options="gradientLineChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Missing Data/ Null Value Charts</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="missingDataChart.series"
              :options="missingDataChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

    </BRow>
  </Layout>
</template>