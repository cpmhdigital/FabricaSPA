<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import PainelBase from '@/components/PainelBase.vue'
import BaseCards from '@/components/CardsBase.vue'
import GraficoConclusao from '@/components/GraficoConclusao.vue'

import TabelaPedidos from '@/views/ordem-producao/ListaPedidos.vue'
import ListaPedidosMatriz from '@/views/ordem-producao/ListaPedidosMatriz.vue'

import api from '@/services/axios'

type TabelaAtiva = 'pcp' | 'aguardando'
type TipoPedido = 'todos' | 'nacional' | 'internacional'

const router = useRouter()

/** role real vem da API (/me) */
const perfilUsuario = ref<string>('')

async function carregarMe() {
  const { data } = await api.get('/api/me')
  perfilUsuario.value = String(data?.roles?.[0] ?? '')
}

const tabelaAtiva = ref<TabelaAtiva>('pcp')

const tituloTabela = computed(() =>
  tabelaAtiva.value === 'pcp'
    ? 'Chegada de pedidos e encaminhamento para Produção'
    : 'Aguardando Produção'
)

/* ==========================
   FILTROS: Modalidade + Tipo
========================== */
const selectedModalidade = ref<string>('todas')
const selectedTipo = ref<TipoPedido>('todos')

const modalidadeOptions = [
  { value: 'todas', label: 'Todas' },
  { value: 'customlife', label: 'CustomLIFE' },
  { value: 'atm', label: 'ATM' },
  { value: 'ancorfix', label: 'AncorFix' },
] as const

const tipoOptions = [
  { value: 'todos', label: 'Todos' },
  { value: 'nacional', label: 'Nacional' },
  { value: 'internacional', label: 'Internacional' },
] as const

const modalidadeLabel = computed(
  () => modalidadeOptions.find(o => o.value === selectedModalidade.value)?.label ?? '—'
)

const tipoLabel = computed(
  () => tipoOptions.find(o => o.value === selectedTipo.value)?.label ?? '—'
)

/* ==========================
   BADGE “Chegada de pedidos”
========================== */
const chegadaCount = ref<number>(0)
const loadingChegada = ref(false)

function handleTabelaStats(payload: { total: number }) {
  chegadaCount.value = Number(payload?.total ?? 0)
}

function openChegadas() {
  tabelaAtiva.value = 'pcp'
}

/* ==========================
   GRÁFICO
========================== */
type PeriodoGrafico = '3m' | '6m' | '12m'
const periodoGrafico = ref<PeriodoGrafico>('6m')

const periodoGraficoLabel = computed(() => {
  if (periodoGrafico.value === '3m') return 'Últimos 3 meses'
  if (periodoGrafico.value === '12m') return 'Últimos 12 meses'
  return 'Últimos 6 meses'
})

/* ==========================
   CARDS
========================== */
type CardItem = {
  label: string
  value: number
  sub: string
  icon: string
  iconColor: string
  iconBg: string
}

const cards = ref<CardItem[]>([
  { label: 'Projetos', value: 0, sub: 'Para aprovação', icon: 'bi bi-check2-square', iconColor: '#0d6efd', iconBg: '#e7f1ff' },
  { label: 'Pedidos',  value: 0, sub: 'Em execução',    icon: 'bi bi-play-circle',  iconColor: '#3cc105', iconBg: '#def1e8' },
  { label: 'Projetos', value: 0, sub: 'Em atraso',      icon: 'bi bi-exclamation-triangle-fill', iconColor: '#dc3545', iconBg: '#f8d7da' },
  { label: 'Pedidos',  value: 0, sub: 'Pedidos novos',  icon: 'bi bi-clock-history', iconColor: '#fd7e14', iconBg: '#fff3cd' },
])

const loadingCards = ref(false)

async function carregarCards() {
  loadingCards.value = true
  try {
    const { data } = await api.get('/api/painel/cards', {
      params: { perfil: perfilUsuario.value },
    })

    const paraAprovacao = Number(data?.para_aprovacao ?? 0)
    const emExecucao    = Number(data?.em_execucao ?? 0)
    const emAtraso      = Number(data?.em_atraso ?? 0)
    const novos         = Number(data?.novos ?? 0)

    cards.value = [
      { ...cards.value[0], value: paraAprovacao, sub: 'Para aprovação' },
      { ...cards.value[1], value: emExecucao,    sub: 'Em execução' },
      { ...cards.value[2], value: emAtraso,      sub: 'Em atraso' },
      { ...cards.value[3], value: novos,         sub: 'Pedidos novos' },
    ]
  } catch (e) {
    cards.value = cards.value.map(c => ({ ...c, value: 0 }))
    console.error('Erro ao carregar cards:', e)
  } finally {
    loadingCards.value = false
  }
}


/* ==========================
   MATRIZ: SEMPRE APROVADOS
========================== */
const statusMatriz = 'aprovado' as const

const matrizMinimizada = ref(false)
function handleExportar() { console.log('Exportar matriz') }
function handleColunas() { console.log('Configurar colunas') }
function handleMinimizar() { matrizMinimizada.value = !matrizMinimizada.value }

/* ==========================
   INIT
========================== */
onMounted(async () => {
  await carregarMe()
  await carregarCards()
})
</script>

<template>
  <PainelBase title="Painel de Produção">
    <template #cards>
      <BaseCards :cards="cards" />
    </template>

    <template #acompanhamento>
      <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" type="button">
            Modalidade: <strong>{{ modalidadeLabel }}</strong>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li v-for="m in modalidadeOptions" :key="m.value">
              <button class="dropdown-item" :class="{ active: selectedModalidade === m.value }" type="button"
                @click="selectedModalidade = m.value">
                {{ m.label }}
              </button>
            </li>
          </ul>
        </div>

        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" type="button">
            Tipo: <strong>{{ tipoLabel }}</strong>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li v-for="t in tipoOptions" :key="t.value">
              <button class="dropdown-item" :class="{ active: selectedTipo === t.value }" type="button"
                @click="selectedTipo = t.value">
                {{ t.label }}
              </button>
            </li>
          </ul>
        </div>

        <button class="btn btn-primary btn-sm" type="button" @click="openChegadas" :disabled="loadingChegada">
          <i class="bi bi-check2-circle me-1"></i>
          Chegada de pedidos
          <span class="badge bg-white text-primary ms-2">{{ chegadaCount }}</span>
        </button>
      </div>

      <div class="mt-2">
        <div class="pcp-title">{{ tituloTabela }}</div>
      </div>

      <div class="mt-3">
        <TabelaPedidos
          :key="`${selectedModalidade}-${selectedTipo}`"
          mode="full"
          status="aguardando"
          :perfil="perfilUsuario"
          :modalidade="selectedModalidade"
          :tipo="selectedTipo"
          @stats="handleTabelaStats"
        />
      </div>
    </template>
    
    <template #grafico>
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold">Métrica de Conclusão</div>

        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" type="button">
            {{ periodoGraficoLabel }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" :class="{ active: periodoGrafico === '3m' }" @click="periodoGrafico = '3m'">Últimos 3 meses</button></li>
            <li><button class="dropdown-item" :class="{ active: periodoGrafico === '6m' }" @click="periodoGrafico = '6m'">Últimos 6 meses</button></li>
            <li><button class="dropdown-item" :class="{ active: periodoGrafico === '12m' }" @click="periodoGrafico = '12m'">Últimos 12 meses</button></li>
          </ul>
        </div>
      </div>

      <GraficoConclusao />
    </template>


    <template #tabela>
      <div class="pcp-header">
        <div class="pcp-title">Acompanhamento da Produção</div>
        <div class="pcp-tools">
          <button class="tool-btn" type="button" title="Exportar" @click="handleExportar">
            <i class="bi bi-box-arrow-up-right"></i>
          </button>
          <button class="tool-btn" type="button" title="Colunas" @click="handleColunas">
            <i class="bi bi-layout-three-columns"></i>
          </button>
          <button class="tool-btn" type="button" title="Minimizar" @click="handleMinimizar">
            <i class="bi" :class="matrizMinimizada ? 'bi-plus-lg' : 'bi-dash-lg'"></i>
          </button>
        </div>
      </div>

      <div v-if="!matrizMinimizada" class="px-3 mt-2">
        <ListaPedidosMatriz :status="statusMatriz" />
      </div>
    </template>
  </PainelBase>
</template>

<style scoped>
.pcp-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px 10px;
}

.pcp-title {
  font-weight: 900;
  font-size: 16px;
  color: #1f2a37;
}

.pcp-tools {
  display: flex;
  gap: 8px;
}

.tool-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #eef1f6;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.tool-btn i {
  color: #6b7280;
}

.tool-btn:hover {
  background: #f6f8fd;
}
</style>
