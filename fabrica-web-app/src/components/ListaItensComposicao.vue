<template>
  <div v-if="itensPaginados.length" class="accordion" :id="`accordion-${titulo}`">
    <div
      v-for="item in itensPaginados"
      :key="item.id"
      class="accordion-item mb-3 shadow-sm rounded-3 border-0"
    >
      <h2 class="accordion-header">
        <button
          class="accordion-button bg-opacity-10 fw-semibold"
          :class="classeHeader"
          type="button"
          data-bs-toggle="collapse"
          :data-bs-target="`#collapse-${item.id}`"
        >
          <small>{{ item.codigo }}</small> - {{ item.descricao }}
          <span v-if="item.fluxo" class="badge ms-2 bg-success">
            {{ item.fluxo.nome_fluxo ?? 'Sem nome' }}
          </span>
        </button>
      </h2>

      <div
        :id="`collapse-${item.id}`"
        class="accordion-collapse collapse"
        :data-bs-parent="`#accordion-${titulo}`"
      >
        <div class="accordion-body">
          <div class="border rounded-3 p-3 mb-3 bg-light bg-opacity-50">
            <!-- 🔹 Cabeçalho de detalhes -->
            <div class="row align-items-center mb-3">
              <div class="col">
                <h6 class="m-0 text-secondary">
                  <small class="text-muted">Detalhes</small> — {{ item.tipo }}
                </h6>
              </div>

              <div class="col-auto">
                <div class="btn-group">
                  <button
                    class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="modal"
                    :data-bs-target="modalVisualizar"
                    title="Visualizar"
                  >
                    <i class="bi bi-eye"></i>
                  </button>
                  <button
                    class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal"
                    :data-bs-target="modalEditar"
                    title="Editar"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm" title="Excluir">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- 🔹 Informações básicas -->
            <div class="row small">
              <div class="col-md-6 mb-2">
                <p class="mb-0">
                  <strong>Código:</strong> <span class="text-muted">{{ item.codigo }}</span>
                </p>
              </div>
              <div class="col-md-6 mb-2">
                <p class="mb-0">
                  <strong>Anvisa:</strong> <span class="text-muted">{{ item.anvisa }}</span>
                </p>
              </div>
            </div>
          </div>

          <section v-if="item.filhos.length" aria-label="Composição do item">
            <h2 class="h6 text-muted mt-3">Composição:</h2>

            <ul class="list-group list-group-flush">
              <li v-for="filho in item.filhos" :key="filho.id" class="list-group-item">
                <article>
                  <header class="d-flex align-items-center">
                    <span class="me-2">
                      <small>{{ filho.codigo }}</small>
                    </span>
                    <strong>{{ filho.descricao }}</strong>
                    <span class="text-muted ms-2">
                      <small>({{ filho.tipo }})</small>
                    </span>
                  </header>

                  <!-- Subnível (filhos dos filhos) -->
                  <ul v-if="filho.filhos.length" class="ms-4 mt-2 list-unstyled">
                    <li v-for="sub in filho.filhos" :key="sub.id" class="py-1 mx-2">
                      <span>
                        <small>{{ sub.codigo }}</small> - {{ sub.descricao }}
                      </span>
                      <span class="text-muted ms-1">
                        <small>({{ sub.tipo }})</small>
                      </span>
                    </li>
                  </ul>
                </article>
              </li>
            </ul>
          </section>
        </div>
      </div>
    </div>

    <!-- 🔹 Paginação -->
    <nav v-if="totalPaginas > 1" aria-label="Navegação de páginas">
      <ul class="pagination justify-content-center mt-4">
        <li class="page-item" :class="{ disabled: paginaAtual === 1 }">
          <button class="page-link" @click="paginaAtual--" :disabled="paginaAtual === 1">
            Anterior
          </button>
        </li>

        <li
          v-for="pagina in totalPaginas"
          :key="pagina"
          class="page-item"
          :class="{ active: pagina === paginaAtual }"
        >
          <button class="page-link" @click="paginaAtual = pagina">
            {{ pagina }}
          </button>
        </li>

        <li class="page-item" :class="{ disabled: paginaAtual === totalPaginas }">
          <button class="page-link" @click="paginaAtual++" :disabled="paginaAtual === totalPaginas">
            Próximo
          </button>
        </li>
      </ul>
    </nav>
  </div>

  <div v-else class="alert alert-warning text-center">
    Nenhum {{ titulo.toLowerCase() }} encontrado.
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Fluxo {
  id: number
  nome_fluxo: string
}

interface Item {
  id: number
  descricao: string
  codigo: string
  anvisa: string
  tipo: string
  fluxo?: Fluxo | null
  filhos: Item[]
}

const props = defineProps<{
  itens: Item[]
  titulo: string
  modalEditar?: string
  modalVisualizar?: string
  classeHeader?: string
}>()

// 🔹 Paginação
const paginaAtual = ref(1)
const itensPorPagina = 5 // altere se quiser mais ou menos por página

// Atualiza se os itens mudarem
watch(
  () => props.itens,
  () => (paginaAtual.value = 1),
)

const totalPaginas = computed(() => Math.ceil(props.itens.length / itensPorPagina))

const itensPaginados = computed(() => {
  const inicio = (paginaAtual.value - 1) * itensPorPagina
  const fim = inicio + itensPorPagina
  return props.itens.slice(inicio, fim)
})
</script>
