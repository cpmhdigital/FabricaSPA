<template>
  <div class="card mb-4 shadow-sm rounded-4 border-0">
    <div class="card-header bg-success text-white fs-5 rounded-top-4">
      <i class="bi bi-box-seam me-2"></i> Dados do Pedido
    </div>
    <div class="card-body small text-muted" v-if="pedido">
      <div class="row mb-2">
        <div class="col-md-4">
          <strong>Nº Pedido:</strong> {{ pedido.numero_pedido }}
        </div>
        <div class="col-md-4"><strong>Doutor(a):</strong> {{ pedido.doutor }}</div>
        <div class="col-md-4"><strong>Paciente:</strong> {{ pedido.paciente }}</div>
      </div>
      <div class="row">
        <div class="col-md-4"><strong>Lote:</strong> {{ pedido.lote }}</div>
        <!-- <div class="col-md-4"><strong>Produto:</strong> {{ pedido.it }}</div> -->
        <div class="col-md-4"><strong>Data Aceite:</strong> {{ pedido.data_pedido }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Pedido {
  numero_pedido: string | number
  origem: string
  doutor: string
  paciente: string
  lote: string
  item: string
  data_pedido: string | Date
}

defineProps<{ pedido: Pedido | null }>()
</script>
