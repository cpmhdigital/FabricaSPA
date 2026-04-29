<template>
  <div id="main" class="container my-5">
    <!-- Botão Voltar -->
    <div>
      <button
        class="btn btn-outline-primary shadow-sm border-0 rounded-5 d-flex align-items-center justify-content-center px-4 py-2 mt-5 mb-5"
        @click="$router.back()"
      >
        <i class="bi bi-arrow-left"></i>
        <span class="ms-2">Voltar</span>
      </button>
    </div>

    <!-- Formulário -->
    <form @submit.prevent="salvarEtapa">
      <!-- Configuração da Etapa -->
      <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-light text-secondary rounded-top-4">
          <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Configuração da Etapa</h5>
        </div>

        <div class="card-body p-4">
          <input type="hidden" v-model="editid" />

          <!-- Nome da Etapa -->
          <div class="mb-3">
            <label for="editnome" class="form-label fw-semibold">
              Nome <span class="text-danger">*</span>
            </label>
            <input
              type="text"
              id="editnome"
              class="form-control text-capitalize"
              v-model="editnome"
              required
            />
          </div>

          <!-- Parâmetros -->
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label fw-semibold">Parâmetros da etapa</label>
              <button
                type="button"
                class="btn btn-primary px-4 rounded-pill"
                @click="adicionarParametro"
                :disabled="parametros.length >= 3"
              >
                <i class="bi bi-plus-circle"></i> Adicionar parâmetro
              </button>
            </div>
            <small class="text-muted">Máximo de 3 parâmetros permitidos</small>

            <div class="row g-3 mt-3">
              <div
                v-for="(parametro, index) in parametros"
                :key="parametro.id"
                class="col-12 col-md-6 col-lg-6 col-xl-4"
              >
                <div class="border rounded p-3 h-100">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="border-bottom border-primary">Parâmetro {{ index + 1 }}</h6>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger rounded-circle"
                      @click="removerParametro(index)"
                      title="Remover parâmetro"
                    >
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>

                  <!-- Nome -->
                  <div class="mb-3">
                    <label class="form-label">Nome do parâmetro</label>
                    <input type="text" class="form-control" v-model="parametro.nome" required />
                  </div>

                  <!-- Tipo -->
                  <div class="mb-3">
                    <label class="form-label">Tipo de parâmetro</label>
                    <select class="form-select" v-model="parametro.tipo" required>
                      <option value="">Selecione...</option>
                      <option value="texto">Texto</option>
                      <option value="numero">Número</option>
                      <option value="simnao">Escolha única (Sim/Não)</option>
                      <option value="data">Data</option>
                    </select>
                  </div>

                  <!-- Limites numéricos -->
                  <div v-if="parametro.tipo === 'numero'">
                    <label class="form-label">Necessita de número máximo?</label>
                    <div class="d-flex flex-wrap gap-3">
                      <div
                        class="form-check"
                        v-for="op in [
                          { val: 'sim', lbl: 'Sim' },
                          { val: 'nao', lbl: 'Não' },
                          { val: 'produto', lbl: 'Limitar pela quantidade do produto' },
                        ]"
                        :key="op.val"
                      >
                        <input
                          type="radio"
                          :id="`limite-${op.val}-${index}`"
                          class="form-check-input"
                          v-model="parametro.limite"
                          :value="op.val"
                        />
                        <label class="form-check-label" :for="`limite-${op.val}-${index}`">
                          {{ op.lbl }}
                        </label>
                      </div>
                    </div>

                    <div class="row g-2 mt-3" v-if="parametro.limite === 'sim'">
                      <div class="col">
                        <label class="form-label">Mínimo</label>
                        <input type="number" class="form-control" v-model="parametro.min" />
                      </div>
                      <div class="col">
                        <label class="form-label">Máximo</label>
                        <input type="number" class="form-control" v-model="parametro.max" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Opções booleanas -->
          <div class="row g-4">
            <div class="col-md-4" v-for="(opcao, idx) in opcoes" :key="idx">
              <label class="form-label fw-semibold">{{ opcao.label }}</label>
              <div class="d-flex gap-3 flex-wrap">
                <div class="form-check" v-for="(value, i) in opcao.valores" :key="i">
                  <input
                    class="form-check-input"
                    type="radio"
                    :name="opcao.name"
                    :value="Number(value)"
                    v-model.number="opcao.model"
                    :required="opcao.required"
                  />
                  <label class="form-check-label">{{ valueLabel(value) }}</label>
                </div>
              </div>
            </div>
          </div>

          <!-- IT e REV -->
          <div class="mt-4">
            <label for="iterev" class="form-label fw-semibold">IT e REV</label>
            <Multiselect
              id="iterev"
              v-model="iterev"
              :options="opcoesItRev"
              :multiple="true"
              :close-on-select="false"
              placeholder="Selecione IT/REV"
              label="nome"
              track-by="id"
            />
          </div>
        </div>
      </div>

      <!-- Máquinas -->
      <div class="card shadow border-0 rounded-4 mt-5 p-3">
        <h6 class="fw-semibold mb-2">
          <i class="bi bi-cpu me-2 text-primary"></i> Máquinas Vinculadas à Etapa
        </h6>

        <div class="m-2">
          <label class="form-label">Adicione máquinas à etapa</label>
          <Multiselect
            id="maquinas"
            v-model="maquinas"
            :options="opcoesMaquinas"
            :multiple="true"
            :close-on-select="false"
            placeholder="Selecione as Máquinas correspondentes"
            :custom-label="(maquina) => `${maquina.codigo} - ${maquina.modelo}`"
            track-by="id"
          />
        </div>
      </div>

      <!-- Checklist -->
      <div class="card shadow border-0 rounded-4 mt-5 p-3">
        <small class="text-muted">
          <i class="bi bi-info-circle"></i> Preencha o checklist desta etapa. <b>Se não houver</b>,
          apenas deixe em branco.
        </small>

        <div class="row mt-5">
          <!-- Checklist Pré -->
          <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> Checklist Pré</h6>
              </div>

              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li
                    v-for="(item, i) in itemsPre"
                    :key="i"
                    class="list-group-item d-flex justify-content-between align-items-center"
                    :class="{ 'text-muted bg-light': item.inativo }"
                  >
                    <div class="flex-grow-1">
                      <!-- Modo de edição inline -->
                      <input
                        v-if="item.editando"
                        v-model="item.text"
                        class="form-control form-control-sm"
                        @keyup.enter="item.editando = false"
                        @blur="item.editando = false"
                        autofocus
                      />
                      <span
                        v-else
                        @dblclick="editarItem('pre', i)"
                        :style="{
                          textDecoration: item.inativo ? 'line-through' : 'none',
                          cursor: 'pointer',
                        }"
                      >
                        <i
                          class="bi"
                          :class="
                            item.inativo
                              ? 'bi-x-circle text-danger'
                              : 'bi-check-circle text-success'
                          "
                        ></i>
                        <span class="ms-2">{{ item.text }}</span>
                      </span>
                    </div>

                    <button
                      type="button"
                      class="btn btn-sm"
                      :class="item.inativo ? 'btn-outline-success' : 'btn-outline-secondary'"
                      @click="toggleItemAtivo('pre', i)"
                    >
                      <i :class="item.inativo ? 'bi bi-arrow-repeat' : 'bi bi-slash-circle'"></i>
                      {{ item.inativo ? 'Ativar' : 'Inativar' }}
                    </button>
                  </li>
                </ul>

                <!-- Novo item -->
                <div class="input-group mt-3">
                  <input
                    v-model="novoItemPre"
                    type="text"
                    placeholder="Digite novo item..."
                    class="form-control rounded-start-pill"
                  />
                  <button
                    class="btn btn-primary rounded-end-pill px-4"
                    type="button"
                    @click="addItem('pre')"
                  >
                    <i class="bi bi-plus-lg"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Checklist Pós -->
          <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
              <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i> Checklist Pós</h6>
              </div>

              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li
                    v-for="(item, i) in itemsPos"
                    :key="i"
                    class="list-group-item d-flex justify-content-between align-items-center"
                    :class="{ 'text-muted bg-light': item.inativo }"
                  >
                    <div class="flex-grow-1">
                      <!-- Modo de edição inline -->
                      <input
                        v-if="item.editando"
                        v-model="item.text"
                        class="form-control form-control-sm"
                        @keyup.enter="item.editando = false"
                        @blur="item.editando = false"
                        autofocus
                      />
                      <span
                        v-else
                        @dblclick="editarItem('pos', i)"
                        :style="{
                          textDecoration: item.inativo ? 'line-through' : 'none',
                          cursor: 'pointer',
                        }"
                      >
                        <i
                          class="bi"
                          :class="
                            item.inativo
                              ? 'bi-x-circle text-danger'
                              : 'bi-check-circle text-success'
                          "
                        ></i>
                        <span class="ms-2">{{ item.text }}</span>
                      </span>
                    </div>

                    <button
                      type="button"
                      class="btn btn-sm"
                      :class="item.inativo ? 'btn-outline-success' : 'btn-outline-secondary'"
                      @click="toggleItemAtivo('pos', i)"
                    >
                      <i :class="item.inativo ? 'bi bi-arrow-repeat' : 'bi bi-slash-circle'"></i>
                      {{ item.inativo ? 'Ativar' : 'Inativar' }}
                    </button>
                  </li>
                </ul>

                <!-- Novo item -->
                <div class="input-group mt-3">
                  <input
                    v-model="novoItemPos"
                    type="text"
                    placeholder="Digite novo item..."
                    class="form-control rounded-start-pill"
                  />
                  <button
                    class="btn btn-success rounded-end-pill px-4"
                    type="button"
                    @click="addItem('pos')"
                  >
                    <i class="bi bi-plus-lg"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Botão Salvar -->
      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-success px-4 rounded-pill">
          <i class="bi bi-check-circle me-2"></i> Salvar
        </button>
      </div>
    </form>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted, reactive, type Ref } from 'vue'
import Multiselect from 'vue-multiselect'
import Swal from 'sweetalert2'
import api from '@/services/axios.ts'
import 'vue-multiselect/dist/vue-multiselect.css'
import 'sweetalert2/dist/sweetalert2.min.css'

// 🔹 Recupera o ID vindo via state (não aparece na URL)
const etapaId = history.state?.id ?? localStorage.getItem('etapaId')
const etapaIdNumber = etapaId ? Number(etapaId) : null

// 🔹 Flag para indicar se é edição ou novo
const isEditando = etapaIdNumber !== null && !isNaN(etapaIdNumber)

// ===================== Tipos =====================
interface Item {
  text: string
  inativo?: boolean
  editando?: boolean
}

interface Parametro {
  id: number
  nome: string
  tipo: string
  limite: string | null
  min: number | null
  max: number | null
}

interface ItRev {
  id: number
  nome: string
  url?: string
}

interface Maquina {
  id: number
  codigo: string | number
  modelo: string
}

// ===================== Refs =====================
const editid = ref('')
const editnome = ref('')
const parametros = ref<Parametro[]>([])
const maxParametros = 3

// IT/REV
const iterev = ref<ItRev[]>([])
const opcoesItRev = ref<ItRev[]>([])

// Máquinas
const maquinas = ref<Maquina[]>([])
const opcoesMaquinas = ref<Maquina[]>([])

// Checklist
const itemsPre = ref<Item[]>([])
const itemsPos = ref<Item[]>([])
const novoItemPre = ref('')
const novoItemPos = ref('')

// Opções booleanas
const opcoes = reactive([
  {
    label: 'Mais de um colaborador?',
    name: 'maisDeUmColaborador',
    valores: [1, 0],
    model: null as number | null,
    required: true,
  },
  {
    label: 'Necessita de anexo?',
    name: 'necessitaAnexo',
    valores: [1, 0],
    model: null as number | null,
    required: true,
  },
  {
    label: 'MP obrigatório?',
    name: 'mpObrigatoria',
    valores: [1, 0],
    model: null as number | null,
    required: true,
  },
])

// ===================== Checklist =====================
function editarItem(tipo: 'pre' | 'pos', index: number) {
  const lista = tipo === 'pre' ? itemsPre : itemsPos
  lista.value[index].editando = true
}

function addItem(tipo: 'pre' | 'pos') {
  const novoItem = tipo === 'pre' ? novoItemPre : novoItemPos
  const lista = tipo === 'pre' ? itemsPre : itemsPos

  if (novoItem.value.trim()) {
    lista.value.push({ text: novoItem.value.trim(), inativo: false, editando: false })
    novoItem.value = ''
    mostrarToast('Item salvo com sucesso!', 'success')
  }
}

function toggleItemAtivo(tipo: 'pre' | 'pos', index: number) {
  const lista = tipo === 'pre' ? itemsPre : itemsPos
  lista.value[index].inativo = !lista.value[index].inativo
}

// ===================== Utilitários =====================
function mostrarToast(msg: string, tipo: 'success' | 'error' | 'warning' = 'success') {
  Swal.fire({
    icon: tipo,
    title: msg,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: '#fff',
  })
}

function valueLabel(value: number) {
  return value === 1 ? 'Sim' : 'Não'
}

function limparFormulario() {
  editid.value = ''
  editnome.value = ''
  parametros.value = []
  iterev.value = []
  maquinas.value = []
  itemsPre.value = []
  itemsPos.value = []
  opcoes.forEach((o) => (o.model = null))
}

// ===================== Parâmetros =====================
function adicionarParametro() {
  if (parametros.value.length < maxParametros) {
    parametros.value.push({
      id: Date.now(),
      nome: '',
      tipo: '',
      limite: null,
      min: null,
      max: null,
    })
  } else {
    mostrarToast(`Limite máximo de ${maxParametros} parâmetros atingido.`, 'warning')
  }
}

function removerParametro(index: number) {
  parametros.value.splice(index, 1)
}

// ===================== API =====================
async function carregarOpcoes<T>(endpoint: string, destino: Ref<T[]>) {
  try {
    const { data } = await api.get<T[]>(endpoint)
    destino.value = data
  } catch (error) {
    console.error(`Erro ao carregar ${endpoint}:`, error)
    mostrarToast(`Erro ao carregar ${endpoint}`, 'error')
  }
}

async function carregarEtapa(id: number) {
  try {
    const { data } = await api.get(`/api/etapas/${id}`)

    editid.value = data.id
    editnome.value = data.nome_etapa

    parametros.value =
      data.parametros?.map((p: any) => ({
        id: p.id,
        nome: p.nome,
        tipo: p.tipo,
        limite: p.limite,
        min: p.min,
        max: p.max,
      })) || []

    itemsPre.value = data.checklist_pre?.map((txt: string) => ({ text: txt })) || []
    itemsPos.value = data.checklist_pos?.map((txt: string) => ({ text: txt })) || []
    iterev.value = data.it_revs || []
    maquinas.value = data.maquinas || []

    const colaboracao = data.colaboracao_multipla ?? null
    const anexo = data.anexo ?? null
    const mp = data.obrigatorio_mp ?? null

    const opcCol = opcoes.find((o) => o.name === 'maisDeUmColaborador')
    if (opcCol) opcCol.model = colaboracao ? 1 : 0

    const opcAn = opcoes.find((o) => o.name === 'necessitaAnexo')
    if (opcAn) opcAn.model = anexo ? 1 : 0

    const opcMp = opcoes.find((o) => o.name === 'mpObrigatoria')
    if (opcMp) opcMp.model = mp ? 1 : 0
  } catch (error) {
    console.error('Erro ao carregar etapa:', error)
    mostrarToast('Não foi possível carregar os dados da etapa.', 'error')
  }
}

// ===================== Salvar =====================
const loading = ref(false)

async function salvarEtapa() {
  loading.value = true
  try {
    const { data: etapas } = await api.get('/api/etapas')
    const nomeExiste = etapas.some(
      (e: any) =>
        e.nome_etapa.trim().toLowerCase() === editnome.value.trim().toLowerCase() &&
        e.id !== etapaIdNumber,
    )

    if (nomeExiste) {
      mostrarToast('Já existe uma etapa com esse nome!', 'warning')
      return
    }

    const payload = {
      nome_etapa: editnome.value,
      colaboracao_multipla: opcoes.find((o) => o.name === 'maisDeUmColaborador')?.model ?? 0,
      anexo: opcoes.find((o) => o.name === 'necessitaAnexo')?.model ?? 0,
      obrigatorio_mp: opcoes.find((o) => o.name === 'mpObrigatoria')?.model ?? 0, // <--- nome correto
      parametros: parametros.value.map((p) => ({
        nome: p.nome,
        tipo: p.tipo,
        limite: p.limite || null,
        min: p.min,
        max: p.max,
      })),
      it_revs: iterev.value.map((i) => i.id),
      maquinas: maquinas.value.map((m) => m.id),
      checklist_pre: itemsPre.value.filter((i) => !i.inativo).map((i) => i.text),
      checklist_pos: itemsPos.value.filter((i) => !i.inativo).map((i) => i.text),
    }


    let response
    if (isEditando) {
      response = await api.put(`/api/etapas/${etapaIdNumber}`, payload)
      mostrarToast(`Etapa atualizada com sucesso!`, 'success')
    } else {
      response = await api.post('/api/etapas', payload)
      mostrarToast(`Etapa "${response.data.etapa.nome_etapa}" criada com sucesso!`, 'success')
      limparFormulario()
    }
  } catch (error: any) {
    console.error('Erro ao salvar etapa:', error)

    const errors = error.response?.data?.errors

    if (errors) {
      const mensagens = Object.values(errors).flat().join('<br>')
      Swal.fire({
        icon: 'error',
        title: 'Erro ao salvar a etapa',
        html: mensagens,
        confirmButtonColor: '#d33',
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Erro inesperado',
        text: 'Não foi possível salvar a etapa. Tente novamente mais tarde.',
      })
    }
  } finally {
    loading.value = false
  }
}

// ===================== Montagem =====================
onMounted(async () => {
  await Promise.all([
    carregarOpcoes<ItRev>('/api/itrev', opcoesItRev),
    carregarOpcoes<Maquina>('/api/maquinas', opcoesMaquinas),
  ])

  if (isEditando && etapaIdNumber) {
    await carregarEtapa(etapaIdNumber)
  }
})
</script>
