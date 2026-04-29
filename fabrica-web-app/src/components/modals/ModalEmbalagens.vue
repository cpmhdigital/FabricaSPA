<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{ tipo: string }>()

const form = ref({
  codigo: '',
  descricao: '',
  anvisa: ''
})

function salvar() {
  alert(`${props.tipo} "${form.value.descricao}" salvo com sucesso!`)
}
</script>

<template>
  <div class="modal fade" id="modalEmbalagens" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Adicionar Embalagem</h5>
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
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-danger" @click="salvar">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>
