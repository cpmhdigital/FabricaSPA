<script setup lang="ts">
const props = defineProps<{
  titulo: string
  descricao?: string
  textoBotao?: string
  iconeBotao?: string
  espacamento?: 'nenhum' | 'pequeno' | 'medio' | 'grande'
  semCard?: boolean
}>()

const emit = defineEmits<{
  (e: 'novo'): void
}>()

const espacamentoClasse = {
  nenhum: '',
  pequeno: 'mt-3',
  medio: 'mt-5',
  grande: 'mt-7',
}
</script>

<template>
  <div class="container-fluid min-vh-100 py-5">
    <div class="mb-5">
      <h4 class="lead fs-4">{{ titulo }}</h4>

      <div class="d-flex align-items-center gap-2 mt-2">
        <hr class="m-0" style="width: 100px; border-top: 2px solid #000" />
        <small v-if="descricao" class="text-muted">{{ descricao }}</small>
      </div>
    </div>

    <div class="d-flex justify-content-end mb-5" v-if="textoBotao">
      <button class="btn btn-success gap-2 shadow-sm" @click="emit('novo')">
        <i v-if="iconeBotao" :class="iconeBotao"></i>
        {{ textoBotao }}
      </button>
    </div>

    <div v-if="props.semCard">
      <slot />
    </div>

    <div
      v-else
      class="card shadow-sm border-0 rounded-3"
      :class="espacamentoClasse[props.espacamento || 'medio']"
    >
      <div class="card-body">
        <slot />
      </div>
    </div>
  </div>
</template>
