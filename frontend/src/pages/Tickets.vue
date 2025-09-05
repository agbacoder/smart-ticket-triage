<template>
  <div class="ticket-list">
    <!-- Header -->
    <div class="ticket-list__header">
      <h1 class="ticket-list__title">Tickets</h1>
      <div class="ticket-list__actions">
        <input
          v-model="searchQuery"
          placeholder="Search tickets"
          class="ticket-list__search"
        />
        <select v-model="statusFilter" class="ticket-list__filter">
          <option value="">All Statuses</option>
          <option v-for="s in statuses" :key="s" :value="s">
            {{ formatStatus(s) }}
          </option>
        </select>
        <button @click="openNewTicketModal" class="ticket-list__new-button">
          New Ticket
        </button>
      </div>
    </div>

    <!-- Table -->
    <table class="ticket-list__table">
      <thead>
        <tr>
          <th class="col-subject">Subject</th>
          <th class="col-status">Status</th>
          <th class="col-category">Category</th>
          <th class="col-confidence">Confidence</th>
          <th class="col-note">Note</th>
          <th class="col-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ticket in ticketsStore.tickets" :key="ticket.id">
          <td>{{ ticket.subject }}</td>
          <td>
            <span
              :class="[
                'ticket-list__status-badge',
                'ticket-list__status-badge--' + ticket.status,
              ]"
            >
              {{ formatStatus(ticket.status) }}
            </span>
          </td>
          <td>
            <select
              v-model="ticket.category"
              class="ticket-list__category-dropdown"
              @change="handleTicketPatch({ id: ticket.id, field: 'category', value: ticket.category })"
            >
              <option v-for="c in ticketsStore.categories" :key="c" :value="c">
                {{ c }}
              </option>
            </select>
          </td>
          <td>
            <div class="ticket-list__confidence">
              {{ formatConfidence(ticket.confidence) }}
              <span
                v-if="ticket.explanation"
                class="ticket-list__tooltip"
                :title="ticket.explanation"
              >ⓘ</span>
            </div>
          </td>
          <td>
            <span v-if="ticket.note" class="ticket-list__note-badge">📝</span>
          </td>
          <td class="actions-cell">
            <button
              @click="openTicketDetail(ticket)"
              class="ticket-list__view-button"
            >
              View
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="ticket-list__pagination">
      <button
        @click="prevPage"
        :disabled="ticketsStore.pagination.currentPage === 1"
        class="ticket-list__pagination-button"
      >
        Prev
      </button>
      <span>{{ ticketsStore.pagination.currentPage }} / {{ ticketsStore.pagination.lastPage }}</span>
      <button
        @click="nextPage"
        :disabled="ticketsStore.pagination.currentPage === ticketsStore.pagination.lastPage"
        class="ticket-list__pagination-button"
      >
        Next
      </button>
    </div>

    <!-- Modals -->
    <NewTicketModal
      v-if="showNewTicketModal"
      @close="showNewTicketModal = false"
      @submit="createTicket"
    />

    <TicketModal
      v-if="showTicketModal"
      :ticket="selectedTicket"
      :categories="ticketsStore.categories"
      @close="closeTicketDetail"
      @patch="handleTicketPatch"
      @save="handleTicketSave"
      @classified="handleClassified"
    />
  </div>
</template>

<script>
import { ticketsStore } from "@/store/ticket.js";
import NewTicketModal from "@/components/NewTicketModal.vue";
import TicketModal from "@/components/TicketModal.vue";

export default {
  name: "Tickets",
  components: { NewTicketModal, TicketModal },
  data() {
    return {
      ticketsStore,
      searchQuery: "",
      statusFilter: "",
      showNewTicketModal: false,
      showTicketModal: false, 
      selectedTicket: null,
    };
  },
  computed: {
    statuses() {
      return ["open", "in_progress", "closed"];
    },
  },
  watch: {
    searchQuery: "applyFilters",
    statusFilter: "applyFilters",
  },
  async created() {
    await ticketsStore.fetchTickets();
  },
  methods: {
    formatStatus(status) {
      if (status === "in_progress") return "In progress";
      return status ? status.charAt(0).toUpperCase() + status.slice(1) : "";
    },
    formatConfidence(value) {
      if (value === null || value === undefined) return "—";
      const num = parseFloat(value);
      return isNaN(num) ? "—" : num.toFixed(2);
    },
    applyFilters() {
      ticketsStore.fetchTickets(
        { search: this.searchQuery, status: this.statusFilter },
        1
      );
    },
    openNewTicketModal() {
      this.showNewTicketModal = true;
    },
    closeNewTicketModal() {
      this.showNewTicketModal = false;
    },
    async createTicket({ subject, body }) {
      await ticketsStore.addTicket({ subject, body });
      this.closeNewTicketModal();
    },
    async prevPage() {
      if (ticketsStore.pagination.currentPage > 1) {
        await ticketsStore.fetchTickets(
          { search: this.searchQuery, status: this.statusFilter },
          ticketsStore.pagination.currentPage - 1
        );
      }
    },
    async nextPage() {
      if (ticketsStore.pagination.currentPage < ticketsStore.pagination.lastPage) {
        await ticketsStore.fetchTickets(
          { search: this.searchQuery, status: this.statusFilter },
          ticketsStore.pagination.currentPage + 1
        );
      }
    },
    async handleClassified(updatedTicket) {
      if (!this.showTicketModal) return; 

      if (updatedTicket) {
        const fresh = ticketsStore.tickets.find(
          (t) => Number(t.id) === Number(updatedTicket.id)
        );
        if (fresh) {
          this.selectedTicket = { ...fresh };
          return;
        }
      }

      // fallback — re-fetch page and reselect
      await ticketsStore.fetchTickets(
        { search: this.searchQuery, status: this.statusFilter },
        ticketsStore.pagination.currentPage
      );
      if (this.selectedTicket) {
        const fresh = ticketsStore.tickets.find(
          (t) => Number(t.id) === Number(this.selectedTicket.id)
        );
        if (fresh) this.selectedTicket = { ...fresh };
      }
    },
    openTicketDetail(ticket) {
      this.selectedTicket = { ...ticket };
      this.showTicketModal = true;
    },
    closeTicketDetail() {
      this.showTicketModal = false;
      this.selectedTicket = null;
    },
    async handleTicketPatch({ id, field, value }) {
      await ticketsStore.updateTicket(id, field, value);
    },
    async handleTicketSave(updatedTicket) {
      await ticketsStore.replaceTicket(updatedTicket);

      // keep modal ticket in sync only if open
      if (this.showTicketModal) {
        const fresh = ticketsStore.tickets.find((t) => t.id === updatedTicket.id);
        if (fresh) this.selectedTicket = { ...fresh };
      }
    },
  },
};
</script>

<style src="@/assets/css/ticket.css"></style>
