<script setup lang="ts">
import SidebarMenu from './Sidebar.vue'
</script>

<template>
  <div class="app-container">
    <SidebarMenu />
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>

.app-container {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background-color: transparent;
}

.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 0;
  box-sizing: border-box;
  min-width: 0;
  background-color: #f5f7fa;
}

.main-content::-webkit-scrollbar {
  width: 6px;
}
.main-content::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.2);
  border-radius: 3px;
}
</style>
