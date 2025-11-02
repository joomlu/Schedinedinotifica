<script>
import getChartColorsArray from "@/common/getChartColorsArray";
import { layoutComputed } from "@/state/helpers.js"

export default {
  data() {
    return {
      series: [{
        data: [1010, 1640, 490, 1255, 1050, 689, 800, 420, 1085, 589],
        name: "Sessions",
      },],
      chartOptions: {
        chart: {
          type: "bar",
          height: 436,
          toolbar: {
            show: false,
          },
        },
        plotOptions: {
          bar: {
            borderRadius: 4,
            horizontal: true,
            distributed: true,
            dataLabels: {
              position: "top",
            },
          },
        },
        dataLabels: {
          enabled: true,
          offsetX: 32,
          style: {
            fontSize: "12px",
            fontWeight: 400,
            colors: ["#adb5bd"],
          },
        },

        legend: {
          show: false,
        },
        grid: {
          show: false,
        },
        xaxis: {
          categories: [
            "India",
            "United States",
            "China",
            "Indonesia",
            "Russia",
            "Bangladesh",
            "Canada",
            "Brazil",
            "Vietnam",
            "UK",
          ],
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
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
            };
            break;
          case "saas":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
            };
            break;
          case "corporate":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
            };
            break;
          case "galaxy":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4", "--vz-primary", "--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4", "--vz-primary-rgb, 0.4"]')
            };
            break;
          case "material":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-primary", "--vz-primary", "--vz-info", "--vz-info", "--vz-danger", "--vz-primary", "--vz-primary", "--vz-warning", "--vz-primary", "--vz-primary"]')
            };
            break;
          case "creative":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
            };
            break;
          case "minimal":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary-rgb, 0.45", "--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary"]')
            };
            break;
          case "modern":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
            };
            break;
          case "interactive":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
            };
            break;
          case "classic":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary-rgb, 0.45", "--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary", "--vz-primary"]')
            };
            break;
          case "vintage":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]')
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
    <BCardHeader class="align-items-center d-flex">
      <BCardTitle class="mb-0 flex-grow-1">Sessions by Countries</BCardTitle>
      <div class="hstack gap-1">
        <BButton type="button" variant="soft-secondary" size="sm" class="material-shadow-none">ALL</BButton>
        <BButton type="button" variant="soft-primary" size="sm" class="material-shadow-none">1M</BButton>
        <BButton type="button" variant="soft-secondary" size="sm" class="material-shadow-none">6M</BButton>
      </div>
    </BCardHeader>
    <BCardBody class="p-0">
      <div>
        <apexchart class="apex-charts" dir="ltr" height="436" :series="series" :options="chartOptions"></apexchart>
      </div>
    </BCardBody>
  </BCard>
</template>