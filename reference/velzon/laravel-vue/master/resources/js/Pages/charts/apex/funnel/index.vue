<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import { funnelChart, pyramidChart } from "./data";

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
      funnelChart: funnelChart,
      pyramidChart: pyramidChart,
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
        this.funnelChart = {
          ...this.funnelChart,
          chartOptions: {
            ...this.funnelChart.chartOptions,
            colors: getChartColorsArray('["--vz-success"]')
          }
        };
        this.pyramidChart = {
          ...this.pyramidChart,
          chartOptions: {
            ...this.pyramidChart.chartOptions,
            colors: getChartColorsArray('["--vz-info", "--vz-danger", "--vz-primary", "--vz-secondary", "--vz-success", "--vz-dark", "--vz-warning"]')
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
    <PageHeader title="Funnel Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol xl="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Funnel Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="funnelChart.series"
              :options="funnelChart.chartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

      <BCol xl="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Pyramid</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <div>
              <apexchart class="apex-charts" height="350" dir="ltr" :series="pyramidChart.series"
                :options="pyramidChart.chartOptions"></apexchart>
            </div>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>