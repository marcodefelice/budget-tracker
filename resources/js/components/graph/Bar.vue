<template>
  <div>
    <div id="chart"></div>
  </div>
</template>

<script>
import ApexCharts from 'apexcharts'
import axios from 'axios'

var options = {
  chart: {
    type: 'bar'
  },
  series: [
    {
      name: 'incoming',
      data: []
    }
  ],
  xaxis: {
    categories: []
  }
}

const X_API_KEY = { "X-API-KEY": "7221" };

export default {
    mounted() {

        axios.get("/api/graph/incoming/salary/",{
          headers: X_API_KEY,
      }).then((resp) => {
          let data = resp.data

          data.forEach(function(e) {
            options.series[0].data.push(e.total)
            options.xaxis.categories.push(e.year)
          })

          console.log('Component mounted.')
          var chart = new ApexCharts(document.querySelector('#chart'), options)
          chart.render()
        })
        .catch((error) => {
          // handle error
          console.error(error);
        });

    }
}

</script>
