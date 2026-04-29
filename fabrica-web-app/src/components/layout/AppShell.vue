<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue'
</script>

<template>
  <div class="app-shell">
    <Sidebar />
    <main class="app-main">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.app-shell{
  min-height: 100vh;
  background: #f5f7fb; /* fundo do print */
  display: flex;
}

.app-main{
  flex: 1;
  min-width: 0;
  padding: 18px 22px 22px;
}
</style>
