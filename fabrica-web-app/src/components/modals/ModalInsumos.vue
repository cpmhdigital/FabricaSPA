<script setup lang="ts">
import { ref } from 'vue'


const form = ref({
  codigo: '',
  descricao: '',
  anvisa: ''
})
</script>

<template>
  <div class="modal fade" id="modalInsumos" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">Adicionar Insumos</h5>
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
          <button class="btn btn-danger" >Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>
