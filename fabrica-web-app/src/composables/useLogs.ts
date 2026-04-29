import { ref } from 'vue'
import api from '@/services/axios.ts'

export interface Log {
  id: number
  description: string
  causer_type?: string
  causer_id?: number
  properties: {
    ip?: string
    user_agent?: string
  }
  created_at: string
}

const logs = ref<Log[]>([])

const carregarLogs = async () => {
  try {
    const response = await api.get('/api/logs') 
    logs.value = response.data.logs
  } catch (error: any) {
    console.error('Erro ao carregar logs:', error.response?.data || error.message)
  }
}

export function useLogs() {
  return {
    logs,
    carregarLogs,
  }
}
