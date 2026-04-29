<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue'
import type { ItemProducao } from '@/composables/useProducao'
import AcoesItem from './AcoesItem.vue'
import Swal from 'sweetalert2'
import api from '@/services/axios'

const props = defineProps<{
  item: ItemProducao
  pedido: Pedido
}>()

const emit = defineEmits<{
  (e: 'iniciar', payload: { pedido_item_unidade_id: number }): void
  (e: 'parar', payload: { pedido_item_unidade_id: number }): void
  (e: 'finalizar', payload: { pedido_item_unidade_id: number }): void
  (e: 'salvar', payload: { pedido_item_unidade_id: number }): void
  (e: 'reprovar', payload: { pedido_item_unidade_id: number }): void
  (e: 'adicionar-parametro'): void
}>()

/* ------------------------ ACÕES ------------------------ */

const acao = (tipo: 'INICIO' | 'PAUSA' | 'FINALIZACAO' | 'REPROVACAO') => {
  const mapa = {
    INICIO: 'iniciar',
    PAUSA: 'parar',
    FINALIZACAO: 'finalizar',
    REPROVACAO: 'reprovar',
  } as const

  const evento = mapa[tipo]
  emit(evento, { pedido_item_unidade_id: props.item.pedido_item_unidade_id })
}

const salvando = ref(false)

/* ------------------------ STATUS ------------------------ */

const status = computed(() => props.item.status)

const primeiraEtapa = computed(() =>
  props.item.etapas?.find((e) => e.status === 'em_andamento') ??
  props.item.etapas?.[0] ??
  null,
)

const STATUS_INFO = {
  em_producao: {
    label: 'Em andamento',
    icon: 'bi bi-play-fill',
    badge: 'bg-success',
    border: 'borda-status-em-producao',
  },
  parado: {
    label: 'Pausado',
    icon: 'bi bi-pause-fill',
    badge: 'bg-warning text-dark',
    border: 'borda-status-parado',
  },
  finalizado: {
    label: 'Finalizado',
    icon: 'bi bi-check-circle-fill',
    badge: 'bg-secondary',
    border: 'borda-status-finalizado',
  },
  reprovado: {
    label: 'Reprovado',
    icon: 'bi bi-x-circle-fill',
    badge: 'bg-danger',
    border: 'borda-status-reprovado',
  },
  aguardando: {
    label: 'Aguardando início',
    icon: 'bi bi-hourglass-split',
    badge: 'bg-light text-dark',
    border: '',
  },
}
const statusInfo = computed(() => STATUS_INFO[status.value] ?? STATUS_INFO.aguardando)

/* ------------------------ VALIDAÇÃO MP ------------------------ */
const mpPreenchida = computed(() => {
  const mps = primeiraEtapa.value?.mps ?? []
  return mps.some((mp) => mp.valor > 0 && mp.unidade && mp.lote)
})

function outraMPPreenchida(index) {
  const mps = primeiraEtapa.value?.mps ?? []
  return mps.some((mp, i) => i !== index && mp.valor > 0 && mp.unidade && mp.lote)
}

/* ------------------------ CARREGAR MPs DO BANCO ------------------------ */
async function carregarMPsDoBanco() {
  try {
    const etapa = primeiraEtapa.value
    if (!etapa) return

    const { data } = await api.get('/api/pedido-mps/buscar', {
      params: {
        pedido_id: props.pedido.id,
        pedido_item_unidade_id: props.item.pedido_item_unidade_id,
        etapa_id: etapa.id,
      },
    })

    etapa.mps = etapa.mps.map((mpLocal) => {
      const mpSalva = data.find((m) => m.materia_prima_id === mpLocal.id)
      if (!mpSalva) return mpLocal

      return {
        ...mpLocal,
        valor: Number(mpSalva.valor),
        unidade: mpSalva.unidade,
        lote: mpSalva.lote,
      }
    })
  } catch (err) {
    console.error('Erro ao carregar MPs:', err)
  }
}

/* ---- chamar automaticamente ao abrir o componente --- */
onMounted(() => {
  carregarMPsDoBanco()
})

/* ---- recarregar MPs caso a etapa atual mude ---- */
watch(primeiraEtapa, () => {
  carregarMPsDoBanco()
})

/* ------------------------ FINALIZAR ETAPA ------------------------ */
function tentarFinalizar() {
  if (!mpPreenchida.value && primeiraEtapa.value?.obrigatorio_mp === 1) {
    Swal.fire({
      icon: 'warning',
      title: 'Atenção',
      text: 'Preencha pelo menos uma matéria-prima antes de finalizar a etapa.',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007A5A',
    })
    return
  }

  Swal.fire({
    title: 'Finalizar Etapa?',
    text: 'Deseja realmente finalizar esta etapa?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, finalizar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#007A5A',
    cancelButtonColor: '#dc3545',
  }).then((result) => {
    if (result.isConfirmed) {
      acao('FINALIZACAO')
      Swal.fire({
        icon: 'success',
        title: 'Etapa finalizada!',
        timer: 1800,
        showConfirmButton: false,
        confirmButtonColor: '#007A5A',
      })
    }
  })
}

/* ------------------------ SALVAR MPs E PARÂMETROS ------------------------ */
async function salvarTudo() {
  const etapa = primeiraEtapa.value
  if (!etapa) return

  salvando.value = true

  try {
    /* -------- salvar parâmetros -------- */
    const parametrosParaSalvar = etapa.parametros.filter(
      (p) => p.valor !== '' && p.valor !== null,
    )

    if (parametrosParaSalvar.length) {
      await api.post('/api/pedido-parametros-producao', {
        pedido_id: props.pedido.id,
        item_id: props.item.produto_id,
        pedido_item_unidade_id: props.item.pedido_item_unidade_id,
        etapa_id: etapa.id,
        parametros: parametrosParaSalvar.map((p) => ({
          parametro_id: p.id,
          valor: p.valor,
        })),
      })
    }

    /* -------- salvar MPs -------- */
    const mpsParaSalvar =
      etapa.mps?.filter(
        (mp) => mp.valor > 0 && mp.unidade && mp.lote,
      ) ?? []

    if (mpsParaSalvar.length) {
      await api.post('/api/pedido-mps', {
        pedido_id: props.pedido.id,
        pedido_item_unidade_id: props.item.pedido_item_unidade_id,
        etapa_id: etapa.id,
        mps: mpsParaSalvar.map((mp) => ({
          mp_id: mp.id,
          valor: mp.valor,
          unidade: mp.unidade,
          lote: mp.lote,
        })),
      })
    }

    Swal.fire({
      icon: 'success',
      title: 'Dados salvos!',
      timer: 1200,
      showConfirmButton: false,
    })

    /* 📌 Recarregar MPs salvas do banco depois de salvar */
    carregarMPsDoBanco()
  } catch (err) {
    console.error('Erro ao salvar MPs/parâmetros:', err)
  } finally {
    salvando.value = false
  }
}
</script>


<template>
  <div :class="['card border-0 shadow-sm rounded-4 p-3 position-relative', statusInfo.border]">
    <div class="card-body d-flex flex-column gap-3">
      <!-- Cabeçalho -->
      <div class="border-bottom pb-2 mb-2">
        <h6 class="fw-bold text-dark mb-1 text-truncate">{{ item.descricao }}</h6>
        <div class="text-muted small">
          <template v-if="item.anvisa"><strong>ANVISA:</strong> {{ item.anvisa }}</template>
          <template v-if="item.it"><span v-if="item.anvisa"> | </span><strong>ITs:</strong> {{ item.it }}</template>
          <template v-if="item.maquinas"><span v-if="item.anvisa || item.it"> | </span><strong>Máquina:</strong>
            {{ item.maquinas }}</template>
        </div>
        <div class="text-muted small mt-2 badge">
          <strong>Produção: </strong>
          <span class="text-success fw-semibold">{{ item.qtdProduzida }}</span> /
          <span class="text-secondary">{{ item.qtdPlanejada }}</span>
        </div>
      </div>

      <!-- Etapa atual -->
      <div class="d-flex justify-content-between align-items-center bg-light rounded-4 px-3 py-2">
        <div>
          <h6 class="fw-semibold mb-0 text-dark">{{ primeiraEtapa?.nome ?? '—' }}</h6>
          <small class="text-muted">Etapa atual de produção</small>
        </div>
        <span class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill fw-semibold text-white"
          :class="statusInfo.badge">
          <i :class="statusInfo.icon"></i> <span>{{ statusInfo.label }}</span>
        </span>
      </div>

      <!-- Parâmetros -->
      <div v-if="primeiraEtapa?.parametros?.length" class="mt-3">
        <strong class="d-block mb-2">Parâmetros de Processo:</strong>
        <div v-for="(p, idx) in primeiraEtapa.parametros" :key="idx"
          class="input-group input-group-sm mb-2 shadow-sm rounded-3">
          <span class="input-group-text bg-light fw-semibold">{{ p.nome }}</span>
          <input v-model="p.valor" type="text" class="form-control text-center" :placeholder="`Digite ${p.nome}`" />
        </div>
      </div>

      <!-- Matérias-primas -->
      <div v-if="primeiraEtapa?.obrigatorio_mp === 1" class="mt-4">
        <strong class="d-block mb-2">Matérias-Primas (Obrigatório)</strong>
        <div v-for="(mp, idx) in primeiraEtapa.mps" :key="idx"
          class="border rounded-4 p-3 mb-3 bg-white shadow-sm position-relative">
          <div v-if="outraMPPreenchida(idx)"
            class="position-absolute top-0 start-0 w-100 h-100 bg-light bg-opacity-50 d-flex align-items-center justify-content-center rounded-4"
            style="backdrop-filter: blur(1px)">
            <small class="text-muted fst-italic">Somente uma MP pode ser utilizada</small>
          </div>
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Matéria-Prima</label>
              <input v-model="mp.nome" type="text" class="form-control form-control-sm text-center bg-light" readonly />
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Quantidade</label>
              <input v-model.number="mp.valor" type="number" min="0" step="0.01"
                class="form-control form-control-sm text-center" placeholder="Ex: 12.5"
                :disabled="outraMPPreenchida(idx)" />
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold">Unidade</label>
              <select v-model="mp.unidade" class="form-select form-select-sm text-center"
                :disabled="outraMPPreenchida(idx)">
                <option value="">Selecione</option>
                <option value="unid">unid</option>
                <option value="g">grama (g)</option>
                <option value="kg">quilograma (kg)</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Lote</label>
              <input v-model="mp.lote" type="text" class="form-control form-control-sm text-center"
                placeholder="Ex: L-2025A" :disabled="outraMPPreenchida(idx)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Ações -->
      <AcoesItem :disabled="status === 'reprovado'" @iniciar="acao('INICIO')" @parar="acao('PAUSA')"
        @finalizar="tentarFinalizar" @salvar="salvarTudo" @reprovar="acao('REPROVACAO')" />
    </div>
  </div>
</template>

<style scoped>
.borda-status-em-producao {
  border-left: 6px solid #28a745 !important;
}

.borda-status-parado {
  border-left: 6px solid #ffc107 !important;
}

.borda-status-finalizado {
  border-left: 6px solid #6c757d !important;
}

.borda-status-reprovado {
  border-left: 6px solid #dc3545 !important;
}

.card {
  transition:
    box-shadow 0.3s ease,
    transform 0.2s ease;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
}
</style>
