<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import BaseLayout from '@/components/BaseLayout.vue'
import TabelaBase from '@/components/TabelaBase.vue'
import AcoesTabela from '@/components/AcoesTabela.vue'
import ModalBase from '@/components/ModalBase.vue'
import PaginationMobile from '@/components/TabelaMobile.vue'
import api from '@/services/axios'
import type { ColDef } from 'ag-grid-community'
import { useRouter } from 'vue-router'

interface Setor {
  id: number
  nome: string
}

const router = useRouter()
const setores = ref<Setor[]>([])
const carregando = ref(true)
const erro = ref('')

// --------------------
// Modal e formulário
// --------------------
const mostrarModal = ref(false)
const novoSetor = ref({ nome: '' })

const adicionarSetor = () => (mostrarModal.value = true)
const fecharModal = () => (mostrarModal.value = false)

const salvarSetor = async () => {
  if (!novoSetor.value.nome.trim()) {
    return Swal.fire({
      icon: 'warning',
      title: 'Atenção',
      text: 'Informe o nome do setor.',
      toast: true,
      position: 'top-end',
      timer: 2000,
      showConfirmButton: false,
    })
  }

  try {
    const resposta = await api.post('/api/setores', { nome: novoSetor.value.nome })

    Swal.fire({
      icon: 'success',
      title: `Setor "${resposta.data.nome}" salvo!`,
      toast: true,
      position: 'top-end',
      timer: 2000,
      showConfirmButton: false,
    })

    fecharModal()
    novoSetor.value = { nome: '' }
    await carregarSetores()
  } catch (error) {
    console.error('Erro ao salvar setor:', error)
    Swal.fire({
      icon: 'error',
      title: 'Erro',
      text: 'Ocorreu um erro ao salvar o setor. Tente novamente.',
      toast: true,
      position: 'top-end',
      timer: 3000,
      showConfirmButton: false,
    })
  }
}

// --------------------
// Colunas da Tabela
// --------------------
const colunas: ColDef[] = [
  { field: 'id', headerName: '#', width: 80, cellClass: 'text-muted text-center' },
  {
    field: 'nome',
    headerName: 'Nome do Setor',
    flex: 2,
    cellClass: 'fw-semibold',
    tooltipField: 'nome',
    editable: true,
    cellEditor: 'agTextCellEditor',
    cellEditorParams: { maxLength: 50 },
  },
  {
    headerName: 'Ações',
    field: 'id',
    flex: 1,
    cellRenderer: 'AcoesTabela',
    cellRendererParams: {
      onEditar: (data: Setor) => {
        router.push({ path: `/ordem-producao/setores/${data.id}/editar` })
      },
      onExcluir: async (data: Setor) => {
        const id = data.id
        const confirm = await Swal.fire({
          icon: 'warning',
          title: 'Excluir Setor?',
          text: 'Deseja realmente excluir este setor?',
          showCancelButton: true,
          confirmButtonText: 'Sim, excluir',
          cancelButtonText: 'Cancelar',
        })
        if (confirm.isConfirmed) {
          try {
            await api.delete(`/api/setores/${id}`)
            Swal.fire({
              icon: 'success',
              title: 'Setor excluído!',
              toast: true,
              position: 'top-end',
              timer: 2000,
              showConfirmButton: false,
            })
            carregarSetores()
          } catch (error) {
            console.error('Erro ao excluir setor:', error)
            Swal.fire({
              icon: 'error',
              title: 'Erro',
              text: 'Não foi possível excluir o setor.',
              toast: true,
              position: 'top-end',
              timer: 3000,
              showConfirmButton: false,
            })
          }
        }
      },
    },
    cellClass: 'text-center',
  },
]

// --------------------
// Carregar Setores
// --------------------
const carregarSetores = async () => {
  try {
    carregando.value = true
    const response = await api.get('/api/setores')
    setores.value = response.data
  } catch (e) {
    console.error('Erro ao carregar setores:', e)
    erro.value = 'Erro ao buscar setores. Tente novamente mais tarde.'
  } finally {
    carregando.value = false
  }
}

onMounted(() => {
  carregarSetores()
})
</script>

<template>
  <BaseLayout
    titulo="Setores da Produção"
    descricao="Gerenciamento dos Setores no Processo de Produção."
    textoBotao="Novo Setor"
    iconeBotao="bi bi-plus"
    @novo="adicionarSetor"
    semCard
  >
    <!-- Tabela desktop -->
    <div class="d-none d-md-block">
      <TabelaBase
        :rowData="setores"
        :columnDefs="colunas"
        :components="{ AcoesTabela }"
        :pagination="true"
        :paginationPageSize="12"
      />
    </div>

    <PaginationMobile :itens="setores" :itensPorPagina="6">
      <template #default="{ item }">
        <div class="card mb-2 shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-1">{{ item.nome }}</h6>
            </div>
            <button
              class="btn btn-sm btn-primary"
              @click="router.push({ path: '/ordem-producao/nova-etapa', query: { id: item.id } })"
            >
              Ver
            </button>
          </div>
        </div>
      </template>
    </PaginationMobile>

    <!-- Modal -->
    <ModalBase title="Novo Setor" :show="mostrarModal" @save="salvarSetor" @cancel="fecharModal">
      <template #default>
        <div class="mb-4">
          <label for="nome" class="form-label">Nome do Setor</label>
          <input
            type="text"
            id="nome"
            class="form-control form-control-lg"
            v-model="novoSetor.nome"
            placeholder="Digite o nome do Setor"
          />
        </div>
      </template>

      <template #footer>
        <button
          type="button"
          class="btn btn-success btn-lg rounded-pill px-4 d-flex align-items-center"
          @click="salvarSetor"
        >
          <i class="bi bi-check-circle me-2"></i> Salvar
        </button>
      </template>
    </ModalBase>

    <a
      @click="$router.back()"
      class="d-flex align-items-center gap-2 mt-5 text-decoration-none"
      :class="'btn btn-link text-secondary p-0'"
    >
      <i class="bi bi-arrow-left-circle" style="font-size: 1.2rem"></i>
      <span class="ms-2" style="font-size: 1rem">Voltar</span>
    </a>
  </BaseLayout>
</template>
