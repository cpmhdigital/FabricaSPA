<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <h4 class="mb-1 fw-semibold text-dark">Assistente de Configuração</h4>
        <div class="text-muted">
          Crie etapas e faça associações com setor ou fluxo em poucos passos.
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-light btn-sm" type="button" @click="resetarWizard">
          Limpar
        </button>
      </div>
    </div>

    <!-- Acesso rápido -->
    <div class="card card-clear mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <h6 class="mb-0 fw-semibold text-dark">Acesso rápido</h6>
          <small class="text-muted">Atalhos para cadastros</small>
        </div>

        <div class="row g-3">
          <div class="col-md-4" v-for="item in indicadores" :key="item.label">
            <button class="quick-card" type="button" @click="item.to && router.push(item.to)">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <div class="icon-chip">
                    <i :class="item.icon"></i>
                  </div>
                  <div class="text-start">
                    <div class="fw-semibold text-dark">{{ item.label }}</div>
                    <div class="text-muted small">Gerenciar</div>
                  </div>
                </div>

                <span class="badge rounded-pill text-bg-light border">
                  {{ item.count }}
                </span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tipo de associação -->
    <div class="card card-clear mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <h6 class="mb-0 fw-semibold text-dark">Tipo de associação</h6>
          <small class="text-muted">Escolha como vincular as etapas</small>
        </div>

        <div class="nav nav-pills pills-clear">
          <button
            v-for="tipo in tiposAssociacao"
            :key="tipo"
            class="nav-link"
            :class="{ active: tipoAssociacao === tipo }"
            type="button"
            @click="iniciarAssociacao(tipo)"
          >
            <span v-if="tipo === 'setor'">Associar a Setor</span>
            <span v-else>Associar a Fluxo</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Wizard -->
    <div class="card card-clear">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div>
            <h6 class="mb-1 fw-semibold text-dark">Assistente</h6>
            <small class="text-muted">
              Passo {{ passoAtual }} de {{ totalPassos }}
            </small>
          </div>

          <div class="text-muted small">
            Progresso: <span class="fw-semibold">{{ Math.round(progresso) }}%</span>
          </div>
        </div>

        <div class="progress progress-thin mb-4">
          <div
            class="progress-bar"
            role="progressbar"
            :style="{ width: progresso + '%' }"
          ></div>
        </div>

        <!-- PASSO 1 -->
        <template v-if="passoAtual === 1">
          <div class="step-card">
            <div class="step-title">
              <i class="bi bi-diagram-3 me-2"></i>
              1. Selecionar Etapas
            </div>
            <p class="text-muted mb-3">
              Selecione as etapas que farão parte do processo.
            </p>

            <Multiselect
              v-model="etapasSelecionadasObjs"
              :options="etapas"
              :multiple="true"
              track-by="id"
              label="nome_etapa"
              placeholder="Selecione as etapas..."
              :close-on-select="false"
              class="multiselect-clear"
            />

            <div v-if="etapasSelecionadasObjs.length" class="mt-3">
              <small class="text-muted d-block mb-2">Selecionadas:</small>
              <div class="d-flex flex-wrap gap-2">
                <span
                  v-for="etapa in etapasSelecionadasObjs"
                  :key="etapa.id"
                  class="badge rounded-pill text-bg-light border"
                >
                  {{ etapa.nome_etapa }}
                </span>
              </div>
            </div>
          </div>
        </template>

        <!-- PASSO 2 (SETOR) -->
        <template v-if="passoAtual === 2 && tipoAssociacao === 'setor'">
          <div class="step-card">
            <div class="step-title">
              <i class="bi bi-building-gear me-2"></i>
              2. Associar Etapas ao Setor
            </div>
            <p class="text-muted mb-3">
              Escolha um setor e confirme as etapas selecionadas.
            </p>

            <label class="form-label fw-semibold">Setor</label>
            <Multiselect
              v-model="setorSelecionadoObj"
              :options="setores"
              track-by="id"
              label="nome"
              placeholder="Selecione um setor..."
              :close-on-select="true"
              class="multiselect-clear"
            />

            <div class="mt-4">
              <small class="text-muted d-block mb-2">Etapas selecionadas:</small>
              <div class="list-clean">
                <div
                  v-for="etapa in etapasSelecionadasObjs"
                  :key="etapa.id"
                  class="list-clean-item"
                >
                  <i class="bi bi-check-circle-fill text-success me-2"></i>
                  <span class="fw-semibold">{{ etapa.nome_etapa }}</span>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- PASSO 3 (FLUXO) -->
        <template v-if="passoAtual === 3 && tipoAssociacao === 'fluxo'">
          <div class="step-card">
            <div class="step-title">
              <i class="bi bi-shuffle me-2"></i>
              3. Criar Fluxo e Ordenar Etapas
            </div>
            <p class="text-muted mb-3">
              Defina o nome do fluxo e organize a ordem das etapas.
            </p>

            <label class="form-label fw-semibold">
              Nome do Fluxo <span class="text-danger">*</span>
            </label>
            <input
              v-model="nomeFluxo"
              type="text"
              class="form-control form-control-sm input-clear mb-3"
              placeholder="Ex: Fluxo de Produção Padrão"
            />

            <label class="form-label fw-semibold">Etapas (arraste para ordenar)</label>
            <Draggable
              v-model="etapasSelecionadasObjs"
              item-key="id"
              class="list-clean"
              ghost-class="drag-ghost"
              chosen-class="drag-chosen"
            >
              <template #item="{ element, index }">
                <div class="list-clean-item d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold">{{ index + 1 }}.</span>
                    <span class="fw-semibold">{{ element.nome_etapa }}</span>
                  </div>

                  <button
                    class="btn btn-light btn-sm icon-btn"
                    type="button"
                    @click="etapasSelecionadasObjs.splice(index, 1)"
                    title="Remover"
                  >
                    <i class="bi bi-x-lg text-danger"></i>
                  </button>
                </div>
              </template>
            </Draggable>

            <div
              v-if="etapasSelecionadasObjs.length === 0"
              class="text-muted mt-3 text-center fst-italic"
            >
              Nenhuma etapa adicionada.
            </div>
          </div>
        </template>

        <!-- PASSO 4 (REVISÃO) -->
        <template v-if="passoAtual === 4">
          <div class="step-card">
            <div class="step-title">
              <i class="bi bi-clipboard-check me-2"></i>
              4. Revisão Final
            </div>
            <p class="text-muted mb-3">
              Confirme os dados antes de concluir.
            </p>

            <div class="row g-3 mb-3">
              <div class="col-md-6" v-for="card in resumoCards" :key="card.label">
                <div class="mini-card">
                  <small class="text-muted d-block">{{ card.label }}</small>
                  <div class="fw-semibold text-dark">{{ card.value }}</div>
                </div>
              </div>
            </div>

            <div class="mini-card">
              <small class="text-muted d-block mb-2">Etapas (ordem definida)</small>

              <div class="d-flex flex-wrap gap-2">
                <span
                  v-for="(etapa, index) in etapasSelecionadasObjs"
                  :key="etapa.id"
                  class="badge rounded-pill text-bg-light border"
                >
                  {{ index + 1 }}. {{ etapa.nome_etapa }}
                </span>
              </div>

              <div v-if="etapasSelecionadasObjs.length === 0" class="text-muted mt-2 fst-italic">
                Nenhuma etapa adicionada.
              </div>
            </div>
          </div>
        </template>

        <!-- Ações -->
        <div class="d-flex justify-content-between mt-4">
          <button class="btn btn-light" type="button" @click="voltar" :disabled="passoAtual === 1">
            Voltar
          </button>

          <button class="btn btn-success" type="button" @click="avancar">
            {{ passoAtual === totalPassos ? 'Concluir' : 'Avançar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Multiselect from 'vue-multiselect'
import Draggable from 'vuedraggable'
import api from '@/services/axios.ts'
import Swal from 'sweetalert2'

const router = useRouter()

// --------------------
// Interfaces
// --------------------
interface Etapa {
  id: number
  nome_etapa: string
}

interface Setor {
  id: number
  nome: string
}

interface Fluxo {
  id: number
  nome: string
}

// --------------------
// Dados
// --------------------
const setores = ref<Setor[]>([])
const etapas = ref<Etapa[]>([])
const fluxos = ref<Fluxo[]>([])

// --------------------
// Seleções
// --------------------
const etapasSelecionadasObjs = ref<Etapa[]>([])
const setorSelecionadoObj = ref<Setor | null>(null)
const tipoAssociacao = ref<'setor' | 'fluxo'>('setor')
const nomeFluxo = ref('')

// Tipos de associação disponíveis
const tiposAssociacao: Array<'setor' | 'fluxo'> = ['setor', 'fluxo']

// --------------------
// Wizard
// --------------------
const passoAtual = ref(1)
const totalPassos = 4
const progresso = computed(() => (passoAtual.value / totalPassos) * 100)

// --------------------
// Indicadores
// --------------------
const indicadores = computed(() => [
  { label: 'Etapas', to: '/ordem-producao/etapa', count: etapas.value.length, icon: 'bi bi-diagram-3' },
  { label: 'Setores', to: '/ordem-producao/setor', count: setores.value.length, icon: 'bi bi-building-gear' },
  { label: 'Fluxos', to: '/ordem-producao/fluxo', count: fluxos.value.length, icon: 'bi bi-shuffle' },
])

// --------------------
// Cards resumo
// --------------------
const resumoCards = computed(() => [
  {
    label: tipoAssociacao.value === 'setor' ? 'Setor Selecionado' : 'Nome do Fluxo',
    value:
      tipoAssociacao.value === 'setor'
        ? setorSelecionadoObj.value?.nome || '-'
        : nomeFluxo.value || '-',
  },
  {
    label: 'Tipo de Associação',
    value: tipoAssociacao.value === 'setor' ? 'Setor' : 'Fluxo',
  },
])

// --------------------
// Funções do Wizard
// --------------------
const iniciarAssociacao = (tipo: 'setor' | 'fluxo') => {
  tipoAssociacao.value = tipo
  passoAtual.value = 1
  etapasSelecionadasObjs.value = []
  setorSelecionadoObj.value = null
  nomeFluxo.value = ''
}

function resetarWizard() {
  iniciarAssociacao(tipoAssociacao.value)
}

const avancar = async () => {
  switch (passoAtual.value) {
    case 1:
      if (!etapasSelecionadasObjs.value.length) {
        return Swal.fire('Atenção', 'Selecione ao menos uma etapa.', 'warning')
      }
      passoAtual.value = tipoAssociacao.value === 'fluxo' ? 3 : 2
      break

    case 2:
      if (tipoAssociacao.value === 'setor' && !setorSelecionadoObj.value) {
        return Swal.fire('Atenção', 'Selecione um setor.', 'warning')
      }
      passoAtual.value = 4
      break

    case 3:
      if (!nomeFluxo.value.trim()) {
        return Swal.fire('Atenção', 'Informe um nome para o fluxo.', 'warning')
      }
      passoAtual.value = 4
      break

    case 4:
      await concluir()
      break
  }
}

const concluir = async () => {
  try {
    if (tipoAssociacao.value === 'setor') {
      const payload = {
        setor_id: setorSelecionadoObj.value?.id,
        etapas: etapasSelecionadasObjs.value.map((e) => e.id),
      }

      await api.patch('/api/etapas/associar-setor', payload)
      await Swal.fire('Sucesso', 'Etapas associadas ao setor com sucesso!', 'success')
      iniciarAssociacao(tipoAssociacao.value)
    }

    if (tipoAssociacao.value === 'fluxo') {
      const payload = {
        nome_fluxo: nomeFluxo.value,
        etapas: etapasSelecionadasObjs.value.map((e, index) => ({
          id: e.id,
          ordem: index + 1,
        })),
      }

      await api.post('/api/fluxos', payload)
      await Swal.fire('Sucesso', 'Fluxo criado e etapas associadas com sucesso!', 'success')
      iniciarAssociacao(tipoAssociacao.value)
    }
  } catch (error) {
    console.error(error)
    Swal.fire('Erro', 'Ocorreu um erro ao salvar. Tente novamente.', 'error')
  }
}

const voltar = () => {
  if (passoAtual.value > 1) passoAtual.value--
}

// --------------------
// Carregamento inicial
// --------------------
onMounted(async () => {
  try {
    const [setoresRes, etapasRes, fluxosRes] = await Promise.all([
      api.get('/api/setores'),
      api.get('/api/etapas'),
      api.get('/api/fluxos'),
    ])
    setores.value = setoresRes.data
    etapas.value = etapasRes.data
    fluxos.value = fluxosRes.data
  } catch (error) {
    console.error(error)
    Swal.fire('Erro', 'Não foi possível carregar os dados iniciais.', 'error')
  }
})
</script>

<style scoped>
/* Base “clear” */
.card-clear {
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 14px;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
  background: #fff;
}

/* Pills discretos */
.pills-clear .nav-link {
  border-radius: 999px;
  padding: 0.35rem 0.75rem;
  color: #334155;
  background: #f1f5f9;
  border: 1px solid rgba(0, 0, 0, 0.04);
  font-size: 0.9rem;
}
.pills-clear .nav-link.active {
  background: #0f766e;
  border-color: #0f766e;
  color: #fff;
}

/* Quick cards */
.quick-card {
  width: 100%;
  border: 1px solid rgba(0,0,0,.06);
  background: #fff;
  border-radius: 14px;
  padding: 14px 14px;
  box-shadow: 0 1px 8px rgba(0,0,0,.04);
  transition: transform .12s ease, box-shadow .12s ease;
}
.quick-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(0,0,0,.06);
}

.icon-chip {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  color: #0f766e;
  border: 1px solid rgba(0,0,0,.04);
}

/* Wizard steps */
.step-card {
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
  padding: 16px;
  background: #fff;
  box-shadow: 0 1px 8px rgba(0,0,0,.04);
}
.step-title {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 6px;
}

/* Progress */
.progress-thin {
  height: 10px;
  border-radius: 999px;
  background: #f1f5f9;
  overflow: hidden;
}
.progress-thin .progress-bar {
  background: #0f766e;
}

/* Mini cards */
.mini-card {
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
  padding: 14px;
  background: #fff;
  box-shadow: 0 1px 8px rgba(0,0,0,.04);
}

/* Lists */
.list-clean {
  display: grid;
  gap: 10px;
}
.list-clean-item {
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 12px;
  padding: 10px 12px;
  background: #fff;
  box-shadow: 0 1px 8px rgba(0,0,0,.04);
}

/* Inputs */
.input-clear {
  border-radius: 10px;
  border: 1px solid rgba(0,0,0,.12);
}

/* Multiselect clean */
.multiselect-clear :deep(.multiselect) {
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,.12);
  min-height: 42px;
}
.multiselect-clear :deep(.multiselect__tags) {
  border: 0;
  padding: 8px 10px;
}
.multiselect-clear :deep(.multiselect__tag) {
  background: #0f766e;
  color: #fff;
  border-radius: 999px;
}
.multiselect-clear :deep(.multiselect__input),
.multiselect-clear :deep(.multiselect__single) {
  background: transparent;
}

/* Draggable feedback */
.drag-ghost {
  opacity: 0.6;
}
.drag-chosen {
  outline: 2px dashed rgba(15, 118, 110, 0.4);
  outline-offset: 2px;
}

.icon-btn {
  border-radius: 999px;
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
