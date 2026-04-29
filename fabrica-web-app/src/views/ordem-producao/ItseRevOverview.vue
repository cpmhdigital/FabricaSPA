<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import TabelaBase from '@/components/TabelaBase.vue'
import ModalBase from '@/components/ModalBase.vue'
import api from '@/services/axios'
import Swal from 'sweetalert2'

interface ItRev {
  id: number
  nome: string
  url: string
  it_id_original?: number | null
  versao?: string
  versoes?: ItRev[]
}

const router = useRouter()

const itRevs = ref<ItRev[]>([])
const filtro = ref('')
const paginaAtual = ref(1)
const itensPorPagina = 5

const carregando = ref(true)
const erro = ref('')
const mostrarModal = ref(false)
const modoEdicao = ref<'novo' | 'nova-versao'>('novo')
const nomeAnterior = ref('')
const novoItRev = ref<Partial<ItRev>>({ nome: '', url: '' })
const versoesExistentes = ref<ItRev[]>([])
let idOriginal: number | null = null

function mostrarToast(msg: string, tipo: 'success' | 'error') {
  Swal.fire({
    icon: tipo,
    title: msg,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  })
}

const agruparITs = (lista: ItRev[]) => {
  const mapa = new Map<number, ItRev[]>()

  lista.forEach((it) => {
    const chave = it.it_id_original || it.id
    if (!mapa.has(chave)) mapa.set(chave, [])
    mapa.get(chave)!.push(it)
  })

  const agrupados: ItRev[] = []
  mapa.forEach((grupo) => {
    grupo.sort((a, b) => {
      const vA = parseFloat(a.versao?.replace('v', '') || '1')
      const vB = parseFloat(b.versao?.replace('v', '') || '1')
      return vA - vB
    })
    agrupados.push(grupo[grupo.length - 1]) // última versão
  })

  return agrupados
}

const carregarIts = async () => {
  try {
    carregando.value = true
    erro.value = ''

    const response = await api.get('/api/itrev')
    itRevs.value = agruparITs(response.data)
  } catch (error: any) {
    console.error('Erro ao carregar ITs:', error.response?.data || error)
    erro.value = 'Erro ao buscar ITs. Tente novamente mais tarde.'
    mostrarToast(erro.value, 'error')
  } finally {
    carregando.value = false
  }
}

onMounted(carregarIts)

function limparFiltro() {
  filtro.value = ''
  paginaAtual.value = 1
}

const adicionarItRev = () => {
  novoItRev.value = { nome: '', url: '' }
  modoEdicao.value = 'novo'
  nomeAnterior.value = ''
  idOriginal = null
  mostrarModal.value = true
}

const abrirItRev = (it: ItRev) => {
  router.push({ name: 'HistoricoItRev', params: { id: it.id } })
}

const criarNovaVersao = async (it: ItRev) => {
  novoItRev.value = { nome: it.nome, url: it.url }
  nomeAnterior.value = it.nome
  modoEdicao.value = 'nova-versao'
  idOriginal = it.it_id_original || it.id

  const response = await api.get(`/api/itrev/${idOriginal}/versoes`)
  versoesExistentes.value = response.data

  mostrarModal.value = true
}

const fecharModal = () => {
  mostrarModal.value = false
}

const gerarNovaVersao = () => {
  if (!versoesExistentes.value.length) return 'v1.0'
  const ultimaVersao = versoesExistentes.value[versoesExistentes.value.length - 1].versao || 'v1.0'
  const numero = parseFloat(ultimaVersao.replace('v', '')) + 0.1
  return 'v' + numero.toFixed(1)
}

const salvarItRev = async () => {
  try {
    const payload: Partial<ItRev> = {
      nome: novoItRev.value.nome,
      url: novoItRev.value.url,
    }

    if (modoEdicao.value === 'nova-versao') {
      payload.it_id_original = idOriginal!
      payload.versao = gerarNovaVersao()
    }

    await api.post('/api/itrev', payload)
    mostrarToast('IT salva com sucesso!', 'success')
    fecharModal()
    await carregarIts()
  } catch (error) {
    console.error(error)
    mostrarToast('Erro ao salvar IT.', 'error')
  }
}

const excluirItRev = async (id: number) => {
  const confirmacao = await Swal.fire({
    title: 'Tem certeza?',
    text: 'Deseja excluir esta IT/REV e todas as versões?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar',
  })

  if (!confirmacao.isConfirmed) return

  try {
    await api.delete(`/api/itrev/${id}`)
    Swal.fire('Sucesso', 'IT/REV e todas as versões foram apagadas', 'success')
    await carregarIts()
  } catch (error) {
    console.error(error)
    Swal.fire('Erro', 'Falha ao apagar IT/REV', 'error')
  }
}

const itensFiltrados = computed(() => {
  const q = filtro.value.trim().toLowerCase()
  if (!q) return itRevs.value
  return itRevs.value.filter((it) => it.nome.toLowerCase().includes(q))
})

const itensPaginados = computed(() => {
  const start = (paginaAtual.value - 1) * itensPorPagina
  return itensFiltrados.value.slice(start, start + itensPorPagina)
})

const colunas = [
  { headerName: '#', field: 'id', width: 80, cellClass: 'text-muted text-center' },
  { headerName: 'Nome', field: 'nome', flex: 1, cellClass: 'fw-semibold', tooltipField: 'nome' },
  {
    headerName: 'Versão',
    field: 'versao',
    width: 110,
    cellClass: 'text-center fw-semibold text-primary',
    valueFormatter: (params: any) => params.value || 'v1.0',
  },
  {
    headerName: 'Arquivo',
    field: 'url',
    flex: 2,
    cellRenderer: (params: any) => {
      if (!params.value) return `<span class="text-muted">—</span>`
      return `
        <a href="${params.value}" target="_blank" rel="noopener noreferrer"
          class="badge text-bg-success">
           <i class="bi bi-box-arrow-up-right me-1"></i> Acessar
        </a>
      `
    },
    cellClass: 'text-center',
  },
  {
    headerName: 'Ações',
    field: 'id',
    width: 260,
    cellRenderer: () => {
      return `
        <div class="d-flex justify-content-center gap-2 py-1">
          <button class="btn btn-sm btn-outline-primary" data-action="abrir">
            <i class="bi bi-eye me-1" data-action="abrir"></i> Ver
          </button>

          <button class="btn btn-sm btn-primary" data-action="nova-versao">
            <i class="bi bi-plus-circle me-1" data-action="nova-versao"></i> Versão
          </button>

          <button class="btn btn-sm btn-outline-danger" data-action="excluir" title="Excluir">
            <i class="bi bi-trash3" data-action="excluir"></i>
          </button>
        </div>
      `
    },
    cellClass: 'text-center',
    onCellClicked: function (params: any) {
      const action = params.event?.target?.getAttribute('data-action')
      if (action === 'abrir') abrirItRev(params.data)
      else if (action === 'nova-versao') criarNovaVersao(params.data)
      else if (action === 'excluir') excluirItRev(params.data.id)
    },
  },
]
</script>

<template>
  <BaseLayout
    titulo="Gerenciamento IT/REV"
    descricao="Uploads e versionamento de IT/REV"
    textoBotao="Novo IT/Rev"
    iconeBotao="bi bi-plus"
    semCard
    @novo="adicionarItRev"
  >
    <!-- Toolbar -->
    <div class="card card-clear mb-3">
      <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="input-group input-group-sm search-clear" style="max-width: 520px; width: 100%;">
            <span class="input-group-text bg-white">
              <i class="bi bi-search"></i>
            </span>
            <input
              type="text"
              v-model="filtro"
              class="form-control"
              placeholder="Pesquisar IT/Rev..."
            />
            <button class="btn btn-light" type="button" @click="limparFiltro" :disabled="!filtro">
              Limpar
            </button>
          </div>

          <div class="text-muted small">
            <span class="fw-semibold">{{ itensFiltrados.length }}</span> registros
          </div>
        </div>
      </div>
    </div>

    <!-- Estados -->
    <div v-if="carregando" class="alert alert-info">
      Carregando...
    </div>

    <div v-else-if="erro" class="alert alert-danger d-flex align-items-center justify-content-between">
      <div>{{ erro }}</div>
      <button class="btn btn-light btn-sm" type="button" @click="carregarIts">Tentar novamente</button>
    </div>

    <!-- Mobile -->
    <div v-else class="d-block d-md-none">
      <div v-for="item in itensPaginados" :key="item.id" class="card card-clear mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold text-dark">{{ item.nome }}</div>
              <div class="text-muted small">
                Versão: <span class="fw-semibold text-primary">{{ item.versao || 'v1.0' }}</span>
              </div>
            </div>

            <a
              v-if="item.url"
              :href="item.url"
              target="_blank"
              rel="noopener noreferrer"
              class="badge text-bg-success"
            >
              <i class="bi bi-box-arrow-up-right me-1"></i> Acessar
            </a>
            <span v-else class="badge text-bg-light border text-muted">Sem arquivo</span>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-sm btn-outline-primary w-100" type="button" @click="abrirItRev(item)">
              Ver
            </button>

            <button class="btn btn-sm btn-primary w-100" type="button" @click="criarNovaVersao(item)">
              Nova versão
            </button>

            <button
              class="btn btn-sm btn-outline-danger"
              type="button"
              @click="excluirItRev(item.id)"
              title="Excluir"
            >
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop -->
    <div v-else class="d-none d-md-block">
      <TabelaBase
        :rowData="itensFiltrados"
        :columnDefs="colunas"
        :pagination="true"
        :paginationPageSize="12"
      />
    </div>

    <!-- Modal -->
    <ModalBase
      v-model:show="mostrarModal"
      :title="modoEdicao === 'nova-versao' ? 'Criar nova versão de IT' : 'Novo IT/Rev'"
      size="md"
      @save="salvarItRev"
      @cancel="fecharModal"
    >
      <template #default>
        <div v-if="modoEdicao === 'nova-versao'" class="alert alert-info py-2">
          Criando nova versão de: <strong>{{ nomeAnterior }}</strong>
        </div>

        <div class="mb-3">
          <label for="nome" class="form-label fw-semibold">Nome da IT</label>
          <input
            id="nome"
            v-model="novoItRev.nome"
            type="text"
            class="form-control input-clear"
            placeholder="Digite o nome da IT"
            required
          />
        </div>

        <div class="mb-2">
          <label for="url" class="form-label fw-semibold">URL do documento</label>
          <input
            id="url"
            v-model="novoItRev.url"
            type="url"
            class="form-control input-clear"
            placeholder="https://exemplo.com/documento.pdf"
            required
          />
          <small class="text-muted">Insira uma URL válida (PDF, imagem, etc.).</small>
        </div>
      </template>

      <template #footer>
        <button type="button" class="btn btn-success rounded-3 px-4" @click="salvarItRev">
          <i class="bi bi-check-circle me-2"></i> Salvar
        </button>
      </template>
    </ModalBase>
  </BaseLayout>
</template>

<style scoped>
/* Clear UI */
.card-clear {
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 14px;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
  background: #fff;
}

.search-clear .input-group-text {
  border-right: 0;
}
.search-clear .form-control {
  border-left: 0;
}

.input-clear {
  border-radius: 10px;
  border: 1px solid rgba(0, 0, 0, 0.12);
}
</style>
