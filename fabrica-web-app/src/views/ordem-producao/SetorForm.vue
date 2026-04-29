<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import BaseLayout from '@/components/BaseLayout.vue'
import TabelaBase from '@/components/TabelaBase.vue'
import AcoesTabela from '@/components/AcoesTabela.vue'
import api from '@/services/axios'
import type { ColDef } from 'ag-grid-community'

interface Etapa {
  id: number
  nome: string
  ordem: number
  setor_id?: number
}

interface Setor {
  id: number
  nome: string
}

const route = useRoute()
const router = useRouter()

const setor = ref<Setor | null>(null)
const etapas = ref<Etapa[]>([])
const etapasSelecionadasObjs = ref<Etapa[]>([])
const carregando = ref(true)
const erro = ref('')

const carregarDados = async () => {
  const id = route.params.id
  if (!id) return (erro.value = 'ID do setor não informado.')

  try {
    carregando.value = true
    const [setorData, etapasData] = await Promise.all([
      api.get(`/api/setores/${id}`),
      api.get(`/api/setores/${id}/etapas`),
    ])

    setor.value = setorData.data
    etapas.value = etapasData.data
    etapasSelecionadasObjs.value = [...etapas.value]
  } catch (e) {
    erro.value = 'Erro ao buscar os dados. Tente novamente mais tarde.'
    console.error('Erro ao carregar dados:', e)
  } finally {
    carregando.value = false
  }
}

const salvarSetor = async () => {
  if (!setor.value?.nome.trim()) return showAlert('warning', 'Atenção', 'Informe o nome do setor.')

  try {
    await api.put(`/api/setores/${setor.value.id}`, { nome: setor.value.nome })
    showAlert('success', 'Setor atualizado!', 'Alterações salvas com sucesso.')
  } catch (error) {
    showAlert('error', 'Erro', 'Não foi possível atualizar o setor. Tente novamente mais tarde.')
    console.error('Erro ao atualizar setor:', error)
  }
}

const concluirAssociacao = async () => {
  if (!setor.value || etapasSelecionadasObjs.value.length === 0) return

  try {
    const payload = {
      setor_id: setor.value.id,
      etapas: etapasSelecionadasObjs.value.map((e) => e.id),
    }
    await api.patch('/api/etapas/associar-setor', payload)
    showAlert('success', 'Etapas associadas!', 'As etapas foram vinculadas com sucesso ao setor.')
    carregarDados()
  } catch (error) {
    showAlert(
      'error',
      'Erro',
      'Não foi possível associar as etapas ao setor. Tente novamente mais tarde.',
    )
    console.error('Erro ao associar etapas:', error)
  }
}

const showAlert = (icon: 'success' | 'error' | 'warning', title: string, text: string) => {
  Swal.fire({
    icon,
    title,
    text,
    toast: true,
    position: 'top-end',
    timer: 2500,
    showConfirmButton: false,
  })
}

const colunas: ColDef[] = [
  { headerName: 'Nome da Etapa', field: 'nome_etapa', flex: 2, cellClass: 'fw-semibold' },
  {
    headerName: 'Ações',
    field: 'id',
    width: 150,
    cellRenderer: 'AcoesTabela',
    cellRendererParams: {
      onEditar: (data: Etapa) =>
        router.push({ path: '/ordem-producao/nova-etapa', query: { id: data.id } }),
      onExcluir: async (data: Etapa) => {
        const confirm = await Swal.fire({
          icon: 'warning',
          title: 'Remover do setor?',
          text: 'Deseja realmente desassociar esta etapa do setor?',
          showCancelButton: true,
          confirmButtonText: 'Sim, remover',
          cancelButtonText: 'Cancelar',
        })
        if (confirm.isConfirmed) {
          try {
            await api.patch(`/api/etapas/${data.id}/desassociar-setor`)
            showAlert(
              'success',
              'Etapa removida do setor!',
              'A etapa foi desassociada com sucesso.',
            )
            carregarDados()
          } catch (error) {
            showAlert(
              'error',
              'Erro',
              'Não foi possível remover a etapa do setor. Tente novamente.',
            )
            console.error('Erro ao desassociar etapa:', error)
          }
        }
      },
    },
    cellClass: 'text-center',
  },
]

onMounted(() => carregarDados())
</script>

<template>
  <BaseLayout
    :titulo="setor ? `Setor: ${setor.nome}` : 'Setor'"
    descricao="Visualize e gerencie as etapas vinculadas a este setor."
    semCard
  >
    <div v-if="carregando" class="text-center my-5">
      <div class="spinner-border text-success" role="status"></div>
      <p class="mt-2">Carregando dados...</p>
    </div>

    <div v-else-if="erro" class="alert alert-danger text-center">{{ erro }}</div>

    <div v-else>
      <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3">
          <div class="flex-grow-1">
            <label class="form-label fw-semibold mb-1">Nome do Setor</label>
            <input
              type="text"
              class="form-control form-control-sm rounded-3"
              v-model="setor.nome"
              placeholder="Digite o nome do setor"
            />
          </div>
          <div class="mt-2 mt-md-4">
            <button class="btn btn-success btn-sm rounded-3 px-4" @click="salvarSetor">
              <i class="bi bi-check2-circle me-1"></i> Salvar
            </button>
          </div>
        </div>
      </div>

      <TabelaBase
        :rowData="etapas"
        :columnDefs="colunas"
        :components="{ AcoesTabela }"
        :pagination="true"
        :paginationPageSize="10"
      />

      <a
        @click="$router.back()"
        class="d-flex align-items-center gap-2 mt-4 text-decoration-none"
        :class="'btn btn-link text-secondary p-0'"
      >
        <i class="bi bi-arrow-left-circle" style="font-size: 1.2rem"></i>
        <span class="ms-2" style="font-size: 1rem">Voltar</span>
      </a>
    </div>
  </BaseLayout>
</template>
