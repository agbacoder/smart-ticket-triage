<template>
  <aside :class="['sidebar', { 'sidebar--collapsed': isCollapsed }]">
    <div class="sidebar__logo">
      <span v-if="!isCollapsed">HelpDesk</span>
      <button class="sidebar__toggle" @click="toggleSidebar" aria-label="Toggle sidebar">
        <!-- Hamburger SVG -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round" class="sidebar__toggle-icon">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>
    </div>
    <nav class="sidebar__nav">
      <router-link
        to="/tickets"
        class="sidebar__nav-item"
        active-class="sidebar__nav-item--active"
      >
        <span class="sidebar__nav-icon">🎫</span>
        <span class="sidebar__nav-label" v-if="!isCollapsed">Tickets</span>
      </router-link>
      <router-link
        to="/dashboard"
        class="sidebar__nav-item"
        active-class="sidebar__nav-item--active"
      >
        <span class="sidebar__nav-icon">📊</span>
        <span class="sidebar__nav-label" v-if="!isCollapsed">Dashboard</span>
      </router-link>
    </nav>
  </aside>
</template>

<script>
export default {
  name: "Sidebar",
  data() {
    return {
      isCollapsed: false,
    };
  },
  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed;
    },
  },
};
</script>

<style src="@/assets/css/sidebar.css"></style>
