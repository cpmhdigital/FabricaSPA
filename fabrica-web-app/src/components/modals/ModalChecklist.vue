<template>
  <div
    class="modal fade show d-block backdrop-blur"
    tabindex="-1"
    v-if="visivel"
  >
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Cabeçalho -->
        <div class="modal-header bg-gradient text-white py-3 px-4">
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-check2-square me-2"></i>
            Checklist {{ tipo === 'pre' ? 'Pré-etapa' : 'Pós-etapa' }}
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            @click="fechar"
          ></button>
        </div>

        <!-- Corpo -->
        <div class="modal-body bg-light p-4">
          <div
            v-if="checklistAtual.length"
            class="d-flex flex-column gap-3"
          >
            <div
              v-for="check in checklistAtual"
              :key="check.id"
              class="form-check p-3 rounded-3 border bg-white shadow-sm hover-shadow"
            >
              <input
                type="checkbox"
                v-model="check.resposta"
                class="form-check-input me-2"
                :id="'check-' + check.id"
              />
              <label
                class="form-check-label fw-semibold text-secondary"
                :for="'check-' + check.id"
              >
                {{ check.nome }}
              </label>
            </div>
          </div>
          <div v-else class="text-muted fst-italic text-center py-4">
            Nenhum item de checklist disponível.
          </div>
        </div>

        <!-- Rodapé -->
        <div class="modal-footer bg-white border-0 px-4 pb-4">
          <button class="btn btn-outline-secondary px-4" @click="fechar">
            <i class="bi bi-x-circle me-2"></i>Cancelar
          </button>
          <button class="btn btn-success px-4" @click="confirmar">
            <i class="bi bi-check-circle me-2"></i>Confirmar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useProducao } from '@/composables/useProducao'
import type { ItemProducao } from '@/composables/useProducao'

const visivel = ref(false)
const itemAtual = ref<ItemProducao | null>(null)
const tipo = ref<'pre' | 'pos'>('pre')
const checklistAtual = ref<any[]>([])

function abrir(item: ItemProducao, t: 'pre' | 'pos') {
  itemAtual.value = item
  tipo.value = t
  checklistAtual.value =
    t === 'pre'
      ? item.etapas.find((e) => e.status === 'em_andamento')?.checklist_pre ?? []
      : item.etapas.find((e) => e.status === 'em_andamento')?.checklist_pos ?? []
  visivel.value = true
}

function fechar() {
  visivel.value = false
}

function confirmar() {
  const { registrarHistorico } = useProducao()

  if (tipo.value === 'pre') {
    itemAtual.value!.status = 'em_producao'
    registrarHistorico(itemAtual.value!, 'INICIO', 'Checklist pré-etapa concluído')
  } else {
    registrarHistorico(itemAtual.value!, 'FINALIZACAO', 'Checklist pós-etapa concluído')
    const etapa = itemAtual.value!.etapas.find((e) => e.status === 'em_andamento')
    if (etapa) etapa.status = 'concluida'
  }

  fechar()
}

defineExpose({ abrir })
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #198754, #157347);
}

.backdrop-blur {
  background-color: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
}

.hover-shadow {
  transition: all 0.2s ease-in-out;
}
.hover-shadow:hover {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}
</style>
