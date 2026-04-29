<template>
  <BaseLayout
    titulo="Histórico de IT/REV"
    descricao="Visualize o histórico completo desse documento"
    semCard
  >
    <!-- Documento atual -->
    <div v-if="itOriginal" class="card mb-4 border-0 shadow-sm bg-light">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1 fw-semibold text-success">
            <i class="bi bi-file-earmark-text-fill me-2"></i>{{ itOriginal.nome }}
          </h5>
          <p class="mb-1 text-muted small">
            Versão atual: <span class="fw-bold text-dark">{{ ultimaVersao }}</span>
          </p>
          <a :href="itOriginal.url" target="_blank" class="btn btn-outline-success btn-sm mt-2">
            <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Arquivo
          </a>
        </div>
      </div>
    </div>

    <!-- Histórico de versões -->
    <div v-if="versoes.length">
      <h6 class="mb-3 text-secondary">Histórico de versões</h6>

      <div
        v-for="v in versoesOrdenadas"
        :key="v.id"
        class="card mb-2 border-0 shadow-sm bg-light"
      >
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold text-dark">
              <i class="bi bi-clock-history me-1 text-primary"></i>{{ v.nome }}
              <span class="badge bg-primary ms-2">{{ v.versao }}</span>
            </div>
            <a
              :href="v.url"
              target="_blank"
              class="d-block mt-1 text-decoration-none text-muted small"
            >
              <i class="bi bi-link-45deg me-1"></i>{{ v.url }}
            </a>
          </div>
          <small class="text-muted">{{ formatarData(v.created_at) }}</small>
        </div>
      </div>
    </div>

    <div v-else class="text-muted fst-italic">Nenhuma versão encontrada para este documento.</div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import BaseLayout from '@/components/BaseLayout.vue'
import api from '@/services/axios'

const route = useRoute()
const itOriginal = ref<any>(null)
const versoes = ref<any[]>([])
const carregando = ref(true)

const carregarHistorico = async () => {
  try {
    const id = route.params.id
    const response = await api.get(`/api/itrev/${id}`)
    itOriginal.value = response.data.original
    versoes.value = response.data.versoes
  } catch (error) {
    console.error('Erro ao carregar histórico:', error)
  } finally {
    carregando.value = false
  }
}

const ultimaVersao = computed(() => {
  if (!versoes.value.length) return itOriginal.value?.versao
  return versoes.value[versoes.value.length - 1].versao
})

const versoesOrdenadas = computed(() => {
  return [...versoes.value].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
})

const formatarData = (data: string) => {
  const d = new Date(data)
  return d.toLocaleDateString('pt-BR')
}

onMounted(carregarHistorico)
</script>
