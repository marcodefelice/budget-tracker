<template>
  <div class="mt-10">
    <div class="py-5">
      <FilterBar/>
    </div>
    <div class="flex flex-wrap">
    <div class="w-full">
      <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
        <div class="px-4 py-5 flex-auto">
              <CardLineBarIncomingExpensesChart ID_GRAPH="line" type="line"/>
        </div>
      </div>
    </div>
      <div class="w-full xl:w-4/12 px-4">
        <card-bar-chart path="category" title="Category" ID_GRAPH="category_all"/>
      </div>
      <div class="w-full xl:w-4/12 px-4">
        <card-pie-chart path="label" title="Label" ID_GRAPH="label"/>
      </div>
      <div class="w-full xl:w-4/12 px-4">
        <CardLineBarIncomingExpensesChart :filter="true" ID_GRAPH="bar" title="Incoming & Expenses" type="bar"/>
      </div>
    </div>
    <div class="flex flex-wrap mt-4">
      <div class="w-full xl:w-8/12 mb-12 xl:mb-0 px-4">
        <card-page-visits />
      </div>
      <div class="w-full xl:w-4/12 px-4">
        <card-social-traffic />
      </div>
    </div>
  </div>
</template>
<script>
import FilterBar from "@/components/Navbars/FilterBar.vue";
import CardBarChart from "@/components/Cards/Char/CardBarChart.vue";
import CardPieChart from "@/components/Cards/Char/CardPieChart.vue";
import CardLineBarIncomingExpensesChart from "@/components/Cards/Char/CardLineBar_IncomingExpensesChart.vue";
import CardPageVisits from "@/components/Cards/CardPageVisits.vue";
import CardSocialTraffic from "@/components/Cards/CardSocialTraffic.vue";
export default {
  name: "dashboard-page",
  components: {
    FilterBar,
    CardBarChart,
    CardPieChart,
    CardLineBarIncomingExpensesChart,
    CardPageVisits,
    CardSocialTraffic,
  },
  data() {
    return {
      openTab: 1
    }
  },
  methods: {
    toggleTabs: function(tabNumber){
      this.openTab = tabNumber
    }
  }
};
</script>
