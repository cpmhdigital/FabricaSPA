<script setup lang="ts">
import { defineProps } from 'vue'

const props = defineProps<{
  params: {
    data: unknown
    onEditar?: (row: unknown) => void
    onExcluir?: (row: unknown) => void
  }
}>()

const editar = () => props.params.onEditar?.(props.params.data)
const excluir = () => props.params.onExcluir?.(props.params.data)
</script>

<template>
  <div class="d-flex gap-2 justify-content-center">
    <button v-if="props.params.onEditar" class="btn btn-sm btn-outline-primary m-1" @click="editar">
      <i class="bi bi-eye me-1"></i>
    </button>

    <button
      v-if="props.params.onExcluir"
      class="btn btn-sm btn-light m-1 text-danger"
      @click="excluir"
    >
      <i class="bi bi-trash"></i>
    </button>
  </div>
</template>
