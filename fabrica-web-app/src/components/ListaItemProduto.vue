<template>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <!-- Cabeçalho -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="text-primary mb-0">
          <i :class="`bi ${props.icone} me-1`"></i>{{ props.titulo }}
        </h6>

        <div class="d-flex gap-2">
          <input
            v-model="termoBusca"
            @keyup.enter="emitBuscar"
            placeholder="Digite o código e pressione Enter"
            class="form-control"
          />
          <button class="btn btn-sm btn-outline-success" @click="emitAdicionar">
            <i class="bi bi-plus-circle me-1"></i> Novo
          </button>
        </div>
      </div>

      <!-- Lista de itens -->
      <div
        v-for="(item, i) in props.modelValue"
        :key="item.id || i"
        class="mb-3 p-3 border rounded"
      >
        <div class="d-flex flex-wrap gap-2 align-items-start">
          <!-- Campos do item -->
          <template v-for="campo in props.campos" :key="campo.key">
            <div :class="campo.class || 'col-auto d-flex flex-column'">
              <input
                v-if="campo.type === 'text'"
                v-model="item[campo.key]"
                :placeholder="campo.label"
                class="form-control form-control-sm w-100"
                @blur="campo.key === 'codigo' ? verificarCodigoComponente(i) : null"
              />

              <small
                v-if="campo.key === 'codigo' && item.codigoUsado"
                class="text-danger mt-1"
                style="font-size: 0.8rem"
              >
                <i class="bi bi-info-circle-fill me-1"></i> Código já cadastrado. Utilize o campo de
                busca para preencher corretamente.
              </small>

              <select
                v-if="campo.type === 'select'"
                v-model="item[campo.key]"
                class="form-select form-select-sm w-100 mt-1"
              >
                <option disabled value="">Selecione</option>
                <option v-for="opt in campo.options" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </template>

          <!-- Botão remover -->
          <button class="btn btn-sm btn-outline-danger ms-auto" @click="$emit('remover', i)">
            <i class="bi bi-trash"></i>
          </button>
        </div>

        <!-- Sublista de MPs -->
        <div v-if="item.materiasPrimas" class="mt-3 ps-3 border-start border-success">
          <h6 class="text-success small mb-2">
            <i class="bi bi-droplet me-1"></i>
            Matérias-Primas do {{ props.tituloSingular }}
          </h6>

          <div
            v-for="(mp, j) in item.materiasPrimas"
            :key="mp.id || j"
            class="d-flex flex-wrap gap-2 align-items-start mb-2"
          >
            <template v-for="campo in props.camposMp" :key="campo.key">
              <div :class="campo.class || 'col-auto'">
                <input
                  v-if="campo.type === 'text'"
                  v-model="mp[campo.key]"
                  :placeholder="campo.label"
                  class="form-control form-control-sm"
                />
              </div>
            </template>

            <button
              class="btn btn-sm btn-outline-danger ms-auto"
              @click="$emit('remover-mp', i, j)"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>

          <button class="btn btn-sm btn-outline-success mt-2" @click="$emit('adicionar-mp', i)">
            <i class="bi bi-plus-lg"></i> Adicionar MP
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import api from '@/services/axios'

// ----------------------
// Props e emits
// ----------------------
const verificarCodigoComponente = async (index: number) => {
  const item = props.modelValue[index]
  const codigo = item.codigo
  if (!codigo) {
    item.codigoUsado = false
    return
  }

  try {
    const res = await api.get('/api/itens/verificar-codigo', { params: { codigo } })
    item.codigoUsado = res.data.produto || res.data.componente
  } catch (err) {
    console.error(err)
    item.codigoUsado = false
  }
}

interface Option {
  value: string | number
  label: string
}

interface Campo {
  key: string
  label: string
  type: 'text' | 'select'
  options?: Option[]
  class?: string
}

interface Item {
  id?: number | string
  codigo?: string
  descricao?: string
  anvisa?: string
  fluxo_id?: number
  tipo?: string
  materiasPrimas?: Item[]
  codigoUsado?: boolean
  [key: string]: string | number | boolean | Item[] | undefined
}

const props = defineProps<{
  titulo: string
  tituloSingular: string
  icone: string
  modelValue: Item[]
  campos: Campo[]
  camposMp?: Campo[]
}>()

const emit = defineEmits<{
  (e: 'remover', index: number): void
  (e: 'adicionar'): void
  (e: 'buscar', termo: string): void
  (e: 'adicionar-mp', index: number): void
  (e: 'remover-mp', index: number, mpIndex: number): void
}>()

const termoBusca = ref('')

const emitBuscar = () => emit('buscar', termoBusca.value)
const emitAdicionar = () => emit('adicionar')
</script>
