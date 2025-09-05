<template>
  <teleport to="body">
    <div class="ticket-detail-modal" @click.self="close">
      <div class="ticket-detail-modal__content">
        <div class="ticket-detail-modal__header">
          <h2 class="ticket-detail-modal__title">View Ticket</h2>
        </div>

        <div class="ticket-detail-modal__scroll">
          <div class="ticket-detail-modal__body">
            <strong>Subject:</strong> {{ ticket?.subject }}
          </div>

          <div class="ticket-detail-modal__body">
            <strong>Description:</strong> {{ ticket?.body }}
          </div>

          <label>
            Category
            <div class="ticket-detail-modal__category">
              <input
                v-model="localTicket.category"
                type="text"
                class="ticket-detail-modal__input"
                placeholder="Type or select category"
              />
              <select
                class="ticket-detail-modal__select ticket-detail-modal__select--small"
                @change="applyCategory($event)"
              >
                <option disabled selected value="">-- Pick --</option>
                <option v-for="c in categories" :key="c" :value="c">
                  {{ c }}
                </option>
              </select>
              <button class="small" @click="saveCategory" title="Save category">Save</button>
            </div>
          </label>

          <label>
            Status
            <select
              v-model="localTicket.status"
              class="ticket-detail-modal__select"
              @change="onStatusChange"
            >
              <option v-for="s in statuses" :key="s" :value="s">
                {{ formatStatus(s) }}
              </option>
            </select>
          </label>

          <label>
            Note
            <textarea
              v-model="localTicket.note"
              class="ticket-detail-modal__textarea"
              @blur="onNoteBlur"
            ></textarea>
          </label>

          <div class="ticket-detail-modal__confidence">
            <p>Confidence: {{ confidenceValue }}</p>
          </div>
          <div class="ticket-detail-modal__explanation">
            {{ localTicket.explanation }}
          </div>
        </div>

        <div class="ticket-detail-modal__actions">
          <button @click="runClassification" :disabled="loading">
            <span v-if="loading">Running...</span>
            <span v-else>Run Classification</span>
          </button>
          <button @click="onSave" :disabled="!isValid">Save</button>
          <button @click="close">Close</button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import { ticketsStore } from "@/store/ticket.js";

export default {
  name: "TicketModal",
  props: {
    ticket: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
  },
  emits: ["close", "patch", "save", "classified"],
  data() {
    return {
      localTicket: {},
      loading: false,
      errors: {},
      ticketsStore,
    };
  },
  watch: {
    ticket: {
      immediate: true,
      handler(newTicket) {
        // Defensive: create a copy so local edits don't mutate parent object directly
        this.localTicket = newTicket ? { ...newTicket } : {};
      },
    },
  },
  computed: {
    statuses() {
      return ["open", "in_progress", "closed"];
    },
    confidenceValue() {
      const v = Number(this.localTicket.confidence);
      return isNaN(v) ? "—" : v.toFixed(2);
    },
    isValid() {
      return true; 
    },
  },
  methods: {
    close() {
      this.$emit("close");
    },
    // Auto save data for category inputs
    saveCategory() {
      if (!this.ticket?.id) return;
      this.$emit("patch", { id: this.ticket.id, field: "category", value: this.localTicket.category });
    },
    applyCategory(ev) {
      const chosen = ev.target.value;
      if (!this.ticket?.id) return;
      this.localTicket.category = chosen;
      this.$emit("patch", { id: this.ticket.id, field: "category", value: chosen });
    },
    onStatusChange() {
      if (!this.ticket?.id) return;
      this.$emit("patch", { id: this.ticket.id, field: "status", value: this.localTicket.status });
    },
    onNoteBlur() {
      if (!this.ticket?.id) return;
      this.$emit("patch", { id: this.ticket.id, field: "note", value: this.localTicket.note });
    },
    onSave() {
      this.$emit("save", { ...this.localTicket, id: this.ticket.id });
    },
    async runClassification() {
      if (!this.ticket?.id) return;
      this.loading = true;
      try {
        const updated = await ticketsStore.classifyTicket(this.ticket.id);
        if (updated) {
          this.localTicket = { ...updated };
          this.$emit("classified", updated);
        }
      } catch (e) {
        console.error("Classification error:", e);
      } finally {
        this.loading = false;
      }
    },
    formatStatus(s) {
      if (s === "in_progress") return "In progress";
      return s ? s.charAt(0).toUpperCase() + s.slice(1) : "";
    },
  },
};
</script>

<style src="@/assets/css/ticket-detail-modal.css"></style>
