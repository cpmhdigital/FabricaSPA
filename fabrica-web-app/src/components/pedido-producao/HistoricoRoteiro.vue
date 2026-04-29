<template>
  <div v-for="item in itens" :key="item.id" class="mb-5">
    <div class="timeline position-relative ms-4">
      <div
        v-for="(etapa, i) in item.etapas"
        :key="etapa.id"
        class="timeline-item position-relative pb-4"
      >
    
        <div
          v-if="i !== item.etapas.length - 1"
          class="position-absolute start-0 top-0 ms-1 border-start border-2 h-100"
          :class="{
            'border-success': etapa.status === 'concluida',
            'border-warning': etapa.status === 'em_andamento',
            'border-secondary': etapa.status === 'pendente',
          }"
        ></div>


        <div
          class="d-flex align-items-start position-relative"
          style="z-index: 1; cursor: pointer"
          @click="abrirDetalhes(etapa)"
        >
          <div
            class="rounded-circle me-3 flex-shrink-0"
            :class="{
              'bg-success': etapa.status === 'concluida',
              'bg-warning': etapa.status === 'em_andamento',
              'bg-secondary': etapa.status === 'pendente',
            }"
            style="width: 12px; height: 12px"
          ></div>

          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold">{{ etapa.nome }}</span>
              <span
                class="badge text-capitalize"
                :class="{
                  'bg-success': etapa.status === 'concluida',
                  'bg-warning text-dark': etapa.status === 'em_andamento',
                  'bg-secondary': etapa.status === 'pendente',
                }"
              >
                {{ etapa.status.replace('_', ' ') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalDetalhesEtapa" tabindex="-1" ref="modalEl">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow">
        <div class="modal-header bg-primary text-white py-2 rounded-top-4">
          <h6 class="modal-title">
            Detalhes da Etapa: {{ etapaSelecionada?.nome }}
          </h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body bg-light">
          <div v-if="loading" class="text-center py-3">
            <div class="spinner-border text-primary"></div>
          </div>

          <div v-else-if="historico.length" class="mb-3">
            <strong>Histórico de Produção:</strong>
            <ul class="list-group list-group-flush mt-2">
              <li
                v-for="h in historico"
                :key="h.id"
                class="list-group-item d-flex justify-content-between align-items-center small"
              >
                <div>
                  <span class="fw-semibold">{{ h.usuario.name }}</span> — {{ h.acao }}
                  <span v-if="h.observacao">({{ h.observacao }})</span>
                </div>
                <span class="text-muted">{{ new Date(h.data_hora).toLocaleString() }}</span>
              </li>
            </ul>
          </div>

          <div v-else class="text-muted text-center">Nenhum histórico encontrado.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Modal } from 'bootstrap'
import { useHistorico } from '@/composables/useHistorico'

const { historico, loading, carregarHistorico } = useHistorico()

interface Etapa {
  id: number
  nome: string
  status: 'pendente' | 'em_andamento' | 'concluida'
}
interface Item {
  id: number
  etapas: Etapa[]
}

defineProps<{ itens: Item[] }>()

const modalEl = ref<HTMLElement | null>(null)
const etapaSelecionada = ref<Etapa | null>(null)

async function abrirDetalhes(etapa: Etapa) {
  etapaSelecionada.value = etapa
  const modal = new Modal(modalEl.value!)
  modal.show()
  await carregarHistorico(etapa.id)
}
</script>

<style scoped>
.timeline {
  border-left: 2px solid #dee2e6;
  padding-left: 1rem;
}
.timeline-item:last-child {
  padding-bottom: 0;
}
</style>
