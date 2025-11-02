<script>
import { VueEcharts } from "vue3-echarts";

import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { layoutComputed } from "@/state/helpers.js"
import getChartColorsArray from "@/common/getChartColorsArray";
import {
  lineChart,
  areaChart,
  polarChart,
  stackedLineChart,
  basicBar,
  lineYCategoryChart,
  stackedAreaChart,
  horizontalBarChart,
  stepLineChart,
  pieChart,
  bubbleChart,
  doughnutChart,
  candlestickChart,
  mixedBarChart,
  stackedHorizontalBarChart,
  guageChart,
  graphChart,
  treemapChart,
  sunburstChart,
  scatterChart,
  parallelChart,
  sankeyChart,
  funnelChart,
} from "./data";

export default {
  components: { Layout, PageHeader, VueEcharts },
  data() {
    return {
      lineChart: lineChart,
      scatterChart: scatterChart,
      areaChart: areaChart,
      stackedAreaChart: stackedAreaChart,
      polarChart: polarChart,
      basicBar: basicBar,
      horizontalBarChart: horizontalBarChart,
      stackedLineChart: stackedLineChart,
      graphChart: graphChart,
      lineYCategoryChart: lineYCategoryChart,
      stepLineChart: stepLineChart,
      candlestickChart: candlestickChart,
      pieChart: pieChart,
      sunburstChart: sunburstChart,
      treemapChart: treemapChart,
      parallelChart: parallelChart,
      sankeyChart: sankeyChart,
      funnelChart: funnelChart,
      bubbleChart: bubbleChart,
      doughnutChart: doughnutChart,
      stackedHorizontalBarChart: stackedHorizontalBarChart,
      mixedBarChart: mixedBarChart,
      guageChart: guageChart,
    }
  },
  computed: {
    ...layoutComputed,
    layoutTheme() {
      return this.$store ? this.$store.state.layout.layoutTheme : {} || {};
    },
  },
  methods: {
    handleUpdate() {
      setTimeout(() => {
        this.lineChart = {
          ...this.lineChart,
          color: getChartColorsArray('["--vz-success"]')
        };
        this.stackedLineChart = {
          ...this.stackedLineChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
        };
        this.areaChart = {
          ...areaChart,
          color: getChartColorsArray('["--vz-success"]')
        };
        this.stackedAreaChart = {
          ...stackedAreaChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
        };
        this.stepLineChart = {
          ...stepLineChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning"]')
        };
        this.lineYCategoryChart = {
          ...lineYCategoryChart,
          color: getChartColorsArray('["--vz-success"]')
        };
        this.basicBar = {
          ...basicBar,
          color: getChartColorsArray('["--vz-success"]')
        };
        this.horizontalBarChart = {
          ...horizontalBarChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success"]')
        };
        this.stackedHorizontalBarChart = {
          ...stackedHorizontalBarChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
        };
        this.pieChart = {
          ...pieChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
        };
        this.doughnutChart = {
          ...doughnutChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]')
        };
        this.graphChart = {
          ...graphChart,
          color: getChartColorsArray('["--vz-primary"]')
        };
        this.treemapChart = {
          ...treemapChart,
          color: getChartColorsArray('["--vz-primary", "--vz-success"]')
        };
        this.scatterChart = {
          ...scatterChart,
          color: getChartColorsArray('["--vz-primary"]')
        };
        this.sunburstChart = {
          ...sunburstChart,
          color: getChartColorsArray('["--vz-warning", "--vz-success"]')
        };
        this.polarChart = {
          ...polarChart,
          color: getChartColorsArray('["--vz-success"]')
        };
        this.parallelChart = {
          ...parallelChart,
          color: getChartColorsArray('["--vz-success"]')
        };
        this.sankeyChart = {
          ...sankeyChart,
          color: getChartColorsArray('["--vz-info", "--vz-success", "--vz-warning", "--vz-danger", "--vz-primary"]')
        };
        this.mixedBarChart = {
          ...mixedBarChart,
          color: getChartColorsArray('["--vz-success","--vz-primary","--vz-danger"]')
        };
        this.funnelChart = {
          ...funnelChart,
          color: getChartColorsArray('["--vz-info", "--vz-success","--vz-warning", "--vz-danger", "--vz-primary"]')
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
    <PageHeader title="E Charts" pageTitle="Charts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Line Chart</BCardTitle>
            <vue-echarts :option="lineChart" style="height: 300px" ref="chart" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Stacked Line Chart</BCardTitle>
            <vue-echarts :option="stackedLineChart" style="height: 300px" ref="chart" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Area Chart</BCardTitle>
            <vue-echarts :option="areaChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Stacked Area Chart</BCardTitle>
            <vue-echarts :option="stackedAreaChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Step Line</BCardTitle>
            <vue-echarts :option="stepLineChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Line Y Category</BCardTitle>
            <vue-echarts :option="lineYCategoryChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Basic Bar</BCardTitle>
            <vue-echarts :option="basicBar" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Horizontal Bar</BCardTitle>
            <vue-echarts :option="horizontalBarChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Stacked Horizontal Bar</BCardTitle>
            <vue-echarts :option="stackedHorizontalBarChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Candlestick Chart</BCardTitle>
            <vue-echarts :option="candlestickChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Pie Chart</BCardTitle>
            <vue-echarts :option="pieChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Doughnut Chart</BCardTitle>
            <vue-echarts :option="doughnutChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Graph Chart</BCardTitle>
            <vue-echarts :option="graphChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Treemap Chart</BCardTitle>
            <vue-echarts :option="treemapChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Basic Scatter Chart</BCardTitle>
            <vue-echarts :option="scatterChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Sunburst Chart</BCardTitle>
            <vue-echarts :option="sunburstChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Bubble Chart</BCardTitle>
            <vue-echarts :option="bubbleChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Polar Chart</BCardTitle>
            <vue-echarts :option="polarChart.polar" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Parallel Chart</BCardTitle>
            <vue-echarts :option="parallelChart" style="height: 300px" ref="chart" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Sankey Chart</BCardTitle>
            <vue-echarts :option="sankeyChart" style="height: 300px" ref="chart" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Mix Line-Bar</BCardTitle>
            <vue-echarts :option="mixedBarChart" style="height: 300px" ref="chart" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Funnel Chart</BCardTitle>
            <vue-echarts :option="funnelChart" style="height: 300px" ref="chart" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Gauge Chart</BCardTitle>
            <vue-echarts :option="guageChart" style="height: 300px" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>