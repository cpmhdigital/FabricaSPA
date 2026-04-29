<template>
  <div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content modal-clear">
        <!-- HEADER -->
        <div class="modal-header header-clear">
          <div class="w-100">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h5 class="modal-title mb-0 fw-semibold text-dark">
                    Pedido <span class="text-muted">#{{ detalhes?.numero_pedido || '—' }}</span>
                  </h5>

                  <span class="badge rounded-pill text-bg-light border">
                    {{ detalhes?.status || '—' }}
                  </span>

                  <span
                    class="badge rounded-pill"
                    :class="detalhes?.data_aprovacao_pcp ? 'text-bg-success' : 'text-bg-warning'"
                  >
                    PCP: {{ detalhes?.data_aprovacao_pcp ? 'Aprovado' : 'Pendente' }}
                  </span>
                </div>

                <div class="text-muted small mt-2">
                  <span class="me-3">
                    Doutor: <span class="text-dark fw-semibold">{{ detalhes?.doutor || '—' }}</span>
                  </span>
                  <span>
                    Paciente: <span class="text-dark fw-semibold">{{ detalhes?.paciente || '—' }}</span>
                  </span>
                </div>
              </div>

              <div class="text-end d-none d-md-block">
                <div class="text-muted small">Data do pedido</div>
                <div class="fw-semibold text-dark">{{ formatarData(detalhes?.data_pedido) }}</div>

                <div class="text-muted small mt-2">Aprovação PCP</div>
                <div class="fw-semibold">
                  <span v-if="detalhes?.data_aprovacao_pcp" class="text-success">
                    {{ formatarData(detalhes?.data_aprovacao_pcp) }}
                  </span>
                  <span v-else class="text-warning">Pendente</span>
                </div>
              </div>
            </div>

            <!-- versão mobile dos metadados -->
            <div class="d-block d-md-none mt-3">
              <div class="meta-grid">
                <div class="meta-item">
                  <div class="meta-label">Data do pedido</div>
                  <div class="meta-value">{{ formatarData(detalhes?.data_pedido) }}</div>
                </div>
                <div class="meta-item">
                  <div class="meta-label">Aprovação PCP</div>
                  <div class="meta-value">
                    <span v-if="detalhes?.data_aprovacao_pcp" class="text-success">
                      {{ formatarData(detalhes?.data_aprovacao_pcp) }}
                    </span>
                    <span v-else class="text-warning">Pendente</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="handleFechar"
          ></button>
        </div>

        <!-- BODY -->
        <div class="modal-body">
          <!-- Loading -->
          <div v-if="carregando" class="card card-clear mb-3">
            <div class="card-body d-flex align-items-center gap-2">
              <span class="spinner-border spinner-border-sm"></span>
              <span class="text-muted">Carregando detalhes...</span>
            </div>
          </div>

          <!-- Empty -->
          <div v-else-if="!itensDetalhados.length" class="alert alert-warning text-center">
            Nenhum item encontrado para este pedido.
          </div>

          <!-- Conteúdo -->
          <div v-else class="d-grid gap-3">
            <div v-for="item in itensDetalhados" :key="item.id" class="card card-clear">
              <!-- Item header -->
              <div class="card-body pb-2">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                  <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <div class="fw-semibold text-dark">
                        {{ item.codigo }} — {{ item.descricao }}
                      </div>
                      <span class="badge rounded-pill text-bg-light border">
                        Fluxo: {{ item.fluxo }}
                      </span>
                    </div>
                    <div class="text-muted small mt-1">
                      Quantidade: <span class="text-dark fw-semibold">{{ item.quantidade }}</span>
                      <span class="mx-2">•</span>
                      Unidades: <span class="text-dark fw-semibold">{{ item.unidades.length }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Unidades (accordion) -->
              <div class="accordion accordion-flush" :id="`acc-${item.id}`">
                <div
                  v-for="(uni, i) in item.unidades"
                  :key="uni.id"
                  class="accordion-item"
                >
                  <h2 class="accordion-header" :id="`h-${item.id}-${uni.id}`">
                    <button
                      class="accordion-button collapsed accordion-btn-clear"
                      type="button"
                      data-bs-toggle="collapse"
                      :data-bs-target="`#c-${item.id}-${uni.id}`"
                      aria-expanded="false"
                      :aria-controls="`c-${item.id}-${uni.id}`"
                    >
                      <div class="d-flex align-items-center justify-content-between w-100 pe-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                          <span class="fw-semibold text-dark">
                            Unidade {{ i + 1 }}/{{ item.unidades.length }}
                          </span>
                          <span class="badge rounded-pill text-bg-light border">
                            Código: {{ uni.codigo }}
                          </span>
                        </div>

                        <span class="text-muted small d-none d-sm-inline">
                          Ver etapas
                        </span>
                      </div>
                    </button>
                  </h2>

                  <div
                    :id="`c-${item.id}-${uni.id}`"
                    class="accordion-collapse collapse"
                    :aria-labelledby="`h-${item.id}-${uni.id}`"
                    :data-bs-parent="`#acc-${item.id}`"
                  >
                    <div class="accordion-body pt-2">
                      <!-- timeline -->
                      <div class="timeline-wrap">
                        <div class="timeline-horizontal">
                          <div v-for="etapa in uni.etapas" :key="etapa.id" class="th-item">
                            <div class="th-icon" :class="etapa.status"></div>

                            <div class="th-label fw-semibold">
                              {{ etapa.nome }}
                            </div>

                            <div
                              class="th-status small"
                              :class="{
                                'text-success': etapa.status === 'concluida',
                                'text-primary': etapa.status === 'atual',
                                'text-warning': etapa.status === 'pausado',
                                'text-secondary': etapa.status === 'futura',
                                'text-danger': etapa.status === 'reprovado'
                              }"
                            >
                              {{ etapa.label }}
                            </div>

                            <div v-if="etapa.usuario" class="small text-muted">
                              por: <strong class="text-dark">{{ etapa.usuario }}</strong>
                            </div>

                            <div v-if="etapa.data" class="small text-secondary">
                              {{ etapa.data }}
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- /timeline -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- /accordion -->
            </div>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer footer-clear">
          <button class="btn btn-light" data-bs-dismiss="modal" type="button" @click="handleFechar">
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Modal } from 'bootstrap'
import api from '@/services/axios'

const props = defineProps<{ pedidoId: number | null }>()
const emit = defineEmits<{ (e: 'fechar'): void }>()

const detalhes = ref<any>(null)
const itensDetalhados = ref<any[]>([])
const carregando = ref(false)
let modal: any = null

function formatarData(dataStr: string | null | undefined): string {
  if (!dataStr) return '—'
  const d = new Date(dataStr)
  return isNaN(d.getTime())
    ? String(dataStr)
    : d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR')
}

function resetar() {
  detalhes.value = null
  itensDetalhados.value = []
  carregando.value = false
}

function handleFechar() {
  resetar()
  emit('fechar')
}

async function carregarDetalhes(id: number) {
  carregando.value = true
  try {
    const { data } = await api.get(`/api/pedidos/light/${id}`)
    detalhes.value = data

    itensDetalhados.value = (data.itens || []).map((item: any) => {
      const produto = item.produto || {}
      const fluxo = produto.fluxo || {}
      const fluxoEtapas = fluxo.etapas || []

      const unidades = (item.unidades || []).map((uni: any) => {
        const hist = uni.ultimo_historico
        const etapaAtualID = hist?.etapa_id ?? null

        const indexAtual = etapaAtualID
          ? fluxoEtapas.findIndex((e: any) => e.id === etapaAtualID)
          : -1

        const etapas = fluxoEtapas.map((etapa: any, idx: number) => {
          let status = 'futura'
          let label = 'Aguardando'
          let dataLabel = null
          let usuario = null

          if (idx < indexAtual) {
            status = 'concluida'
            label = 'Concluída'
          }

          if (idx === indexAtual) {
            const acao = (hist?.acao ?? '').toUpperCase()

            if (acao.includes('INICIO')) {
              status = 'atual'
              label = 'Em andamento'
            } else if (acao.includes('PAUSA')) {
              status = 'pausado'
              label = 'Pausado'
            } else if (acao.includes('REPROV')) {
              status = 'reprovado'
              label = 'Reprovado'
            } else {
              status = 'concluida'
              label = 'Concluída'
            }

            dataLabel = formatarData(hist?.data_hora)
            usuario = hist?.usuario?.name ?? null
          }

          return {
            id: etapa.id,
            nome: etapa.nome_etapa,
            status,
            label,
            data: dataLabel,
            usuario,
          }
        })

        return {
          id: uni.id,
          codigo: uni.unidade_codigo || '—',
          etapas,
        }
      })

      return {
        id: item.id,
        codigo: produto.codigo || '—',
        descricao: produto.descricao || '—',
        fluxo: fluxo.nome_fluxo || '—',
        quantidade: item.quantidade || 0,
        unidades,
      }
    })
  } catch (error) {
    console.error('Erro ao carregar detalhes do pedido:', error)
  } finally {
    carregando.value = false
  }
}

watch(
  () => props.pedidoId,
  async (val) => {
    if (!val) return

    await carregarDetalhes(val)

    if (!modal) {
      modal = new Modal(document.getElementById('modalDetalhes')!)
    }

    modal.show()
  },
)
</script>

<style scoped>
/* =========================
   Clear UI base
========================= */
.modal-clear {
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.card-clear {
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 14px;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
  background: #fff;
}

.header-clear {
  background: #fff;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.footer-clear {
  background: #fff;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.meta-item {
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 12px;
  padding: 10px 12px;
  background: #fff;
}
.meta-label {
  font-size: 12px;
  color: #6c757d;
}
.meta-value {
  font-weight: 600;
  color: #212529;
}

.accordion-btn-clear {
  background: #fff;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  padding: 12px 16px;
}
.accordion-button:not(.collapsed) {
  color: inherit;
  background: #f8fafc;
  box-shadow: none;
}

/* =========================
   Timeline (mantém sua base)
========================= */
.timeline-wrap {
  overflow-x: auto;
  padding-bottom: 10px;
}

.timeline-horizontal {
  display: flex;
  align-items: flex-start;
  gap: 40px;
  overflow-x: auto;
  padding-bottom: 10px;
  scrollbar-width: thin;
}

.th-item {
  text-align: center;
  min-width: 160px;
  position: relative;
}

.th-icon {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  margin: 0 auto 8px auto;
  border: 3px solid #adb5bd;
  background: #fff;
}

.th-item::after {
  content: '';
  position: absolute;
  top: 11px;
  left: calc(50% + 18px);
  width: 60px;
  height: 3px;
  background-color: #dee2e6;
}

.th-item:last-child::after {
  display: none;
}

.th-icon.concluida {
  background: #28a745;
  border-color: #28a745;
}

.th-icon.atual {
  background: #0d6efd;
  border-color: #0d6efd;
}

.th-icon.pausado {
  background: #ffc107;
  border-color: #ffc107;
}

.th-icon.futura {
  background: #6c757d;
  border-color: #6c757d;
}

.th-icon.reprovado {
  background: #dc3545;
  border-color: #dc3545;
}

@media (max-width: 768px) {
  .timeline-horizontal {
    display: block;
    padding-left: 20px;
  }

  .th-item {
    text-align: left;
    min-width: unset;
    margin-bottom: 40px;
    padding-left: 30px;
  }

  .th-icon {
    margin: 0;
    position: absolute;
    left: 0;
  }

  .th-item::after {
    content: '';
    position: absolute;
    left: 9px;
    top: 26px;
    width: 3px;
    height: calc(100% - 26px);
    background-color: #dee2e6;
  }

  .th-item:last-child::after {
    display: none;
  }

  .th-label {
    margin-left: 10px;
  }

  .meta-grid {
    grid-template-columns: 1fr;
  }
}
</style>
