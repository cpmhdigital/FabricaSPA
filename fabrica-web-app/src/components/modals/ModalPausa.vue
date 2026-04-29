<template>
  <div class="modal fade show d-block backdrop-blur" tabindex="-1" v-if="visivel">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header bg-gradient text-white py-3 px-4">
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-pause-circle-fill me-2"></i>
            Motivo da Pausa
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="fechar"></button>
        </div>

        <div class="modal-body bg-light p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold text-secondary"> Selecione o motivo </label>
            <select v-model="motivo" class="form-select shadow-sm border-0 rounded-3">
              <option disabled value="">Selecione...</option>
              <option>Almoço</option>
              <option>Lanche</option>
              <option>Fim do Expediente</option>
              <option>Outros</option>
            </select>
          </div>

          <div>
            <label for="observacao" class="form-label fw-semibold text-secondary">
              Observação
            </label>
            <input
              type="text"
              id="observacao"
              v-model="observacao"
              class="form-control shadow-sm border-0 rounded-3"
              placeholder="Adicione um detalhe, se necessário..."
            />
          </div>
        </div>

        <div class="modal-footer bg-white border-0 px-4 pb-4">
          <button class="btn btn-outline-secondary px-4" @click="fechar">
            <i class="bi bi-x-circle me-2"></i>Cancelar
          </button>
          <button class="btn btn-success px-4" :disabled="!motivo" @click="confirmar">
            <i class="bi bi-check-circle me-2"></i>Confirmar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import api from '@/services/axios'
const emit = defineEmits(['parar'])

const visivel = ref(false)
const motivo = ref('')
const observacao = ref('')
const itemAtual = ref<any>(null)

function abrir(item: any) {
  console.log('ITEM RECEBIDO PELO MODAL:', item)
  itemAtual.value = item
  visivel.value = true
}

function fechar() {
  visivel.value = false
  motivo.value = ''
  observacao.value = ''
}

/*
-----------------------------------------------------
 CONFIRMAR PAUSA
 - grava no registro_paradas
 - seta status = parado
 - emite evento "parar"
-----------------------------------------------------
*/
async function confirmar() {
  const etapaAtual = itemAtual.value.etapas.find((e) => e.status === 'em_andamento')

  const motivoMap: Record<string, string> = {
    Almoço: 'almoco',
    Lanche: 'lanche',
    'Fim do Expediente': 'fim_expediente',
    Outros: 'outro',
  }

  const payload = {
    pedido_id: itemAtual.value.pedido_id,
    item_id: itemAtual.value.produto_id,
    pedido_item_unidade_id: itemAtual.value.pedido_item_unidade_id,
    etapa_id: etapaAtual?.id ?? null,
    motivo: motivoMap[motivo.value],
    observacao: observacao.value || null,
    data_inicio: new Date().toISOString(),
    data_fim: null,
  }

  console.log('PAYLOAD ENVIADO:', payload)

  try {
    await api.post('/api/registro-paradas', payload)

    /* REGISTRAR HISTÓRICO DE PRODUÇÃO COMO PAUSA */
    await api.post('/api/historico-producao', {
      pedido_id: itemAtual.value.pedido_id,
      pedido_item_id: itemAtual.value.id,
      pedido_item_unidade_id: itemAtual.value.pedido_item_unidade_id,
      etapa_id: etapaAtual?.id ?? null,
      usuario_id: 1,
      acao: 'PAUSA',
      observacao: observacao.value || motivo.value,
    })

    itemAtual.value.status = 'parado'

    emit('parar', itemAtual.value)

    fechar()
  } catch (e) {
    console.error('Erro ao registrar parada:', e)
    alert('Erro ao registrar parada!')
  }
}

defineExpose({ abrir })
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #0d6efd, #0a58ca);
}

.backdrop-blur {
  background-color: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
}
</style>
