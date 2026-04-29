<template>
  <div class="mb-2 ps-3 border-start">
    <div class="d-flex justify-content-between align-items-center p-2 rounded hover-shadow" :class="bgClass">
      <div>
        <i :class="iconClass" class="me-1"></i>
        <strong>{{ item.codigo }}</strong> - {{ item.descricao }}
      </div>

      <!-- Exibe o campo Qtd apenas se for produto/componente e estiver habilitado -->
      <div v-if="mostrarQuantidade && (normalizedTipo === 'produto' || normalizedTipo === 'componente')"
        class="d-flex align-items-center gap-2">
        <label class="mb-0">Qtd:</label>
        <input type="number" min="1" class="form-control form-control-sm rounded-pill text-center"
          v-model.number="item.quantidade" style="width: 70px" />
      </div>
    </div>

    <!-- Renderiza filhos recursivamente -->
    <div v-if="item.filhos && item.filhos.length" class="mt-1">
      <ItemTree v-for="child in item.filhos" :key="child.id" :item="child" :mostrar-quantidade="mostrarQuantidade" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, computed, watchEffect } from 'vue'

/** Tipagem do item */
interface ItemTreeNode {
  id: number | string
  codigo: string
  descricao: string
  tipo?: string
  quantidade?: number
  filhos?: ItemTreeNode[]
}

const props = defineProps<{
  item: ItemTreeNode
  mostrarQuantidade?: boolean
}>()

const normalizedTipo = computed(() => {
  if (props.item.tipo === 'produto' || props.item.tipo === 'componente') return props.item.tipo
  if (props.item.filhos && props.item.filhos.length) return 'componente'
  return 'outro'
})


/** Garante que item.quantidade tenha valor padrão 1 */
watchEffect(() => {
  if ((normalizedTipo.value === 'produto' || normalizedTipo.value === 'componente') && !props.item.quantidade) {
    props.item.quantidade = 1
  }
})

/** Ícone dinâmico */
const iconClass = computed(() => {
  if (normalizedTipo.value === 'produto') return 'bi bi-box-seam text-success'
  if (props.item.filhos && props.item.filhos.length) return 'bi bi-gear text-primary'
  return 'bi bi-box text-secondary'
})

/** Cor de fundo */
const bgClass = computed(() => {
  return normalizedTipo.value === 'produto' ? 'bg-success bg-opacity-10' : 'bg-light'
})

</script>

<style scoped>
.hover-shadow:hover {
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
  transition: box-shadow 0.2s;
}
</style>
