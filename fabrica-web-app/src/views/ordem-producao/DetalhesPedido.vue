<template>
  <div class="container py-4">
    <button class="btn btn-outline-secondary rounded-pill mb-4" @click="$router.back()">
      <i class="bi bi-arrow-left me-2"></i>
    </button>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
      <div class="card-header bg-success text-white rounded-top-4 fs-5">
        <i class="bi bi-box-seam me-2"></i>
        Detalhes do Pedido #{{ pedido?.numero_pedido || '—' }}
      </div>

      <div class="card-body p-4">

        <!-- Loading -->
        <div v-if="carregando" class="text-center py-5">
          <div class="spinner-border text-success" role="status"></div>
        </div>

        <!-- Conteúdo -->
        <div v-else>

          <!-- Dados principais -->
          <div class="row g-3 mb-4">

            <div class="col-md-6">
              <label class="form-label fw-semibold">Doutor(a)</label>
              <input type="text" class="form-control" v-model="pedido.doutor" disabled />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Paciente</label>
              <input type="text" class="form-control" v-model="pedido.paciente" disabled />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tipo</label>
              <div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" value="Nacional" v-model="pedido.tipo" />
                  <label class="form-check-label">Nacional</label>
                </div>

                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" value="Importado" v-model="pedido.tipo" />
                  <label class="form-check-label">Importado</label>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Lote</label>
              <input type="text" class="form-control" v-model="pedido.lote" />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Taxa Extra</label>
              <div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" :value="0" v-model="pedido.taxa_extra" />
                  <label class="form-check-label">Não</label>
                </div>

                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" :value="1" v-model="pedido.taxa_extra" />
                  <label class="form-check-label">Sim</label>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Data do Pedido</label>
              <span class="form-control">
                {{ formatarData(pedido.data_pedido) || '—' }}
              </span>
            </div>
          </div>

          <!-- Datas adicionais -->
          <div class="alert alert-secondary d-flex justify-content-between align-items-center mb-4 rounded-4 border-0">
            <div class="text-center">
              <small class="text-muted d-block mb-1">Data do Aceite</small>
              <span>{{ formatarData(pedido.data_aceite) || '—' }}</span>
            </div>

            <div class="text-center">
              <small class="text-muted d-block mb-1">Data Prevista Produção</small>
              <span>{{ formatarData(pedido.data_producao) || '—' }}</span>
            </div>

            <div class="text-center">
              <small class="text-muted d-block mb-1">Data Prevista Entrega</small>
              <span>{{ formatarData(pedido.data_prevista_entrega) || '—' }}</span>
            </div>
          </div>

          <!-- Modal -->
          <ModalItensExtras ref="modalItensExtras" @salvo="recarregarPedido" />

          <!-- Ações -->
          <div class="d-flex align-items-end gap-3 m-3">
            <button class="btn btn-primary rounded-pill d-flex align-items-center gap-2" @click="abrirModalItensExtras">
              <i class="bi bi-plus-circle"></i>
              Itens do Pedido
            </button>

            <div class="flex-grow-1">
              <label class="form-label fw-semibold">Arquivo STL</label>
              <input type="text" class="form-control rounded-pill" placeholder="Selecione ou digite o arquivo" />
            </div>
          </div>

          <hr />

          <div class="text-end">
            <button class="btn btn-success px-4 rounded-pill fw-semibold" @click="salvarAlteracoes"
              :disabled="salvando || !modalAberto">
              <i class="bi bi-save me-2"></i>
              {{ salvando ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/axios'
import Swal from 'sweetalert2'
import ModalItensExtras from '@/components/modals/ModalItensExtras.vue'

const pedido = ref<any>(null)
const carregando = ref(true)
const salvando = ref(false)

const modalItensExtras = ref<InstanceType<typeof ModalItensExtras> | null>(null)
const modalAberto = ref(false)

const route = useRoute()

/* ================= FORMATAR DATA ================= */
const formatarData = (data?: string | null) => {
  if (!data) return ''
  const d = new Date(data)
  if (isNaN(d.getTime())) return ''
  return d.toLocaleDateString('pt-BR')
}

/* ================= LOAD PEDIDO ================= */
async function carregarPedidoPorId(id: any) {
  const { data } = await api.get(`/api/pedidos/${id}`)
  pedido.value = data
  await carregarFilhosItens(data?.pedido_itens || [])
}

onMounted(async () => {
  const id =
    window.history.state?.id ||
    (route as any).state?.id ||
    route.params?.id ||
    localStorage.getItem('pedidoId')

  if (!id) {
    carregando.value = false
    Swal.fire('Erro', 'ID do pedido não encontrado.', 'error')
    return
  }

  try {
    await carregarPedidoPorId(id)
  } catch (e) {
    Swal.fire('Erro', 'Não foi possível carregar os dados do pedido.', 'error')
  } finally {
    carregando.value = false
  }
})

const recarregarPedido = async () => {
  if (!pedido.value?.id) return
  carregando.value = true
  try {
    await carregarPedidoPorId(pedido.value.id)
  } catch {
    Swal.fire('Erro', 'Falha ao recarregar pedido.', 'error')
  } finally {
    carregando.value = false
  }
}

/* ================= FILHOS ================= */
const carregarFilhosItens = async (lista: any[]) => {
  if (!Array.isArray(lista)) return

  const resultados = await Promise.all(
    lista.map(async (pi: any) => {
      // Se o backend já trouxe filhos, preserva
      if (Array.isArray(pi.filhos) && pi.filhos.length) return pi

      // Fallback: buscar árvore do catálogo por produto_id
      const produtoId = pi.produto_id ?? pi.produto?.id
      if (!produtoId) {
        pi.filhos = []
        return pi
      }

      try {
        const resp = await api.get(`/api/itens/${produtoId}`)
        pi.filhos = resp.data?.filhos ?? []
      } catch {
        pi.filhos = []
      }

      return pi
    })
  )

  if (pedido.value) pedido.value.pedido_itens = resultados
}


/* ================= MODAL ================= */
const abrirModalItensExtras = () => {
  if (!pedido.value?.pedido_itens?.length) {
    Swal.fire('Atenção', 'Pedido sem itens.', 'warning')
    return
  }

  modalItensExtras.value?.abrir(pedido.value.pedido_itens, pedido.value.id)
  modalAberto.value = true
}

/* ================= SALVAR (ALTERAÇÕES DO PEDIDO) ================= */
/**
 * Este método deve salvar APENAS os campos do pedido (tipo, taxa_extra, etc).
 * Os itens/extras são salvos dentro do ModalItensExtras.vue (no método salvar() do modal).
 */
const salvarAlteracoes = async () => {
  if (!pedido.value?.id) {
    Swal.fire('Erro', 'Pedido inválido.', 'error')
    return
  }

  if (!modalAberto.value) {
    Swal.fire('Atenção', 'Abra o modal de Itens do Pedido antes de salvar.', 'warning')
    return
  }

  salvando.value = true
  try {
    await api.put(`/api/pedidos/${pedido.value.id}`, {
      tipo: pedido.value.tipo,
      taxa_extra: pedido.value.taxa_extra
    })

    Swal.fire('Sucesso', 'Pedido atualizado com sucesso!', 'success')
    await recarregarPedido()
  } catch (err: any) {
    console.error('erro salvar pedido:', err?.response?.data || err)
    Swal.fire('Erro', 'Falha ao salvar alterações.', 'error')
  } finally {
    salvando.value = false
  }
}
</script>
