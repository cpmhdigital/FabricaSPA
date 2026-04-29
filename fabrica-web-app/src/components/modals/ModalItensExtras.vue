<template>
  <div class="modal fade" id="ModalItensExtras" tabindex="-1" aria-hidden="true" ref="modalEl">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">

        <!-- Cabeçalho -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="bi bi-info-circle me-2"></i>
            Pedido para Produzir
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <!-- Abas dos produtos (rolável, touch-friendly) -->
        <div class="px-3 pt-3 pb-0">
          <div class="nav-tabs-scroll-wrapper">
            <ul class="nav nav-tabs flex-nowrap">
              <li class="nav-item" v-for="(prod, index) in itensPedido" :key="prod.id">
                <button class="nav-link mb-1" :class="{ active: abaSelecionada === index }"
                  @click="abaSelecionada = index" type="button">
                  <small class="fw-semibold d-block">{{ prod.codigo }}</small>
                  <small class="fw-semibold d-block">{{ prod.descricao }}</small>
                </button>
              </li>
            </ul>
          </div>
        </div>

        <div v-if="itensPedido.length" class="px-3 mb-4">

          <!-- Quantidade do Produto Principal -->
          <div class="d-flex align-items-center gap-3 mb-3">

            <!-- Código e descrição -->
            <div class="flex-grow-1">
              <div class="fw-semibold">
                {{ itensPedido[abaSelecionada].codigo }} — {{ itensPedido[abaSelecionada].descricao }}
              </div>
              <div class="text-muted small">Produto principal</div>
            </div>

            <!-- Input de quantidade -->
            <input v-model.number="itensPedido[abaSelecionada].quantidade" type="number" min="1"
              class="form-control form-control-sm text-center" style="width: 110px;"
              @input="validarQuantidade(itensPedido[abaSelecionada])" aria-label="Quantidade do produto principal" />
          </div>

          <!-- Árvore de componentes/filhos -->
          <ItemTree v-if="itensPedido[abaSelecionada]" :item="itensPedido[abaSelecionada]" :mostrar-quantidade="true" />

        </div>


        <div class="modal-body">

          <hr class="my-3" />

          <!-- Itens Extras -->
          <section>
            <h6 class="fw-semibold text-muted mb-3">
              <i class="bi bi-plus-circle me-2 text-primary"></i> Itens Extras
            </h6>

            <div class="row gx-2 gy-3">
              <div class="col-6 col-md-4 col-lg-3" v-for="tipo in tipos" :key="tipo.nome">
                <div class="dropdown w-100 position-relative">
                  <button class="btn btn-outline-secondary w-100 text-start text-truncate touch-btn" type="button"
                    @click="abrirDropdown(tipo.nome)">
                    <span class="d-flex align-items-center">
                      <i :class="tipo.icone + ' me-2'"></i>
                      <span class="flex-fill">{{ tipo.label }}</span>
                      <i class="bi ms-2"
                        :class="dropdownAberto === tipo.nome ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </span>
                  </button>

                  <div v-if="dropdownAberto === tipo.nome" class="dropdown-menu show w-100 p-2 shadow"
                    style="max-height: 48vh; overflow-y: auto; position: absolute; z-index: 1050;">
                    <div class="mb-2 px-1">
                      <input v-model="tipo.filtro" type="search" class="form-control form-control-sm"
                        placeholder="Filtrar..." aria-label="Filtrar itens" />
                    </div>

                    <div v-if="filtrarItens(tipo).length">
                      <button v-for="item in filtrarItens(tipo)" :key="item.id"
                        class="dropdown-item d-flex justify-content-between align-items-center py-2" type="button"
                        @click="selecionarItem(item)">
                        <div class="text-truncate">
                          <strong class="me-1 small">{{ item.codigo }}</strong>
                          <span class="small text-muted">{{ item.descricao }}</span>
                        </div>
                      </button>
                    </div>

                    <div v-else class="text-muted fst-italic px-3 py-2 small">
                      Nenhum item encontrado.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quantidade -->
            <div v-if="itemSelecionado" class="mt-3 border-top pt-3">
              <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                <div class="me-0 me-sm-3 w-100">
                  <div class="fw-semibold">{{ itemSelecionado.codigo }} — {{ itemSelecionado.descricao }}</div>
                  <div class="text-muted small">Tipo: {{ itemSelecionado.tipo || '—' }}</div>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <input v-model.number="itemSelecionado.quantidade" type="number" min="1"
                    class="form-control form-control-sm text-center" style="width: 110px;"
                    @input="validarQuantidade(itemSelecionado)" aria-label="Quantidade do item selecionado" />
                  <button class="btn btn-primary btn-sm touch-btn" @click="adicionarItem" type="button">
                    adicionar
                  </button>
                </div>
              </div>
            </div>

            <!-- Itens adicionados -->
            <div v-if="listaAtiva().length" class="mt-4">
              <h6 class="fw-semibold text-muted mb-2 small">
                <i class="bi bi-list-check me-2 text-primary"></i> Itens Adicionados
              </h6>

              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light small">
                    <tr>
                      <th>Código</th>
                      <th>Descrição</th>
                      <th class="text-center" style="width: 110px">Qtd</th>
                      <th class="text-end" style="width: 60px"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(it, idx) in listaAtiva()" :key="it.id">
                      <td class="text-truncate" style="max-width: 180px;">{{ it.codigo }}</td>
                      <td class="text-truncate" style="max-width: 320px;">{{ it.descricao }}</td>
                      <td class="text-center">
                        <input v-model.number="it.quantidade" type="number" min="1"
                          class="form-control form-control-sm text-center" style="width: 90px; margin: 0 auto;"
                          @input="validarQuantidade(it)" aria-label="Quantidade do item adicionado" />
                      </td>
                      <td class="text-end">
                        <button class="btn btn-outline-danger btn-sm" @click="removerItem(idx)" type="button">
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Confirm -->
            <div class="mt-4">
              <div class="form-check">
                <input id="confirmarItens" class="form-check-input" type="checkbox" v-model="confirmado" />
                <label for="confirmarItens" class="form-check-label small text-muted">
                  Confirmo que conferi todos os itens antes de salvar.
                </label>
              </div>
            </div>

          </section>
        </div>

        <!-- Rodapé -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold touch-btn" @click="salvar"
            :disabled="!confirmado">
            <i class="bi bi-save me-2"></i> Salvar Itens
          </button>
        </div>

      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { Modal } from 'bootstrap'
import ItemTree from '@/components/ItemTree.vue'
import api from '@/services/axios'

/* ================= EMITS ================= */
const emit = defineEmits<{
  (e: 'salvo'): void
}>()

/* ================= TYPES ================= */
interface Item {
  id: number
  pedido_item_id?: number
  codigo: string
  descricao: string
  tipo?: string
  quantidade: number
  filhos?: Item[]
}

interface Tipo {
  nome: string
  label: string
  icone: string
  filtro: string
}

/* ================= MODAL ================= */
const modalEl = ref<HTMLElement | null>(null)
let modalInstance: Modal | null = null

const fechar = () => {
  modalInstance?.hide()
}

/* ================= ESTADO ================= */
const itensPedido = ref<Item[]>([])
const abaSelecionada = ref(0)
const pedidoId = ref<number | null>(null)

const todosItens = ref<Item[]>([])
const itemSelecionado = ref<Item | null>(null)

/**
 * Extras separados por produto
 * produto_id => Item[]
 */
const itensSelecionadosPorProduto = ref<Record<number, Item[]>>({})

const dropdownAberto = ref<string | null>(null)
const confirmado = ref(false)

/* ================= TIPOS ================= */
const tipos = ref<Tipo[]>([
  { nome: 'parafuso', label: 'Parafuso', icone: 'bi bi-nut', filtro: '' },
  { nome: 'componente', label: 'Componente', icone: 'bi bi-gear', filtro: '' },
  { nome: 'embalagem', label: 'Embalagem', icone: 'bi bi-box', filtro: '' },
  { nome: 'insumo', label: 'Insumo', icone: 'bi bi-beaker', filtro: '' }
])

/* ================= LOAD ================= */
const carregarItensPorTipo = async (tipo: string) => {
  const { data } = await api.get(`/api/itens/tipo?tipo=${tipo}`)
  const novos = (data || []).map((i: any) => ({
    ...i,
    quantidade: 1
  }))

  todosItens.value = [
    ...todosItens.value.filter(i => i.tipo !== tipo),
    ...novos
  ]
}

onMounted(async () => {
  await nextTick()
  if (modalEl.value) modalInstance = new Modal(modalEl.value)

  for (const t of tipos.value) {
    await carregarItensPorTipo(t.nome)
  }
})

/* ================= ABRIR MODAL ================= */
const abrir = async (listaDeItens: any[], idPedido: number) => {
  pedidoId.value = idPedido

  itensPedido.value = (listaDeItens || [])
    .map((pi: any) => {
      const produto =
        pi.produto ??
        pi.componente ??
        (pi.produto_id
          ? {
            id: pi.produto_id,
            codigo: pi.produto_codigo,
            descricao: pi.produto_descricao
          }
          : null)

      if (!produto?.id) return null

      //  filhos podem vir em pi.filhos (seu caso) ou em pi.componentes (outros endpoints)
      const filhosBrutos = Array.isArray(pi.filhos) && pi.filhos.length
        ? pi.filhos
        : Array.isArray(pi.componentes) && pi.componentes.length
          ? pi.componentes
          : []

      return {
        id: produto.id,
        pedido_item_id: pi.id,
        codigo: produto.codigo,
        descricao: produto.descricao,
        tipo: 'produto',
        quantidade: pi.quantidade ?? 1,

        //  agora os filhos aparecem
        filhos: filhosBrutos.map((c: any) => {
          // se vier no formato "filho direto"
          if (c?.codigo && c?.descricao) {
            return {
              id: c.id,
              codigo: c.codigo,
              descricao: c.descricao,
              tipo: c.tipo ?? 'componente',
              quantidade: c.quantidade ?? 1,
              filhos: c.filhos ?? [] // caso tenha níveis
            }
          }

          // fallback: formato antigo "componente nested"
          return {
            id: c.componente?.id ?? c.componente_id,
            codigo: c.componente?.codigo ?? '—',
            descricao: c.componente?.descricao ?? '—',
            tipo: c.componente?.tipo ?? 'componente',
            quantidade: c.quantidade ?? 1,
            filhos: []
          }
        })
      }
    })
    .filter(Boolean) as Item[]

  abaSelecionada.value = 0
  confirmado.value = false
  itemSelecionado.value = null
  dropdownAberto.value = null
  itensSelecionadosPorProduto.value = {}

  await nextTick()
  modalInstance?.show()
}

defineExpose({
  abrir
})
/* ================= HELPERS ================= */
const filtrarItens = (tipo: Tipo) => {
  const termo = tipo.filtro.toLowerCase()
  return todosItens.value.filter(i =>
    i.tipo === tipo.nome &&
    (!termo ||
      i.codigo.toLowerCase().includes(termo) ||
      i.descricao.toLowerCase().includes(termo))
  )
}

const abrirDropdown = (tipo: string) => {
  dropdownAberto.value = dropdownAberto.value === tipo ? null : tipo
}

const validarQuantidade = (item: Item | null) => {
  if (!item || item.quantidade < 1) item!.quantidade = 1
}

const selecionarItem = (item: Item) => {
  itemSelecionado.value = { ...item, quantidade: 1 }
  dropdownAberto.value = null
}

/* ================= EXTRAS ================= */
const listaAtiva = (): Item[] => {
  const produto = itensPedido.value[abaSelecionada.value]
  if (!produto) return []

  if (!itensSelecionadosPorProduto.value[produto.id]) {
    itensSelecionadosPorProduto.value[produto.id] = []
  }

  return itensSelecionadosPorProduto.value[produto.id]
}

const adicionarItem = () => {
  if (!itemSelecionado.value) return

  const lista = listaAtiva()
  const idx = lista.findIndex(i => i.id === itemSelecionado.value!.id)

  if (idx !== -1) {
    lista[idx].quantidade += itemSelecionado.value.quantidade
  } else {
    lista.push({ ...itemSelecionado.value })
  }

  itemSelecionado.value = null
}

const removerItem = (index: number) => {
  listaAtiva().splice(index, 1)
}

/* ================= SALVAR ================= */
const salvar = async () => {
  if (!pedidoId.value) return

  Object.entries(itensSelecionadosPorProduto.value).forEach(([produtoId, extras]) => {
    const produto = itensPedido.value.find(p => p.id === Number(produtoId))
    if (!produto) return
  })

  await api.post(`/api/pedidos/${pedidoId.value}/itens`, {
    itens: itensPedido.value.map(p => ({
      pedido_item_id: p.pedido_item_id,
      produto_id: p.id,
      quantidade: p.quantidade,

      componentes: (p.filhos || []).map(c => ({
        componente_id: c.id,
        quantidade: c.quantidade
      })),

      extras: (itensSelecionadosPorProduto.value[p.id] || []).map(e => ({
        item_id: e.id,
        quantidade: e.quantidade
      }))
    }))
  })



  emit('salvo')
  fechar()
}
</script>

<style scoped>
/* Touch-friendly sizes */
.touch-btn {
  padding: 0.6rem 0.9rem;
  font-size: 0.95rem;
  border-radius: 2em;
}


/* Aba rolável */
.nav-tabs-scroll-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 0.5rem;
}

.nav-tabs {
  display: flex;
  gap: 0.5rem;
  white-space: nowrap;
}

.nav-tabs .nav-item {
  flex: 0 0 auto;
}

.nav-tabs .nav-link {
  min-width: 10rem;
  text-align: left;
  border-radius: 0.75rem;
  padding: 0.6rem 0.9rem;
  line-height: 1.0;
}

.nav-tabs .nav-link.active {
  background: #87af96;
  color: #fff;
  border: none;
}

/* Make dropdown menu clearly above content on small screens */
.dropdown-menu.show {
  max-width: 100%;
}

/* Table responsiveness improvements for tablets */
.table-responsive {
  -webkit-overflow-scrolling: touch;
}


/* Modal content padding adjustments for tablet */
@media (min-width: 768px) and (max-width: 1199px) {
  .modal-dialog {
    max-width: 900px;
  }

  .nav-tabs .nav-link {
    min-width: 12rem;
  }
}

/* Larger screens: keep balanced */
@media (min-width: 1200px) {
  .modal-dialog {
    max-width: 1140px;
  }

  .nav-tabs .nav-link {
    min-width: 14rem;
  }
}

/* Small screens (phones) — keep usable */
@media (max-width: 575px) {
  .nav-tabs .nav-link {
    min-width: 9rem;
    padding: 0.5rem 0.7rem;
  }

  .touch-btn {
    padding: 0.45rem 0.7rem;
    font-size: 0.9rem;
  }
}
</style>
