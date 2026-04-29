<template>
  <div>
    <div
      v-for="(usuarios, setor) in dados"
      :key="setor"
      class="mb-4"
    >
      <!-- Cabeçalho do setor -->
      <h5 class="fw-bold border-bottom pb-1 mb-2">
        {{ setor }}
        — <small class="text-muted">{{ usuarios.length }} usuário(s)</small>
      </h5>

      <!-- Lista de usuários -->
      <div
        v-for="u in usuarios"
        :key="u.id"
        class="d-flex align-items-center p-3 rounded mb-2 user-card shadow-sm"
      >
        <!-- Avatar + Status -->
        <div class="position-relative">
          <div
            class="avatar rounded-circle d-flex justify-content-center align-items-center"
            :style="avatarStyle(u.nome)"
          >
            {{ u.nome?.charAt(0)?.toUpperCase() || "?" }}
          </div>

          <span
            class="status-dot"
            :class="onlineSet.has(u.id) ? 'bg-success' : 'bg-secondary'"
          ></span>
        </div>

        <!-- Informações -->
        <div class="ms-3 flex-grow-1">
          <div class="fw-semibold">{{ u.nome }}</div>
          <small class="text-muted">{{ u.email }}</small>
        </div>

        <!-- Status + Última saída -->
        <div class="text-end">
          <span
            :class="onlineSet.has(u.id) ? 'text-success fw-bold' : 'text-muted'"
          >
            {{ onlineSet.has(u.id) ? "online" : "offline" }}
          </span>

         <!--  <div v-if="u.last_logout" class="text-muted small">
            Última saída: {{ formatDateTime(u.last_logout) }}
          </div> -->
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/* =======================================================
    IMPORTS
======================================================= */
import { computed } from "vue"
import { usuariosOnline } from "@/plugins/ablyPresence"

/* =======================================================
    PROPS
======================================================= */
const props = defineProps({
  dados: { type: Object, required: true },
})

/* =======================================================
    IDs que estão online
======================================================= */
const onlineSet = computed(() => {
  return new Set(usuariosOnline.value.map(u => u.id))
})

/* =======================================================
    Funções auxiliares
======================================================= */

/** Formata datetime (YYYY-MM-DD HH:mm:ss → dd/mm/yyyy HH:MM) */
function formatDateTime(dateStr: string) {
  if (!dateStr) return ""

  const d = new Date(dateStr)

  const dia = String(d.getDate()).padStart(2, "0")
  const mes = String(d.getMonth() + 1).padStart(2, "0")
  const ano = d.getFullYear()

  const hora = String(d.getHours()).padStart(2, "0")
  const minuto = String(d.getMinutes()).padStart(2, "0")

  return `${dia}/${mes}/${ano} ${hora}:${minuto}`
}

/** Gera cor do avatar baseado no nome */
function avatarStyle(name = "") {
  const colors = ["#9aaed3", "#2cc990", "#ffb020", "#f66d9b", "#6f42c1"]
  const code = (name || "x").charCodeAt(0)
  const color = colors[code % colors.length]

  return {
    background: color,
    width: "46px",
    height: "46px",
    color: "#fff",
    fontWeight: "600",
    fontSize: "18px",
  }
}
</script>

<style scoped>
.user-card {
  background: #fafafa;
  transition: 0.2s;
}

.user-card:hover {
  background: #f0f0f0;
}

.avatar {
  position: relative;
}

.status-dot {
  position: absolute;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  bottom: 0;
  right: 0;
  border: 2px solid white;
}
</style>
