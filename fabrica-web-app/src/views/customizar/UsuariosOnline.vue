<template>
  <BaseLayout
    titulo="Usuários On-line"
    descricao="Usuários online no sistema"
    semCard
  >
    <div class="container py-3">

      <!-- TÍTULO + CONTADOR -->
      <div class="d-flex justify-content-between align-items-end mb-3">
        <p></p>
        <span class="badge bg-success">
          {{ totalOnline }} online
        </span>
      </div>

      <!-- ABAS -->
      <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
          <button
            class="nav-link"
            :class="{ active: activeTab === 'online' }"
            @click="activeTab = 'online'"
          >
            Online ({{ totalOnline }})
          </button>
        </li>

        <li class="nav-item" role="presentation">
          <button
            class="nav-link"
            :class="{ active: activeTab === 'todos' }"
            @click="activeTab = 'todos'"
          >
            Todos os usuários
          </button>
        </li>
      </ul>

      <!-- CAMPO DE BUSCA -->
      <div class="input-group mb-3">
        <span class="input-group-text bg-white">
          <i class="bi bi-search"></i>
        </span>

        <input
          v-model="q"
          @input="onInput"
          type="search"
          class="form-control"
          placeholder="Pesquisar por nome, email ou setor..."
        />
      </div>

      <!-- CONTEÚDO DAS ABAS -->
      <div v-if="activeTab === 'online'">
        <UserList
          :dados="groupedOnline"
          titulo="Usuários Online"
          :total="totalOnline"
        />
      </div>

      <div v-else>
        <UserList
          :dados="groupedAll"
          titulo="Todos os Usuários"
          :total="totalAll"
        />
      </div>

    </div>
  </BaseLayout>
</template>

<script setup lang="ts">
/* -----------------------------
    IMPORTS
------------------------------ */
import { ref, computed } from "vue"
import { usuariosOnline } from "@/plugins/ablyPresence"
import UserList from "../../components/UserList.vue"
import api from "@/services/axios"
import BaseLayout from '@/components/BaseLayout.vue'

/* -----------------------------
    ESTADOS
------------------------------ */
const q = ref("")
const debouncedQuery = ref("")
const allUsers = ref<any[]>([])
const activeTab = ref<"online" | "todos">("online")

/* -----------------------------
    DEBOUNCE DA BUSCA
------------------------------ */
let debounceTimer: any = null

function onInput() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    debouncedQuery.value = q.value.toLowerCase().trim()
  }, 250)
}

/* -----------------------------
    BUSCAR TODOS USUÁRIOS
------------------------------ */
async function carregarTodos() {
  const r = await api.get("/api/usuarios/lista")
  allUsers.value = r.data.users
}
carregarTodos()

/* -----------------------------
    AGRUPAMENTO E FILTRO
------------------------------ */
function agrupar(lista: any[]) {
  const termo = debouncedQuery.value
  const grupos = new Map<string, any[]>()

  for (const u of lista) {
    const setor = u.departamento_nome || "Sem Departamento"

    const nome = (u.nome || u.name || "").toLowerCase()
    const email = (u.email || "").toLowerCase()
    const setorLower = setor.toLowerCase()

    // Filtragem
    if (termo) {
      const match =
        nome.includes(termo) ||
        email.includes(termo) ||
        setorLower.includes(termo)

      if (!match) continue
    }

    // Criar grupo se não existir
    if (!grupos.has(setor)) grupos.set(setor, [])

    // Adicionar ao grupo
    grupos.get(setor)!.push(u)
  }

  // Ordenar alfabeticamente por setor
  return Object.fromEntries(
    Array.from(grupos.entries()).sort(([a], [b]) => a.localeCompare(b))
  )
}

/* -----------------------------
    COMPUTEDS
------------------------------ */
const groupedOnline = computed(() => agrupar(usuariosOnline.value))
const groupedAll = computed(() => agrupar(allUsers.value))

const totalOnline = computed(() => usuariosOnline.value.length)
const totalAll = computed(() => allUsers.value.length)
</script>
