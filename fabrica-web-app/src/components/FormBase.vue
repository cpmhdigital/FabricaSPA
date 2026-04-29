<script setup lang="ts">
const props = defineProps<{
  titulo: string
  subtitulo?: string
  codigo?: string
}>()
</script>

<template>
  <header class="text-center my-5">
    <h3 class="fw-normal text-secondary">
      Procedimento do Sistema de Gestão da Qualidade
    </h3>
    <h4 class="fw-bold text-uppercase text-success">
      {{ titulo }}
    </h4>
    <small v-if="codigo" class="text-uppercase text-muted">{{ codigo }}</small>
  </header>

  <section class="container bg-white shadow rounded p-4 my-5">
    <hr class="border-top border-2 border-light" />
    <slot />
  </section>
</template>
