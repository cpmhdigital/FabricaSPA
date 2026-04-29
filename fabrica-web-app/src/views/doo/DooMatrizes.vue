<template>
  <BaseLayout titulo="DOO • Matrizes" descricao="Consulta e sincronização de matrizes vindas do DOO." semCard>
    <div class="container py-4">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="input-group" style="max-width: 520px; width: 100%;">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input v-model="q" class="form-control" placeholder="Buscar matriz..." />
              <button class="btn btn-light" type="button" @click="q = ''" :disabled="!q">Limpar</button>
            </div>

            <button class="btn btn-success" type="button" @click="sincronizar" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-arrow-repeat me-2"></i>
              Sincronizar
            </button>
          </div>
          <div v-if="erro" class="mt-3 alert alert-danger">
            {{ erro }}
          </div>


          <div v-if="!loading && !erro && !listaFiltrada.length" class="mt-3 alert alert-warning">
            Nenhuma matriz encontrada.
          </div>


          <div class="table-responsive mt-3" v-if="listaFiltrada.length">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th style="width: 120px;">Código</th>
                  <th>Descrição</th>
                  <th style="width: 140px;" class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in listaFiltrada" :key="m.id">
                  <td class="fw-semibold">{{ m.codigo }}</td>
                  <td class="text-muted">{{ m.descricao }}</td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" type="button">Ver</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import BaseLayout from '@/components/BaseLayout.vue'
import api from '@/services/axios'

type Matriz = { id: number; codigo: string; descricao: string }

const q = ref('')
const loading = ref(false)
const erro = ref('')
const matrizes = ref<Matriz[]>([])

const listaFiltrada = computed(() => {
  const s = q.value.trim().toLowerCase()
  if (!s) return matrizes.value
  return matrizes.value.filter((m) =>
    (m.codigo || '').toLowerCase().includes(s) || (m.descricao || '').toLowerCase().includes(s),
  )
})

async function carregar() {
  try {
    erro.value = ''
    loading.value = true
    // ajuste para o seu endpoint real:
    const { data } = await api.get<Matriz[]>('/api/doo/matrizes')
    matrizes.value = data || []
  } catch (e) {
    console.error(e)
    erro.value = 'Não foi possível carregar as matrizes.'
    matrizes.value = []
  } finally {
    loading.value = false
  }
}

async function sincronizar() {
  try {
    erro.value = ''
    loading.value = true
    await api.get('/api/doo/matrizes')
    await api.get('/api/doo/health')
    await api.post('/api/doo/matrizes/sync')
    await carregar()
  } catch (e) {
    console.error(e)
    erro.value = 'Falha ao sincronizar.'
  } finally {
    loading.value = false
  }
}

onMounted(carregar)
</script>
