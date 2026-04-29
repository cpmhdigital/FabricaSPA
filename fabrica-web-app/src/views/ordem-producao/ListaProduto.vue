<template>
  <BaseLayout
    titulo="Gerenciamento de Produtos"
    descricao="Visualize e gerencie produtos e seus componentes de forma hierárquica."
    semCard
  >
    <div class="container py-4">
      <div class="row g-3">
        <!-- 🔍 FILTROS LATERAIS -->
        <aside class="col-lg-3">
          <div class="filtros-arvore p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="fw-semibold">Filtros</div>
              <button class="btn btn-light btn-sm" type="button" @click="limparFiltros">
                Limpar
              </button>
            </div>

            <FiltroLista
              titulo="Fluxos"
              :itens="fluxos"
              v-model="fluxosSelecionados"
              item-label="nome_fluxo"
            />

            <div class="my-3"></div>

            <FiltroLista
              titulo="Matérias-Primas"
              :itens="materiasPrimas"
              v-model="materiasSelecionadas"
              item-label="descricao"
            />

            <button
              class="btn btn-outline-success w-100 mt-3 rounded-3"
              data-bs-toggle="modal"
              data-bs-target="#modalCloneProduto"
              type="button"
            >
              <i class="bi bi-files me-2"></i> Clonar Produto
            </button>
          </div>
        </aside>

        <!-- 📦 CONTEÚDO PRINCIPAL -->
        <main class="col-lg-9">
          <!-- 🔹 HEADER: ABAS + AÇÕES -->
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="nav nav-pills pills-clear">
              <button
                v-for="opcao in opcoesFiltro"
                :key="opcao"
                class="nav-link"
                :class="{ active: abaAtiva === opcao }"
                type="button"
                @click="abaAtiva = opcao"
              >
                {{ opcao }}
                <span
                  v-if="opcao === 'Não cadastrados' && pendentesCount"
                  class="badge rounded-pill text-bg-danger ms-2"
                >
                  {{ pendentesCount }}
                </span>
              </button>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button
                v-if="abaAtiva !== 'Não cadastrados'"
                class="btn btn-success btn-sm px-3 rounded-3"
                :data-bs-target="modalAdicionarTarget"
                data-bs-toggle="modal"
                type="button"
              >
                <i class="bi bi-plus-circle me-2"></i>
                Adicionar
              </button>

              <button
                v-else
                class="btn btn-outline-danger btn-sm px-3 rounded-3"
                @click="carregarPendentes"
                :disabled="carregandoPendentes"
                type="button"
              >
                <span v-if="carregandoPendentes" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-arrow-clockwise me-2"></i>
                Atualizar
              </button>
            </div>
          </div>

          <!-- 🔹 TOOLBAR: BUSCA + CONTADOR -->
          <div class="card card-clear mb-3">
            <div class="card-body py-3">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="input-group input-group-sm search-clear" style="max-width: 540px; width: 100%;">
                  <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                  </span>
                  <input
                    v-model="filtro"
                    type="text"
                    class="form-control"
                    :placeholder="`Buscar ${abaAtiva.toLowerCase()}...`"
                  />
                </div>

                <div class="text-muted small">
                  <span class="fw-semibold">{{ itensFiltrados.length }}</span> itens exibidos
                </div>
              </div>
            </div>
          </div>

          <!-- 🔹 LOADING -->
          <div v-if="carregando" class="alert alert-info">
            Carregando itens...
          </div>

          <!-- 🔹 LISTA PRINCIPAL -->
          <ListaItensComposicao
            v-if="!carregando && itensFiltrados.length"
            :itens="itensFiltrados"
            :titulo="abaAtiva"
            :modalEditar="modalEditar(abaAtiva)"
            :modalVisualizar="modalVisualizar(abaAtiva)"
            :classeHeader="classeHeader(abaAtiva)"
            @selecionar="handleSelecionarItem"
          />

          <div v-if="!carregando && !itensFiltrados.length" class="alert alert-warning text-center">
            Nenhum {{ abaAtiva.toLowerCase() }} encontrado.
          </div>
        </main>
      </div>
    </div>

    <!-- 🔹 MODAIS -->
    <ModalCloneProduto />
    <ModalComponente />
    <ModalProduto />
    <ModalParafuso />
    <ModalSemiacabado />
    <ModalEmbalagens tipo="Embalagem" />
    <ModalInsumos />

    <!-- ✅ MODAL FINALIZAR CADASTRO -->
    <ModalFinalizarCadastro
      :item="itemSelecionado"
      :fluxos="fluxos"
      @salvo="handleSalvoFinalizar"
    />
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/services/axios'

import BaseLayout from '@/components/BaseLayout.vue'
import FiltroLista from '@/components/FiltroLista.vue'
import ListaItensComposicao from '@/components/ListaItensComposicao.vue'

import ModalCloneProduto from '@/components/modals/ModalCloneProduto.vue'
import ModalComponente from '@/components/modals/ModalComponente.vue'
import ModalProduto from '@/components/modals/ModalProduto.vue'
import ModalParafuso from '@/components/modals/ModalParafuso.vue'
import ModalSemiacabado from '@/components/modals/ModalSemiacabado.vue'
import ModalEmbalagens from '@/components/modals/ModalEmbalagens.vue'
import ModalInsumos from '@/components/modals/ModalInsumos.vue'
import ModalFinalizarCadastro from '@/components/modals/ModalFinalizarCadastro.vue'

type Aba =
  | 'Produtos'
  | 'Componentes'
  | 'Parafusos'
  | 'Semiacabados'
  | 'Insumos'
  | 'Embalagens'
  | 'Não cadastrados'

interface Fluxo {
  id: number
  nome_fluxo: string
}

interface MateriaPrima {
  id: number
  descricao: string
}

interface Item {
  id: number
  descricao: string
  codigo: string
  anvisa?: string | null
  tipo: string
  fluxo: Fluxo | null
  filhos: Item[]
}

interface FiltrosResponse {
  materias_primas: MateriaPrima[]
  fluxo: Fluxo[]
}

const opcoesFiltro: Aba[] = [
  'Produtos',
  'Componentes',
  'Parafusos',
  'Semiacabados',
  'Insumos',
  'Embalagens',
  'Não cadastrados',
]

const abaAtiva = ref<Aba>('Produtos')
const filtro = ref<string>('')

const produtos = ref<Item[]>([])
const carregando = ref<boolean>(false)

const pendentes = ref<Item[]>([])
const carregandoPendentes = ref<boolean>(false)

const materiasPrimas = ref<MateriaPrima[]>([])
const materiasSelecionadas = ref<number[]>([])
const fluxos = ref<Fluxo[]>([])
const fluxosSelecionados = ref<number[]>([])

const itemSelecionado = ref<Item | null>(null)

onMounted(async () => {
  await carregarFiltros()
  await carregarProdutos()
  await carregarPendentes()
})

async function carregarFiltros() {
  try {
    const { data } = await api.get<FiltrosResponse>('/api/itens/filtros')
    materiasPrimas.value = data.materias_primas || []
    fluxos.value = data.fluxo || []
    fluxosSelecionados.value = fluxos.value.map((f) => f.id)
  } catch (error) {
    console.error('Erro ao carregar filtros:', error)
  }
}

async function carregarProdutos() {
  carregando.value = true
  try {
    const { data } = await api.get<Item[]>('/api/itens')
    produtos.value = (data || []).map(formatarProduto)
  } catch (error) {
    console.error('Erro ao carregar produtos:', error)
  } finally {
    carregando.value = false
  }
}

async function carregarPendentes() {
  carregandoPendentes.value = true
  try {
    const { data } = await api.get<Item[]>('/api/itens/pendentes')
    pendentes.value = (data || []).map(formatarProduto)
  } catch (error) {
    console.error('Erro ao carregar pendentes:', error)
    pendentes.value = []
  } finally {
    carregandoPendentes.value = false
  }
}

function formatarProduto(item: Item): Item {
  return {
    id: item.id,
    descricao: item.descricao,
    codigo: item.codigo,
    anvisa: item.anvisa ?? null,
    tipo: item.tipo,
    fluxo: item.fluxo ? { id: item.fluxo.id, nome_fluxo: item.fluxo.nome_fluxo } : null,
    filhos: (item.filhos || []).map(formatarProduto),
  }
}

function limparFiltros() {
  filtro.value = ''
  materiasSelecionadas.value = []
  fluxosSelecionados.value = fluxos.value.map((f) => f.id)
}

function contemMateriaPrima(item: Item, selecionadas: number[]): boolean {
  if (item.tipo === 'materia_prima' && selecionadas.includes(item.id)) return true
  return item.filhos?.some((f) => contemMateriaPrima(f, selecionadas)) ?? false
}

function extrairTipo(itens: Item[], tipo: string): Item[] {
  return itens.flatMap((item) => [
    ...(item.tipo === tipo ? [item] : []),
    ...(item.filhos?.length ? extrairTipo(item.filhos, tipo) : []),
  ])
}

function isPendente(i: Item): boolean {
  const tipo = (i.tipo || '').trim().toUpperCase()
  const anvisa = (i.anvisa || '').trim().toUpperCase()
  return tipo === 'PENDENTE' || anvisa === 'PENDENTE'
}

function uniqueBy<T>(arr: T[], keyFn: (x: T) => string | number): T[] {
  const map = new Map<string | number, T>()
  for (const item of arr) map.set(keyFn(item), item)
  return [...map.values()]
}

const pendentesCount = computed(() => pendentes.value.filter(isPendente).length)

const itensFiltrados = computed<Item[]>(() => {
  const busca = filtro.value.trim().toLowerCase()

  // ✅ ABA: NÃO CADASTRADOS
  if (abaAtiva.value === 'Não cadastrados') {
    const base = pendentes.value
      .filter(isPendente)
      .filter((i) => {
        if (!busca) return true
        return (
          i.descricao?.toLowerCase().includes(busca) ||
          i.codigo?.toLowerCase().includes(busca)
        )
      })

    return uniqueBy(base, (i) => i.id)
  }

  const tipoMap: Record<Exclude<Aba, 'Não cadastrados'>, string> = {
    Produtos: 'produto',
    Componentes: 'componente',
    Parafusos: 'parafuso',
    Semiacabados: 'semiacabado',
    Insumos: 'insumo',
    Embalagens: 'embalagem',
  }

  const tipoAtual = tipoMap[abaAtiva.value as Exclude<Aba, 'Não cadastrados'>]

  // base do seu código (pode repetir em componentes/parafusos)
  const base =
    ['Componentes', 'Parafusos'].includes(abaAtiva.value)
      ? extrairTipo(produtos.value, tipoAtual)
      : produtos.value.filter((p) => p.tipo === tipoAtual)

  // ✅ remove repetição nessas abas
  const todosItens =
    ['Componentes', 'Parafusos'].includes(abaAtiva.value)
      ? uniqueBy(base, (i) => i.id)
      : base

  return todosItens.filter((p) => {
    const nomeValido =
      p.descricao?.toLowerCase().includes(busca) ||
      p.codigo?.toLowerCase().includes(busca)

    const mpValido =
      !materiasSelecionadas.value.length ||
      contemMateriaPrima(p, materiasSelecionadas.value)

    const fluxoValido =
      !fluxosSelecionados.value.length ||
      (p.fluxo?.id && fluxosSelecionados.value.includes(p.fluxo.id))

    return nomeValido && mpValido && fluxoValido
  })
})

function modalEditar(aba: Aba): string {
  const map: Record<Aba, string> = {
    Produtos: '#modalProduto',
    Componentes: '#modalComponente',
    Parafusos: '#modalParafuso',
    Semiacabados: '#modalSemiacabado',
    Insumos: '#modalInsumos',
    Embalagens: '#modalEmbalagens',
    'Não cadastrados': '#modalFinalizarCadastro',
  }
  return map[aba]
}

function modalVisualizar(aba: Aba): string {
  const map: Record<Aba, string> = {
    Produtos: '#modalVisualizarProduto',
    Componentes: '#modalVisualizarComponente',
    Parafusos: '#modalVisualizarParafuso',
    Semiacabados: '#modalVisualizarSemiacabado',
    Insumos: '#modalVisualizarInsumos',
    Embalagens: '#modalVisualizarEmbalagens',
    'Não cadastrados': '#modalFinalizarCadastro',
  }
  return map[aba]
}

function classeHeader(aba: Aba): string {
  if (aba === 'Não cadastrados') return 'bg-danger'
  return aba === 'Produtos' ? 'bg-success' : 'bg-primary'
}

const modalAdicionarTarget = computed(() => modalEditar(abaAtiva.value))

function handleSelecionarItem(item: Item) {
  if (isPendente(item)) {
    abrirFinalizarCadastro(item)
    return
  }
  itemSelecionado.value = item
}

function abrirFinalizarCadastro(item: Item) {
  itemSelecionado.value = item
  const el = document.getElementById('modalFinalizarCadastro')
  // @ts-expect-error bootstrap global
  const modal = el ? new bootstrap.Modal(el) : null
  modal?.show()
}

async function handleSalvoFinalizar() {
  await carregarPendentes()
  await carregarProdutos()
  abaAtiva.value = 'Produtos'
}
</script>

<style scoped>
/* cards mais leves */
.card-clear {
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
  box-shadow: 0 1px 8px rgba(0,0,0,.04);
}

/* pills discretos */
.pills-clear .nav-link {
  border-radius: 999px;
  padding: .35rem .75rem;
  color: #334155;
  background: #f1f5f9;
  border: 1px solid rgba(0,0,0,.04);
}

.pills-clear .nav-link.active {
  background: #0f766e;
  border-color: #0f766e;
  color: #fff;
}

/* sidebar clean */
.filtros-arvore {
  position: sticky;
  top: 100px;
  max-height: 80vh;
  overflow-y: auto;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
  box-shadow: 0 1px 8px rgba(0,0,0,.04);
}

/* search clean */
.search-clear .input-group-text {
  border-right: 0;
}
.search-clear .form-control {
  border-left: 0;
}
</style>
