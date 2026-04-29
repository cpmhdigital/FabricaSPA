<template>
  <div class="mb-3">
    <strong>Roteiro de Produção:</strong>

    <ul class="list-group mt-2 small">
      <li
        v-for="(etapa, index) in roteiro"
        :key="etapa.id"
        class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom"
        :class="{
          'bg-light': etapa.status === 'pendente',
          'list-group-item-success': etapa.status === 'concluida',
          'list-group-item-warning': etapa.status === 'em_andamento',
        }"
      >
        <div>
          <span class="fw-semibold">{{ index + 1 }}.</span>
          {{ etapa.nome }}
        </div>

        <div>
          <span v-if="etapa.status === 'pendente'" class="badge bg-secondary">Pendente</span>
          <span v-if="etapa.status === 'em_andamento'" class="badge bg-warning text-dark">Em andamento</span>
          <span v-if="etapa.status === 'concluida'" class="badge bg-success">Concluída</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
interface EtapaRoteiro {
  id: number
  nome: string
  status: 'pendente' | 'em_andamento' | 'concluida'
}

defineProps<{
  roteiro: EtapaRoteiro[]
}>()
</script>
