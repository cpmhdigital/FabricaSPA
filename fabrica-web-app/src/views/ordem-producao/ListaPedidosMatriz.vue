<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/axios'

const props = defineProps<{
  status: string // aqui você vai usar "aprovado"
}>()

const router = useRouter()
const loading = ref(false)

const pedidosRaw = ref<any[]>([])
const search = ref('')
const pageSize = ref(4)
const page = ref(1)

/** Modal */
const modalOpen = ref(false)
const selectedPedido = ref<any | null>(null)

function openPedido(p: any) {
  selectedPedido.value = p
  modalOpen.value = true
}
function closeModal() {
  modalOpen.value = false
  selectedPedido.value = null
}

function handleVerPedido(pedido: any) {
  router.push({ name: 'pedido-producao', state: { id: pedido.id } })
}

function safeStr(v: any) {
  return (v ?? '').toString()
}

/**
 * Matriz de verdade: célula = status/etapa compacta
 * Detalhe = title + modal
 */
function normalizePedido(p: any) {
  const pedidoLabel = String(p.numero_pedido ?? p.id ?? '—')
  const itens = Array.isArray(p.itens) ? p.itens : []

  const itensNorm = itens.map((it: any, idx: number) => {
    const nome =
      it?.produto?.codigo ??
      it?.produto?.nome ??
      it?.codigo ??
      it?.nome ??
      `Item ${idx + 1}`

    const etapa =
      it?.etapa_atual ??
      it?.etapa ??
      it?.status_producao ??
      it?.status ??
      '—'

    const setor =
      it?.setor_atual ??
      it?.setor ??
      '—'

    const prazoRaw = it?.prazo ?? it?.data_entrega ?? it?.data_prazo ?? null
    const prazo = prazoRaw ? new Date(prazoRaw) : null

    const hoje = new Date()
    const atrasado = prazo ? prazo < new Date(hoje.toDateString()) : false

    return {
      raw: it,
      nome: safeStr(nome),
      etapa: safeStr(etapa || '—'),
      setor: safeStr(setor || '—'),
      prazo,
      atrasado,
    }
  })

  return {
    raw: p,
    id: p.id,
    pedido: pedidoLabel,
    paciente: safeStr(p.paciente ?? p.nome_paciente ?? '—'),
    medico: safeStr(p.doutor ?? p.responsavel ?? '—'),
    tipo: safeStr(p.tipo ?? '—'),
    itens: itensNorm,
  }
}

const pedidos = computed(() => pedidosRaw.value.map(normalizePedido))

const carregar = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/api/pedidos', { params: { status: props.status } })
    pedidosRaw.value = Array.isArray(data) ? data : []
    page.value = 1
  } catch (e) {
    console.error('Erro ao buscar pedidos:', e)
  } finally {
    loading.value = false
  }
}

watch(() => props.status, carregar, { immediate: true })

function formatShort(d: Date) {
  const day = String(d.getDate()).padStart(2, '0')
  const month = d.toLocaleDateString('pt-BR', { month: 'short' })
  const m = month.charAt(0).toUpperCase() + month.slice(1)
  return `${day} ${m}`
}

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return pedidos.value

  return pedidos.value.filter((p) => {
    const inPedido = p.pedido.toLowerCase().includes(q)
    const inPaciente = p.paciente.toLowerCase().includes(q)
    const inMedico = p.medico.toLowerCase().includes(q)
    const inTipo = p.tipo.toLowerCase().includes(q)
    const inItens = p.itens.some((it: any) =>
      it.nome.toLowerCase().includes(q) ||
      it.etapa.toLowerCase().includes(q) ||
      it.setor.toLowerCase().includes(q)
    )
    return inPedido || inPaciente || inMedico || inTipo || inItens
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize.value)))

const paginated = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return filtered.value.slice(start, start + pageSize.value)
})

watch(pageSize, () => (page.value = 1))
function prevPage() { if (page.value > 1) page.value-- }
function nextPage() { if (page.value < totalPages.value) page.value++ }

/** Colunas dinâmicas (Item 1..N) */
const maxItens = computed(() => {
  const arr = paginated.value.map((p) => p.itens.length)
  return arr.length ? Math.max(...arr) : 0
})
const itemCols = computed(() => Array.from({ length: maxItens.value }, (_, i) => i))

/** helper: reduz etapa para caber na célula (sem perder sentido) */
function shortEtapa(etapa: string) {
  const t = (etapa || '—').trim()
  if (t.length <= 16) return t
  return t.slice(0, 15) + '…'
}
</script>

<template>
  <div class="matrix-card">
    <!-- Search -->
    <div class="matrix-search">
      <i class="bi bi-search"></i>
      <input
        v-model="search"
        class="matrix-search-input"
        type="text"
        placeholder="Buscar pedido, paciente, item ou etapa..."
      />
      <button class="matrix-filter-btn" type="button" title="Filtros">
        <i class="bi bi-funnel"></i>
      </button>
    </div>

    <!-- Matrix -->
    <div class="matrix-wrap">
      <table class="matrix-table">
        <thead>
          <tr>
            <th style="width: 120px;">Pedido</th>
            <th style="min-width: 220px;">Paciente</th>
            <th style="min-width: 220px;">Médico</th>
            <th style="width: 120px;">Tipo</th>

            <th v-for="i in itemCols" :key="i" class="th-item">
              Item {{ i + 1 }}
            </th>

            <th style="width: 120px; text-align:right;">Ações</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td :colspan="4 + itemCols.length + 1" class="muted td-center">Carregando...</td>
          </tr>

          <tr v-else-if="paginated.length === 0">
            <td :colspan="4 + itemCols.length + 1" class="muted td-center">Nenhum pedido encontrado.</td>
          </tr>

          <tr v-else v-for="p in paginated" :key="p.id">
            <td class="pedido-link" @click="openPedido(p.raw)">{{ p.pedido }}</td>
            <td class="td-ellipsis">{{ p.paciente }}</td>
            <td class="td-ellipsis">{{ p.medico }}</td>
            <td>{{ p.tipo }}</td>

            <!-- MATRIZ: célula compacta -->
            <td v-for="i in itemCols" :key="i" class="td-matrix">
              <template v-if="p.itens[i]">
                <button
                  class="cell-pill"
                  :class="p.itens[i].atrasado ? 'late' : 'ok'"
                  type="button"
                  @click="openPedido(p.raw)"
                  :title="`${p.itens[i].nome} • ${p.itens[i].etapa} • Setor: ${p.itens[i].setor}${p.itens[i].prazo ? ' • Prazo: ' + formatShort(p.itens[i].prazo) : ''}`"
                >
                  <span class="dot"></span>
                  <span class="txt">{{ shortEtapa(p.itens[i].etapa) }}</span>
                  <span v-if="p.itens[i].prazo" class="sub">• {{ formatShort(p.itens[i].prazo) }}</span>
                </button>
              </template>
              <span v-else class="empty">—</span>
            </td>

            <td class="actions">
              <button class="icon-action" type="button" title="Detalhes" @click="openPedido(p.raw)">
                <i class="bi bi-grid-3x3-gap"></i>
              </button>
              <button class="icon-action" type="button" title="Abrir Produção" @click="handleVerPedido(p.raw)">
                <i class="bi bi-box-arrow-up-right"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="matrix-footer">
      <div class="muted">
        Mostrando
        <strong>{{ paginated.length ? (page - 1) * pageSize + 1 : 0 }}</strong>
        a
        <strong>{{ (page - 1) * pageSize + paginated.length }}</strong>
        de
        <strong>{{ filtered.length }}</strong>
        pedidos
      </div>

      <div class="right">
        <div class="page-size">
          <span class="muted">Itens</span>
          <select v-model.number="pageSize" class="page-select">
            <option :value="4">4</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
          </select>
        </div>

        <div class="pager">
          <button class="pager-btn" :disabled="page === 1" @click="prevPage">
            <i class="bi bi-chevron-left"></i>
          </button>
          <span class="pager-text">Página</span>
          <span class="pager-pill">{{ page }}</span>
          <span class="pager-text">de {{ totalPages }}</span>
          <button class="pager-btn" :disabled="page === totalPages" @click="nextPage">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modalOpen" class="modal-mask" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-head">
          <div class="modal-title">
            Pedido <strong>#{{ selectedPedido?.numero_pedido ?? selectedPedido?.id }}</strong> — Itens
          </div>
          <button class="modal-close" type="button" @click="closeModal">×</button>
        </div>

        <div class="modal-body">
          <div v-if="!selectedPedido?.itens?.length" class="muted">Sem itens.</div>

          <div v-else class="modal-list">
            <div v-for="(it, idx) in selectedPedido.itens" :key="idx" class="modal-item">
              <div class="modal-item-top">
                <div class="modal-item-name">
                  {{ it?.produto?.codigo ?? it?.produto?.nome ?? it?.codigo ?? it?.nome ?? `Item ${idx + 1}` }}
                </div>
                <span class="pill">
                  {{ it?.etapa_atual ?? it?.etapa ?? it?.status_producao ?? it?.status ?? '—' }}
                </span>
              </div>
              <div class="modal-item-sub muted">
                Setor: {{ it?.setor_atual ?? it?.setor ?? '—' }}
              </div>
            </div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn-lite" type="button" @click="closeModal">Fechar</button>
          <button class="btn-primary" type="button" @click="handleVerPedido(selectedPedido)">
            Abrir Produção
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.matrix-card{
  background:#ffffff;
  border:1px solid #eef1f6;
  border-radius:16px;
  box-shadow:0 10px 30px rgba(17,24,39,.05);
  overflow:hidden;
}

/* Search */
.matrix-search{
  margin: 14px 16px 10px;
  height: 44px;
  border-radius: 12px;
  border:1px solid #eef1f6;
  background:#f8fafc;
  display:flex;
  align-items:center;
  gap:10px;
  padding: 0 12px;
}
.matrix-search i{ color:#9ca3af; }
.matrix-search-input{
  border:none;
  outline:none;
  background:transparent;
  width:100%;
  font-size:13px;
  color:#374151;
}
.matrix-filter-btn{
  width:36px;height:36px;
  border-radius:10px;
  border:1px solid #eef1f6;
  background:#ffffff;
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
}
.matrix-filter-btn i{ color:#6b7280; }
.matrix-filter-btn:hover{ background:#f6f8fd; }

/* Table */
.matrix-wrap{
  padding: 0 8px 6px;
  overflow-x:auto;
  -webkit-overflow-scrolling: touch;
}
.matrix-table{
  width:100%;
  min-width: 980px;
  border-collapse:separate;
  border-spacing:0;
  font-size:13px;
}
.matrix-table thead th{
  background:#f6f8fd;
  color:#6b7280;
  font-weight:800;
  padding:12px 12px;
  border-top:1px solid #eef1f6;
  border-bottom:1px solid #eef1f6;
  white-space:nowrap;
}
.matrix-table thead th:first-child{
  border-left:1px solid #eef1f6;
  border-top-left-radius:12px;
}
.matrix-table thead th:last-child{
  border-right:1px solid #eef1f6;
  border-top-right-radius:12px;
}

.matrix-table tbody td{
  padding:12px 12px;
  border-bottom:1px solid #eef1f6;
  color:#374151;
  background:#ffffff;
  vertical-align:middle;
}
.matrix-table tbody tr:hover td{
  background:#fbfcff;
}

.th-item{ min-width: 190px; }
.td-matrix{ min-width: 190px; }

.td-center{ text-align:center; }
.td-ellipsis{
  max-width: 260px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.pedido-link{
  color:#0f3d2e;
  font-weight:900;
  cursor:pointer;
}
.pedido-link:hover{ text-decoration:underline; }

.empty{ color:#c0c6d4; }

/* MATRIX CELL: pill compacta */
.cell-pill{
  width:100%;
  display:flex;
  align-items:center;
  gap:8px;
  border-radius:12px;
  padding:8px 10px;
  border:1px solid #eef1f6;
  background:#ffffff;
  cursor:pointer;
  text-align:left;
  min-width:0;
}
.cell-pill:hover{ background:#f6f8fd; }

.cell-pill .dot{
  width:10px;height:10px;border-radius:99px;
  background:#10b981;
  flex:0 0 auto;
}
.cell-pill.late .dot{ background:#ef4444; }

.cell-pill.ok{
  border-color:#d7efe4;
}
.cell-pill.late{
  border-color:#ffd5d2;
}

.cell-pill .txt{
  font-weight:900;
  color:#374151;
  min-width:0;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.cell-pill .sub{
  font-weight:800;
  color:#9ca3af;
  white-space:nowrap;
  flex:0 0 auto;
}

/* Actions */
.actions{
  text-align:right;
  white-space:nowrap;
}
.icon-action{
  width:34px;height:34px;
  border-radius:10px;
  border:1px solid #eef1f6;
  background:#ffffff;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  margin-left:6px;
}
.icon-action i{ color:#6b7280; }
.icon-action:hover{ background:#f6f8fd; }

/* Footer */
.matrix-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:12px 16px 14px;
  flex-wrap:wrap;
}
.muted{ color:#9ca3af; font-size:12px; }
.right{ display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.page-size{ display:flex; align-items:center; gap:8px; }
.page-select{
  height:34px;
  border-radius:10px;
  border:1px solid #eef1f6;
  background:#ffffff;
  padding:0 10px;
  font-size:12px;
  color:#374151;
}
.pager{ display:flex; align-items:center; gap:8px; }
.pager-btn{
  width:34px;height:34px;
  border-radius:10px;
  border:1px solid #eef1f6;
  background:#ffffff;
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
}
.pager-btn:disabled{ opacity:.5; cursor:not-allowed; }
.pager-text{ font-size:12px; color:#9ca3af; }
.pager-pill{
  min-width:34px;
  height:34px;
  padding:0 10px;
  border-radius:10px;
  border:1px solid #eef1f6;
  background:#f6f8fd;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:12px;
  font-weight:900;
  color:#374151;
}

/* Modal */
.modal-mask{
  position:fixed;
  inset:0;
  background: rgba(17,24,39,.35);
  display:flex;
  align-items:center;
  justify-content:center;
  padding:16px;
  z-index: 999;
}
.modal-card{
  width: 760px;
  max-width: 100%;
  background:#ffffff;
  border:1px solid #eef1f6;
  border-radius:16px;
  box-shadow:0 20px 60px rgba(17,24,39,.18);
  overflow:hidden;
}
.modal-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:12px 14px;
  border-bottom:1px solid #eef1f6;
}
.modal-title{ font-weight:900; color:#111827; }
.modal-close{
  width:36px;height:36px;
  border-radius:10px;
  border:1px solid #eef1f6;
  background:#ffffff;
  cursor:pointer;
  font-size:20px;
  line-height:1;
}
.modal-close:hover{ background:#f6f8fd; }
.modal-body{ padding:14px; }
.modal-list{ display:flex; flex-direction:column; gap:10px; }
.modal-item{
  border:1px solid #eef1f6;
  border-radius:14px;
  padding:10px 12px;
}
.modal-item-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.modal-item-name{ font-weight:900; color:#111827; min-width:0; }
.pill{
  font-size:12px;
  font-weight:900;
  background:#f6f8fd;
  border:1px solid #eef1f6;
  border-radius:999px;
  padding:6px 10px;
  color:#374151;
  white-space:nowrap;
}
.modal-item-sub{ margin-top:6px; }
.modal-foot{
  display:flex;
  justify-content:flex-end;
  gap:10px;
  padding:12px 14px;
  border-top:1px solid #eef1f6;
}
.btn-lite{
  height:38px;
  border-radius:12px;
  border:1px solid #eef1f6;
  background:#ffffff;
  padding:0 12px;
  font-weight:900;
  color:#374151;
}
.btn-lite:hover{ background:#f6f8fd; }
.btn-primary{
  height:38px;
  border-radius:12px;
  border:1px solid #0f3d2e;
  background:#0f3d2e;
  padding:0 12px;
  font-weight:900;
  color:#ffffff;
}
.btn-primary:hover{ opacity:.92; }
</style>
