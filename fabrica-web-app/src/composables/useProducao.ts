// src/composables/useProducao.ts
import { ref, computed } from 'vue'
import api from '@/services/axios'
import Swal from 'sweetalert2'

/* -----------------------------------------------------------
   TIPAGENS
----------------------------------------------------------- */

export interface Parametro {
  id: number
  nome: string
  valor?: string | number | null
}

export interface MP {
  id: number
  nome: string
  valor?: string | number | null
  unidade?: string | null
  lote?: string | null
}

export interface Checklist {
  id: number
  etapa_id: number
  nome: string
  tipo: 'pre' | 'pos'
}

export interface ApiEtapa {
  id: number
  nome_etapa: string
  maquinas?: { codigo: string }[]
  it_revs?: { nome: string }[]
  parametros?: { id: number; nome: string }[]
  obrigatorio_mp?: number
  checklists_pre?: Checklist[]
  checklists_pos?: Checklist[]
  pivot?: { ordem?: number }
}

export interface Etapa {
  id: number
  ordem: number
  nome: string
  maquina: string | null
  it: string | null
  status: 'pendente' | 'em_andamento' | 'concluida'
  parametros: Parametro[]
  obrigatorio_mp: number | null
  mps?: MP[]
  checklist_pre: Checklist[]
  checklist_pos: Checklist[]
}

export interface ApiUnidade {
  id: number
  unidade_codigo: string
  historicos: { acao: string }[]
}

export interface ApiProduto {
  id: number
  descricao: string
  anvisa: string
  codigo: string
  fluxo?: { etapas: ApiEtapa[] }
  // no seu JSON nem sempre vem; e quando vem, geralmente NÃO tem id garantido
  materias_primas?: { id?: number; descricao: string }[]
}

export interface ApiComponenteWrapper {
  id: number
  componente_id: number
  quantidade: number
  tipo: string
  componente: ApiProduto // mesma estrutura: codigo/descricao/anvisa/fluxo
}

export interface ApiItemPedido {
  id: number
  quantidade: number
  unidades: ApiUnidade[]
  produto: ApiProduto
  componentes?: ApiComponenteWrapper[]
}

export interface ItemProducao {
  id: number
  pedido_id: number
  pedido_item_unidade_id: number
  produto_id: number
  descricao: string
  anvisa: string
  codigo: string
  it: string | null
  maquinas: string | null
  qtdPlanejada: number
  qtdProduzida: number
  parametros: Parametro[]
  etapas: Etapa[]
  status: 'aguardando' | 'em_producao' | 'parado' | 'finalizado' | 'reprovado'
  tipo_item: 'produto' | 'componente'
  origem_id: number // id do produto/componente (pra debug)
}

export interface Pedido {
  id: number
  numero_pedido: string
  doutor: string
  paciente: string
  lote: string
  tipo: string
  data_pedido: string
  data_entrega_prevista?: string
  status: string

  // backend pode mandar snake_case ou camelCase
  pedido_itens?: ApiItemPedido[]
  pedidoItens?: ApiItemPedido[]

  etapa_atual: Etapa | null
}

interface ProducaoCallbacks {
  onAbrirChecklist?: (item: ItemProducao, tipo: 'pre' | 'pos') => void
  onAbrirPausa?: (item: ItemProducao) => void
}

/* -----------------------------------------------------------
   HELPERS
----------------------------------------------------------- */

function getPedidoItens(data: Pedido): ApiItemPedido[] {
  return (data.pedido_itens ?? data.pedidoItens ?? []) as ApiItemPedido[]
}

function ordenarEtapasPorPivot(etapas: ApiEtapa[]): ApiEtapa[] {
  return [...(etapas ?? [])].sort((a, b) => {
    const oa = a?.pivot?.ordem ?? 9999
    const ob = b?.pivot?.ordem ?? 9999
    return oa - ob
  })
}

function definirStatus(historicos: { acao: string }[]): ItemProducao['status'] {
  const ultima = historicos?.[historicos.length - 1]?.acao
  return (
    {
      INICIO: 'em_producao',
      PAUSA: 'parado',
      FINALIZACAO: 'finalizado',
      REPROVACAO: 'reprovado',
    }[ultima] ?? 'aguardando'
  )
}

function montarEtapas(etapasApi: ApiEtapa[], materiasPrimas: { id?: number; descricao: string }[]): Etapa[] {
  const etapasOrdenadas = ordenarEtapasPorPivot(etapasApi)

  return etapasOrdenadas.map((et, idx) => {
    const ordem = et?.pivot?.ordem ?? idx + 1

    return {
      id: et.id,
      ordem,
      nome: et.nome_etapa,
      maquina: et.maquinas?.[0]?.codigo ?? null,
      it: et.it_revs?.[0]?.nome ?? null,

      // por enquanto: primeira etapa fica "em_andamento"
      // (se depois você tiver etapa atual por unidade, dá pra ajustar)
      status: idx === 0 ? 'em_andamento' : 'pendente',

      parametros:
        et.parametros?.map((p) => ({
          id: p.id,
          nome: p.nome,
          valor: '',
        })) ?? [],

      obrigatorio_mp: et.obrigatorio_mp ?? null,

      // ✅ MP sem id no payload => gera id estável com índice
      mps: et.obrigatorio_mp
        ? (materiasPrimas ?? []).map((mp, i) => ({
            id: mp.id ?? i + 1,
            nome: mp.descricao,
            valor: null,
            unidade: null,
            lote: null,
          }))
        : [],

      checklist_pre: et.checklists_pre ?? [],
      checklist_pos: et.checklists_pos ?? [],
    }
  })
}

/* -----------------------------------------------------------
   COMPOSABLE useProducao
----------------------------------------------------------- */

export function useProducao(callbacks?: ProducaoCallbacks) {
  const pedido = ref<Pedido | null>(null)
  const itens = ref<ItemProducao[]>([])
  const etapaAtual = ref<Etapa | null>(null)

  const loading = ref(true)
  const salvando = ref(false)

  /* -----------------------------------------------------------
     🔵 CARREGAR PEDIDO
  ----------------------------------------------------------- */
  async function carregarPedido(id: number | string) {
    try {
      loading.value = true

      const { data } = await api.get<Pedido>(`/api/pedidos/${id}`)

      console.log('[DEBUG] Pedido completo:', data)
      console.log('[DEBUG] pedido_itens:', data.pedido_itens)
      console.log('[DEBUG] pedidoItens:', data.pedidoItens)
      console.log('[DEBUG] total itens:', getPedidoItens(data).length)

      pedido.value = data
      etapaAtual.value = data.etapa_atual ?? null

      itens.value = montarItens(data)

      for (const item of itens.value) {
        await preencherParametrosSalvos(item)
      }
    } catch (e) {
      console.error('Erro ao carregar pedido:', e)
    } finally {
      loading.value = false
    }
  }

  /* -----------------------------------------------------------
     MONTAR ITENS (produto + componentes)
  ----------------------------------------------------------- */
  function montarItens(data: Pedido): ItemProducao[] {
    const pedidoItens = getPedidoItens(data)

    return pedidoItens.flatMap((i: ApiItemPedido) => {
      const unidades = i.unidades ?? []

      const produto = i.produto
      const etapasProduto = produto?.fluxo?.etapas ?? []
      const mpsProduto = produto?.materias_primas ?? []

      // monta cards do PRODUTO por unidade
      const cardsProduto = unidades.map((unidade: ApiUnidade): ItemProducao => {
        const status = definirStatus(unidade.historicos ?? [])
        const etapasMontadas = montarEtapas(etapasProduto, mpsProduto)

        // pega a primeira etapa por ordem
        const primeira = [...etapasMontadas].sort((a, b) => a.ordem - b.ordem)[0]

        return {
          id: i.id,
          pedido_id: data.id,
          pedido_item_unidade_id: unidade.id,
          produto_id: produto.id,

          descricao: produto.descricao,
          anvisa: produto.anvisa,
          codigo: produto.codigo,

          it: primeira?.it ?? null,
          maquinas: primeira?.maquina ?? null,

          qtdProduzida: parseInt((unidade.unidade_codigo ?? '').split('/')[0]) || 1,
          qtdPlanejada: i.quantidade,

          parametros: (primeira?.parametros ?? []).map((p) => ({ ...p, valor: null })),
          etapas: etapasMontadas,
          status,

          tipo_item: 'produto',
          origem_id: produto.id,
        }
      })

      // monta cards dos COMPONENTES por unidade
      const comps = i.componentes ?? []
      const cardsComponentes = comps.flatMap((c) => {
        const comp = c.componente
        const etapasComp = comp?.fluxo?.etapas ?? []
        const mpsComp = comp?.materias_primas ?? []

        return unidades.map((unidade: ApiUnidade): ItemProducao => {
          const status = definirStatus(unidade.historicos ?? [])
          const etapasMontadas = montarEtapas(etapasComp, mpsComp)
          const primeira = [...etapasMontadas].sort((a, b) => a.ordem - b.ordem)[0]

          return {
            // id do card: usa wrapper id do componente pra diferenciar
            id: c.id,
            pedido_id: data.id,
            pedido_item_unidade_id: unidade.id,
            produto_id: comp.id,

            descricao: comp.descricao,
            anvisa: comp.anvisa,
            codigo: comp.codigo,

            it: primeira?.it ?? null,
            maquinas: primeira?.maquina ?? null,

            qtdProduzida: parseInt((unidade.unidade_codigo ?? '').split('/')[0]) || 1,
            qtdPlanejada: c.quantidade ?? 1,

            parametros: (primeira?.parametros ?? []).map((p) => ({ ...p, valor: null })),
            etapas: etapasMontadas,
            status,

            tipo_item: 'componente',
            origem_id: comp.id,
          }
        })
      })

      return [...cardsProduto, ...cardsComponentes]
    })
  }

  /* -----------------------------------------------------------
     BUSCAR PARAMETROS SALVOS
  ----------------------------------------------------------- */
  interface ApiParametroSalvo {
    parametro_id: number
    valor: string | number | null
  }

  async function buscarParametros(item: ItemProducao): Promise<ApiParametroSalvo[]> {
    const etapa = item.etapas.find((e) => e.status === 'em_andamento')
    if (!etapa) return []

    const { data } = await api.get<ApiParametroSalvo[]>('/api/pedido-parametros-producao', {
      params: {
        pedido_id: pedido.value?.id,
        produto_id: item.produto_id,
        pedido_item_unidade_id: item.pedido_item_unidade_id,
        etapa_id: etapa.id,
      },
    })

    return data ?? []
  }

  async function preencherParametrosSalvos(item: ItemProducao) {
    const etapa = item.etapas.find((e) => e.status === 'em_andamento')
    if (!etapa) return

    const valores = await buscarParametros(item)

    valores.forEach((v) => {
      const param = etapa.parametros.find((p) => p.id === v.parametro_id)
      if (param) param.valor = v.valor
    })
  }

  /* -----------------------------------------------------------
     SALVAR PARAMETROS
  ----------------------------------------------------------- */
  async function salvarParametros(item: ItemProducao) {
    const etapa = item.etapas.find((e) => e.status === 'em_andamento')
    if (!etapa) return

    await api.post('/api/parametros-producao', {
      pedido_id: pedido.value?.id,
      produto_id: item.produto_id,
      pedido_item_unidade_id: item.pedido_item_unidade_id,
      etapa_id: etapa.id,
      usuario_id: 1,
      parametros: etapa.parametros.map((p) => ({
        parametro_id: p.id,
        valor: p.valor,
      })),
    })
  }

  /* -----------------------------------------------------------
     HISTORICO
  ----------------------------------------------------------- */
  async function registrarHistorico(item: ItemProducao, acao: string, observacao = '') {
    const etapa = item.etapas.find((e) => e.status === 'em_andamento')
    if (!etapa) return

    await api.post('/api/historico-producao', {
      pedido_id: pedido.value?.id,
      pedido_item_id: item.id,
      pedido_item_unidade_id: item.pedido_item_unidade_id,
      etapa_id: etapa.id,
      usuario_id: 1,
      acao,
      observacao,
    })
  }

  /* -----------------------------------------------------------
     CONTROLES
  ----------------------------------------------------------- */
  function iniciar(item: ItemProducao) {
    const etapa = item.etapas.find((e) => e.status === 'em_andamento')

    if (etapa?.checklist_pre?.length) {
      callbacks?.onAbrirChecklist?.(item, 'pre')
      return
    }

    item.status = 'em_producao'
    registrarHistorico(item, 'INICIO', 'Início da produção')
  }

  function parar(item: ItemProducao) {
    callbacks?.onAbrirPausa?.(item)
  }

  async function finalizar(item: ItemProducao) {
    const etapa = item.etapas.find((e) => e.status === 'em_andamento')
    if (!etapa) return

    if (etapa.checklist_pos?.length) {
      callbacks?.onAbrirChecklist?.(item, 'pos')
      return
    }

    await registrarHistorico(item, 'FINALIZACAO', 'Etapa concluída')
    etapa.status = 'concluida'

    if (pedido.value?.id) await carregarPedido(pedido.value.id)
  }

  async function reprovar(item: ItemProducao) {
    const { value: obs } = await Swal.fire({
      title: 'Motivo da reprovação',
      input: 'textarea',
      inputLabel: 'Descreva o motivo',
      inputPlaceholder: 'Digite aqui...',
      inputAttributes: { 'aria-label': 'Digite o motivo' },
      showCancelButton: true,
      confirmButtonText: 'Reprovar',
      cancelButtonText: 'Cancelar',
    })

    if (!obs) return

    item.status = 'reprovado'
    await registrarHistorico(item, 'REPROVACAO', obs)
  }

  /* -----------------------------------------------------------
     PROGRESSO
  ----------------------------------------------------------- */
  const totalConcluidos = computed(() => {
    const arr = Array.isArray(itens.value) ? itens.value : []
    return arr.reduce((s, it) => s + (it.status === 'finalizado' ? 1 : 0), 0)
  })

  const progressoGeral = computed(() => {
    const arr = Array.isArray(itens.value) ? itens.value : []
    if (arr.length === 0) return 0
    return Math.round((totalConcluidos.value / arr.length) * 100)
  })

  return {
    pedido,
    itens,
    etapaAtual,
    loading,

    carregarPedido,
    salvarParametros,
    buscarParametros,
    preencherParametrosSalvos,

    iniciar,
    parar,
    finalizar,
    reprovar,
    registrarHistorico,

    progressoGeral,
    totalConcluidos,
    salvando,
  }
}
