import getChartColorsArray from "@/common/getChartColorsArray";

// Column with Datalabels

const columnDatalabelChart = {
  series: [
    {
      name: "Inflation",
      data: [2.5, 3.2, 5.0, 10.1, 4.2, 3.8, 3, 2.4, 4.0, 1.2, 3.5, 0.8],
    },
  ],
  chartOptions: {
    chart: {
      height: 350,
      type: "bar",
      toolbar: {
        show: false,
      },
    },
    plotOptions: {
      bar: {
        dataLabels: {
          position: "top", // top, center, bottom
        },
      },
    },
    dataLabels: {
      enabled: true,
      formatter: function (val) {
        return val + "%";
      },
      offsetY: -20,
      style: {
        fontSize: "12px",
        colors: ["#adb5bd"],
      },
    },
    colors: getChartColorsArray('["--vz-primary"]'),
    grid: {
      borderColor: "#f1f1f1",
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
      position: "bottom",
      labels: {
        show: true,
      },
      axisBorder: {
        show: true,
      },
      axisTicks: {
        show: true,
      },
      crosshairs: {
        fill: {
          type: "gradient",
          gradient: {
            colorFrom: "#D8E3F0",
            colorTo: "#BED1E6",
            stops: [0, 100],
            opacityFrom: 0.4,
            opacityTo: 0.5,
          },
        },
      },
      tooltip: {
        enabled: true,
        offsetY: -35,
      },
    },
    fill: {
      gradient: {
        shade: "light",
        type: "horizontal",
        shadeIntensity: 0.25,
        gradientToColors: undefined,
        inverseColors: true,
        opacityFrom: 1,
        opacityTo: 1,
        stops: [50, 0, 100, 100],
      },
    },
    yaxis: {
      axisBorder: {
        show: true,
      },
      axisTicks: {
        show: true,
      },
      labels: {
        show: true,
        formatter: function (val) {
          return val + "%";
        },
      },
    },
    // title: {
    //   text: "Monthly Inflation in Argentina, 2002",
    //   floating: true,
    //   offsetY: 320,
    //   align: "center",
    //   style: {
    //     color: "#444",
    //     fontWeight: 500,
    //   },
    // },
  },
};

// Gradient Donut Chart

const gradientDonutChart = {
  series: [44, 55, 24],
  chartOptions: {
    chart: {
      height: 210,
      type: "donut",
    },
    plotOptions: {
      pie: {
        startAngle: -90,
        endAngle: 270,
      },
    },
    labels: ["Desktop", "Mobile", "Laptop"],
    dataLabels: {
      enabled: false,
    },
    fill: {
      type: "gradient",
    },
    legend: {
      position: "bottom",
      // formatter: function (val, opts) {
      //   return val + " - " + opts.w.globals.series[opts.seriesIndex];
      // },
    },
    colors: getChartColorsArray(
      '["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
    ),
  },
};

export { columnDatalabelChart,  gradientDonutChart };

