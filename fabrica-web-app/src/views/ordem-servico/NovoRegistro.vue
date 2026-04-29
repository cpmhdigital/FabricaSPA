<script setup lang="ts">
import FormBase from '@/components/FormBase.vue'

interface PedidoServico {
  numeroPedido: string
  fluxo: string
  lote: string
  doutor: string
  paciente: string
  observacoes: string
  tipo: 'Nacional' | 'Internacional'
  taxaExtra: boolean
}
</script>

<template>
  <FormBase titulo="Ordem de Serviço" codigo="FRM.PRO.006 REV.00">
    <form @submit.prevent="enviarFormulario" class="row g-3">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="setor" class="form-label"
            >Para qual setor se destina a tarefa? <span class="text-danger">*</span></label
          >
          <select class="form-select" name="setor" id="setor" required>
            <option value="0" selected disabled>Escolha um setor</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="dtentrega" class="form-label"
            >Data limite para execução da tarefa <span class="text-danger">*</span></label
          >
          <input class="form-control" name="dtentrega" id="dtentrega" type="date" required />
          <small class="text-muted">Prazo sujeito a aprovação de acordo com calendário</small>
        </div>
      </div>

      <div class="row g-3 mt-3">
        <div class="col-md-6">
          <label for="lote" class="form-label">Lote</label>
          <input class="form-control" name="lote" id="lote" type="text" required />
        </div>
        <div class="col-md-6">
          <label for="nped" class="form-label">Nº Pedido</label>
          <input class="form-control" name="nped" id="nped" type="text" />
        </div>
      </div>

      <div class="mt-4">
        <label for="descricao" class="form-label">
          Descrição da Tarefa <span class="text-danger">*</span>
        </label>
        <small class="text-muted d-block mb-2">
          Detalhar de forma <b>clara</b> as informações mais importantes para o operador.
        </small>
        <textarea
          class="form-control"
          name="descricao"
          id="descricao"
          rows="3"
          maxlength="400"
          onkeyup="limite_textarea(this.value)"
        ></textarea>
        <small class="text-muted"><span id="cont">400</span> caracteres restantes</small>
      </div>

      <div class="row g-3 mt-4">
        <div class="col-md-6">
          <label class="form-label"
            >Grau de urgência da tarefa <span class="text-danger">*</span></label
          >
          <div class="d-flex flex-wrap align-items-center gap-2">
            <small class="text-muted">Pode aguardar</small>
            <template v-for="i in 5" :key="i">
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  :value="i"
                  :id="'grau' + i"
                  name="grauurgencia"
                  required
                />
                <label class="form-check-label" :for="'grau' + i">{{ i }}</label>
              </div>
            </template>
            <small class="text-muted">Executar o quanto antes</small>
          </div>
        </div>

        <div class="col-md-6">
          <label for="formFile" class="form-label"> Arquivos necessários para execução </label>
          <div class="bg-light border p-3 rounded text-center">
            <label for="formFile">
              <i class="fa-3x fas fa-upload text-secondary"></i>
            </label>
            <small id="file-name" class="d-block text-success"></small>
            <span class="filedata d-block"></span>
            <span class="loading d-none">Carregando arquivo...</span>
          </div>
          <input
            class="form-control d-none"
            type="file"
            id="formFile"
            name="formFile"
            onchange="getImageData(event)"
          />
          <small class="text-muted">Imagens, DXF, desenhos, etc.</small>
          <div class="progressBar">
            <div class="progress"></div>
          </div>
        </div>
      </div>

      <div class="d-none">
        <label for="urlThrowback" class="form-label">URL</label>
        <input class="form-control" name="urlThrowback" id="urlThrowback" type="text" />
      </div>

      <div class="mt-3">
        <label for="obs" class="form-label">Observações</label>
        <input class="form-control" name="obs" id="obs" type="text" />
      </div>

      <div class="text-center py-4">
        <button class="btn btn-success px-5 fw-semibold" type="submit" name="submit" id="submit">
          Enviar
        </button>
      </div>
    </form>
  </FormBase>
</template>
