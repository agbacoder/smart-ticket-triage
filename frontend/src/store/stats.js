import { reactive } from "vue";
import axiosClient from "@/axios.js";

export const statsStore = reactive({
  stats: null,

  async fetchStats() {
    try {
      const res = await axiosClient.get("/stats");
      this.stats = res.data.data;
    } catch (err) {
      console.error("Error fetching stats:", err);
      this.stats = null;
    }
  },
});
