<template>
  <div class="modal fade" id="modalFinalizarCadastro" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content shadow-lg">
        <div class="modal-header bg-danger text-white">
          <div>
            <h5 class="modal-title fw-bold mb-0">Finalizar cadastro</h5>
            <small class="text-white-50" v-if="item">
              Item #{{ item.id }} · Código: <strong>{{ item.codigo }}</strong>
            </small>
          </div>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div v-if="!item" class="alert alert-warning">
            Nenhum item selecionado.
          </div>

          <div v-else>
            <!-- Dados principais -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Descrição</label>
                <input v-model="form.descricao" class="form-control" type="text" />
              </div>

              <div class="col-md-3">
                <label class="form-label">Código</label>
                <input v-model="form.codigo" class="form-control" type="text" />
              </div>

              <div class="col-md-3">
                <label class="form-label">ANVISA</label>
                <input v-model="form.anvisa" class="form-control" type="text" placeholder="Opcional" />
              </div>

              <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select v-model="form.tipo" class="form-select">
                  <option value="produto">Produto</option>
                  <option value="componente">Componente</option>
                  <option value="parafuso">Parafuso</option>
                  <option value="semiacabado">Semiacabado</option>
                  <option value="insumo">Insumo</option>
                  <option value="embalagem">Embalagem</option>
                </select>
              </div>

              <div class="col-md-8">
                <label class="form-label">Fluxo</label>
                <select v-model="form.fluxo_id" class="form-select">
                  <option :value="null">— Selecione —</option>
                  <option v-for="f in fluxos" :key="f.id" :value="f.id">
                    {{ f.nome_fluxo }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Composição -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Composição (componentes)</div>
                <div class="text-muted small">Adicione os itens que compõem este cadastro</div>
              </div>

              <div class="card-body">
                <!-- Busca de itens para adicionar -->
                <div class="row g-2 align-items-end mb-3">
                  <div class="col-md-7">
                    <label class="form-label">Buscar item</label>
                    <input
                      v-model="buscaItem"
                      class="form-control"
                      type="text"
                      placeholder="Digite descrição ou código..."
                      @input="debouncedBuscar()"
                    />
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Quantidade</label>
                    <input v-model.number="qtdAdicionar" class="form-control" type="number" min="1" />
                  </div>

                  <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-primary" type="button" :disabled="!itemSelecionadoBusca" @click="adicionarComponente()">
                      <i class="bi bi-plus-circle me-1"></i> Adicionar
                    </button>
                  </div>

                  <div class="col-12" v-if="resultadosBusca.length">
                    <div class="list-group">
                      <button
                        v-for="r in resultadosBusca"
                        :key="r.id"
                        type="button"
                        class="list-group-item list-group-item-action"
                        :class="{ active: itemSelecionadoBusca?.id === r.id }"
                        @click="itemSelecionadoBusca = r"
                      >
                        <div class="d-flex justify-content-between">
                          <div class="fw-semibold">{{ r.descricao }}</div>
                          <div class="small opacity-75">{{ r.codigo }}</div>
                        </div>
                        <div class="small opacity-75">
                          Tipo: {{ r.tipo }} · Fluxo: {{ r.fluxo?.nome_fluxo || '—' }}
                        </div>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Tabela de composição -->
                <div v-if="form.componentes.length" class="table-responsive">
                  <table class="table table-sm align-middle">
                    <thead>
                      <tr>
                        <th>Item</th>
                        <th style="width: 120px;">Qtd</th>
                        <th style="width: 90px;" class="text-end">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(c, idx) in form.componentes" :key="c.item_id">
                        <td>
                          <div class="fw-semibold">{{ c.descricao }}</div>
                          <div class="text-muted small">#{{ c.item_id }} · {{ c.codigo }}</div>
                        </td>

                        <td>
                          <input v-model.number="c.quantidade" type="number" min="1" class="form-control form-control-sm" />
                        </td>

                        <td class="text-end">
                          <button class="btn btn-sm btn-outline-danger" type="button" @click="removerComponente(idx)">
                            Remover
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div v-else class="alert alert-secondary mb-0">
                  Nenhum componente adicionado ainda.
                </div>
              </div>
            </div>

            <div v-if="erro" class="alert alert-danger">{{ erro }}</div>
            <div v-if="sucesso" class="alert alert-success">{{ sucesso }}</div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">
            Cancelar
          </button>

          <button class="btn btn-danger" type="button" :disabled="salvando || !item" @click="salvar()">
            <span v-if="salvando" class="spinner-border spinner-border-sm me-2"></span>
            Salvar e tirar de PENDENTE
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import api from '@/services/axios'

interface Fluxo {
  id: number
  nome_fluxo: string
}

interface ItemBusca {
  id: number
  descricao: string
  codigo: string
  tipo: string
  fluxo: Fluxo | null
}

const props = defineProps<{
  item: { id: number; descricao: string; codigo: string; anvisa?: string | null; tipo: string; fluxo: Fluxo | null } | null
  fluxos: Fluxo[]
}>()

const emit = defineEmits<{
  (e: 'salvo'): void
}>()

type ComponenteForm = {
  item_id: number
  descricao: string
  codigo: string
  quantidade: number
}

const salvando = ref(false)
const erro = ref<string>('')
const sucesso = ref<string>('')

const form = reactive<{
  descricao: string
  codigo: string
  anvisa: string | null
  tipo: string
  fluxo_id: number | null
  componentes: ComponenteForm[]
}>({
  descricao: '',
  codigo: '',
  anvisa: null,
  tipo: 'produto',
  fluxo_id: null,
  componentes: [],
})

watch(
  () => props.item,
  (i) => {
    erro.value = ''
    sucesso.value = ''
    form.componentes = []

    if (!i) return
    form.descricao = i.descricao || ''
    form.codigo = i.codigo || ''
    form.anvisa = (i.anvisa ?? null) as any
    form.tipo = i.tipo || 'produto'
    form.fluxo_id = i.fluxo?.id ?? null
  },
  { immediate: true }
)

// ------------------------
// Busca de itens para compor
// ------------------------
const buscaItem = ref('')
const resultadosBusca = ref<ItemBusca[]>([])
const itemSelecionadoBusca = ref<ItemBusca | null>(null)
const qtdAdicionar = ref<number>(1)

let timer: number | null = null
function debouncedBuscar() {
  if (timer) window.clearTimeout(timer)
  timer = window.setTimeout(() => buscarItens(), 350)
}

async function buscarItens() {
  const q = buscaItem.value.trim()
  resultadosBusca.value = []
  itemSelecionadoBusca.value = null
  if (!q || q.length < 2) return

  try {
    // Ajuste conforme sua API:
    // exemplo: GET /api/itens/busca?q=xxx
    const { data } = await api.get<ItemBusca[]>('/api/itens/busca', { params: { q } })
    resultadosBusca.value = data || []
  } catch (e) {
    // não bloqueia o modal
    console.error(e)
  }
}

function adicionarComponente() {
  if (!itemSelecionadoBusca.value) return
  const r = itemSelecionadoBusca.value

  const existente = form.componentes.find((c) => c.item_id === r.id)
  if (existente) {
    existente.quantidade += Math.max(1, qtdAdicionar.value || 1)
    return
  }

  form.componentes.push({
    item_id: r.id,
    descricao: r.descricao,
    codigo: r.codigo,
    quantidade: Math.max(1, qtdAdicionar.value || 1),
  })
}

function removerComponente(idx: number) {
  form.componentes.splice(idx, 1)
}

// ------------------------
// Salvar
// ------------------------
const itemId = computed(() => props.item?.id ?? null)

async function salvar() {
  erro.value = ''
  sucesso.value = ''

  if (!itemId.value) return

  // validações mínimas
  if (!form.descricao.trim()) {
    erro.value = 'Informe a descrição.'
    return
  }
  if (!form.codigo.trim()) {
    erro.value = 'Informe o código.'
    return
  }
  if (!form.tipo) {
    erro.value = 'Selecione o tipo.'
    return
  }

  salvando.value = true
  try {
    // Ajuste o endpoint conforme seu backend:
    // PUT /api/itens/{id}/finalizar
    await api.put(`/api/itens/${itemId.value}/finalizar`, {
      descricao: form.descricao,
      codigo: form.codigo,
      anvisa: form.anvisa,
      tipo: form.tipo,
      fluxo_id: form.fluxo_id,
      componentes: form.componentes.map((c) => ({
        item_id: c.item_id,
        quantidade: c.quantidade,
      })),
    })

    sucesso.value = 'Cadastro finalizado com sucesso.'

    // fecha modal
    const el = document.getElementById('modalFinalizarCadastro')
    // @ts-expect-error bootstrap global
    const modal = el ? bootstrap.Modal.getInstance(el) : null
    modal?.hide()

    emit('salvo')
  } catch (e: any) {
    erro.value = e?.response?.data?.message || 'Erro ao salvar.'
  } finally {
    salvando.value = false
  }
}
</script>
