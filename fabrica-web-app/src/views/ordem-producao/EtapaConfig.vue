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

interface Etapa {
  id: number
  nome_etapa: string
}

const etapas = ref<Etapa[]>([])
const carregando = ref(true)
const erro = ref('')

// Detecta se é mobile dinamicamente
const isMobile = ref(window.innerWidth < 768)

window.addEventListener('resize', () => {
  isMobile.value = window.innerWidth < 768
})

const buscarEtapas = async () => {
  try {
    carregando.value = true
    const { data } = await api.get('/api/etapas')
    etapas.value = data
  } catch (error) {
    console.error(error)
    erro.value = 'Erro ao buscar etapas. Tente novamente mais tarde.'
  } finally {
    carregando.value = false
  }
}

const excluirEtapa = async (etapa: Etapa) => {
  const confirm = await Swal.fire({
    title: 'Tem certeza?',
    text: `Deseja excluir a etapa "${etapa.nome_etapa}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
  })

  if (!confirm.isConfirmed) return

  try {
    await api.delete(`/api/etapas/${etapa.id}`)
    etapas.value = etapas.value.filter((e) => e.id !== etapa.id)

    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: `Etapa "${etapa.nome_etapa}" excluída com sucesso!`,
      showConfirmButton: false,
      timer: 3000,
    })
  } catch (error) {
    console.error('Erro ao excluir etapa:', error)
    Swal.fire({
      icon: 'error',
      title: 'Erro ao excluir etapa',
      text: 'Não foi possível completar a exclusão. Tente novamente.',
    })
  }
}

function criarNovaEtapa() {
  localStorage.removeItem('etapaId')
  router.push({ path: '/ordem-producao/formulario-etapa' })
}

function editarEtapa(id: number) {
  localStorage.removeItem('etapaId')
  router.push({
    path: '/ordem-producao/formulario-etapa',
    state: { id },
  })
}

const colunas: ColDef[] = [
  {
    headerName: '#',
    field: 'id',
    width: 80,
    cellClass: 'text-muted text-center',
  },
  {
    headerName: 'Nome',
    field: 'nome_etapa',
    flex: 1,
    cellClass: 'fw-semibold',
  },
  {
    headerName: 'Ações',
    field: 'id',
    width: 200,
    cellRenderer: 'AcoesTabela',
    cellRendererParams: {
      onEditar: (data: Etapa) => editarEtapa(data.id),
      onExcluir: (data: Etapa) => excluirEtapa(data),
    },
    cellClass: 'text-center',
  },
]

onMounted(() => {
  localStorage.removeItem('etapaId')
  buscarEtapas()
})
</script>

<template>
  <BaseLayout titulo="Configurar Etapas" descricao="Associar IT/Rev, Máquinas, Checklists" semCard>
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-primary d-flex align-items-center gap-2" @click="criarNovaEtapa">
        <i class="bi bi-plus-lg"></i>
        <span>Nova Etapa</span>
      </button>
    </div>

    <TabelaBase
      v-if="!carregando && !isMobile"
      :rowData="etapas"
      :columnDefs="colunas"
      :components="{ AcoesTabela }"
      :pagination="true"
      :paginationPageSize="12"
    />

    <PaginationMobile v-if="!carregando && isMobile" :itens="etapas" :itensPorPagina="6">
      <template #default="{ item }">
        <div class="card mb-2 shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center">
            <h6 class="mb-1">{{ item.nome_etapa }}</h6>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-outline-primary" @click="editarEtapa(item.id)">
                Editar
              </button>
            </div>
          </div>
        </div>
      </template>
    </PaginationMobile>

    <div v-if="erro" class="alert alert-danger mt-3">
      {{ erro }}
    </div>
  </BaseLayout>
</template>
