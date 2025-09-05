<template>
  <teleport to="body">
    <div class="new-ticket-modal" @click.self="close">
      <div class="new-ticket-modal__content">
        <h2 class="new-ticket-modal__title">Create New Ticket</h2>

        <!-- Subject -->
        <input
          type="text"
          v-model="localSubject"
          @blur="touched.subject = true"
          placeholder="Subject"
          class="new-ticket-modal__input"
          :class="{ 'is-invalid': subjectError }"
        />
        <span v-if="subjectError" class="error-text">Subject is required</span>

        <!-- Body -->
        <textarea
          v-model="localBody"
          @blur="touched.body = true"
          placeholder="Description"
          class="new-ticket-modal__textarea"
          :class="{ 'is-invalid': bodyError }"
        ></textarea>
        <span v-if="bodyError" class="error-text">Description is required</span>

        <!-- Actions -->
        <div class="new-ticket-modal__actions">
          <button
            @click="submit"
            class="new-ticket-modal__submit"
            :disabled="!isFormValid || loading"
          >
            <span v-if="loading">Submitting...</span>
            <span v-else>Submit</span>
          </button>
          <button @click="close" class="new-ticket-modal__cancel">Cancel</button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
export default {
  name: "NewTicketModal",
  emits: ["close", "submit"], 
  props: {
    subject: { type: String, default: "" },
    body: { type: String, default: "" },
  },
  data() {
    return {
      localSubject: this.subject,
      localBody: this.body,
      touched: { subject: false, body: false },
      loading: false,
    };
  },
  computed: {
    subjectError() {
      return this.touched.subject && this.localSubject.trim().length === 0;
    },
    bodyError() {
      return this.touched.body && this.localBody.trim().length === 0;
    },
    isFormValid() {
      return (
        this.localSubject.trim().length > 0 &&
        this.localBody.trim().length > 0
      );
    },
  },
  methods: {
    close() {
      this.$emit("close");
    },
    async submit() {
      this.touched.subject = true;
      this.touched.body = true;
      if (!this.isFormValid) return;

      this.loading = true;
      try {
        await this.$emit("submit", {
          subject: this.localSubject.trim(),
          body: this.localBody.trim(),
        });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style src="@/assets/css/ticket-modal.css"></style>
