<template>
  <!-- Barra de Pesquisa -->
  <div class="row mb-4">
    <div class="col-12 col-md-6 mx-auto">
      <input
        v-model="busca"
        type="text"
        class="form-control form-control-lg shadow-sm rounded-pill ps-4"
        placeholder="Buscar pedido, item ou unidade..."
      >
    </div>
  </div>

  <!-- Kanban Columns -->
  <div class="kanban-container row g-4">
    <div class="col-12 col-md-6 col-lg-4" v-for="col in colunas" :key="col.status">
      <div class="kanban-column card border-0 shadow-sm">

        <!-- Header -->
        <div class="kanban-header fw-bold text-white">
          <span>{{ col.titulo }}</span>
          <span class="badge bg-light text-dark ms-2">{{ col.items.length }}</span>
        </div>

        <!-- Body -->
        <div class="kanban-body">
          <transition-group name="fade" tag="div">
            <KanbanCard
              v-for="item in col.items"
              :key="item.pedido_id"
              :pedido="item"
              @abrir="abrirDetalhes"
            />
          </transition-group>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <PedidoDetalhes
    :pedidoId="pedidoSelecionado"
    @fechar="pedidoSelecionado = null"
  />
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useKanbanStore } from "@/stores/kanbanStore";

import KanbanCard from "@/components/kanban/KanbanCard.vue";
import PedidoDetalhes from "@/components/kanban/PedidoDetalhes.vue";

const kanban = useKanbanStore();

const colunas = ref<any[]>([]);
const pedidoSelecionado = ref<number | null>(null);
const busca = ref("");

onMounted(async () => {
  await kanban.carregarKanban();
  montarColunas();
  kanban.ouvirAtualizacoesKanban();
});

watch([() => kanban.pedidos, busca], montarColunas, { deep: true });

function montarColunas() {
  const termo = busca.value.toLowerCase();

  colunas.value = [
    { status: "aguardando", titulo: "Aguardando", items: [] },
    { status: "em_producao", titulo: "Em Produção", items: [] },
    { status: "atrasado", titulo: "Atrasados", items: [] },
  ];

  kanban.pedidos
    .filter((p) => {
      if (!termo) return true;

      return (
        p.numero_pedido?.toString().includes(termo) ||
        p.itens?.some((i) =>
          i.codigo?.toLowerCase().includes(termo) ||
          i.unidades.some((u) =>
            u.codigo?.toLowerCase().includes(termo)
          )
        )
      );
    })
    .forEach((p) => {
      const statusPedido = obterStatusPedido(p);
      const col = colunas.value.find((c) => c.status === statusPedido);
      if (col) col.items.push(p);
    });
}

function obterStatusPedido(pedido: any): string {
  const statusUnidades: string[] = [];

  pedido.itens.forEach((item: any) => {
    item.unidades.forEach((unidade: any) => {
      statusUnidades.push(unidade.status);
    });
  });

  if (statusUnidades.every(s => s === "aguardando")) {
    return "aguardando";
  }

  const emProducaoStatus = ["em_andamento", "reprovado", "pausado"];
  if (statusUnidades.some(s => emProducaoStatus.includes(s))) {
    return "em_producao";
  }

  if (pedido.atrasado === true) {
    return "atrasado";
  }

  return "aguardando";
}

function abrirDetalhes(id: number) {
  pedidoSelecionado.value = id;
}
</script>


<style scoped>
/* Layout geral */
.kanban-container {
  padding-bottom: 20px;
}

/* Coluna */
.kanban-column {
  border-radius: 14px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 82vh;
  max-height: 82vh;
  background: #ffffff;
  transition: transform 0.15s ease-in-out;
}

.kanban-column:hover {
  transform: translateY(-3px);
}

/* Cabeçalho com gradiente */
.kanban-header {
  background: linear-gradient(135deg, #a1a4a8, #d1d5db);
  padding: 14px 18px;
  font-size: 1.1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Corpo */
.kanban-body {
  padding: 14px;
  background: #f3f6fb;
  overflow-y: auto;
  flex-grow: 1;
  border-top: 1px solid #e5eaf3;
}

/* Scrollbar custom */
.kanban-body::-webkit-scrollbar {
  width: 6px;
}
.kanban-body::-webkit-scrollbar-thumb {
  background: #bfc7d1;
  border-radius: 4px;
}

/* Fade suave dos cards */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

</style>
