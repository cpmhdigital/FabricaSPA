<template>
  <div class="modal fade" id="modalComponente" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <i class="bi bi-box-seam me-2"></i>
          <h5 class="modal-title">Gerenciar Componentes</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Código</label>
              <input v-model="componente.codigo" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Descrição</label>
              <input v-model="componente.descricao" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">ANVISA</label>
              <input v-model="componente.anvisa" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Fluxo</label>
              <select v-model="componente.fluxo" class="form-select">
                <option disabled value="">Selecione o fluxo</option>
                <option v-for="fluxo in fluxosDisponiveis" :key="fluxo">{{ fluxo }}</option>
              </select>
            </div>
          </div>

          <hr />

          <!-- Matéria-Prima -->
          <ListaItemProduto
            titulo="Matéria-Prima"
            tituloSingular="Matéria-Prima"
            icone="bi-droplet"
            v-model="componente.mp"
            :resultados="resultadosBuscaMP"
            :campos="camposMP"
            @buscar="onBuscaMP"
            @adicionar="adicionarMP"
            @selecionar="selecionarMP"
            @remover="removerMP"
          />

          <!-- Parafusos -->
          <ListaItemProduto
            titulo="Parafusos"
            tituloSingular="Parafuso"
            icone="bi-nut"
            v-model="componente.parafusos"
            :resultados="resultadosBuscaParafuso"
            :campos="camposParafuso"
            @buscar="onBuscaParafuso"
            @adicionar="adicionarParafuso"
            @selecionar="selecionarParafuso"
            @remover="removerParafuso"
          />

          <!-- Embalagens -->
          <ListaItemProduto
            titulo="Embalagens"
            tituloSingular="Embalagem"
            icone="bi-box"
            v-model="componente.embalagens"
            :resultados="resultadosBuscaEmbalagem"
            :campos="camposEmbalagem"
            @buscar="onBuscaEmbalagem"
            @adicionar="adicionarEmbalagem"
            @selecionar="selecionarEmbalagem"
            @remover="removerEmbalagem"
          />

          <!-- Insumos -->
          <ListaItemProduto
            titulo="Insumos"
            tituloSingular="Insumo"
            icone="bi-nut"
            v-model="componente.insumos"
            :resultados="resultadosBuscaInsumo"
            :campos="camposInsumo"
            @buscar="onBuscaInsumo"
            @adicionar="adicionarInsumo"
            @selecionar="selecionarInsumo"
            @remover="removerInsumo"
          />
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success" @click="salvar">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ListaItemProduto from '@/components/ListaItemProduto.vue'

const componente = ref({
  codigo: '',
  descricao: '',
  anvisa: '',
  fluxo: '',
  mp: [],
  parafusos: [],
  embalagens: [],
  insumos: [],
})

// Fluxos disponíveis
const fluxosDisponiveis = ['Fluxo A', 'Fluxo B']

// Campos configuráveis
const camposMP = [
  { key: 'codigo', label: 'Código', type: 'text', class: 'w-25' },
  { key: 'descricao', label: 'Descrição', type: 'text', class: 'w-25' },
  { key: 'anvisa', label: 'ANVISA', type: 'text', class: 'w-25' },
]

const camposParafuso = [
  { key: 'codigo', label: 'Código', type: 'text', class: 'w-25' },
  { key: 'descricao', label: 'Descrição', type: 'text', class: 'w-25' },
  { key: 'anvisa', label: 'ANVISA', type: 'text', class: 'w-25' },
  { key: 'idfluxo', label: 'Fluxo', type: 'text', class: 'w-25' },
  { key: 'grupo', label: 'Grupo', type: 'text', class: 'w-25' },
]

const camposEmbalagem = camposMP
const camposInsumo = camposMP

// Resultados mock
const resultadosBuscaMP = ref([])
const resultadosBuscaParafuso = ref([])
const resultadosBuscaEmbalagem = ref([])
const resultadosBuscaInsumo = ref([])

// Métodos mock
const onBuscaMP = (termo: string) => console.log('buscar MP', termo)
const adicionarMP = () => componente.value.mp.push({ codigo: '', nome: '', anvisa: '' })
const selecionarMP = (item: any) => console.log('selecionado', item)
const removerMP = (i: number) => componente.value.mp.splice(i, 1)

const onBuscaParafuso = (termo: string) => console.log('buscar parafuso', termo)
const adicionarParafuso = () =>
  componente.value.parafusos.push({ codigo: '', nome: '', anvisa: '', fluxo: '', grupo: '' })
const selecionarParafuso = (item: any) => console.log('selecionado', item)
const removerParafuso = (i: number) => componente.value.parafusos.splice(i, 1)

const onBuscaEmbalagem = (termo: string) => console.log('buscar embalagem', termo)
const adicionarEmbalagem = () =>
  componente.value.embalagens.push({ codigo: '', nome: '', anvisa: '' })
const selecionarEmbalagem = (item: any) => console.log('selecionado', item)
const removerEmbalagem = (i: number) => componente.value.embalagens.splice(i, 1)

const onBuscaInsumo = (termo: string) => console.log('buscar insumo', termo)
const adicionarInsumo = () => componente.value.insumos.push({ codigo: '', nome: '', anvisa: '' })
const selecionarInsumo = (item: any) => console.log('selecionado', item)
const removerInsumo = (i: number) => componente.value.insumos.splice(i, 1)

function salvar() {
  console.log('Componente salvo', componente.value)
}
</script>
