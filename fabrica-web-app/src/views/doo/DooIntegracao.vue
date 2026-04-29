<template>
  <BaseLayout
    titulo="DOO • Integração"
    descricao="Configurações e testes de conexão com o DOO."
    semCard
  >
    <div class="container py-4">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
              <div class="fw-semibold">Status da integração</div>
              <div class="text-muted small">Verifica conectividade e credenciais.</div>
            </div>

            <button class="btn btn-primary" type="button" @click="testar" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-plug me-2"></i>
              Testar conexão
            </button>
          </div>

          <div class="mt-3" v-if="msg" :class="ok ? 'alert alert-success' : 'alert alert-danger'">
            {{ msg }}
          </div>
        </div>
      </div>
    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseLayout from '@/components/BaseLayout.vue'
import api from '@/services/axios'

const loading = ref(false)
const msg = ref('')
const ok = ref(false)

async function testar() {
  try {
    loading.value = true
    msg.value = ''
    ok.value = false

    // ajuste para o seu endpoint real:
    const { data } = await api.get('/api/doo/health')
    ok.value = true
    msg.value = data?.message || 'Conexão OK.'
  } catch (e) {
    console.error(e)
    ok.value = false
    msg.value = 'Falha ao conectar no DOO.'
  } finally {
    loading.value = false
  }
}
</script>
