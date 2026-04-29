<template>
  <div class="mt-4 p-4 bg-white rounded shadow-sm">

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h5 class="mb-1 text-primary fw-bold">Progresso da Produção</h5>
        <small class="text-muted">Visão geral do pedido</small>
      </div>

      <div class="text-end">
        <div class="small text-muted">Início</div>
        <div class="fw-bold">{{ displayDataInicio }}</div>
      </div>
    </div>

    <!-- Info gerais -->
    <div class="row text-center mb-4 gx-3">
      <div class="col">
        <small class="text-muted">Data de Entrega</small>
        <div class="fw-bold">{{ displayDataEntrega }}</div>
      </div>
      <div class="col">
        <small class="text-muted">Data Prevista</small>
        <div class="fw-bold">{{ displayDataPrevista }}</div>
      </div>
      <div class="col">
        <small class="text-muted">Progresso Total</small>
        <div class="fw-bold text-success">{{ progressoTotal }}%</div>
      </div>
    </div>

    <!-- Barra geral -->
    <div class="progress mb-4" style="height: 24px">
      <div class="progress-bar" :class="barColor" :style="{ width: progressoTotal + '%' }">
        {{ progressoTotal }}%
      </div>
    </div>

   <!--
    <h6 class="fw-semibold mb-2 text-secondary">Itens</h6>

    <template v-if="pedido?.itens?.length">
      <div
        v-for="item in pedido.itens"
        :key="item.item_id"
        class="mb-4 p-3 border rounded bg-light"
      >

        <div class="d-flex justify-content-between mb-1">
          <div class="fw-bold">{{ item.nome }}</div>
          <div class="fw-bold">{{ item.progresso }}%</div>
        </div>

        <div class="progress mb-2" style="height: 12px">
          <div class="progress-bar bg-info" :style="{ width: item.progresso + '%' }"></div>
        </div>

        <div
          v-for="u in item.unidades"
          :key="u.id"
          class="p-2 mt-2 border rounded bg-white"
        >
          <div class="d-flex justify-content-between">
            <div>
              <div class="fw-semibold">
                Unidade {{ u.codigo }} — {{ actionToStatus(u.status, u.decisao) }}
              </div>

              <div class="small text-muted">
                Etapa Atual: <b>{{ u.etapa_atual ?? '—' }}</b> |
                Próxima: <b>{{ u.proxima_etapa ?? '—' }}</b>
              </div>
            </div>

            <button class="btn btn-sm btn-outline-primary" @click="mostrarHistorico(u)">
              Histórico
            </button>
          </div>

          <div class="progress mt-2" style="height: 8px">
            <div class="progress-bar" :style="{ width: u.progresso + '%' }"></div>
          </div>

          <div class="small text-muted mt-1">
            {{ u.etapas_concluidas }} / {{ u.total_etapas }} etapas concluídas
          </div>
        </div>
      </div>
    </template> -->

   <!--  <div v-else class="text-center py-3 text-muted">
      Nenhum item encontrado neste pedido.
    </div> -->
  </div>
</template>
<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useProgressoProducao } from '@/composables/useProgressoProducao'
import Swal from "sweetalert2"

const props = defineProps({
  pedidoId: { type: Number, required: true },
})

const { pedido, carregarPedido, formatDisplay, actionToStatus, calcularProgressoTotal } =
  useProgressoProducao()

onMounted(() => {
  carregarPedido(props.pedidoId)
})

// Datas
const displayDataInicio   = computed(() => formatDisplay(pedido.value?.data_inicio))
const displayDataEntrega  = computed(() => formatDisplay(pedido.value?.data_entrega))
const displayDataPrevista = computed(() => formatDisplay(pedido.value?.data_prevista))

// Progresso total
const progressoTotal = computed(() =>
  calcularProgressoTotal(pedido.value?.itens ?? [])
)

// Cores da barra geral
const barColor = computed(() => {
  const v = progressoTotal.value
  if (v < 40) return 'bg-danger'
  if (v < 80) return 'bg-warning'
  return 'bg-success'
})

// SweetAlert — Histórico de unidade
function mostrarHistorico(unidade: any) {
  const h = unidade?.historicos ?? []

  if (!h.length) {
    Swal.fire("Histórico vazio", "Nenhuma ação registrada.", "info")
    return
  }

  let html = `<div class='text-start'>`

  h.forEach(log => {
    html += `
      <div class="mb-2">
        <b>${log.acao}</b> – ${new Date(log.data_hora).toLocaleString("pt-BR")}<br>
        <small>Etapa: ${log.etapa_id}</small><br>
        ${log.tipo_decisao ? `<small>Decisão: <b>${log.tipo_decisao}</b></small>` : ""}
      </div>
      <hr>
    `
  })

  html += `</div>`

  Swal.fire({
    title: `Histórico da unidade ${unidade.codigo}`,
    html,
    width: 600,
    confirmButtonText: "Fechar",
  })
}
</script>
