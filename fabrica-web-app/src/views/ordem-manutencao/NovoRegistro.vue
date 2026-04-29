<script setup lang="ts">
import FormBase from '@/components/FormBase.vue'
</script>

<template>
  <FormBase titulo="Ordem de Manutenção" codigo="FRM.PRO.011 REV.0">
    <form @submit.prevent="enviarFormulario" class="row g-4">
      <div class="col-md-2 border-end">
        <div class="mb-3">
          <label for="dtcriacao_text" class="form-label">Data Criação da OM</label>
          <input
            class="form-control"
            name="dtcriacao"
            id="dtcriacao_text"
            type="text"
            onclick="switchToDateTime()"
          />
          <input
            class="form-control d-none"
            name="dtcriacao"
            id="dtcriacao_datetime"
            type="datetime-local"
          />
        </div>
      </div>

      <div class="col-md-3">
        <div class="mb-3">
          <label for="idMaquina" class="form-label text-danger">Nº Máquina *</label>
          <input class="form-control" name="idMaquina" id="idMaquina" type="text" />
        </div>
      </div>

      <div class="col-md-3">
        <div class="mb-3">
          <label for="omNomeMaquina" class="form-label">Nome Máquina</label>
          <input
            class="form-control"
            name="omNomeMaquina"
            id="omNomeMaquina"
            type="text"
            readonly
          />
        </div>
      </div>

      <div class="col-md-4">
        <div class="mb-3">
          <label for="omIdentificadorMaquina" class="form-label">Marca/ Modelo / N° Série</label>
          <input
            class="form-control"
            name="omIdentificadorMaquina"
            id="omIdentificadorMaquina"
            type="text"
            readonly
          />
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Tipo Manutenção <span class="text-danger">*</span></label>
        <div class="form-check">
          <input
            class="form-check-input"
            type="radio"
            name="tipo_manutencao"
            id="manutencaoPreventiva"
            value="Manutenção Preventiva"
            required
          />
          <label class="form-check-label" for="manutencaoPreventiva">Manutenção Preventiva</label>
        </div>
        <div class="form-check">
          <input
            class="form-check-input"
            type="radio"
            name="tipo_manutencao"
            id="manutencaoCorretiva"
            value="Manutenção Corretiva"
            required
          />
          <label class="form-check-label" for="manutencaoCorretiva">Manutenção Corretiva</label>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label"
          >A máquina está operacional? <span class="text-danger">*</span></label
        >
        <div class="form-check">
          <input
            class="form-check-input"
            type="radio"
            name="maqOperavel"
            id="maqOperavel1"
            value="Operável"
            required
          />
          <label class="form-check-label" for="maqOperavel1">Sim</label>
        </div>
        <div class="form-check">
          <input
            class="form-check-input"
            type="radio"
            name="maqOperavel"
            id="maqOperavel2"
            value="Não Operável"
            required
          />
          <label class="form-check-label" for="maqOperavel2">Não</label>
        </div>
        <div class="mt-2">
          <label for="tempoNaoOperacional" class="form-label"
            >Por quanto tempo ficará não operacional?</label
          >
          <input
            class="form-control"
            name="tempoNaoOperacional"
            id="tempoNaoOperacional"
            type="text"
          />
        </div>
      </div>

      <div class="col-12">
        <label for="descricao" class="form-label"
          >Descreva a manutenção <span class="text-danger">*</span></label
        >
        <small class="text-muted d-block mb-1"
          >As informações devem ser <b>claras e completas</b></small
        >
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

      <div class="col-md-6">
        <label class="form-label d-block"
          >Grau de urgência da tarefa <span class="text-danger">*</span></label
        >
        <div class="form-check form-check-inline">
          <small class="form-check-label">Pode aguardar</small>
        </div>
        <div class="form-check form-check-inline" v-for="i in 5" :key="i">
          <input
            class="form-check-input"
            type="radio"
            :value="i"
            name="grauurgencia"
            :id="'urgencia' + i"
            required
          />
          <label class="form-check-label" :for="'urgencia' + i">{{ i }}</label>
        </div>
        <div class="form-check form-check-inline">
          <small class="form-check-label">Executar o quanto antes</small>
        </div>
      </div>

      <div class="col-md-6">
        <label for="formFile" class="form-label">Arquivos necessários</label>
        <div class="border p-3 bg-light text-center rounded">
          <label for="formFile"><i class="fas fa-upload fa-3x text-secondary"></i></label>
          <input
            class="form-control d-none"
            type="file"
            id="formFile"
            name="formFile"
            onchange="getImageData(event)"
          />
          <small class="text-success d-block" id="file-name"></small>
          <small class="text-muted">Imagens, DXF, desenhos, etc.</small>
        </div>
      </div>

      <div class="col-12">
        <label for="obs" class="form-label">Observações</label>
        <input class="form-control" name="obs" id="obs" type="text" />
      </div>

      <div class="col-12 text-end">
        <button class="btn btn-success" type="submit" id="submit">Enviar</button>
      </div>
    </form>
  </FormBase>
</template>
