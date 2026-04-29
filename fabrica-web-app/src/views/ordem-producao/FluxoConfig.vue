<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
import BaseLayout from '@/components/BaseLayout.vue'
import TabelaBase from '@/components/TabelaBase.vue'
import AcoesTabela from '@/components/AcoesTabela.vue'
import PaginationMobile from '@/components/TabelaMobile.vue'
import api from '@/services/axios'
import type { ColDef } from 'ag-grid-community'

const router = useRouter()

interface Fluxo {
  id: number
  nome_fluxo: string
  descricao?: string
  criado_em: string
}

const fluxos = ref<Fluxo[]>([])
const carregando = ref(true)
const erro = ref('')

const buscarFluxos = async () => {
  try {
    carregando.value = true
    const response = await api.get('/api/fluxos')
    fluxos.value = response.data
  } catch (e: unknown) {
    console.error(e)
    erro.value = 'Erro ao buscar fluxos. Tente novamente mais tarde.'
  } finally {
    carregando.value = false
  }
}

const excluirFluxo = async (fluxo: Fluxo) => {
  const confirm = await Swal.fire({
    title: 'Tem certeza?',
    text: `Deseja excluir o fluxo "${fluxo.nome_fluxo}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
  })

  if (confirm.isConfirmed) {
    try {
      await api.delete(`/api/fluxos/${fluxo.id}`)

      // Remove da lista local sem precisar recarregar tudo
      fluxos.value = fluxos.value.filter((f) => f.id !== fluxo.id)

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: `Fluxo "${fluxo.nome_fluxo}" excluído com sucesso!`,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      })
    } catch (e) {
      console.error(e)
      Swal.fire({
        icon: 'error',
        title: 'Erro ao excluir',
        text: `Não foi possível excluir o fluxo "${fluxo.nome_fluxo}".`,
      })
    }
  }
}

const colunas: ColDef[] = [
  { headerName: '#', field: 'id', width: 80, cellClass: 'text-muted text-center' },
  {
    headerName: 'Fluxo',
    field: 'nome_fluxo',
    flex: 1,
    cellClass: 'fw-semibold',
    tooltipField: 'nome_fluxo',
    editable: true,
    cellEditor: 'agTextCellEditor',
    cellEditorParams: { maxLength: 50 },
  },
  {
    headerName: 'Ações',
    field: 'id',
    width: 200,
    cellRenderer: 'AcoesTabela',
    cellRendererParams: {
      onEditar: (data: Fluxo) =>
        router.push({ path: '/ordem-producao/novo-fluxo', query: { id: data.id } }),
      onExcluir: (data: Fluxo) => excluirFluxo(data),
    },
    cellClass: 'text-center',
  },
]

onMounted(buscarFluxos)
</script>

<template>
  <BaseLayout titulo="Gerenciamento de Fluxos" descricao="Fluxo para Produção de Produtos" semCard>
    <div class="d-flex justify-content-end mb-3">
      <router-link
        to="/ordem-producao/novo-fluxo"
        class="btn btn-success d-flex align-items-center gap-2"
      >
        <i class="bi bi-plus-lg"></i>
        <span>Novo Fluxo</span>
      </router-link>
    </div>

    <!-- Tabela apenas para desktop -->
    <div class="d-none d-md-block">
      <TabelaBase
        :rowData="fluxos"
        :columnDefs="colunas"
        :components="{ AcoesTabela }"
        :pagination="true"
        :paginationPageSize="12"
        :paginationPageSizeSelector="[12, 20, 50, 100]"
      />
    </div>

    <!-- Tabela apenas para mobile -->
    <PaginationMobile :itens="fluxos" :itensPorPagina="6">
      <template #default="{ item }">
        <div class="card mb-2 shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-1">{{ item.nome_fluxo }}</h6>
            </div>
            <div class="d-flex gap-2">
              <button
                class="btn btn-sm btn-outline-primary"
                @click="router.push({ path: '/ordem-producao/novo-fluxo', query: { id: item.id } })"
              >
                Editar
              </button>
            </div>
          </div>
        </div>
      </template>
    </PaginationMobile>

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
