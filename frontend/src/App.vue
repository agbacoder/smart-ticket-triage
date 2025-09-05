<template>
  <div class="layout">
    <!-- Sidebar (persistent) -->
    <Sidebar />

    <!-- Main section -->
    <div class="layout__main">
      <Header />

      <!-- Routed views -->
      <main class="layout__content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import Header from "@/components/Header.vue";

export default {
  name: "App",
  components: { Sidebar, Header },
};
</script>

<style src="@/assets/css/layout.css"></style>
