<script setup lang="ts">
import { ref, watch, computed, onBeforeUnmount } from 'vue'
import FormBase from '@/components/FormBase.vue'
import api from '@/services/axios'
import Swal from 'sweetalert2'
import { useBuscarItem, type Item } from '@/composables/useBuscarItem'

const { sugestoes, resultado, filhos, carregando, erro, autocomplete, buscarItemCompleto } = useBuscarItem()

// Estados
const numeroPedido = ref('')
const erroNumeroPedido = ref('')
const itensSelecionados = ref<Array<{ pai: Item, filhos: Item[] }>>([])

const lote = ref('')
const doutor = ref('')
const paciente = ref('')
const tipoNacional = ref<'Nacional' | 'Internacional'>('Nacional')
const taxaExtra = ref(false)

const codigoBusca = ref('')

// UI/UX do autocomplete
const dropdownOpen = ref(false)
const activeIndex = ref(-1)

const hasSugestoes = computed(() => (sugestoes.value?.length ?? 0) > 0)
const hasQuery = computed(() => codigoBusca.value.trim().length > 0)

// Debounce simples (sem lib)
let t: number | undefined
watch(codigoBusca, (nova) => {
  window.clearTimeout(t)
  const q = nova.trim()

  if (!q) {
    sugestoes.value = []
    dropdownOpen.value = false
    activeIndex.value = -1
    return
  }

  dropdownOpen.value = true
  t = window.setTimeout(async () => {
    await autocomplete(q)
    activeIndex.value = sugestoes.value.length ? 0 : -1
  }, 250)
})

onBeforeUnmount(() => window.clearTimeout(t))

// Seleção
async function selecionar(item: Item) {
  // opcional: manter o código no input ou limpar para nova busca
  // codigoBusca.value = item.codigo
  dropdownOpen.value = false
  activeIndex.value = -1

  await buscarItemCompleto(item)

  if (resultado.value) {
    const jaExiste = itensSelecionados.value.some(i => i.pai.id === resultado.value!.id)
    if (!jaExiste) {
      itensSelecionados.value.push({
        pai: resultado.value,
        filhos: [...filhos.value]
      })
    }
  }

  sugestoes.value = []
  codigoBusca.value = '' // UX melhor: pronto para buscar outro
}

function limparBusca() {
  codigoBusca.value = ''
  sugestoes.value = []
  dropdownOpen.value = false
  activeIndex.value = -1
}

function onFocusBusca() {
  if (hasQuery.value) dropdownOpen.value = true
}

function onBlurBusca() {
  // delay pra permitir clique na sugestão
  window.setTimeout(() => {
    dropdownOpen.value = false
    activeIndex.value = -1
  }, 120)
}

function onKeydownBusca(e: KeyboardEvent) {
  if (!dropdownOpen.value) {
    if (e.key === 'ArrowDown' && hasSugestoes.value) {
      dropdownOpen.value = true
      activeIndex.value = 0
      e.preventDefault()
    }
    return
  }

  if (e.key === 'Escape') {
    dropdownOpen.value = false
    activeIndex.value = -1
    e.preventDefault()
    return
  }

  if (e.key === 'ArrowDown') {
    if (!hasSugestoes.value) return
    activeIndex.value = Math.min(activeIndex.value + 1, sugestoes.value.length - 1)
    e.preventDefault()
    return
  }

  if (e.key === 'ArrowUp') {
    if (!hasSugestoes.value) return
    activeIndex.value = Math.max(activeIndex.value - 1, 0)
    e.preventDefault()
    return
  }

  if (e.key === 'Enter') {
    if (activeIndex.value >= 0 && sugestoes.value[activeIndex.value]) {
      selecionar(sugestoes.value[activeIndex.value])
      e.preventDefault()
    }
  }
}

// Itens selecionados (expand/collapse + remover)
const expandido = ref<Record<number, boolean>>({})

function removerSelecionado(id: number) {
  itensSelecionados.value = itensSelecionados.value.filter(i => i.pai.id !== id)
}

function toggleExpand(index: number) {
  expandido.value[index] = !expandido.value[index]
}

// Limpa erro ao digitar número do pedido
watch(numeroPedido, () => (erroNumeroPedido.value = ''))

// Envio formulário
async function enviarFormulario() {
  if (!itensSelecionados.value.length) {
    return Swal.fire('Erro', 'Selecione ao menos um produto.', 'error')
  }

  try {
    const resp = await api.post('/api/pedidos', {
      numero_pedido: numeroPedido.value,
      lote: lote.value,
      doutor: doutor.value,
      paciente: paciente.value,
      tipo: tipoNacional.value,
      taxa_extra: taxaExtra.value
    })

    const pedidoId = resp.data.id

    await api.post(`/api/pedidos/${pedidoId}/itens`, {
      itens: itensSelecionados.value.map(i => ({
        produto_id: i.pai.id,
        quantidade: 1,
        componentes: i.filhos.map(f => ({
          componente_id: f.id,
          quantidade: 1
        }))
      }))
    })

    Swal.fire('Sucesso', 'Pedido enviado com sucesso!', 'success')

    numeroPedido.value = ''
    limparBusca()
    itensSelecionados.value = []

    resultado.value = null
    filhos.value = []
    sugestoes.value = []

    lote.value = ''
    doutor.value = ''
    paciente.value = ''
    tipoNacional.value = 'Nacional'
    taxaExtra.value = false
    expandido.value = {}
  } catch (err: any) {
    if (err.response?.data?.errors?.numero_pedido) {
      erroNumeroPedido.value = err.response.data.errors.numero_pedido[0]
      return Swal.fire('Erro', erroNumeroPedido.value, 'error')
    }

    Swal.fire('Erro', 'Erro ao enviar pedido', 'error')
  }
}
</script>

<template>
  <FormBase titulo="Ordem de Produção" codigo="FRM.PRO.001 REV.00">
    <form @submit.prevent="enviarFormulario" class="row gy-4 gx-3 p-3">

      <!-- Número do pedido -->
      <div class="col-md-4 col-12">
        <label class="form-label fw-semibold text-dark">Número do Pedido</label>
        <input
          v-model="numeroPedido"
          type="text"
          class="form-control form-control-lg shadow-sm rounded-3"
          placeholder="Ex: 12345"
        />
        <small v-if="erroNumeroPedido" class="text-danger mt-1 d-block">
          {{ erroNumeroPedido }}
        </small>
      </div>

      <!-- Código -->
      <div class="col-md-4 col-12 position-relative">
        <label class="form-label fw-semibold text-dark">Código do Produto</label>

        <div class="search-wrap">
          <i class="bi bi-search search-icon"></i>

          <input
            v-model="codigoBusca"
            type="text"
            class="form-control form-control-lg shadow-sm rounded-4 ps-5 pe-5"
            placeholder="Buscar por código ou descrição..."
            @focus="onFocusBusca"
            @blur="onBlurBusca"
            @keydown="onKeydownBusca"
            autocomplete="off"
          />

          <div v-if="carregando" class="search-spinner spinner-border spinner-border-sm" role="status" aria-label="Carregando"></div>

          <button
            v-if="codigoBusca && !carregando"
            type="button"
            class="btn btn-sm btn-light search-clear"
            @mousedown.prevent
            @click="limparBusca"
            aria-label="Limpar busca"
            title="Limpar"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <!-- Dropdown -->
        <div v-if="dropdownOpen" class="search-dropdown shadow-lg">
          <div v-if="erro" class="px-3 py-2 text-danger small">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ String(erro) }}
          </div>

          <div v-else-if="!carregando && !sugestoes.length" class="px-3 py-3 text-muted small">
            Nenhum resultado para <strong>{{ codigoBusca }}</strong>
          </div>

          <button
            v-for="(s, idx) in sugestoes"
            :key="s.id"
            type="button"
            class="search-item"
            :class="{ active: idx === activeIndex }"
            @mousedown.prevent
            @mouseenter="activeIndex = idx"
            @click="selecionar(s)"
          >
            <div class="d-flex align-items-start gap-3">
              <div class="search-pill">
                <i class="bi bi-box-seam"></i>
              </div>

              <div class="flex-grow-1 text-start">
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <div class="fw-semibold">{{ s.codigo }}</div>
                  <span class="badge rounded-pill text-bg-light border">Produto</span>
                </div>
                <div class="text-muted small text-truncate">
                  {{ s.descricao }}
                </div>
              </div>
            </div>
          </button>

          <div v-if="sugestoes.length" class="px-3 py-2 border-top text-muted small">
            <span class="me-2">↑↓ navegar</span>
            <span class="me-2">Enter selecionar</span>
            <span>Esc fechar</span>
          </div>
        </div>
      </div>

      <!-- Lote -->
      <div class="col-md-4 col-12">
        <label class="form-label fw-semibold text-dark">Lote</label>
        <input
          v-model="lote"
          type="text"
          class="form-control form-control-lg shadow-sm rounded-3"
          placeholder="Ex: L1234"
        />
      </div>

      <!-- Produtos selecionados (AGORA EM COL-12, fora do campo Código) -->
      <div v-if="itensSelecionados.length" class="col-12">
        <div class="selected-wrap selected-wrap--full">
          <div class="selected-header">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div class="d-flex align-items-center gap-2">
                <div class="selected-icon">
                  <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                  <div class="selected-title">Produtos selecionados</div>
                  <div class="selected-sub">
                    {{ itensSelecionados.length }} item(ns) no pedido
                  </div>
                </div>
              </div>

              <button type="button" class="btn btn-sm btn-light selected-clear" @click="itensSelecionados = []">
                <i class="bi bi-trash3 me-2"></i>Limpar
              </button>
            </div>

            <div class="selected-chips">
              <span v-for="it in itensSelecionados" :key="it.pai.id" class="chip">
                <i class="bi bi-box-seam me-2"></i>{{ it.pai.codigo }}
              </span>
            </div>
          </div>

          <div class="selected-list selected-list--full">
            <div v-for="(item, index) in itensSelecionados" :key="item.pai.id" class="selected-card">
              <div class="selected-card-top">
                <div class="d-flex align-items-start gap-3">
                  <div class="selected-badge">
                    <i class="bi bi-box-seam"></i>
                  </div>

                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                      <div class="fw-bold text-uppercase selected-code">{{ item.pai.codigo }}</div>
                      <span class="pill">
                        <i class="bi bi-diagram-3 me-2"></i>{{ item.filhos.length }} subitem(ns)
                      </span>
                    </div>

                    <div class="text-muted small selected-desc">
                      {{ item.pai.descricao }}
                    </div>
                  </div>
                </div>

                <div class="selected-actions">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2"
                    @click="toggleExpand(index)"
                  >
                    <i class="bi" :class="expandido[index] ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    {{ expandido[index] ? 'Ocultar' : 'Detalhar' }}
                  </button>

                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2"
                    @click="removerSelecionado(item.pai.id)"
                    title="Remover"
                  >
                    <i class="bi bi-x-lg"></i>
                    Remover
                  </button>
                </div>
              </div>

              <Transition name="fade">
                <div v-if="expandido[index]" class="selected-card-bottom">
                  <div class="subitems-head">
                    <i class="bi bi-list-check me-2"></i>Componentes / Subitens
                  </div>

                  <div class="subitems">
                    <div v-for="f in item.filhos" :key="f.id" class="subitem-row">
                      <div class="subitem-dot"></div>
                      <div class="flex-grow-1">
                        <div class="subitem-code">
                          {{ f.codigo }}
                          <span class="subitem-tag">Subitem</span>
                        </div>
                        <div class="text-muted small">{{ f.descricao }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </div>

      <!-- Doutor -->
      <div class="col-md-6 col-12">
        <label class="form-label fw-semibold text-dark">Doutor(a)</label>
        <input
          v-model="doutor"
          type="text"
          class="form-control form-control-lg shadow-sm rounded-3 text-capitalize"
          placeholder="Nome do doutor"
        />
      </div>

      <!-- Paciente -->
      <div class="col-md-6 col-12">
        <label class="form-label fw-semibold text-dark">Paciente — iniciais</label>
        <input
          v-model="paciente"
          type="text"
          class="form-control form-control-lg shadow-sm rounded-3 text-uppercase"
          placeholder="Ex: ABC"
        />
      </div>

      <!-- Informações adicionais -->
      <fieldset class="mt-4 p-3 border rounded-4 shadow-sm bg-light">
        <legend class="text-secondary fw-bold fs-6 px-2">Informações Adicionais</legend>

        <div class="row">
          <div class="col-md-6 d-flex flex-wrap gap-4">
            <div class="form-check">
              <input class="form-check-input" type="radio" value="Nacional" v-model="tipoNacional" id="nacional" />
              <label class="form-check-label" for="nacional">Nacional</label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" value="Internacional" v-model="tipoNacional" id="internacional" />
              <label class="form-check-label" for="internacional">Internacional</label>
            </div>
          </div>

          <div class="col-md-6 d-flex align-items-center">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" v-model="taxaExtra" id="taxaExtra" />
              <label class="form-check-label" for="taxaExtra">Taxa extra</label>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- Enviar -->
      <div class="text-center mt-4">
        <button type="submit" class="btn btn-success px-5 py-2 fw-semibold shadow-sm rounded-3">
          <i class="bi bi-send me-2"></i> Enviar Pedido
        </button>
      </div>

    </form>
  </FormBase>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

/* Autocomplete */
.search-wrap { position: relative; }

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.55;
  font-size: 1.1rem;
}

.search-clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  border-radius: 999px;
  padding: 6px 8px;
  line-height: 1;
}

.search-spinner {
  position: absolute;
  right: 48px;
  top: 50%;
  transform: translateY(-50%);
}

.search-dropdown {
  position: absolute;
  width: 100%;
  z-index: 20;
  margin-top: 10px;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, .06);
  border-radius: 16px;
  overflow: hidden;
}

.search-item {
  width: 100%;
  border: 0;
  background: transparent;
  padding: 12px 14px;
  transition: background .15s ease, transform .05s ease;
}

.search-item:hover { background: rgba(13, 110, 253, 0.06); }
.search-item.active { background: rgba(13, 110, 253, 0.10); }

.search-pill {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(13, 110, 253, 0.10);
  color: #0d6efd;
}

/* Selecionados */
.selected-wrap{
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 18px;
  background: #fff;
  overflow: hidden;
}

.selected-wrap--full{
  box-shadow: 0 14px 30px rgba(0,0,0,.06);
}

.selected-header{
  padding: 14px 14px 10px 14px;
  background: linear-gradient(180deg, rgba(13,110,253,.06), rgba(255,255,255,0));
  border-bottom: 1px solid rgba(0,0,0,.06);
}

.selected-icon{
  width: 40px;
  height: 40px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(25,135,84,.10);
  color: #198754;
  font-size: 1.2rem;
}

.selected-title{ font-weight: 700; line-height: 1.1; }

.selected-sub{
  font-size: .85rem;
  color: rgba(0,0,0,.55);
}

.selected-clear{ border-radius: 999px; }

.selected-chips{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
  overflow-x: auto;
  padding-bottom: 2px;
}

.selected-chips::-webkit-scrollbar{ height: 6px; }
.selected-chips::-webkit-scrollbar-thumb{
  background: rgba(0,0,0,.12);
  border-radius: 999px;
}

.chip{
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: .85rem;
  background: rgba(13,110,253,.08);
  border: 1px solid rgba(13,110,253,.18);
  color: rgba(0,0,0,.75);
  white-space: nowrap;
}

.selected-list{
  padding: 12px;
  display: grid;
  gap: 12px;
}

.selected-list--full{
  grid-template-columns: 1fr;
}

@media (min-width: 992px){
  .selected-list--full{
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
}

.selected-card{
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 20px rgba(0,0,0,.04);
  overflow: hidden;
}

.selected-card-top{
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.selected-badge{
  width: 40px;
  height: 40px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(13,110,253,.10);
  color: #0d6efd;
  font-size: 1.1rem;
}

.selected-code{ letter-spacing: .04em; }
.selected-desc{ margin-top: 2px; }

.pill{
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: .8rem;
  background: rgba(0,0,0,.04);
  border: 1px solid rgba(0,0,0,.06);
  color: rgba(0,0,0,.70);
  white-space: nowrap;
}

.selected-actions{
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.selected-card-bottom{
  border-top: 1px solid rgba(0,0,0,.06);
  background: rgba(0,0,0,.02);
  padding: 14px;
}

.subitems-head{
  font-weight: 700;
  font-size: .9rem;
  color: rgba(0,0,0,.70);
  margin-bottom: 10px;
}

.subitems{
  display: grid;
  gap: 10px;
}

.subitem-row{
  display: flex;
  gap: 10px;
  padding: 10px 10px;
  background: #fff;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
}

.subitem-dot{
  width: 10px;
  height: 10px;
  border-radius: 999px;
  margin-top: 6px;
  background: rgba(13,110,253,.45);
  flex: 0 0 auto;
}

.subitem-code{
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}

.subitem-tag{
  font-weight: 600;
  font-size: .75rem;
  padding: 3px 8px;
  border-radius: 999px;
  background: rgba(108,117,125,.12);
  border: 1px solid rgba(108,117,125,.20);
  color: rgba(0,0,0,.60);
}

/* Desktop: ações alinhadas melhor */
@media (min-width: 768px){
  .selected-card-top{
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
  .selected-actions{
    justify-content: flex-end;
  }
}

@media (max-width: 576px) {
  .search-dropdown { border-radius: 14px; }
}
</style>
