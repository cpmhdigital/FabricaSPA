<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import api from '@/services/axios.ts'

// ---------------------------
// Estado principal
// ---------------------------
const busca = ref('')
const produto = ref<any>({
  codigo: '',
  descricao: '',
  anvisa: '',
  fluxo_id: '',
  materiasPrimas: [],
  componentes: [],
  parafusos: [],
  embalagens: [],
  insumos: [],
})
const produtoEncontrado = ref<any>(null)
const fluxosDisponiveis = ref<any[]>([])

// ---------------------------
// Carregar fluxos
// ---------------------------
onMounted(async () => {
  try {
    const res = await api.get('/api/fluxos')
    fluxosDisponiveis.value = res.data
  } catch (err) {
    console.error('Erro ao carregar fluxos:', err)
  }
})

// ---------------------------
// Funções de busca
// ---------------------------
const onBuscaProduto = async (codigo: string, modo: 'clonar' | 'novo' = 'novo') => {
  if (!codigo) return
  try {
    const res = await api.get('/api/itens/buscar', {
      params: { codigo, tipoItem: 'produto', acao: 'pesquisa' },
    })

    const item = res.data.data
    if (res.data.exists && item) {
      if (modo === 'clonar') {
        produto.value = {
          codigo: item.codigo || '',
          descricao: item.descricao || '',
          anvisa: item.anvisa || '',
          fluxo_id: item.fluxo_id || '',
          materiasPrimas: item.materiasPrimas || [],
          componentes: item.componentes || [],
          parafusos: item.parafusos || [],
          embalagens: item.embalagens || [],
          insumos: item.insumos || [],
        }
        produtoEncontrado.value = produto.value
      } else {
        await Swal.fire({
          icon: 'warning',
          title: 'Código já existente!',
          text: `O produto "${item.descricao}" já está cadastrado.`,
        })
        produto.value.codigo = ''
        produtoEncontrado.value = null
      }
    } else {
      produtoEncontrado.value = null
    }
  } catch (err) {
    console.error('Erro na busca de produto:', err)
  }
}

const onBuscaItem = async (codigo: string, tipoItem: string) => {
  if (!codigo) return null
  try {
    const res = await api.get('/api/itens/buscar', {
      params: { codigo, tipoItem, acao: 'pesquisa' },
    })
    return res.data.data
  } catch (err) {
    console.error(`Erro na busca de ${tipoItem}:`, err)
    return null
  }
}

// ---------------------------
// Buscar todos os itens da clonagem
// ---------------------------
const buscarProdutoCompleto = async () => {
  if (!busca.value) return

  try {
    const res = await api.get('/api/itens/buscar', {
      params: { codigo: busca.value, tipoItem: 'produto', acao: 'pesquisa' },
    })

    if (res.data.exists && res.data.data) {
      produto.value = res.data.data
      produto.value.descricao += ' (Cópia)'
      produtoEncontrado.value = produto.value

      await Swal.fire({
        icon: 'success',
        title: 'Produto carregado!',
        text: 'Produto e todos os seus componentes foram carregados para clonagem.',
      })
    } else {
      produtoEncontrado.value = null
      Swal.fire({
        icon: 'info',
        title: 'Produto não encontrado',
        text: 'Nenhum produto com esse código foi localizado.',
      })
    }
  } catch (err) {
    console.error('Erro ao clonar produto:', err)
  }
}

const copiarFilhos = (item: any): any => {
  const novo = { ...item }

  if (item.filhos && item.filhos.length > 0) {
    novo.filhos = item.filhos.map(copiarFilhos)

    if (['componente', 'parafuso'].includes(item.tipo)) {
      novo.materiasPrimas = novo.filhos
      delete novo.filhos
    }
  }

  return novo
}

// ---------------------------
// Clonar Produto
// ---------------------------
const clonarProduto = async (codigo: string) => {
  try {
    const res = await api.get('/api/itens/buscar', {
      params: { codigo, tipoItem: 'produto', acao: 'pesquisa' },
    })

    if (res.data.exists && res.data.data) {
      const produtoOriginal = res.data.data

      // Mantém os filhos/componentes, mas limpa código e descrição
      produto.value = {
        ...produtoOriginal,
        codigo: '',
        descricao: '',
      }

      produtoEncontrado.value = produto.value

      await Swal.fire({
        icon: 'success',
        title: 'Produto carregado!',
        text: 'Composição carregada com sucesso. Informe um novo código e descrição para o produto.',
      })
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Produto não encontrado',
        text: 'Nenhum produto com esse código foi localizado.',
      })
    }
  } catch (err) {
    console.error('Erro ao clonar produto:', err)
  }
}

// ---------------------------
// Fechar modal
// ---------------------------
const fecharModal = () => {
  const modalEl = document.getElementById('modalCloneProduto')
  if (modalEl) {
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)
    modal.hide()
  }
}
</script>
<template>
  <div class="modal fade" id="modalCloneProduto" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Clonar Produto Existente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Campo de busca -->
          <div class="input-group mb-3">
            <input
              v-model="busca"
              type="text"
              class="form-control"
              placeholder="Digite o código do produto..."
            />
            <button class="btn btn-outline-success" @click="buscarProdutoCompleto">
              Pesquisar
            </button>
          </div>

          <!-- Produto encontrado -->
          <div v-if="produtoEncontrado" class="border rounded p-3 bg-light">
            <h6 class="text-success fw-bold">Produto Encontrado:</h6>
            <div class="row g-3 mt-2">
              <div class="col-md-6">
                <label class="form-label">Código</label>
                <input v-model="produtoEncontrado.codigo" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Descrição</label>
                <input v-model="produtoEncontrado.descricao" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">ANVISA</label>
                <input v-model="produtoEncontrado.anvisa" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Fluxo</label>
                <select v-model="produtoEncontrado.fluxo_id" class="form-select">
                  <option v-for="fluxo in fluxosDisponiveis" :key="fluxo.id" :value="fluxo.id">
                    {{ fluxo.nome }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Componentes (Filhos) -->
            <div
              v-if="produtoEncontrado.filhos && produtoEncontrado.filhos.length > 0"
              class="mt-4"
            >
              <h6 class="text-success fw-bold">Componentes:</h6>
              <div
                v-for="(componente, i) in produtoEncontrado.filhos"
                :key="i"
                class="border rounded p-3 my-2 bg-white"
              >
                <p class="mb-1"><strong>Código:</strong> {{ componente.codigo }}</p>
                <p class="mb-1"><strong>Descrição:</strong> {{ componente.descricao }}</p>

                <!-- Matérias-Primas dos Componentes -->
                <div
                  v-if="componente.materiasPrimas && componente.materiasPrimas.length > 0"
                  class="ms-3 mt-2"
                >
                  <h6 class="text-primary">Matérias-Primas:</h6>
                  <ul>
                    <li v-for="(mp, j) in componente.materiasPrimas" :key="j">
                      {{ mp.codigo }} - {{ mp.descricao }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Caso nenhum produto seja encontrado -->
          <div v-else class="text-muted fst-italic text-center mt-3">
            Nenhum produto encontrado.
          </div>
        </div>

        <!-- Rodapé com botões -->
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button
            class="btn btn-success"
            @click="clonarProduto(busca)"
            :disabled="!produtoEncontrado"
          >
            Clonar Produto
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
