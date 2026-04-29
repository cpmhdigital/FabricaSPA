<template>
  <div class="d-flex flex-wrap gap-2 mt-auto">
    <button
      class="btn btn-success btn-sm flex-fill rounded-pill"
      @click="$emit('iniciar')"
      :disabled="props.disabled"
    >
      <i class="bi bi-play-fill me-1"></i> Iniciar
    </button>

    <button
      class="btn btn-warning btn-sm flex-fill rounded-pill"
      @click="$emit('parar')"
      :disabled="props.disabled"
    >
      <i class="bi bi-pause-fill me-1"></i> Pausar
    </button>

    <button
      class="btn btn-primary btn-sm flex-fill rounded-pill"
      @click="$emit('finalizar')"
      :disabled="props.disabled"
    >
      <i class="bi bi-check-circle me-1"></i> Finalizar
    </button>

    <button
      class="btn btn-outline-secondary btn-sm flex-fill rounded-pill"
      @click="$emit('salvar')"
      :disabled="props.disabled"
    >
      Salvar
    </button>
  </div>

  <button
    class="btn btn-outline-danger btn-sm rounded-pill mt-3 w-100"
    @click="$emit('reprovar')"
    :disabled="props.disabled"
  >
    <i class="bi bi-x-circle me-1"></i> Reprovar
  </button>
</template>

<script setup lang="ts">
defineEmits(['iniciar', 'parar', 'finalizar', 'salvar', 'reprovar'])

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  }
})
</script>
