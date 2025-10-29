<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  basicBarChart,
  customDatalabelsChart,
  stackedBarChart,
  stackedBar100Chart,
  barNegativeChart,
  barMarkersChart,
  reversedBarChart,
  patternedBarChart,
  groupedBarChart,
  barImagesChart,
} from "./data";

/**
 * Bar charts component
 */
export default {
  data() {
    return {
      basicBarChart: basicBarChart,
      customDatalabelsChart: customDatalabelsChart,
      stackedBarChart: stackedBarChart,
      stackedBar100Chart: stackedBar100Chart,
      barNegativeChart: barNegativeChart,
      barMarkersChart: barMarkersChart,
      reversedBarChart: reversedBarChart,
      patternedBarChart: patternedBarChart,
      groupedBarChart: groupedBarChart,
      barImagesChart: barImagesChart,
    };
  },
  components: {
    Layout,
    PageHeader
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
        this.basicBarChart = {
          ...this.basicBarChart,
          chartOptions: {
            ...this.basicBarChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.customDatalabelsChart = {
          ...this.customDatalabelsChart,
          chartOptions: {
            ...this.customDatalabelsChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-secondary", "--vz-success", "--vz-info", "--vz-warning", "--vz-danger", "--vz-dark", "--vz-primary", "--vz-success", "--vz-secondary"]')
          }
        };
        this.stackedBarChart = {
          ...this.stackedBarChart,
          chartOptions: {
            ...this.stackedBarChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
          }
        };
        this.stackedBar100Chart = {
          ...this.stackedBar100Chart,
          chartOptions: {
            ...this.stackedBar100Chart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
          }
        };
        this.barNegativeChart = {
          ...this.barNegativeChart,
          chartOptions: {
            ...this.barNegativeChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success"]')
          }
        };
        this.barMarkersChart = {
          ...this.barMarkersChart,
          chartOptions: {
            ...this.barMarkersChart.chartOptions,
            colors: getChartColorsArray('["--vz-success", "--vz-primary"]')
          }
        };
        this.reversedBarChart = {
          ...this.reversedBarChart,
          chartOptions: {
            ...this.reversedBarChart.chartOptions,
            colors: getChartColorsArray('["--vz-info"]')
          }
        };
        this.patternedBarChart = {
          ...this.patternedBarChart,
          chartOptions: {
            ...this.patternedBarChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger"]')
          }
        };
        this.groupedBarChart = {
          ...this.groupedBarChart,
          chartOptions: {
            ...this.groupedBarChart.chartOptions,
            colors: getChartColorsArray('["--vz-primary", "--vz-success"]')
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
    <PageHeader title="Bar Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Basic Bar Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="basicBarChart.series"
              :options="basicBarChart.chartOptions"></apexchart>
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
            <BCardTitle>Custom DataLabels Bar</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="customDatalabelsChart.series"
              :options="customDatalabelsChart.chartOptions"></apexchart>
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
            <BCardTitle>Stacked Bar Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="stackedBarChart.series"
              :options="stackedBarChart.chartOptions"></apexchart>
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
            <BCardTitle>Stacked Bars 100</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="stackedBar100Chart.series"
              :options="stackedBar100Chart.chartOptions"></apexchart>
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
            <BCardTitle>Bar with Negative Values</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="barNegativeChart.series"
              :options="barNegativeChart.chartOptions"></apexchart>
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
            <BCardTitle>Bar with Marker</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="barMarkersChart.series"
              :options="barMarkersChart.chartOptions"></apexchart>
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
            <BCardTitle>Reversed Bar Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="reversedBarChart.series"
              :options="reversedBarChart.chartOptions"></apexchart>
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
            <BCardTitle>Patterned Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="patternedBarChart.series"
              :options="patternedBarChart.chartOptions"></apexchart>
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
            <BCardTitle>Grouped Bar Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="groupedBarChart.series"
              :options="groupedBarChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

    </BRow>
  </Layout>
</template>