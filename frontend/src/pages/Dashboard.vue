<template>
  <div class="dashboard">
    <h1 class="dashboard__title">Dashboard</h1>

    <!-- Categories -->
    <h2 class="dashboard__subtitle">Category</h2>
    <div class="dashboard__cards">
      <div
        class="dashboard__card"
        v-for="(count, category) in ticketsPerCategory"
        :key="category"
        :style="{
          backgroundColor: categoryColors[category],
          color: '#fff'
        }"
      >
        <h3>{{ category }}</h3>
        <p>{{ count }}</p>
      </div>
    </div>

    <!-- Status -->
    <h2 class="dashboard__subtitle">Status</h2>
    <div class="dashboard__cards">
      <div
        class="dashboard__card"
        v-for="(count, status) in ticketsPerStatus"
        :key="status"
        :style="{
          backgroundColor: statusColors[status] || 'var(--alt-bg-color)',
          color: '#fff'
        }"
      >
        <h3>{{ formatStatus(status) }}</h3>
        <p>{{ count }}</p>
      </div>
    </div>

    <!-- Total Tickets -->
    <h2 class="dashboard__subtitle">Total Tickets</h2>
    <div class="dashboard__cards">
      <div class="dashboard__card dashboard__card--total">
        <h3>Total Tickets</h3>
        <p>{{ totalTickets }}</p>
      </div>
    </div>

    <!-- Chart -->
    <div class="dashboard__chart" style="height: 320px;">
      <PieChart :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script>
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
} from "chart.js";
import { Pie } from "vue-chartjs";
import { statsStore } from "@/store/stats.js";
import { ticketsStore } from "@/store/ticket.js"; 

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

export default {
  name: "Dashboard",
  components: { PieChart: Pie },
  data() {
    return {
      statsStore,
      ticketsStore,
      statusColors: {
        open: "#29b6f6",
        in_progress: "#ffa726",
        closed: "#8d6e63",
      },
      colorPalette: [
        "#f9a825",
        "#42a5f5",
        "#66bb6a",
        "#ef5350",
        "#ab47bc",
        "#ff7043",
        "#26c6da",
      ],
    };
  },
  computed: {
    // stats object guard: statsStore.stats may be null initially
    totalTickets() {
      return (
        (this.statsStore.stats && this.statsStore.stats.total_tickets) || 0
      );
    },
    ticketsPerStatus() {
      return (this.statsStore.stats && this.statsStore.stats.status) || {};
    },
    ticketsPerCategory() {
      return (this.statsStore.stats && this.statsStore.stats.category) || {};
    },
    categoryColors() {
      const categories = Object.keys(this.ticketsPerCategory);
      const colors = {};
      categories.forEach((cat, idx) => {
        colors[cat] = this.colorPalette[idx % this.colorPalette.length];
      });
      return colors;
    },
    chartData() {
      const labels = Object.keys(this.ticketsPerCategory);
      const data = Object.values(this.ticketsPerCategory);
      return {
        labels,
        datasets: [
          {
            data,
            backgroundColor: labels.map((cat) => this.categoryColors[cat]),
            borderColor: "#000",
            borderWidth: 2,
          },
        ],
      };
    },
    chartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              color: "#000",
              font: { size: 12 },
              boxWidth: 14,
            },
          },
        },
      };
    },
  },
  methods: {
    formatStatus(status) {
      return status ? status.replace("_", " ") : "";
    },
  },
  async created() {
    await this.statsStore.fetchStats();

    // This ensures dashboard updates after ticket create/classify/etc.
    this.$watch(
      () => this.ticketsStore.tickets.length,
      async () => {
        await this.statsStore.fetchStats();
      }
    );
    this.$watch(
      () => JSON.stringify(this.ticketsStore.tickets.map(t => ({ id: t.id, category: t.category, updated_at: t.updated_at }))),
      async () => {
        await this.statsStore.fetchStats();
      }
    );
  },
};
</script>

<style src="@/assets/css/dashboard.css"></style>
