<script setup lang="ts">
import { ref, computed } from 'vue'
import ListaItemProduto from '@/components/ListaItemProduto.vue'



// Formulário do item (MP ou semiacabado)
const form = ref({
  codigo: '',
  descricao: '',
  anvisa: '',
})

// Dados dos itens
const produto = ref({
  mp: [] as { codigo: string; nome: string; anvisa: string }[],
  semiacabados: [] as { codigo: string; descricao: string; anvisa: number | null; novo: boolean }[],
})

// Campos configuráveis
const camposMP = [
  { key: 'codigo', label: 'Código', type: 'text', class: 'w-25' },
  { key: 'nome', label: 'Nome', type: 'text', class: 'w-25' },
  { key: 'descricao', label: 'Descrição', type: 'text', class: 'w-25' },
  { key: 'anvisa', label: 'ANVISA', type: 'text', class: 'w-25' },
]

const camposSemiacabado = camposMP

// Mock de busca
const resultadosBuscaMP = ref([])
const resultadosBuscaSemiacabado = ref([])

// Métodos MP
const onBuscaMP = (termo: string) => console.log('buscar MP', termo)
const adicionarMP = () => produto.value.mp.push({ codigo: '', nome: '', anvisa: '' })
const selecionarMP = (item: any) => console.log('selecionado MP', item)
const removerMP = (i: number) => produto.value.mp.splice(i, 1)

// Métodos semiacabado
const onBuscaSemiacabado = (termo: string) => console.log('buscar semiacabado', termo)
const adicionarSemiacabado = () =>
  produto.value.semiacabados.push({ codigo: '', descricao: '', anvisa: null, novo: true })
const selecionarSemiacabado = (item: any) => console.log('selecionado semiacabado', item)
const removerSemiacabado = (i: number) => produto.value.semiacabados.splice(i, 1)

// Salvar
function salvar() {
  alert(

  )
}
</script>

<template>
  <div class="modal fade" id="modalSemiacabado" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <!-- Header -->
        <div class="modal-header bg-info text-white rounded-top-4">
          <h5 class="modal-title">Adicionar Semiacabado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <!-- Dados básicos -->
          <div class="row mb-3">
            <div class="col-md-4 mb-3">
              <label class="form-label">Código</label>
              <input v-model="form.codigo" class="form-control" />
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Descrição</label>
              <input v-model="form.descricao" class="form-control" />
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">ANVISA</label>
              <input v-model="form.anvisa" class="form-control" />
            </div>
          </div>

          <hr />

          <!-- Lista de Matéria-Prima -->
          <ListaItemProduto
            titulo="Matéria-Prima"
            tituloSingular="Matéria-Prima"
            icone="bi-droplet"
            v-model="produto.mp"
            :resultados="resultadosBuscaMP"
            :campos="camposMP"
            @buscar="onBuscaMP"
            @adicionar="adicionarMP"
            @selecionar="selecionarMP"
            @remover="removerMP"
          />

          <!-- Lista de Semiacabados -->
          <ListaItemProduto
            titulo="Semiacabados"
            tituloSingular="Semiacabado"
            icone="bi-nut"
            v-model="produto.semiacabados"
            :resultados="resultadosBuscaSemiacabado"
            :campos="camposSemiacabado"
            @buscar="onBuscaSemiacabado"
            @adicionar="adicionarSemiacabado"
            @selecionar="selecionarSemiacabado"
            @remover="removerSemiacabado"
          />
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-info" @click="salvar">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>
