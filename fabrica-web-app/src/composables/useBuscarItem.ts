import { ref } from 'vue'
import api from '@/services/axios'

export interface Item {
  id: number
  descricao: string
  codigo: string
  anvisa?: string | null
  tipo: string
  fluxo: { id: number; nome_fluxo: string } | null
  filhos: Item[]
}

interface BuscarItemResponse {
  exists: boolean
  data?: {
    pai: Item
    filhos: Item[]
  }
}

export function useBuscarItem() {
  const sugestoes = ref<Item[]>([])
  const resultado = ref<Item | null>(null)
  const filhos = ref<Item[]>([])
  const carregando = ref(false)
  const erro = ref<string | null>(null)

  // AUTOCOMPLETE
  async function autocomplete(termo: string) {
    if (!termo || termo.length < 2) {
      sugestoes.value = []
      return
    }

    try {
      const { data } = await api.get('/api/itens-composicao/autocomplete', {
        params: { termo }
      })

      sugestoes.value = data
    } catch (e) {
      console.error(e)
    }
  }

  //  BUSCA DIRETA (pai + filhos)
  async function buscar(codigo: string, tipoItem: string) {
    if (!codigo) return null

    try {
      const { data } = await api.get<BuscarItemResponse>(
        '/api/itens-composicao/buscar',
        { params: { codigo, tipoItem } }
      )

      if (!data.exists || !data.data) return null

      return data.data  // ← { pai, filhos }
    } catch (e) {
      console.error(e)
      return null
    }
  }

  //  USADO QUANDO CLICA NA LISTA
  async function buscarItemCompleto(item: Item) {
    carregando.value = true
    erro.value = null

    try {
      const resultadoBusca = await buscar(item.codigo, item.tipo)

      if (resultadoBusca) {
        resultado.value = resultadoBusca.pai
        filhos.value = resultadoBusca.filhos
      }
    } catch (e) {
      erro.value = "Erro ao buscar item."
    } finally {
      carregando.value = false
    }
  }

  return {
    sugestoes,
    resultado,
    filhos,
    carregando,
    erro,

    autocomplete,
    buscar,
    buscarItemCompleto
  }
}
