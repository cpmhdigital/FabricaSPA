<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

type PainelItem = {
  modulo: string
  label: string
  to: string
  icon?: string
}

const props = defineProps<{
  paineis: PainelItem[]
}>()

const router = useRouter()
const route = useRoute()

const open = ref(false)
const rootRef = ref<HTMLElement | null>(null)

const close = () => (open.value = false)
const toggle = () => (open.value = !open.value)

const current = computed(() => {
  const path = route.path
  const match = props.paineis.find(p => path === p.to || path.startsWith(p.to + '/'))
  return match || props.paineis[0] || { modulo: 'Módulo', label: 'Painel', to: '' }
})

const onDocClick = (e: MouseEvent) => {
  if (!open.value) return
  const target = e.target as Node
  if (rootRef.value && !rootRef.value.contains(target)) close()
}

const onKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') close()
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onKeyDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onKeyDown)
})

watch(() => route.path, () => close())

const go = (to: string) => {
  if (!to) return
  router.push(to)
  close()
}
</script>

<template>
  <div class="select-wrap" ref="rootRef">
    <!-- Pill -->
    <button class="select-pill" type="button" @click="toggle" :aria-expanded="open">
      <span>{{ current.modulo }}</span>
      <i class="bi bi-chevron-right"></i>
      <strong>Painel</strong>
      <i class="bi bi-chevron-down ms-2" :class="{ rot: open }"></i>
    </button>

    <!-- Dropdown (somente painéis) -->
    <div v-show="open" class="select-menu" role="menu">
      <div class="menu-title">Trocar Painel</div>

      <button
        v-for="p in paineis"
        :key="p.to"
        type="button"
        class="menu-item"
        :class="{ active: current.to === p.to }"
        @click="go(p.to)"
      >
        <i v-if="p.icon" class="bi" :class="p.icon"></i>
        <div class="item-text">
          <div class="item-mod">{{ p.modulo }}</div>
          <div class="item-lab">{{ p.label }}</div>
        </div>
        <i class="bi bi-arrow-up-right"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.select-wrap{ position:relative; }

.select-pill{
  display:flex; align-items:center; gap:8px;
  padding:10px 12px;
  border-radius:12px;
  background:#ffffff;
  border:1px solid #eef1f6;
  font-size:13px;
  color:#6b7280;
  cursor:pointer;
}
.select-pill strong{ color:#374151; font-weight:900; }
.select-pill:hover{ background:#f6f8fd; }

.select-pill .rot{ transition: transform .2s ease; }
.select-pill .rot.rot{ transform: rotate(180deg); }

.select-menu{
  position:absolute;
  right:0;
  top: calc(100% + 10px);
  width: 360px;
  max-width: calc(100vw - 40px);
  background:#ffffff;
  border:1px solid #eef1f6;
  border-radius:14px;
  box-shadow:0 20px 60px rgba(17,24,39,.12);
  padding:10px;
  z-index: 50;
}

.menu-title{
  font-weight:900;
  font-size:12px;
  color:#374151;
  padding:8px 10px;
  border-bottom:1px solid #eef1f6;
  margin-bottom:8px;
}

.menu-item{
  width:100%;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 10px;
  border:none;
  background:transparent;
  border-radius:12px;
  cursor:pointer;
  text-align:left;
}
.menu-item:hover{ background:#f6f8fd; }

.menu-item.active{
  background:#e9f5ef;
}

.menu-item i{
  color:#6b7280;
  font-size:16px;
}

.item-text{ flex:1; min-width:0; }
.item-mod{
  font-weight:900;
  font-size:12px;
  color:#374151;
  line-height:1.1;
}
.item-lab{
  font-size:12px;
  color:#6b7280;
  line-height:1.1;
}

.menu-item.active .item-mod{ color:#0f3d2e; }
.menu-item.active i{ color:#0f3d2e; }
</style>
