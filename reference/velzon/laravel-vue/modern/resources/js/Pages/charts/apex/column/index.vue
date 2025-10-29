<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

import {
  basicColumnChart,
  columnDatalabelChart,
  stackedColumnsChart,
  columnStackedChart,
  columnMarkersChart,
  columnRotatedLabel,
  columnNegativeChart,
  rangeColumnChart,
  chartYearOption,
  quarterChartOption,
  distributedColumnchart,
  dynamicQuarterLoadedChart,
  groupedStackedChart,
  dumbbellChart
} from "./data";

/**
 * Column Charts component
 */
export default {
  data() {
    return {
      basicColumnChart: JSON.parse(JSON.stringify(basicColumnChart)),
      columnDatalabelChart: JSON.parse(JSON.stringify(columnDatalabelChart)),
      stackedColumnsChart: JSON.parse(JSON.stringify(stackedColumnsChart)),
      columnStackedChart: JSON.parse(JSON.stringify(columnStackedChart)),
      columnMarkersChart: JSON.parse(JSON.stringify(columnMarkersChart)),
      columnRotatedLabel: JSON.parse(JSON.stringify(columnRotatedLabel)),
      columnNegativeChart: JSON.parse(JSON.stringify(columnNegativeChart)),
      rangeColumnChart: JSON.parse(JSON.stringify(rangeColumnChart)),
      chartYearOptionChart: JSON.parse(JSON.stringify(chartYearOption)),
      quarterChartOptionChart: JSON.parse(JSON.stringify(quarterChartOption)),
      distributedColumnchart: JSON.parse(JSON.stringify(distributedColumnchart)),
      dynamicQuarterLoadedChart: JSON.parse(JSON.stringify(dynamicQuarterLoadedChart)),
      groupedStackedChart: JSON.parse(JSON.stringify(groupedStackedChart)),
      dumbbellChart: JSON.parse(JSON.stringify(dumbbellChart)),
    };
  },
  components: {
    Layout,
    PageHeader
  },
  computed: {
    basicColumnChartOptions() {
      return JSON.parse(JSON.stringify(this.basicColumnChart.chartOptions)); // Create a deep copy for basic column chart options
    },
    basicColumnChartSeries() {
      return JSON.parse(JSON.stringify(this.basicColumnChart.series)); // Create a deep copy for basic column chart series
    },
    columnDatalabelChartOptions() {
      return JSON.parse(JSON.stringify(this.columnDatalabelChart.chartOptions)); // Create a deep copy for column data label chart options
    },
    columnDatalabelChartSeries() {
      return JSON.parse(JSON.stringify(this.columnDatalabelChart.series)); // Create a deep copy for column data label chart series
    },
    stackedColumnsChartOptions() {
      return JSON.parse(JSON.stringify(this.stackedColumnsChart.chartOptions)); // Create a deep copy for stacked columns chart options
    },
    stackedColumnsChartSeries() {
      return JSON.parse(JSON.stringify(this.stackedColumnsChart.series)); // Create a deep copy for stacked columns chart series
    },
    columnStackedChartOptions() {
      return JSON.parse(JSON.stringify(this.columnStackedChart.chartOptions)); // Create a deep copy for column stacked chart options
    },
    columnStackedChartSeries() {
      return JSON.parse(JSON.stringify(this.columnStackedChart.series)); // Create a deep copy for column stacked chart series
    },
    columnMarkersChartOptions() {
      return JSON.parse(JSON.stringify(this.columnMarkersChart.chartOptions)); // Create a deep copy for column markers chart options
    },
    columnMarkersChartSeries() {
      return JSON.parse(JSON.stringify(this.columnMarkersChart.series)); // Create a deep copy for column markers chart series
    },
    columnRotatedLabelOptions() {
      return JSON.parse(JSON.stringify(this.columnRotatedLabel.chartOptions)); // Create a deep copy for column rotated label chart options
    },
    columnRotatedLabelSeries() {
      return JSON.parse(JSON.stringify(this.columnRotatedLabel.series)); // Create a deep copy for column rotated label chart series
    },
    columnNegativeChartOptions() {
      return JSON.parse(JSON.stringify(this.columnNegativeChart.chartOptions)); // Create a deep copy for column negative chart options
    },
    columnNegativeChartSeries() {
      return JSON.parse(JSON.stringify(this.columnNegativeChart.series)); // Create a deep copy for column negative chart series
    },
    rangeColumnChartOptions() {
      return JSON.parse(JSON.stringify(this.rangeColumnChart.chartOptions)); // Create a deep copy for range column chart options
    },
    rangeColumnChartSeries() {
      return JSON.parse(JSON.stringify(this.rangeColumnChart.series)); // Create a deep copy for range column chart series
    },
    chartYearOption() {
      return JSON.parse(JSON.stringify(this.chartYearOptionChart.chartOptions)); // Create a deep copy for year chart options
    },
    chartYearSeries() {
      return JSON.parse(JSON.stringify(this.chartYearOptionChart.series)); // Create a deep copy for year chart options
    },
    quarterChartOption() {
      return JSON.parse(JSON.stringify(this.quarterChartOptionChart.chartOptions)); // Create a deep copy for quarter chart options
    },
    quarterChartSeries() {
      return JSON.parse(JSON.stringify(this.quarterChartOptionChart.series)); // Create a deep copy for quarter chart options
    },
    distributedColumnchartOptions() {
      return JSON.parse(JSON.stringify(this.distributedColumnchart.chartOptions)); // Create a deep copy for distributed column chart options
    },
    distributedColumnchartSeries() {
      return JSON.parse(JSON.stringify(this.distributedColumnchart.series)); // Create a deep copy for distributed column chart series
    },
    dynamicQuarterLoadedChartOptions() {
      return JSON.parse(JSON.stringify(this.dynamicQuarterLoadedChart.chartOptions)); // Create a deep copy for dynamic quarter loaded chart options
    },
    dynamicQuarterLoadedChartSeries() {
      return JSON.parse(JSON.stringify(this.dynamicQuarterLoadedChart.series)); // Create a deep copy for dynamic quarter loaded chart series
    },
    groupedStackedChartOptions() {
      return JSON.parse(JSON.stringify(this.groupedStackedChart.chartOptions)); // Create a deep copy for grouped stacked chart options
    },
    groupedStackedChartSeries() {
      return JSON.parse(JSON.stringify(this.groupedStackedChart.series)); // Create a deep copy for grouped stacked chart series
    },
    dumbbellChartOptions() {
      return JSON.parse(JSON.stringify(this.dumbbellChart.chartOptions)); // Create a deep copy for dumbbell chart options
    },
    dumbbellChartSeries() {
      return JSON.parse(JSON.stringify(this.dumbbellChart.series)); // Create a deep copy for dumbbell chart series
    },
    basicRangeAreaChartOptions() {
      return JSON.parse(JSON.stringify(this.basicRangeAreaChart.chartOptions)); // Create a deep copy
    },
    basicRangeAreaChartSeries() {
      return JSON.parse(JSON.stringify(this.basicRangeAreaChart.series)); // Create a deep copy
    },
    comboChartOptions() {
      return JSON.parse(JSON.stringify(this.comboChart.chartOptions)); // Create a deep copy of combo chart options
    },
    comboChartSeries() {
      return JSON.parse(JSON.stringify(this.comboChart.series)); // Create a deep copy of combo chart series
    },
  }
};
</script>

<template>
  <Layout>
    <PageHeader title="Column Charts" pageTitle="Apexcharts" />
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader class="
              justify-content-between
              d-flex
              align-items-center
            ">
            <BCardTitle>Basic Column Charts</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="basicColumnChartSeries"
              :options="basicColumnChartOptions"></apexchart>
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
            <BCardTitle>Column with Data Labels</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="columnDatalabelChartSeries"
              :options="columnDatalabelChartOptions"></apexchart>
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
            <BCardTitle>Stacked Column Charts</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="stackedColumnsChartSeries"
              :options="stackedColumnsChartOptions"></apexchart>
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
            <BCardTitle>Stacked Column 100</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="columnStackedChartSeries"
              :options="columnStackedChartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol xl="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Grouped Stacked Columns</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="groupedStackedChartSeries"
              :options="groupedStackedChartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>

      <BCol xl="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Dumbbell Chart</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="dumbbellChartSeries"
              :options="dumbbellChartOptions"></apexchart>
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
            <BCardTitle>Column with Markers</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="columnMarkersChartSeries"
              :options="columnMarkersChartOptions"></apexchart>
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
            <BCardTitle>Column with Rotated Labels</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="columnRotatedLabelSeries"
              :options="columnRotatedLabelOptions"></apexchart>
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
            <BCardTitle>Column with Nagetive Values</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="columnNegativeChartSeries"
              :options="columnNegativeChartOptions"></apexchart>
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
            <BCardTitle>Range Column Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="rangeColumnChartSeries"
              :options="rangeColumnChartOptions"></apexchart>
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
            <BCardTitle>Dynamic Loaded Chart</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <div id="dynamicloadedchart-wrap">
              <div id="chart-year">
                <apexchart type="bar" height="330" ref="chart" :options="chartYearOption"
                  :series="chartYearSeries"></apexchart>
              </div>
              <div id="chart-quarter">
                <apexchart type="bar" height="330" ref="chartQuarter" :options="dynamicQuarterLoadedChartOptions"
                  :series="dynamicQuarterLoadedChartSeries"></apexchart>
              </div>
            </div>
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
            <BCardTitle>Distributed Columns Charts</BCardTitle>

          </BCardHeader>
          <BCardBody>
            <apexchart class="apex-charts" height="350" dir="ltr" :series="distributedColumnchartSeries"
              :options="distributedColumnchartOptions"></apexchart>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
