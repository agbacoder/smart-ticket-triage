<template>
  <header class="header">
    <h1 class="header__title">Smart Ticket Triage</h1>
    <div class="header__actions">
      <button class="header__button" @click="toggleTheme">
         Toggle Theme
      </button>
      <button class="header__button">Profile</button>
    </div>
  </header>
</template>

<script>
export default {
  name: "Header",
  methods: {
    toggleTheme() {
      const html = document.documentElement;

      if (html.classList.contains("dark")) {
        html.classList.remove("dark");
        localStorage.setItem("theme", "light");
      } else {
        html.classList.add("dark");
        localStorage.setItem("theme", "dark");
      }
    },
  },
  mounted() {
    // Restore persisted theme on load
    const saved = localStorage.getItem("theme");
    if (saved === "dark") {
      document.documentElement.classList.add("dark");
    }
  },
};
</script>

<style src="@/assets/css/header.css"></style>
