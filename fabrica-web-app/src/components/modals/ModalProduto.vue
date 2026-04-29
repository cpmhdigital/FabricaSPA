<template>
  <div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

        <!-- Cabeçalho -->
        <div class="modal-header bg-success text-white py-3 px-4">
          <div class="d-flex align-items-center">
            <i class="bi bi-box-seam fs-4 me-2"></i>
            <h5 class="modal-title mb-0" id="modalProdutoLabel">
              {{ produto.descricao || 'Novo Produto' }}
            </h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- Corpo -->
        <div class="modal-body bg-light p-4">

          <!-- 🔹 Dados principais -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
              <h6 class="fw-semibold text-success mb-0">
                <i class="bi bi-info-circle me-1"></i> Dados do Produto
              </h6>
            </div>
            <div class="card-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label class="form-label fw-semibold small text-muted">Código</label>
                  <input
                    v-model="produto.codigo"
                    placeholder="Ex: PC-001"
                    class="form-control"
                    @blur="verificarCodigoProduto"
                  />
                  <div v-if="codigoJaUsado.produto" class="mt-1 small text-danger">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Código já está em uso.
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label fw-semibold small text-muted">Descrição</label>
                  <input v-model="produto.descricao" placeholder="Descrição do produto" class="form-control" />
                </div>

                <div class="col-md-3">
                  <label class="form-label fw-semibold small text-muted">Registro ANVISA</label>
                  <input v-model="produto.anvisa" placeholder="Número ANVISA" class="form-control" />
                </div>

                <div class="col-md-3">
                  <label class="form-label fw-semibold small text-muted">Fluxo de Produção</label>
                  <select v-model="produto.fluxo_id" class="form-select">
                    <option disabled value="">Selecione o fluxo</option>
                    <option v-for="fluxo in fluxosObj" :key="fluxo.id" :value="fluxo.id">
                      {{ fluxo.nome_fluxo }}
                    </option>
                  </select>
                </div>

              </div>
            </div>
          </div>

          <!-- 🔹 Estrutura do Produto -->
          <div class="accordion" id="accordionProduto">

            <div class="accordion-item border-0 shadow-sm mb-3">
              <h2 class="accordion-header" id="headingMP">
                <button
                  class="accordion-button collapsed bg-white fw-semibold text-success"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseMP"
                >
                  <i class="bi bi-droplet me-2"></i> Matérias-Primas
                </button>
              </h2>
              <div id="collapseMP" class="accordion-collapse collapse" data-bs-parent="#accordionProduto">
                <div class="accordion-body bg-light-subtle">
                  <ListaItemProduto
                    titulo="Matérias-Primas do Produto"
                    tituloSingular="Matéria-Prima"
                    icone="bi-droplet"
                    v-model="produto.materiasPrimas"
                    :campos="camposMP"
                    :resultados="resultadosBuscaMP"
                    @buscar="onBuscaMP"
                    @adicionar="adicionarMPProduto"
                    @selecionar="selecionarMPProduto"
                    @remover="removerMPProduto"
                  />
                </div>
              </div>
            </div>

            <div class="accordion-item border-0 shadow-sm mb-3">
              <h2 class="accordion-header" id="headingComp">
                <button
                  class="accordion-button collapsed bg-white fw-semibold text-success"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseComp"
                >
                  <i class="bi bi-box me-2"></i> Componentes
                </button>
              </h2>
              <div id="collapseComp" class="accordion-collapse collapse" data-bs-parent="#accordionProduto">
                <div class="accordion-body bg-light-subtle">
                  <ListaItemProduto
                    titulo="Componentes"
                    tituloSingular="Componente"
                    icone="bi-box"
                    v-model="produto.componentes"
                    :campos="camposComponente"
                    :resultados="resultadosBuscaComponente"
                    @buscar="onBuscaComponente"
                    @adicionar="adicionarComponente"
                    @selecionar="selecionarComponente"
                    @remover="removerComponente"
                    :campos-mp="camposMP"
                    @adicionar-mp="adicionarMPComponente"
                    @remover-mp="removerMPComponente"
                  />
                </div>
              </div>
            </div>

            <div class="accordion-item border-0 shadow-sm mb-3">
              <h2 class="accordion-header" id="headingParaf">
                <button
                  class="accordion-button collapsed bg-white fw-semibold text-success"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseParaf"
                >
                  <i class="bi bi-nut me-2"></i> Parafusos
                </button>
              </h2>
              <div id="collapseParaf" class="accordion-collapse collapse" data-bs-parent="#accordionProduto">
                <div class="accordion-body bg-light-subtle">
                  <ListaItemProduto
                    titulo="Parafusos"
                    tituloSingular="Parafuso"
                    icone="bi-nut"
                    v-model="produto.parafusos"
                    :campos="camposParafuso"
                    :campos-mp="camposMP"
                    :resultados="resultadosBuscaParafuso"
                    @buscar="onBuscaParafuso"
                    @adicionar="adicionarParafuso"
                    @selecionar="selecionarParafuso"
                    @remover="removerParafuso"
                    @adicionar-mp="adicionarMPParafuso"
                    @remover-mp="removerMPParafuso"
                  />
                </div>
              </div>
            </div>

            <div class="accordion-item border-0 shadow-sm mb-3">
              <h2 class="accordion-header" id="headingEmb">
                <button
                  class="accordion-button collapsed bg-white fw-semibold text-success"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseEmb"
                >
                  <i class="bi bi-bag me-2"></i> Embalagens
                </button>
              </h2>
              <div id="collapseEmb" class="accordion-collapse collapse" data-bs-parent="#accordionProduto">
                <div class="accordion-body bg-light-subtle">
                  <ListaItemProduto
                    titulo="Embalagens"
                    tituloSingular="Embalagem"
                    icone="bi-bag"
                    v-model="produto.embalagens"
                    :campos="camposEmbalagem"
                    :resultados="resultadosBuscaEmbalagem"
                    @buscar="onBuscaEmbalagem"
                    @adicionar="adicionarEmbalagemProduto"
                    @selecionar="selecionarEmbalagem"
                    @remover="removerEmbalagemProduto"
                  />
                </div>
              </div>
            </div>

            <div class="accordion-item border-0 shadow-sm">
              <h2 class="accordion-header" id="headingIns">
                <button
                  class="accordion-button collapsed bg-white fw-semibold text-success"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseIns"
                >
                  <i class="bi bi-tools me-2"></i> Insumos
                </button>
              </h2>
              <div id="collapseIns" class="accordion-collapse collapse" data-bs-parent="#accordionProduto">
                <div class="accordion-body bg-light-subtle">
                  <ListaItemProduto
                    titulo="Insumos"
                    tituloSingular="Insumo"
                    icone="bi-tools"
                    v-model="produto.insumos"
                    :campos="camposInsumo"
                    :resultados="resultadosBuscaInsumo"
                    @buscar="onBuscaInsumo"
                    @adicionar="adicionarInsumoProduto"
                    @selecionar="selecionarInsumo"
                    @remover="removerInsumoProduto"
                  />
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Rodapé -->
        <div class="modal-footer bg-white border-0 py-3 px-4">
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i> Cancelar
            </button>
            <button type="button" class="btn btn-success px-4" @click="salvarProduto">
              <i class="bi bi-check-circle me-1"></i> Salvar Produto
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import ListaItemProduto from '@/components/ListaItemProduto.vue'
import api from '@/services/axios.ts'
import Swal from 'sweetalert2'

type Item = { id: number; codigo?: string; descricao: string; anvisa?: string; fluxo_id?: string }
type Componente = Item & { materiasPrimas: Item[] }
type Produto = {
  codigo: string
  descricao: string
  anvisa: string
  fluxo_id: string
  materiasPrimas: Item[]
  componentes: Componente[]
  parafusos: Componente[]
  embalagens: Item[]
  insumos: Item[]
}

// Fluxos
type Fluxo = { id: number; nome_fluxo: string }
const fluxosObj = ref<Fluxo[]>([])

onMounted(async () => {
  try {
    const response = await api.get('/api/fluxos')
    fluxosObj.value = response.data
  } catch (err) {
    console.error('Erro ao carregar fluxos:', err)
  }
})

// Produto principal
const produto = ref<Produto>({
  codigo: '',
  descricao: '',
  anvisa: '',
  fluxo_id: '',
  materiasPrimas: [],
  componentes: [],
  parafusos: [],
  embalagens: [],
  insumos: [],
})

// Campos
interface CampoOption {
  label: string
  value: string | number
}
interface Campo {
  key: string
  label: string
  type: 'text' | 'select'
  options?: CampoOption[]
  class?: string
}

const camposMP: Campo[] = [
  { key: 'codigo', label: 'Código', type: 'text', class: 'w-25' },
  { key: 'descricao', label: 'Descrição', type: 'text', class: 'w-25' },
  { key: 'anvisa', label: 'Registro ANVISA', type: 'text', class: 'w-25' },
]

const camposComponente = computed<Campo[]>(() => [
  { key: 'codigo', label: 'Código', type: 'text', class: 'w-20' },
  { key: 'descricao', label: 'Descrição', type: 'text', class: 'w-25' },
  { key: 'anvisa', label: 'Registro ANVISA', type: 'text', class: 'w-25' },
  {
    key: 'fluxo_id',
    label: 'Fluxo',
    type: 'select',
    class: 'w-25',
    options: fluxosObj.value.map((f) => ({ label: f.nome_fluxo, value: f.id })),
  },
])

const camposParafuso = computed(() => camposComponente.value)
const camposEmbalagem = camposMP
const camposInsumo = camposMP

// Resultados de busca
const resultadosBuscaMP = ref<Item[]>([])
const resultadosBuscaComponente = ref<Componente[]>([])
const resultadosBuscaParafuso = ref<Item[]>([])
const resultadosBuscaEmbalagem = ref<Item[]>([])
const resultadosBuscaInsumo = ref<Item[]>([])

type TipoItem = 'materia_prima' | 'componente' | 'parafuso' | 'embalagem' | 'insumo'
// ---------------------------
// Busca inteligente (produto, componente, parafuso...)
// ---------------------------
// ✅ Busca de produto/componente/parafuso com estrutura recursiva
const onBuscaItem = async (
  codigo: string,
  tipoItem: TipoItem | 'produto',
  resultados: Ref<Item[] | Componente[]>,
  selecionarFn?: (item: any, index?: number) => void,
  index?: number,
) => {
  if (!codigo) return

  try {
    const res = await api.get(`/api/itens/buscar`, {
      params: { codigo, tipoItem },
    })

    const { data, exists } = res.data

    if (!exists || !data) {
      await Swal.fire({
        icon: 'info',
        title: 'Não encontrado',
        text: `Nenhum ${tipoItem} encontrado com o código ${codigo}.`,
      })
      return
    }

    // 🔹 Se for produto, monta toda a estrutura
    if (tipoItem === 'produto') {
      const item = data

      produto.value = {
        codigo: '', // novo código
        descricao: `${item.descricao} (Cópia)`,
        anvisa: item.anvisa || '',
        fluxo_id: item.fluxo_id || '',
        materiasPrimas: item.filhos?.filter((f: any) => f.tipo === 'materia_prima') || [],
        componentes:
          item.filhos
            ?.filter((f: any) => f.tipo === 'componente')
            .map((c: any) => ({
              ...c,
              materiasPrimas: c.materiasPrimas || [],
            })) || [],
        parafusos:
          item.filhos
            ?.filter((f: any) => f.tipo === 'parafuso')
            .map((p: any) => ({
              ...p,
              materiasPrimas: p.materiasPrimas || [],
            })) || [],
        embalagens: item.filhos?.filter((f: any) => f.tipo === 'embalagem') || [],
        insumos: item.filhos?.filter((f: any) => f.tipo === 'insumo') || [],
      }

      await Swal.fire({
        icon: 'success',
        title: 'Produto carregado!',
        text: `O produto "${item.descricao}" foi clonado com sucesso.`,
      })
      return
    }

    // 🔹 Caso seja componente/parafuso (com filhos)
    if (tipoItem === 'componente' || tipoItem === 'parafuso') {
      const filhosMP: Item[] = data.filhos?.filter((f: any) => f.tipo === 'materia_prima') || []

      const item = {
        ...data,
        materiasPrimas: filhosMP,
      }

      if (index !== undefined) {
        if (tipoItem === 'componente') {
          produto.value.componentes[index] = item
        } else {
          produto.value.parafusos[index] = item
        }
      } else if (selecionarFn) {
        selecionarFn(item)
      }

      resultados.value = [item]
      return
    }

    // 🔹 Outros tipos simples
    resultados.value = [data]
    if (selecionarFn) selecionarFn(data, index)
  } catch (err) {
    console.error(`Erro ao buscar ${tipoItem}:`, err)
    Swal.fire({
      icon: 'error',
      title: 'Erro na busca',
      text: `Não foi possível buscar o ${tipoItem}.`,
    })
  }
}

// Produto
const onBuscaMP = (codigo: string) =>
  onBuscaItem(codigo, 'materia_prima', resultadosBuscaMP, selecionarMPProduto)

const onBuscaComponente = (codigo: string) =>
  onBuscaItem(codigo, 'componente', resultadosBuscaComponente, selecionarComponente)

const onBuscaParafuso = (codigo: string) =>
  onBuscaItem(codigo, 'parafuso', resultadosBuscaParafuso, selecionarParafuso)

const onBuscaEmbalagem = (codigo: string) =>
  onBuscaItem(codigo, 'embalagem', resultadosBuscaEmbalagem, selecionarEmbalagem)

const onBuscaInsumo = (codigo: string) =>
  onBuscaItem(codigo, 'insumo', resultadosBuscaInsumo, selecionarInsumo)

// ---------------------------
// Mapa global de MPs
// ---------------------------
const mapaMP = ref(new Map<string, Item>())

const registrarMP = (mp: Item) => {
  if (mp.codigo) {
    mapaMP.value.set(mp.codigo, mp)
  }
}

const preencherMPPorCodigo = (mp: Item) => {
  if (!mp.codigo) return
  const existente = mapaMP.value.get(mp.codigo)
  if (existente && (existente.descricao || existente.anvisa)) {
    mp.descricao = existente.descricao
    mp.anvisa = existente.anvisa
  }
}

// ---------------------------
// Funções genéricas para MPs
// ---------------------------
const adicionarMP = (destino: Item[], mp?: Item) => {
  const novaMP: Item = mp ? { ...mp } : { id: Date.now(), codigo: '', descricao: '', anvisa: '' }
  preencherMPPorCodigo(novaMP)
  destino.push(novaMP)
  registrarMP(novaMP)
}

// Produto
const selecionarMPProduto = (mp: Item) => adicionarMP(produto.value.materiasPrimas, mp)
const adicionarMPProduto = () => adicionarMP(produto.value.materiasPrimas)
const removerMPProduto = (i: number) => produto.value.materiasPrimas.splice(i, 1)

// Componentes
const selecionarMPComponente = (index: number, mp: Item) =>
  adicionarMP(produto.value.componentes[index].materiasPrimas, mp)
const adicionarMPComponente = (index: number) =>
  adicionarMP(produto.value.componentes[index].materiasPrimas)
const removerMPComponente = (index: number, mpIndex: number) =>
  produto.value.componentes[index].materiasPrimas.splice(mpIndex, 1)

// Parafusos
const selecionarMPParafuso = (index: number, mp: Item) =>
  adicionarMP(produto.value.parafusos[index].materiasPrimas, mp)
const adicionarMPParafuso = (index: number) =>
  adicionarMP(produto.value.parafusos[index].materiasPrimas)
const removerMPParafuso = (index: number, mpIndex: number) =>
  produto.value.parafusos[index].materiasPrimas.splice(mpIndex, 1)

// Watch global para preencher automaticamente MPs
watch(
  () => [
    produto.value.materiasPrimas,
    produto.value.componentes.flatMap((c) => c.materiasPrimas),
    produto.value.parafusos.flatMap((p) => p.materiasPrimas),
  ],
  (listas) => {
    listas.forEach((lista) => {
      lista.forEach((mp) => {
        if (mp.codigo) {
          preencherMPPorCodigo(mp)
          registrarMP(mp)
        }
      })
    })
  },
  { deep: true },
)

// ---------------------------
// Componentes/Parafusos/Embalagens/Insumos
// ---------------------------
const selecionarComponente = (p: any) =>
  produto.value.componentes.push({
    ...p,
    materiasPrimas: p.materiasPrimas || [], // ✅ mantém se veio da API
  })

const adicionarComponente = () =>
  produto.value.componentes.push({
    id: Date.now(),
    descricao: '',
    codigo: '',
    anvisa: '',
    fluxo_id: '',
    materiasPrimas: [],
  })
const removerComponente = (i: number) => produto.value.componentes.splice(i, 1)

const selecionarParafuso = (p: any) =>
  produto.value.parafusos.push({
    ...p,
    materiasPrimas: p.materiasPrimas || [], // ✅ idem
  })

const adicionarParafuso = () =>
  produto.value.parafusos.push({
    id: Date.now(),
    descricao: '',
    codigo: '',
    anvisa: '',
    fluxo_id: '',
    materiasPrimas: [],
  })
const removerParafuso = (i: number) => produto.value.parafusos.splice(i, 1)

const selecionarEmbalagem = (e: Item) => produto.value.embalagens.push({ ...e })
const adicionarEmbalagemProduto = () =>
  produto.value.embalagens.push({ id: Date.now(), descricao: '', codigo: '', anvisa: '' })
const removerEmbalagemProduto = (i: number) => produto.value.embalagens.splice(i, 1)

const selecionarInsumo = (i: Item) => produto.value.insumos.push({ ...i })
const adicionarInsumoProduto = () =>
  produto.value.insumos.push({ id: Date.now(), descricao: '', codigo: '', anvisa: '' })
const removerInsumoProduto = (i: number) => produto.value.insumos.splice(i, 1)

// ---------------------------
// Modal
// ---------------------------

const codigoJaUsado = ref({ produto: false, componente: false })

const verificarCodigoProduto = async () => {
  if (!produto.value.codigo) {
    codigoJaUsado.value.produto = false
    return
  }

  try {
    const res = await api.get('/api/itens/verificar-codigo', {
      params: { codigo: produto.value.codigo },
    })
    codigoJaUsado.value.produto = res.data.produto
    codigoJaUsado.value.componente = res.data.componente
  } catch (err) {
    console.error('Erro ao verificar código:', err)
  }
}
// ---------------------------
// Salvar Produto (simplificado para debug)
// ---------------------------
const salvarProduto = async () => {
  try {
    Swal.fire({
      title: 'Salvando produto...',
      text: 'Por favor, aguarde.',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading(),
    })

    // 🔹 Validação de fluxos
    for (const c of produto.value.componentes) {
      if (!c.fluxo_id)
        throw new Error(`O componente "${c.descricao || 'Novo'}" precisa ter um fluxo.`)
    }
    for (const p of produto.value.parafusos) {
      if (!p.fluxo_id)
        throw new Error(`O parafuso "${p.descricao || 'Novo'}" precisa ter um fluxo.`)
    }

    // 🔹 Estrutura do produto para envio
    const produtoDebug = {
      tipo: 'produto',
      codigo: produto.value.codigo,
      descricao: produto.value.descricao,
      anvisa: produto.value.anvisa,
      fluxo_id: produto.value.fluxo_id,
      materiasPrimas: produto.value.materiasPrimas.map((mp) => ({ ...mp, tipo: 'materia_prima' })),
      componentes: produto.value.componentes.map((c) => ({
        ...c,
        tipo: 'componente',
        fluxo_id: c.fluxo_id,
        materiasPrimas: c.materiasPrimas.map((mp) => ({ ...mp, tipo: 'materia_prima' })),
      })),
      parafusos: produto.value.parafusos.map((p) => ({
        ...p,
        tipo: 'parafuso',
        fluxo_id: p.fluxo_id,
        materiasPrimas: p.materiasPrimas.map((mp) => ({ ...mp, tipo: 'materia_prima' })),
      })),
      embalagens: produto.value.embalagens.map((e) => ({ ...e, tipo: 'embalagem' })),
      insumos: produto.value.insumos.map((i) => ({ ...i, tipo: 'insumo' })),
    }

    console.log('DEBUG produtoDebug para envio:', produtoDebug)

    // 🔹 Mapas de IDs para evitar duplicação
    const codigoMap = new Map<string, number>()

    // 🔹 Função para criar ou obter ID de item
    const criarOuBuscarItem = async (item: any) => {
      if (!item.codigo) return null
      if (codigoMap.has(item.codigo)) return codigoMap.get(item.codigo)!

      // Tenta buscar pelo backend
      try {
        const res = await api.get('/api/itens/buscar', {
          params: { codigo: item.codigo, tipoItem: item.tipo },
        })
        const idExistente = res.data.data?.id
        if (idExistente) {
          codigoMap.set(item.codigo, idExistente)
          return idExistente
        }
      } catch {}

      // Cria item novo
      const res = await api.post('/api/itens', {
        codigo: item.codigo,
        descricao: item.descricao,
        tipo: item.tipo,
        anvisa: item.anvisa,
        fluxo_id: item.fluxo_id || null,
      })
      const id = res.data.id
      codigoMap.set(item.codigo, id)
      return id
    }

    // 🔹 Criação de todos os itens
    const criarTodosItens = async () => {
      // Produto principal
      await criarOuBuscarItem(produtoDebug)

      // MPs do produto
      for (const mp of produtoDebug.materiasPrimas) await criarOuBuscarItem(mp)

      // Componentes e MPs
      for (const c of produtoDebug.componentes) {
        await criarOuBuscarItem(c)
        for (const mp of c.materiasPrimas) await criarOuBuscarItem(mp)
      }

      // Parafusos e MPs
      for (const p of produtoDebug.parafusos) {
        await criarOuBuscarItem(p)
        for (const mp of p.materiasPrimas) await criarOuBuscarItem(mp)
      }

      // Embalagens e insumos
      for (const e of produtoDebug.embalagens) await criarOuBuscarItem(e)
      for (const i of produtoDebug.insumos) await criarOuBuscarItem(i)
    }

    await criarTodosItens()

    // 🔹 Função para criar composições pai → filho
    const criarComposicao = async (pai: any, filhos: any[], tipo: string) => {
      const paiId = codigoMap.get(pai.codigo)
      if (!paiId) return
      for (const filho of filhos) {
        const filhoId = codigoMap.get(filho.codigo)
        if (!filhoId) continue
        await api.post('/api/itens-composicao', {
          item_pai_id: paiId,
          item_filho_id: filhoId,
          tipo,
        })
      }
    }

    // 🔹 Criação das composições
    await criarComposicao(produtoDebug, produtoDebug.materiasPrimas, 'materia_prima')
    await criarComposicao(produtoDebug, produtoDebug.componentes, 'componente')
    await criarComposicao(produtoDebug, produtoDebug.parafusos, 'parafuso')
    await criarComposicao(produtoDebug, produtoDebug.embalagens, 'embalagem')
    await criarComposicao(produtoDebug, produtoDebug.insumos, 'insumo')

    // MPs internas de componentes/parafusos
    for (const c of produtoDebug.componentes) {
      await criarComposicao(c, c.materiasPrimas, 'materia_prima')
    }
    for (const p of produtoDebug.parafusos) {
      await criarComposicao(p, p.materiasPrimas, 'materia_prima')
    }

    Swal.close()
    await Swal.fire({
      icon: 'success',
      title: 'Produto salvo!',
      text: 'Produto e todos os itens foram enviados com sucesso!',
    })
  } catch (err: any) {
    console.error('❌ Erro ao salvar produto:', err)
    Swal.close()
    await Swal.fire({
      icon: 'error',
      title: 'Erro ao salvar',
      text: err.message || 'Ocorreu um erro ao salvar o produto.',
    })
  }
}
</script>

