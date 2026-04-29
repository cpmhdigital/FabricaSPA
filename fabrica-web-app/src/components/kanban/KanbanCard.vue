<template>
  <div class="kanban-card card border-0 shadow-sm p-3 mb-3 position-relative">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="mb-0 fw-bold text-dark">
        Pedido #{{ pedido.numero_pedido }}
      </h6>

      <span class="badge bg-secondary">
        {{ pedido.itens.length }} item(s)
      </span>
    </div>

    <hr class="my-2">

    <div class="d-flex flex-column">

      <div v-for="item in pedido.itens" :key="item.item_id" class="pb-3 mb-2 border-bottom">

        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="fw-semibold text-primary">
            {{ item.codigo }}
          </span>

          <span class="badge bg-secondary-subtle text-dark border">
            {{ item.unidades.length }} uni
          </span>
        </div>

        <div class="mt-2 ms-1">

          <div v-for="(u, index) in item.unidades" :key="u.id" class="d-flex align-items-center small text-muted mb-1">

            <span class="fw-semibold text-dark me-2">
              {{ index + 1 }}/{{ item.unidades.length }} -
            </span>

            <span class="me-2">
              {{ u.etapa_atual_nome }} -
            </span>

            <span class="status-mini-badge" :class="u.status">
              {{ formatStatus(u.status) }}
            </span>
          </div>

        </div>

      </div>

    </div>

    <button class="btn btn-primary btn-sm w-100 mt-3 fw-semibold" @click="$emit('abrir', pedido.pedido_id)">
      Ver detalhes
    </button>

  </div>
</template>


<script setup lang="ts">
interface Unidade {
  id: number;
  codigo: string;
  etapa_atual_id: number;
  etapa_atual_nome: string;
  status: string;
}

interface Item {
  item_id: number;
  codigo: string;
  nome: string;
  unidades: Unidade[];
}

interface Pedido {
  pedido_id: number;
  numero_pedido: string;
  status: string;
  progresso: number;
  itens: Item[];
}

const props = defineProps<{
  pedido: Pedido
}>();

function formatStatus(s: string) {
  return {
    aguardando: "Aguardando",
    em_andamento: "Em Produção",
    reprovado: "Reprovado",
    pausado: "Pausado"
  }[s] || s;
}
</script>


<style scoped>
.kanban-card {
  border-radius: 12px;
  transition: transform .15s ease, box-shadow .15s ease;
}

.kanban-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
}

.unit-box {
  background: #f8f9fa;
  padding: 8px 10px;
  border-radius: 8px;
  border: 1px solid #eee;
}

/* Badges compactas */
.status-mini-badge {
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.65rem;
  font-weight: 600;
  color: white;
  white-space: nowrap;
}

.status-mini-badge.aguardando {
  background-color: #6C757D;
}

.status-mini-badge.em_andamento {
  background-color: #198754;
}

.status-mini-badge.reprovado {
  background-color: #e74c3c;
}

.status-mini-badge.pausado {
  background-color: #ffc107;
  color: #663c00;
}
</style>
