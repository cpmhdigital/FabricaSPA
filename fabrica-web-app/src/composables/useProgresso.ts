import { computed } from 'vue'

export function useProgresso(itens: any[]) {
  const progressoGeral = computed(() => {
    const etapas = itens.flatMap((i) => i.etapas)
    const total = etapas.length
    const concluidas = etapas.filter((e) => e.status === 'concluida').length
    return total ? Math.round((concluidas / total) * 100) : 0
  })

  const corProgresso = computed(() => {
    if (progressoGeral.value < 50) return 'bg-danger'
    if (progressoGeral.value < 80) return 'bg-warning'
    return 'bg-success'
  })

  function formatarData(data?: string | null) {
    return data ? new Date(data).toLocaleDateString('pt-BR') : '-'
  }

  return { progressoGeral, corProgresso, formatarData }
}
