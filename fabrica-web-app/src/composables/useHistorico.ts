import { ref } from 'vue'
import api from '@/services/axios.ts'

export function useHistorico() {
  const historico = ref<any[]>([])
  const loading = ref(false)

  async function carregarHistorico(etapaId: number) {
    loading.value = true
    historico.value = []

    try {
      const { data } = await api.get(`/api/etapas/${etapaId}/historico`)
      historico.value = data
    } catch (e) {
      console.error('Erro ao carregar histórico da etapa', e)
    } finally {
      loading.value = false
    }
  }

  return { historico, loading, carregarHistorico }
}
