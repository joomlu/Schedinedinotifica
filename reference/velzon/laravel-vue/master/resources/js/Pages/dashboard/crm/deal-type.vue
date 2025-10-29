<script>
import getChartColorsArray from "@/common/getChartColorsArray";
import { layoutComputed } from "@/state/helpers.js"
export default {
  data() {
    return {
      series: [{
        name: "Pending",
        data: [80, 50, 30, 40, 100, 20],
      },
      {
        name: "Loss",
        data: [20, 30, 40, 80, 20, 80],
      },
      {
        name: "Won",
        data: [44, 76, 78, 13, 43, 10],
      },
      ],
      chartOptions: {
        chart: {
          height: 341,
          type: "radar",
          dropShadow: {
            enabled: true,
            blur: 1,
            left: 1,
            top: 1,
          },
          toolbar: {
            show: false,
          },
        },
        stroke: {
          width: 2,
        },
        fill: {
          opacity: 0.2,
        },
        legend: {
          show: true,
          fontWeight: 500,
          offsetX: 0,
          offsetY: -8,
          markers: {
            width: 8,
            height: 8,
            radius: 6,
          },
          itemMargin: {
            horizontal: 10,
            vertical: 0,
          },
        },
        markers: {
          size: 0,
        },
        xaxis: {
          categories: ["2016", "2017", "2018", "2019", "2020", "2021"],
        },
      },
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
        switch (this.layoutTheme) {
          case "default":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-danger", "--vz-success"]')
            };
            break;
          case "saas":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-danger", "--vz-success"]')
            };
            break;
          case "corporate":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-secondary", "--vz-info", "--vz-success"]')
            };
            break;
          case "galaxy":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-danger", "--vz-success"]')
            };
            break;
          case "material":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-danger", "--vz-success"]')
            };
            break;
          case "creative":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-danger", "--vz-success"]')
            };
            break;
          case "minimal":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-primary-rgb, 0.15", "--vz-primary-rgb, 0.35", "--vz-primary-rgb, 0.45"]')
            };
            break;
          case "modern":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-secondary", "--vz-success"]')
            };
            break;
          case "interactive":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-info", "--vz-primary"]')
            };
            break;
          case "classic":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-secondary", "--vz-danger", "--vz-success"]')
            };
            break;
          case "vintage":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-danger", "--vz-success"]')
            };
            break;
        }
      }, 200)
    },
  },
  mounted() {
    this.handleUpdate();
  },
  watch: {
    layoutTheme() {
      this.handleUpdate();
    }
  },
};
</script>

<template>
  <BCard no-body class="card-height-100">
    <BCardHeader class="align-items-center d-flex py-0">
      <BCardTitle class="mb-0 flex-grow-1">Deal Type</BCardTitle>
      <div class="flex-shrink-0">
        <BDropdown variant="link" class="card-header-dropdown" toggle-class="text-reset dropdown-btn" no-caret
          menu-class="dropdown-menu-end" aria-haspopup="true" :offset="{ alignmentAxis: -35, crossAxis: 0, mainAxis: 0 }">
          <template #button-content> <span class="fw-semibold text-uppercase fs-12">Sort by: </span><span
              class="text-muted">Monthly<i class="mdi mdi-chevron-down ms-1"></i></span>
          </template>
          <BDropdownItem>Today</BDropdownItem>
          <BDropdownItem>Weekly</BDropdownItem>
          <BDropdownItem>Monthly</BDropdownItem>
          <BDropdownItem>Yearly</BDropdownItem>
        </BDropdown>
      </div>
    </BCardHeader>
    <BCardBody class="pb-0">
      <apexchart class="apex-charts" height="341" dir="ltr" :series="series" :options="chartOptions"></apexchart>
    </BCardBody>
  </BCard>
</template>