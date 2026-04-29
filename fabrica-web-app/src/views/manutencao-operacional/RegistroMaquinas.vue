<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import TabelaBase from '@/components/TabelaBase.vue'
import AcoesTabela from '@/components/AcoesTabela.vue'
import ModalBase from '@/components/ModalBase.vue'
import api from '@/services/axios'
import type { ColDef, ICellRendererParams } from 'ag-grid-community'
import Swal from 'sweetalert2'

interface Maquina {
  id?: number
  codigo: string
  departamento: string
  tipo: string
  modelo: string
}

const router = useRouter()
const maquinas = ref<Maquina[]>([])
const mostrarModal = ref(false)
const carregando = ref(false)
const erro = ref('')
const novaMaquina = ref<Maquina>({
  codigo: '',
  departamento: 'produção',
  tipo: '',
  modelo: '',
})

const editarMaquina = (id: number) => {
  router.push(`/maquinas/editar/${btoa(id.toString())}`)
}

const excluirMaquina = async (id: number) => {
  const confirmar = await Swal.fire({
    title: 'Tem certeza?',
    text: 'Deseja excluir esta máquina?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar',
  })
  if (!confirmar.isConfirmed) return

  try {
    await api.delete(`/api/maquina/${id}`)
    maquinas.value = maquinas.value.filter((m) => m.id !== id)
    Swal.fire('Excluída!', 'Máquina removida com sucesso.', 'success')
  } catch (error) {
    Swal.fire('Erro!', 'Erro ao excluir máquina.', 'error')
    console.error(error)
  }
}

const salvarMaquina = async () => {
  try {
    const { data } = await api.post('/api/maquina', novaMaquina.value)

    if (data && data.id) {
      maquinas.value.push(data)
      novaMaquina.value = { departamento: 'produção', codigo: '', tipo: '', modelo: '' }
      mostrarModal.value = false
      Swal.fire('Sucesso!', 'Máquina adicionada com sucesso.', 'success')
    }
  } catch (e) {
    console.error(e)
    Swal.fire('Erro!', 'Falha ao adicionar máquina.', 'error')
  }
}

const buscarMaquinas = async () => {
  try {
    carregando.value = true
    const response = await api.get('/api/maquina')
    maquinas.value = response.data
  } catch (e: unknown) {
    if (e instanceof Error) {
      console.error(e.message)
    } else {
      console.error('Erro desconhecido:', e)
    }
    erro.value = 'Erro ao buscar máquinas. Tente novamente mais tarde.'
  } finally {
    carregando.value = false
  }
}

const colunas: ColDef[] = [
  { headerName: 'Código', field: 'codigo', width: 110 },
  { headerName: 'Modelo', field: 'modelo', flex: 1 },
  { headerName: 'Tipo', field: 'tipo', flex: 1 },
  { headerName: 'Departamento', field: 'departamento', flex: 1 },
  {
    headerName: 'Ações',
    field: 'id',
    flex: 1,
    cellRenderer: 'AcoesTabela',
    cellRendererParams: (params: ICellRendererParams<Maquina>) => ({
      data: params.data,
      onEditar: (p: { data: Maquina }) => editarMaquina(p.data.id!),
      onExcluir: (p: { data: Maquina }) => excluirMaquina(p.data.id!),
    }),
    cellClass: 'text-center',
  },
]

onMounted(buscarMaquinas)
</script>

<template>
  <div class="container py-5">
    <div class="text-center mb-4">
      <h4 class="fw-bold text-dark mb-1">
        <i class="bi bi-steam me-2 text-success fs-4"></i>
        Gerenciamento de Máquinas
      </h4>
      <p class="text-muted mb-0">Gerenciamento de registro de máquinas.</p>
    </div>

    <div class="mb-5 d-flex justify-content-end">
      <button class="btn btn-primary shadow-sm" @click="mostrarModal = true">
        <i class="bi bi-plus"></i> Adicionar Máquina
      </button>
    </div>

    <ModalBase title="Adicionar Máquina" v-model:show="mostrarModal" @save="salvarMaquina">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Departamento</label>
          <select class="form-select" v-model="novaMaquina.departamento">
            <option value="produção">Produção</option>
            <option value="inspecao">Inspeção Qualidade</option>
            <option value="outros">Outros</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Código</label>
          <input type="text" class="form-control" v-model="novaMaquina.codigo" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Tipo</label>
          <input type="text" class="form-control" v-model="novaMaquina.tipo" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Modelo</label>
          <input type="text" class="form-control" v-model="novaMaquina.modelo" />
        </div>
      </div>
    </ModalBase>

    <TabelaBase
      :rowData="maquinas"
      :columnDefs="colunas"
      :components="{ AcoesTabela }"
      :pagination="true"
      :paginationPageSize="12"
    />
  </div>
</template>
