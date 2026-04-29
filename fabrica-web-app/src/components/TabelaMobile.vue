<template>
  <div class="d-block d-md-none">
    <!-- Paginação -->
    <nav v-if="totalPaginas > 1" class="d-flex justify-content-start m-4">
      <ul class="pagination mb-0">
        <li class="page-item" :class="{ disabled: paginaAtual === 1 }">
          <button class="page-link" @click="changePage(paginaAtual - 1)">Anterior</button>
        </li>
        <li
          class="page-item"
          v-for="p in totalPaginas"
          :key="p"
          :class="{ active: paginaAtual === p }"
        >
          <button class="page-link" @click="changePage(Number(p))">{{ p }}</button>
        </li>
        <li class="page-item" :class="{ disabled: paginaAtual === totalPaginas }">
          <button class="page-link" @click="changePage(paginaAtual + 1)">Próximo</button>
        </li>
      </ul>
    </nav>

    <!-- Lista de itens -->
    <div v-for="item in itensPaginados" :key="item.id">
      <slot :item="item" />
    </div>
  </div>
</template>

<script setup lang="ts" generic="T extends { id: number | string }">
import { ref, computed, defineProps, watch } from 'vue'

const props = defineProps<{
  itens: T[]
  itensPorPagina?: number
}>()

const paginaAtual = ref(1)
const itensPorPagina = props.itensPorPagina || 5

const totalPaginas = computed(() => Math.ceil(props.itens.length / itensPorPagina))

const itensPaginados = computed(() => {
  const start = (paginaAtual.value - 1) * itensPorPagina
  return props.itens.slice(start, start + itensPorPagina)
})

function changePage(p: number) {
  if (p < 1 || p > totalPaginas.value) return
  paginaAtual.value = p
}

watch(
  () => props.itens,
  () => {
    paginaAtual.value = 1
  }
)
</script>
