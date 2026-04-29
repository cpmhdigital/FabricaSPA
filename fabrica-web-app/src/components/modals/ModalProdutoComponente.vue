<script setup lang="ts">
import { ref } from 'vue'

const form = ref({
  codigo: '',
  descricao: '',
  anvisa: '',
  id_fluxo: ''
})

const fluxosDisponiveis = [
  { id: 1, nome: 'Fluxo CustomLife' },
  { id: 2, nome: 'Fluxo Padrão' }
]

function salvar() {
  alert(`Item "${form.value.descricao}" salvo com sucesso!`)
}
</script>

<template>
  <div class="modal fade" id="modalProdutoComponente" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Adicionar Produto / Componente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Código</label>
            <input v-model="form.codigo" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label">Descrição</label>
            <input v-model="form.descricao" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label">ANVISA</label>
            <input v-model="form.anvisa" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label">Fluxo</label>
            <select v-model="form.id_fluxo" class="form-select">
              <option v-for="fluxo in fluxosDisponiveis" :key="fluxo.id" :value="fluxo.id">
                {{ fluxo.nome }}
              </option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" @click="salvar">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>
