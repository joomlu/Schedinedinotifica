<script>
import { CountTo } from "vue3-count-to";
import getChartColorsArray from "@/common/getChartColorsArray";
import { layoutComputed } from "@/state/helpers.js"
export default {
  components: {
    CountTo,
  },
  data() {
    return {
      series: [{
        name: "Orders",
        type: "area",
        data: [34, 65, 46, 68, 49, 61, 42, 44, 78, 52, 63, 67],
      },
      {
        name: "Earnings",
        type: "bar",
        data: [
          89.25, 98.58, 68.74, 108.87, 77.54, 84.03, 51.24, 28.57, 92.57,
          42.36, 88.51, 36.57,
        ],
      },
      {
        name: "Refunds",
        type: "line",
        data: [8, 12, 7, 17, 21, 11, 5, 9, 7, 29, 12, 35],
      },
      ],
      chartOptions: {
        chart: {
          height: 370,
          type: "line",
          toolbar: {
            show: false,
          },
        },
        stroke: {
          curve: "straight",
          dashArray: [0, 0, 8],
          width: [2, 0, 2.2],
        },
        fill: {
          opacity: [0.1, 0.9, 1],
        },
        markers: {
          size: [0, 0, 0],
          strokeWidth: 2,
          hover: {
            size: 4,
          },
        },
        xaxis: {
          categories: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ],
          axisTicks: {
            show: false,
          },
          axisBorder: {
            show: false,
          },
        },
        grid: {
          show: true,
          xaxis: {
            lines: {
              show: true,
            },
          },
          yaxis: {
            lines: {
              show: false,
            },
          },
          padding: {
            top: 0,
            right: -2,
            bottom: 15,
            left: 10,
          },
        },
        legend: {
          show: true,
          horizontalAlign: "center",
          offsetX: 0,
          offsetY: -5,
          markers: {
            width: 9,
            height: 9,
            radius: 6,
          },
          itemMargin: {
            horizontal: 10,
            vertical: 0,
          },
        },
        plotOptions: {
          bar: {
            columnWidth: "30%",
            barHeight: "70%",
          },
        },
        tooltip: {
          shared: true,
          y: [{
            formatter: function (y) {
              if (typeof y !== "undefined") {
                return y.toFixed(0);
              }
              return y;
            },
          },
          {
            formatter: function (y) {
              if (typeof y !== "undefined") {
                return "$" + y.toFixed(2) + "k";
              }
              return y;
            },
          },
          {
            formatter: function (y) {
              if (typeof y !== "undefined") {
                return y.toFixed(0) + " Sales";
              }
              return y;
            },
          },
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
              colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-danger"]')
            };
            break;
          case "saas":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-success", "--vz-info", "--vz-danger"]')
            };
            break;
          case "corporate":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-light", "--vz-primary", "--vz-secondary"]')
            };
            break;
          case "galaxy":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-secondary", "--vz-primary", "--vz-primary-rgb, 0.50"]')
            };
            break;
          case "material":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-danger"]')
            };
            break;
          case "creative":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-primary", "--vz-danger"]')
            };
            break;
          case "minimal":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-light", "--vz-primary", "--vz-info"]')
            };
            break;
          case "modern":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-warning", "--vz-primary", "--vz-success"]')
            };
            break;
          case "interactive":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-info","--vz-primary", "--vz-danger"]')
            };
            break;
          case "classic":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-light", "--vz-primary", "--vz-secondary"]')
            };
            break;
          case "vintage":
            this.chartOptions = {
              ...this.chartOptions,
              colors: getChartColorsArray('["--vz-success", "--vz-primary", "--vz-secondary"]')
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
  <BCard no-body>
    <BCardHeader class="border-0 align-items-center d-flex">
      <BCardTitle class="mb-0 flex-grow-1">Revenue</BCardTitle>
      <div class="hstack gap-1">
        <BButton type="button" variant="soft-secondary" size="sm" class="material-shadow-none">
          ALL
        </BButton>
        <BButton type="button" variant="soft-secondary" size="sm" class="material-shadow-none">
          1M
        </BButton>
        <BButton type="button" variant="soft-secondary" size="sm" class="material-shadow-none">
          6M
        </BButton>
        <BButton type="button" variant="soft-primary" size="sm" class="material-shadow-none">
          1Y
        </BButton>
      </div>
    </BCardHeader>

    <BCardHeader class="p-0 border-0 bg-light-subtle">
      <BRow class="g-0 text-center">
        <BCol cols="6" sm="3">
          <div class="p-3 border border-dashed border-start-0">
            <h5 class="mb-1">
              <count-to :startVal="0" :endVal="7585" :duration="4000"></count-to>
            </h5>
            <p class="text-muted mb-0">Orders</p>
          </div>
        </BCol>
        <BCol cols="6" sm="3">
          <div class="p-3 border border-dashed border-start-0">
            <h5 class="mb-1">
              $<count-to :startVal="0" :endVal="22.89" :duration="4000" :decimals="2"></count-to>k
            </h5>
            <p class="text-muted mb-0">Earnings</p>
          </div>
        </BCol>
        <BCol cols="6" sm="3">
          <div class="p-3 border border-dashed border-start-0">
            <h5 class="mb-1">
              <count-to :startVal="0" :endVal="367" :duration="4000"></count-to>
            </h5>
            <p class="text-muted mb-0">Refunds</p>
          </div>
        </BCol>
        <BCol cols="6" sm="3">
          <div class="p-3 border border-dashed border-start-0 border-end-0">
            <h5 class="mb-1 text-success">
              <count-to :startVal="0" :endVal="18.92" :duration="4000" :decimals="2"></count-to>%
            </h5>
            <p class="text-muted mb-0">Conversation Ratio</p>
          </div>
        </BCol>
      </BRow>
    </BCardHeader>

    <BCardBody class="p-0 pb-2">
      <div class="w-100">
        <apexchart class="apex-charts" height="370" type="line" dir="ltr" :series="series" :options="chartOptions">
        </apexchart>
      </div>
    </BCardBody>
  </BCard>
</template>