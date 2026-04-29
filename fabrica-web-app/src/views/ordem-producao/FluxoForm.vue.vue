<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Draggable from 'vuedraggable'
import api from '@/services/axios.ts'
import Swal from 'sweetalert2'
import Multiselect from 'vue-multiselect'


interface Etapa {
  id: number
  nome_etapa: string
  tempo_estimado?: number // minutos
}

interface Fluxo {
  id: number
  nome_fluxo: string
  etapas: Etapa[]
  tempo_estimado_dias?: number
  tempo_estimado_dias_extra?: number
}


const route = useRoute()
const fluxo = ref<Fluxo | null>(null)

const nomeFluxo = ref('')
const etapasDisponiveis = ref<Etapa[]>([])
const etapasSelecionadas = ref<Etapa[]>([])
const selectedEtapa = ref<Etapa[]>([])
const tempoEstimadoDias = ref<number | null>(null)
const tempoEstimadoDiasComTaxa = ref<number | null>(null)


function mostrarToast(msg: string, tipo: 'success' | 'error' | 'warning' = 'success') {
  Swal.fire({
    icon: tipo,
    title: msg,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    background: '#fff',
  })
}

const fetchEtapas = async () => {
  try {
    const { data } = await api.get('/api/etapas')
    etapasDisponiveis.value = data
  } catch (error) {
    console.error('Erro ao buscar etapas:', error)
    mostrarToast('Não foi possível carregar as etapas.', 'error')
  }
}

const adicionarEtapa = () => {
  if (selectedEtapa.value.length === 0) {
    mostrarToast('Selecione uma etapa para adicionar.', 'warning')
    return
  }

  selectedEtapa.value.forEach((etapa) => {
    if (!etapasSelecionadas.value.some((e) => e.id === etapa.id)) {
      etapasSelecionadas.value.push({ ...etapa, tempo_estimado: 0 })
    }
  })
  selectedEtapa.value = []
  mostrarToast('Etapa adicionada com sucesso!', 'success')
}

const removerEtapa = (index: number) => {
  etapasSelecionadas.value.splice(index, 1)
  mostrarToast('Etapa removida do fluxo.', 'warning')
}

const salvarFluxo = async () => {
  if (!nomeFluxo.value.trim()) {
    mostrarToast('Informe o nome do fluxo.', 'warning')
    return
  }

  if (etapasSelecionadas.value.length === 0) {
    mostrarToast('Adicione pelo menos uma etapa.', 'warning')
    return
  }

  const etapasSemTempo = etapasSelecionadas.value.filter(
    (e) => !e.tempo_estimado || e.tempo_estimado <= 0,
  )

  if (etapasSemTempo.length > 0) {
    mostrarToast('Informe o tempo estimado (em minutos) para todas as etapas.', 'warning')
    return
  }

  const payload = {
    nome_fluxo: nomeFluxo.value,
    tempo_estimado_dias: tempoEstimadoDias.value,
    tempo_estimado_dias_acelerado: tempoEstimadoDiasComTaxa.value,
    etapas: etapasSelecionadas.value.map((etapa, index) => ({
      id: etapa.id,
      ordem: index + 1,
      tempo_estimado_minutos: etapa.tempo_estimado,
    })),
  }

  try {
    if (fluxo.value?.id) {
      await api.put(`/api/fluxos/${fluxo.value.id}`, payload)
      mostrarToast('Fluxo atualizado com sucesso!', 'success')
    } else {
      await api.post('/api/fluxos', payload)
      mostrarToast('Fluxo salvo com sucesso!', 'success')

      // Resetar formulário
      nomeFluxo.value = ''
      etapasSelecionadas.value = []
      tempoEstimadoDias.value = null
      tempoEstimadoDiasComTaxa.value = null
    }
  } catch (error) {
    console.error('Erro ao salvar fluxo:', error)
    mostrarToast('Não foi possível salvar o fluxo.', 'error')
  }
}

onMounted(async () => {
  await fetchEtapas()
  const id = route.query.id

  if (id) {
    try {
      const { data } = await api.get(`/api/fluxos/${id}`)
      fluxo.value = data
      nomeFluxo.value = data.nome_fluxo
      tempoEstimadoDias.value = data.tempo_estimado_dias || null
      tempoEstimadoDiasComTaxa.value = data.tempo_estimado_dias_acelerado || null
      etapasSelecionadas.value = data.etapas.map((e: any) => ({
        id: e.id,
        nome_etapa: e.nome_etapa,
        tempo_estimado: e.pivot?.tempo_estimado_minutos ?? 0,
      }))

      mostrarToast('Fluxo carregado para edição.', 'success')
    } catch (error) {
      console.error('Erro ao carregar fluxo:', error)
      mostrarToast('Não foi possível carregar os dados do fluxo.', 'error')
    }
  }
})
</script>

<template>
  <div class="container my-5">
    <!-- Botão Voltar -->
    <button
      class="btn btn-outline-secondary shadow-sm border-0 rounded-pill d-flex align-items-center gap-2 mb-4"
      @click="$router.back()"
    >
      <i class="bi bi-arrow-left"></i>
      <span>Voltar</span>
    </button>

    <!-- Formulário -->
    <form @submit.prevent="salvarFluxo" class="card shadow-lg border-0 rounded-4">
      <div class="card-header bg-light rounded-top-4 p-4">
        <h5 class="mb-0 fw-bold text-dark">
          <i class="bi bi-diagram-3-fill me-2"></i>
          {{ fluxo ? 'Editar Fluxo' : 'Novo Fluxo' }}
        </h5>
      </div>

      <div class="card-body p-4">
        <!-- Nome -->
        <div class="mb-4">
          <label class="form-label fw-semibold text-muted">
            Nome do Fluxo <span class="text-danger">*</span>
          </label>
          <input
            v-model="nomeFluxo"
            type="text"
            class="form-control form-control-lg shadow-sm rounded-4"
            placeholder="Ex: Fluxo de Produção Padrão"
            required
          />
        </div>

        <!-- Tempo total -->
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted">
              Tempo Estimado Total (em dias)
            </label>
            <input
              v-model="tempoEstimadoDias"
              type="number"
              min="1"
              class="form-control form-control-lg shadow-sm rounded-4"
              placeholder="Ex: 15"
            />
            <small class="text-muted">Tempo ideal de produção seguindo o cronograma padrão.</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted">
              Tempo Estimado com Produção Acelerada (em dias)
            </label>
            <input
              v-model="tempoEstimadoDiasComTaxa"
              type="number"
              min="1"
              class="form-control form-control-lg shadow-sm rounded-4"
              placeholder="Ex: 10"
            />
            <small class="text-muted">
              Tempo reduzido para produção com taxa extra (prioridade máxima).
            </small>
          </div>
        </div>

        <hr class="my-4" />

        <!-- Etapas -->
        <div>
          <label class="form-label fw-semibold text-muted">Etapas do Fluxo</label>
          <div class="d-flex flex-column flex-md-row align-items-start gap-2">
            <Multiselect
              v-model="selectedEtapa"
              :options="etapasDisponiveis"
              track-by="id"
              label="nome_etapa"
              placeholder="Selecione as etapas..."
              :multiple="true"
              class="flex-grow-1 shadow-sm rounded-3"
              :close-on-select="false"
            />
            <button
              type="button"
              class="btn btn-primary rounded-pill mt-2 mt-md-0"
              @click="adicionarEtapa"
              :disabled="!selectedEtapa.length"
            >
              <i class="bi bi-plus-circle me-1"></i> Adicionar
            </button>
          </div>

          <!-- Lista -->
          <div class="mt-3">
            <Draggable v-model="etapasSelecionadas" item-key="id" class="list-group">
              <template #item="{ element, index }">
                <div
                  class="list-group-item d-flex align-items-center justify-content-between rounded-4 shadow-sm mb-2 p-3 flex-wrap"
                >
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-muted">{{ index + 1 }}.</span>
                    <span class="fw-semibold">{{ element.nome_etapa }}</span>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <input
                      v-model.number="element.tempo_estimado"
                      type="number"
                      min="1"
                      class="form-control form-control-sm rounded-pill text-center"
                      placeholder="Minutos"
                      style="width: 120px"
                    />
                    <span class="text-muted small">min</span>

                    <button
                      type="button"
                      class="btn btn-outline-danger btn-sm rounded-circle"
                      @click="removerEtapa(index)"
                      title="Remover"
                    >
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                </div>
              </template>
            </Draggable>

            <div
              v-if="etapasSelecionadas.length === 0"
              class="text-center text-muted fst-italic mt-3"
            >
              Nenhuma etapa adicionada.
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer bg-white border-0 d-flex justify-content-end p-4">
        <button
          type="submit"
          class="btn btn-primary btn-lg rounded-pill px-4 d-flex align-items-center gap-2"
        >
          <i class="bi bi-check-circle"></i>
          {{ fluxo ? 'Atualizar Fluxo' : 'Salvar Fluxo' }}
        </button>
      </div>
    </form>
  </div>
</template>
