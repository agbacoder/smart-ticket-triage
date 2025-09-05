import { reactive } from "vue";
import axiosClient from "@/axios.js";

export const ticketsStore = reactive({
  tickets: [],
  categories: [],
  pagination: {
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 10,
  },

  // Normalize ticket object so confidence is always a number
  normalizeTicket(t) {
    return {
      ...t,
      category: t.category || null,
      confidence:
        t.confidence !== null && t.confidence !== undefined && t.confidence !== ""
          ? Number(t.confidence)
          : null,
      note: t.note || "",
      explanation: t.explanation || null,
    };
  },

  // Helper: update categories safely
  setCategories(list) {
    if (Array.isArray(list)) {
      this.categories = [...new Set(list.filter((c) => c && c.trim() !== ""))];
    }
  },

  // Fetch tickets (with filters + pagination)
  async fetchTickets(filters = {}, page = 1) {
    try {
      const params = { page, per_page: this.pagination.perPage, ...filters };
      const res = await axiosClient.get("/tickets", { params });
      const payload = res.data.data;

      // normalize tickets
      this.tickets = payload.data.map(this.normalizeTicket);

      // pagination
      this.pagination.currentPage = payload.current_page;
      this.pagination.lastPage = payload.last_page;
      this.pagination.total = payload.total;
      this.pagination.perPage = payload.per_page;

      // categories come from API if provided
      if (payload.categories) {
        this.setCategories(payload.categories);
      } else {
        // fallback: gather from tickets
        this.setCategories(this.tickets.map((t) => t.category));
      }
    } catch (err) {
      console.error("Error fetching tickets:", err);
      this.tickets = [];
      this.categories = [];
    }
  },

  // Add a new ticket
  async addTicket({ subject, body }) {
    try {
      const res = await axiosClient.post("/tickets", { subject, body });
      const newTicket = this.normalizeTicket(res.data.data);

      // add to list (reactivity-safe)
      this.tickets = [newTicket, ...this.tickets];

      // update categories dynamically if backend sends them
      if (res.data.categories) {
        this.setCategories(res.data.categories);
      } else {
        this.setCategories(this.tickets.map((t) => t.category));
      }
    } catch (err) {
      console.error("Error adding ticket:", err.response?.data || err);
    }
  },

  // Update a field in a ticket
  async updateTicket(id, field, value) {
    try {
      await axiosClient.patch(`/tickets/${id}`, { [field]: value });

      const idx = this.tickets.findIndex((t) => t.id === id);
      if (idx !== -1) {
        const updated = { ...this.tickets[idx], [field]: value };
        this.tickets = this.tickets.map((t, i) => (i === idx ? this.normalizeTicket(updated) : t));
      }

      // ✅ categories from tickets after change
      this.setCategories(this.tickets.map((t) => t.category));
    } catch (err) {
      console.error(`Error updating ticket ${id}:`, err.response?.data || err);
    }
  },

  // 🔹 Replace entire ticket
  async replaceTicket(updatedTicket) {
    try {
      await axiosClient.patch(`/tickets/${updatedTicket.id}`, updatedTicket);

      this.tickets = this.tickets.map((t) =>
        Number(t.id) === Number(updatedTicket.id)
          ? this.normalizeTicket({ ...t, ...updatedTicket })
          : t
      );

      this.setCategories(this.tickets.map((t) => t.category));
    } catch (err) {
      console.error("Error replacing ticket:", err);
    }
  },

  // Classification job with polling
  async classifyTicket(id) {
    const delay = (ms) => new Promise((r) => setTimeout(r, ms));

    try {
      const postRes = await axiosClient.post(`/tickets/${id}/classify`);
      console.log("POST classify response:", postRes.status, postRes.data);

      // immediate response with updated ticket
      const maybeTicket = postRes.data?.data;
      if (maybeTicket && maybeTicket.id) {
        const updated = this.normalizeTicket(maybeTicket);
        this.tickets = this.tickets.map((t) =>
          Number(t.id) === Number(updated.id) ? updated : t
        );
        this.setCategories(this.tickets.map((t) => t.category));
        return updated;
      }

      // polling fallback
      const maxAttempts = 20;
      for (let attempt = 1; attempt <= maxAttempts; attempt++) {
        const res = await axiosClient.get(`/tickets/${id}`, {
          headers: { "Cache-Control": "no-cache" },
        });
        const fresh = res.data?.data;
        console.log("poll attempt", attempt, fresh);

        if (
          fresh &&
          (fresh.category ||
            (fresh.confidence !== null &&
              fresh.confidence !== undefined &&
              fresh.confidence !== ""))
        ) {
          const updated = this.normalizeTicket(fresh);
          this.tickets = this.tickets.map((t) =>
            Number(t.id) === Number(updated.id) ? updated : t
          );
          this.setCategories(this.tickets.map((t) => t.category));
          return updated;
        }

        await delay(Math.min(500 * attempt, 2000));
      }

      // final fallback: refresh page
      await this.fetchTickets({}, this.pagination.currentPage);
      return this.tickets.find((t) => Number(t.id) === Number(id)) || null;
    } catch (err) {
      console.error("Error in classifyTicket:", err);
      throw err;
    }
  },
});
