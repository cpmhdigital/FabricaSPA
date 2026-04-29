<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUsuarios } from '@/composables/useUsuarios'
import AcoesTabela from '@/components/AcoesTabela.vue'
import TabelaBase from '@/components/TabelaBase.vue'
import type { ICellRendererParams, ColDef } from 'ag-grid-community'


interface RowData {
  id: number
  name: string
  email: string
  status: string
  departamento?: {
    nome: string
  }
}

const router = useRouter()
const { usuarios, carregarUsuarios, excluirUsuario } = useUsuarios()
const isMobile = ref(false)


const editarUsuario = (id: number) =>
  router.push(`/lista-usuarios/editar/${btoa(id.toString())}`)

const excluir = async (id: number) => {
  if (!confirm('Tem certeza que deseja excluir este usuário?')) return
  try {
    await excluirUsuario(id)
  } catch (error) {
    alert('Erro ao excluir usuário.')
    console.error(error)
  }
}

const frameworkComponents = { AcoesTabela }

onMounted(() => {
  carregarUsuarios()
  isMobile.value = window.innerWidth < 992
  window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 992
  })
})


const colunas: ColDef<RowData>[] = [
  { field: 'id', headerName: 'ID', width: 90 },
  { field: 'name', headerName: 'Nome', flex: 1 },
  { field: 'email', headerName: 'E-mail', flex: 1 },
  {
    field: 'status',
    headerName: 'Status',
    flex: 1,
    cellRenderer: (params: ICellRendererParams<RowData>) => {
      const status = params.value
      const className =
        'badge rounded-pill text-uppercase px-3 py-2 ' +
        (status === 'aprovado'
          ? 'bg-success'
          : status === 'aguardando'
            ? 'bg-warning text-dark'
            : 'bg-secondary')
      return `<span class="${className}">${status}</span>`
    },
  },
  { field: 'departamento.nome', headerName: 'Departamento', flex: 1 },
  {
    headerName: 'Ações',
    cellRenderer: 'AcoesTabela', // componente Vue para renderizar botões
    cellRendererParams: (params: ICellRendererParams<RowData>) => ({
      onEditar: () => params.data && editarUsuario(params.data.id),
    }),
    width: 120,
  },
]
</script>

<template>
  <div class="container-fluid min-vh-100 py-4">
    <div class="mt-5 text-center">
      <h4 class="fw-bold text-dark mb-1">
        <i class="bi bi-people-fill text-success fs-4"></i>
        Gerenciamento de Usuários
      </h4>
      <p class="text-muted mb-0">Visualize, edite e remova usuários do sistema.</p>
    </div>

    <div class="d-flex justify-content-end m-4">
      <button class="btn btn-secondary gap-2 shadow-sm" @click="router.push('/papeis')">
        <i class="bi bi-shield-lock-fill"></i>
        Gerenciar Permissões
      </button>
    </div>

    <!-- Tabela Desktop -->
    <div class="p-3 mt-4" v-if="!isMobile">
      <TabelaBase :rowData="usuarios" :columnDefs="colunas" :components="frameworkComponents" />
    </div>

    <!-- Tabela Mobile -->
    <div v-else class="mt-4">
      <div
        v-for="usuario in usuarios"
        :key="usuario.id"
        class="card shadow-sm border-0 rounded-4 mb-3"
      >
        <div class="card-body">
          <h5 class="fw-semibold text-dark mb-1">{{ usuario.name }}</h5>
          <p class="text-muted small mb-2">
            <i class="bi bi-envelope me-1"></i> {{ usuario.email }}
          </p>
          <p class="mb-2">
            <span
              class="badge rounded-pill text-uppercase px-3 py-2"
              :class="{
                'bg-success': usuario.status === 'aprovado',
                'bg-warning text-dark': usuario.status === 'aguardando',
                'bg-secondary': usuario.status !== 'aprovado' && usuario.status !== 'aguardando',
              }"
            >
              {{ usuario.status }}
            </span>
          </p>
          <p class="text-muted small mb-3">
            <i class="bi bi-diagram-3 me-1"></i>
            {{ usuario.departamento?.nome || '—' }}
          </p>

          <div class="d-flex justify-content-end gap-2">
            <button
              class="btn btn-outline-primary btn-sm rounded-pill px-3"
              @click="editarUsuario(usuario.id)"
            >
              <i class="bi bi-pencil-square"></i> Editar
            </button>
            <button
              class="btn btn-outline-danger btn-sm rounded-pill px-3"
              @click="excluir(usuario.id)"
            >
              <i class="bi bi-trash"></i> Excluir
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
