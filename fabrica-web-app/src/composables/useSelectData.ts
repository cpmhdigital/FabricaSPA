import { ref } from 'vue'
import api from '@/services/axios.ts'

export function useSelectData() {
  const setores = ref([])
  const permissoes = ref([])

  const carregarSetores = async () => {
    const { data } = await api.get('/api/setores')
    setores.value = data.setores
  }

  const carregarPermissoes = async () => {
    const { data } = await api.get('/api/permissoes')
    permissoes.value = data.permissoes
  }

  return {
    setores,
    permissoes,
    carregarSetores,
    carregarPermissoes,
  }
}
