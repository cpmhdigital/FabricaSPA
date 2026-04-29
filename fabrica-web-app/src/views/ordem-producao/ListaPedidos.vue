<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/axios'

type TipoPedido = 'todos' | 'nacional' | 'internacional'
type PerfilUsuario =
  | 'admin'
  | 'operador'
  | 'PCP'
  | 'qualidade_operacional'
  | 'qualidade_inspecao'
  | 'qualidade_liberacao'
  | 'gestor'
  | 'assistente'
  | 'estagiario'


const props = defineProps<{
  status?: string
  mode?: 'pcp' | 'full'
  modalidade?: string
  tipo?: TipoPedido
  perfil?: PerfilUsuario | string
}>()


const emit = defineEmits<{
  (e: 'stats', payload: { total: number }): void
}>()

const mode = computed(() => props.mode ?? 'full')
const router = useRouter()

const loading = ref(false)
const pedidos = ref<any[]>([])

const search = ref('')
const pageSize = ref(4)
const page = ref(1)

/** Somente PCP pode navegar/manipular */
const canManage = computed(() => {
  const role = String(props.perfil ?? '').trim().toLowerCase()
  return role === 'pcp' || role === 'admin'
})


function handleVerPedido(pedido: any) {
  if (!canManage.value) return

  if (pedido.status === 'aprovado' || props.status === 'aprovado') {
    router.push({ name: 'pedido-producao', state: { id: pedido.id } })
  } else {
    router.push({ name: 'pedido-detalhe', state: { id: pedido.id } })
  }
}

function handleEditar(pedido: any) {
  if (!canManage.value) return
  handleVerPedido(pedido)
}

/** Normaliza o dado para o layout do print */
function normalize(p: any) {
  const codigoPedido = p.numero_pedido ?? p.id ?? '—'
  const paciente = p.paciente ?? p.cliente_paciente ?? p.nome_paciente ?? '—'
  const responsavel = p.responsavel ?? p.doutor ?? p.medico ?? '—'

  // tipo do pedido (vem do próprio pedido)
  const tipoRaw = String(p.tipo ?? p.tipo_pedido ?? '').trim().toLowerCase()
  const tipo =
    tipoRaw.includes('inter') ? 'internacional'
      : tipoRaw.includes('nac') ? 'nacional'
        : (p.tipo ?? p.tipo_pedido ?? '—')

  // itens do pedido (snake_case ou camelCase)
  const itens = (p.pedido_itens ?? p.pedidoItens ?? []) as any[]

  // pega todas as descrições de produto
  const descricoes = itens
    .map(i => i?.produto?.descricao ?? i?.produto?.nome ?? '')
    .map((s: any) => String(s).trim())
    .filter(Boolean)

  // normaliza para chaves padrão
  const modalidades = Array.from(new Set(
    descricoes.map((desc) => {
      const raw = desc.toLowerCase()
      if (raw.includes('custom')) return 'customlife'
      if (raw.includes('ancor')) return 'ancorfix'
      if (raw.includes('atm')) return 'atm'
      return raw // fallback
    })
  ))

  // texto para exibir na tabela (se tiver 2+ modalidades, mostra "customlife, atm")
  const modalidadeTxt =
    modalidades.length === 0 ? '—'
      : modalidades.length === 1 ? modalidades[0]
        : modalidades.join(', ')

  const prazoRaw = p.prazo ?? p.data_entrega ?? p.data_prazo ?? null
  const prazo = prazoRaw ? new Date(prazoRaw) : null

  return {
    id: p.id,
    pedido: String(codigoPedido),
    paciente,
    responsavel,
    tipo,
    modalidades,     // <- usado no filtro
    modalidadeTxt,   // <- usado na tabela
    prazo,
    raw: p,
  }
}

const carregarPedidos = async () => {
  loading.value = true
  try {
    const modalidadeParam =
      props.modalidade && props.modalidade !== 'todas' ? props.modalidade : undefined
    const tipoParam = props.tipo && props.tipo !== 'todos' ? props.tipo : undefined

    const { data } = await api.get('/api/pedidos', {
      params: {
        status: props.status ?? undefined,
        modalidade: modalidadeParam,
        tipo: tipoParam,
      },
    })

    pedidos.value = (data || []).map((p: any) => normalize(p))
    page.value = 1
  } catch (error) {
    console.error('Erro ao buscar pedidos:', error)
  } finally {
    loading.value = false
  }
}

/** Filtro local (funciona mesmo se backend não filtrar ainda) */
const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()

  const modalidadeAtiva =
    props.modalidade && props.modalidade !== 'todas' ? String(props.modalidade).toLowerCase() : null

  const tipoAtivo = props.tipo && props.tipo !== 'todos' ? String(props.tipo).toLowerCase() : null

  return pedidos.value.filter((p) => {
    // filtro por modalidade (local)
    const passaModalidade = !modalidadeAtiva
      ? true
      : Array.isArray(p.modalidades) && p.modalidades.some((m: string) => m.toLowerCase() === modalidadeAtiva)

    // filtro por tipo (local)
    const passaTipo = !tipoAtivo ? true : String(p.tipo).toLowerCase().includes(tipoAtivo)

    if (!passaModalidade || !passaTipo) return false

    // filtro por texto (search)
    if (!q) return true

    const prazoTxt = p.prazo ? formatDate(p.prazo).toLowerCase() : ''

    return (
      p.pedido.toLowerCase().includes(q) ||
      String(p.paciente).toLowerCase().includes(q) ||
      String(p.responsavel).toLowerCase().includes(q) ||
      String(p.modalidadeTxt).toLowerCase().includes(q) ||
      String(p.tipo).toLowerCase().includes(q) ||
      prazoTxt.includes(q)
    )

  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize.value)))

const paginated = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return filtered.value.slice(start, start + pageSize.value)
})

function emitStats() {
  emit('stats', { total: filtered.value.length })
}

watch(
  [() => props.status, () => props.modalidade, () => props.tipo],
  async () => {
    await carregarPedidos()
    emitStats()
  },
  { immediate: true },
)

watch([filtered, pageSize], () => {
  page.value = 1
  emitStats()
})

function prevPage() {
  if (page.value > 1) page.value--
}
function nextPage() {
  if (page.value < totalPages.value) page.value++
}

function formatDate(d: Date) {
  const day = String(d.getDate()).padStart(2, '0')
  const month = d.toLocaleDateString('pt-BR', { month: 'long' })
  const year = d.getFullYear()
  const monthCap = month.charAt(0).toUpperCase() + month.slice(1)
  return `${day} ${monthCap} ${year}`
}
</script>

<template>
  <div class="pcp-card">
    <!-- Search -->
    <div class="pcp-search">
      <i class="bi bi-search"></i>
      <input v-model="search" class="pcp-search-input" type="text"
        placeholder="Buscar pedido, paciente, médico, modalidade..." />
      <button class="pcp-filter-btn" type="button" title="Filtros" disabled>
        <i class="bi bi-funnel"></i>
      </button>
    </div>

    <!-- Table -->
    <div class="pcp-table-wrap">
      <table class="pcp-table">
        <thead>
          <tr>
            <th style="width: 110px">Pedido</th>

            <th v-if="mode === 'full'">Paciente</th>
            <th v-if="mode === 'full'">Médico</th>
            <th v-if="mode === 'full'" style="width: 140px">Modalidade</th>
            <th v-if="mode === 'full'" style="width: 110px">Tipo</th>
            <th v-if="mode === 'full'" style="width: 150px">Prazo</th>

            <th style="width: 110px; text-align: right">Ações</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td :colspan="mode === 'full' ? 7 : 2" class="pcp-muted">Carregando...</td>
          </tr>

          <tr v-else-if="paginated.length === 0">
            <td :colspan="mode === 'full' ? 7 : 2" class="pcp-muted">Nenhum pedido encontrado.</td>
          </tr>

          <tr v-else v-for="row in paginated" :key="row.id">
            <!-- Pedido: só clicável se PCP -->
            <td :class="canManage ? 'pcp-link' : 'pcp-link-disabled'" @click="canManage && handleVerPedido(row.raw)"
              :title="canManage ? 'Abrir pedido' : 'Somente PCP pode abrir/manipular'">
              {{ row.pedido }}
            </td>

            <td v-if="mode === 'full'">{{ row.paciente }}</td>
            <td v-if="mode === 'full'">{{ row.responsavel }}</td>
            <td v-if="mode === 'full'">{{ row.modalidadeTxt }}</td>
            <td v-if="mode === 'full'">{{ row.tipo }}</td>

            <td v-if="mode === 'full'">{{ row.prazo ? formatDate(row.prazo) : '—' }}</td>

            <td class="pcp-actions">
              <button class="icon-action" type="button" title="Ver" @click="handleVerPedido(row.raw)"
                :disabled="!canManage">
                <i class="bi bi-search"></i>
              </button>

              <button v-if="canManage" class="icon-action" type="button" title="Editar" @click="handleEditar(row.raw)">
                <i class="bi bi-pencil-square"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer/Pagination -->
    <div class="pcp-footer">
      <div class="pcp-footer-left">
        <span class="pcp-muted">
          Mostrando
          <strong>{{ paginated.length ? (page - 1) * pageSize + 1 : 0 }}</strong>
          a
          <strong>{{ (page - 1) * pageSize + paginated.length }}</strong>
          de
          <strong>{{ filtered.length }}</strong>
          pedidos
        </span>
      </div>

      <div class="pcp-footer-right">
        <div class="page-size">
          <span class="pcp-muted">Itens</span>
          <select v-model.number="pageSize" class="page-select">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
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
  </div>
</template>

<style scoped>
.pcp-card {
  background: #ffffff;
  border: 1px solid #eef1f6;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(17, 24, 39, 0.05);
  overflow: hidden;
}

.pcp-search {
  margin: 0 16px 12px;
  height: 42px;
  border-radius: 12px;
  border: 1px solid #eef1f6;
  background: #f8fafc;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 12px;
}

.pcp-search i {
  color: #9ca3af;
}

.pcp-search-input {
  border: none;
  outline: none;
  background: transparent;
  width: 100%;
  font-size: 13px;
  color: #374151;
}

.pcp-filter-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #eef1f6;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: not-allowed;
  opacity: 0.7;
}

.pcp-filter-btn i {
  color: #6b7280;
}

.pcp-table-wrap {
  padding: 0 8px 6px;
}

.pcp-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 13px;
}

.pcp-table thead th {
  background: #f6f8fd;
  color: #6b7280;
  font-weight: 800;
  padding: 12px 12px;
  border-top: 1px solid #eef1f6;
  border-bottom: 1px solid #eef1f6;
}

.pcp-table thead th:first-child {
  border-left: 1px solid #eef1f6;
  border-top-left-radius: 12px;
}

.pcp-table thead th:last-child {
  border-right: 1px solid #eef1f6;
  border-top-right-radius: 12px;
}

.pcp-table tbody td {
  padding: 12px 12px;
  border-bottom: 1px solid #eef1f6;
  color: #374151;
  background: #ffffff;
}

.pcp-table tbody tr:hover td {
  background: #fbfcff;
}

.pcp-link {
  color: #0f3d2e;
  font-weight: 900;
  cursor: pointer;
}

.pcp-link:hover {
  text-decoration: underline;
}

.pcp-link-disabled {
  color: #6b7280;
  font-weight: 800;
  cursor: default;
}

.pcp-actions {
  text-align: right;
  white-space: nowrap;
}

.icon-action {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #eef1f6;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  margin-left: 6px;
}

.icon-action i {
  color: #6b7280;
}

.icon-action:hover {
  background: #f6f8fd;
}

.icon-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 12px;
  border: 1px solid transparent;
}

.status-pill i {
  font-size: 13px;
}

.status-ok {
  background: #e9f5ef;
  color: #0f3d2e;
  border-color: #d7efe4;
}

.status-late {
  background: #ffe9e7;
  color: #b42318;
  border-color: #ffd5d2;
}

.pcp-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px 14px;
}

.pcp-muted {
  color: #9ca3af;
  font-size: 12px;
}

.pcp-footer-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.page-size {
  display: flex;
  align-items: center;
  gap: 8px;
}

.page-select {
  height: 34px;
  border-radius: 10px;
  border: 1px solid #eef1f6;
  background: #ffffff;
  padding: 0 10px;
  font-size: 12px;
  color: #374151;
}

.pager {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pager-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #eef1f6;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.pager-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pager-text {
  font-size: 12px;
  color: #9ca3af;
}

.pager-pill {
  min-width: 34px;
  height: 34px;
  padding: 0 10px;
  border-radius: 10px;
  border: 1px solid #eef1f6;
  background: #f6f8fd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 900;
  color: #374151;
}
</style>
