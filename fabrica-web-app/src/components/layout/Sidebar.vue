<script setup lang="ts">
import { ref, watchEffect, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/axios.ts'
import { sairDoAbly } from '@/plugins/ablyPresence'
import { useAuthStore } from '@/stores/session'

type MenuLink = { label: string; to: string; permission?: string }
type MenuSection = { title: string; icon: string; permission?: string; links: MenuLink[] }

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const isSidebarOpen = ref(false)
const openIndex = ref<number | null>(null)

const toggleSidebar = () => (isSidebarOpen.value = !isSidebarOpen.value)
const closeSidebar = () => (isSidebarOpen.value = false)

const toggle = (index: number) => {
  openIndex.value = openIndex.value === index ? null : index
}

/** ===== Dropdown do user-pill ===== */
const userMenuOpen = ref(false)
const userPillRef = ref<HTMLElement | null>(null)

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const closeUserMenu = () => {
  userMenuOpen.value = false
}

const goToChangePassword = () => {
  closeUserMenu()
  router.push('/customizar/perfil')
}

const onDocClick = (e: MouseEvent) => {
  if (!userMenuOpen.value) return
  const target = e.target as Node
  if (userPillRef.value && !userPillRef.value.contains(target)) {
    closeUserMenu()
  }
}

const onKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') closeUserMenu()
}

onMounted(() => {
  auth.loadFromStorage()
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onKeyDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onKeyDown)
})

const logout = async () => {
  try {
    await sairDoAbly()
    await api.post('/api/logout')
  } catch (error) {
    console.error('Erro ao sair', error)
  } finally {
    auth.clear()
    router.push('/')
  }
}

/**
 * MENU: cada link/section pode ter permission.
 * Se permission não existir, sempre aparece.
 */
const menuItems: MenuSection[] = [
  {
    title: 'Ordem de Produção',
    icon: 'bi-kanban',
    permission: 'menu ordem producao',
    links: [
      {
        label: 'Painel Produção',
        to: '/ordem-producao/painel-producao',
        permission: 'menu painel producao',
      },
      { label: 'Criar Pedido', to: '/ordem-producao/novo-pedido', permission: 'menu criar pedido' },
      { label: 'Produtos', to: '/ordem-producao/produto', permission: 'menu produtos' },
      { label: 'Instruções de Trabalho', to: '/ordem-producao/its', permission: 'menu its' },
      {
        label: 'Configurar Produção',
        to: '/ordem-producao/configuracao-producao',
        permission: 'menu config producao',
      },
    ],
  },
  {
    title: 'Ordem de Manutenção',
    icon: 'bi-wrench-adjustable-circle',
    permission: 'menu ordem manutencao',
    links: [
      {
        label: 'Painel Manutenção',
        to: '/ordem-manutencao/painel-manutencao',
        permission: 'menu painel manutencao',
      },
      {
        label: 'Nova Ordem de Manutenção',
        to: '/ordem-manutencao/novo-registro',
        permission: 'menu nova om',
      },
    ],
  },
  {
    title: 'Ordem de Serviço',
    icon: 'bi-clipboard-check',
    permission: 'menu ordem servico',
    links: [
      {
        label: 'Painel de Serviço',
        to: '/ordem-servico/painel-servico',
        permission: 'menu painel os',
      },
      {
        label: 'Nova Ordem de Serviço',
        to: '/ordem-servico/novo-registro',
        permission: 'menu nova os',
      },
      { label: 'Setor', to: '/ordem-servico/setor', permission: 'menu setor os' },
    ],
  },
  {
    title: 'Manutenção Operacional',
    icon: 'bi-gear',
    permission: 'menu manutencao operacional',
    links: [
      {
        label: 'Painel Manutenção',
        to: '/ordem-manutencao/painel-manutencao',
        permission: 'menu painel manutencao',
      },
      {
        label: 'Nova Manutenção',
        to: '/ordem-manutencao/novo-registro',
        permission: 'menu nova om',
      },
      {
        label: 'Registro de Máquinas',
        to: '/manutencao-operacional/registro-maquinas',
        permission: 'menu registro maquinas',
      },
    ],
  },

  //  NOVO: DOO
  {
    title: 'DOO',
    icon: 'bi-box-arrow-up-right',
    permission: 'menu doo',
    links: [
      {
        label: 'Matrizes (DOO)',
        to: '/doo/matrizes',
        permission: 'menu doo matrizes',
      },
      {
        label: 'Integração DOO',
        to: '/doo/integracao',
        permission: 'menu doo integracao',
      },
    ],
  },

  {
    title: 'Geral',
    icon: 'bi-sliders',
    permission: 'menu geral',
    links: [
      { label: 'Dashboard', to: '/painel-atividade', permission: 'menu dashboard' },
      {
        label: 'Usuários Online',
        to: '/customizar/usuarios-online',
        permission: 'menu usuarios online',
      },
      {
        label: 'Lista de Usuários',
        to: '/customizar/lista-usuarios',
        permission: 'menu lista usuarios',
      },
      {
        label: 'Documentação',
        to: '/customizar/documentacao',
        permission: 'documentacao api',
      },
    ],
  },
]

const filteredMenuItems = computed<MenuSection[]>(() => {
  return menuItems
    .map((section) => {
      const links = section.links.filter((l) => !l.permission || auth.can(l.permission))
      const sectionVisible = !section.permission || auth.can(section.permission) || links.length > 0
      return sectionVisible ? { ...section, links } : null
    })
    .filter(Boolean) as MenuSection[]
})

/**
 * Abrir o accordion correto pelo prefixo da rota,
 * usando TITLE (porque o menu é filtrado).
 */
const routeToSectionTitle: Record<string, string> = {
  '/ordem-producao': 'Ordem de Produção',
  '/ordem-manutencao': 'Ordem de Manutenção',
  '/ordem-servico': 'Ordem de Serviço',
  '/manutencao-operacional': 'Manutenção Operacional',
  '/doo': 'DOO', // NOVO
  '/customizar': 'Geral',
}

watchEffect(() => {
  const matchedPath = Object.keys(routeToSectionTitle).find((path) => route.path.startsWith(path))
  if (!matchedPath) {
    openIndex.value = null
    return
  }

  const title = routeToSectionTitle[matchedPath]
  const idx = filteredMenuItems.value.findIndex((s) => s.title === title)
  openIndex.value = idx >= 0 ? idx : null
})
</script>

<template>
  <!-- handle mobile -->
  <div class="sidebar-handle" @click="toggleSidebar" v-if="!isSidebarOpen">
    <i class="bi bi-caret-right-fill"></i>
  </div>

  <aside class="sidebar" :class="{ open: isSidebarOpen }">
    <button class="close-btn" @click="closeSidebar" aria-label="Fechar">×</button>

    <div class="brand">
      <div class="brand-mark">S<span class="invert">F</span></div>
      <div class="brand-text">
        <div class="brand-title">SISTEMAS</div>
        <div class="brand-sub">FÁBRICA</div>
      </div>
    </div>

    <!-- USER PILL (clicável) -->
    <div class="user-pill-wrap" ref="userPillRef">
      <button type="button" class="user-pill" @click.stop="toggleUserMenu" :aria-expanded="userMenuOpen"
        aria-haspopup="menu">
        <div class="avatar"></div>
        <div class="user-meta">
          <div class="user-name">{{ auth.user?.nome || '' }}</div>
          <div class="user-role">{{ auth.user?.email || '' }}</div>
        </div>
        <i class="bi bi-chevron-down" :class="{ rotate: userMenuOpen }"></i>
      </button>

      <!-- Dropdown -->
      <div v-show="userMenuOpen" class="user-menu" role="menu">
        <button class="user-menu-item" type="button" role="menuitem" @click="goToChangePassword">
          <i class="bi bi-key me-2"></i>
          Mudar senha
        </button>

        <button class="user-menu-item danger" type="button" role="menuitem" @click="logout">
          <i class="bi bi-box-arrow-right me-2"></i>
          Sair
        </button>
      </div>
    </div>

    <!-- MENU -->
    <nav class="menu">
      <router-link to="/painel-atividade" custom v-slot="{ navigate, href, isActive }">
        <div class="menu-item" :class="{ active: isActive }" @click="navigate" :href="href">
          <i class="bi bi-grid"></i>
          <span>Painel de Atividade</span>
        </div>
      </router-link>

      <div class="menu-section" v-for="(item, index) in filteredMenuItems" :key="item.title">
        <div class="menu-item" :class="{ active: openIndex === index }" @click="toggle(index)">
          <i class="bi" :class="item.icon"></i>
          <span>{{ item.title }}</span>
          <span class="chevron" :class="{ open: openIndex === index }"></span>
        </div>

        <transition name="accordion">
          <div v-show="openIndex === index" class="submenu">
            <router-link v-for="link in item.links" :key="link.to" :to="link.to" custom
              v-slot="{ navigate, href, isActive }">
              <div class="submenu-item" :class="{ current: isActive }" @click="navigate" :href="href">
                {{ link.label }}
              </div>
            </router-link>
          </div>
        </transition>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="version">v1.0.0 © Sistemas Fábrica</div>
    </div>
  </aside>
</template>

<style scoped>
/* ===== SIDEBAR ===== */
.sidebar {
  width: 280px;
  background: #ffffff;
  border-right: 1px solid #eef1f6;
  padding: 18px 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  height: 100vh;
}

/* BRAND */
.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 6px 10px;
}

.brand-mark {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  color: #0f3d2e;
  background: #e9f5ef;
  letter-spacing: 1px;
}

.brand-text .brand-title {
  font-size: 12px;
  color: #6b7280;
  line-height: 1;
}

.brand-text .brand-sub {
  font-size: 18px;
  font-weight: 800;
  color: #0f3d2e;
  line-height: 1.1;
}

/* ===== USER DROPDOWN ===== */
.user-pill-wrap {
  position: relative;
}

.user-pill {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f4f6fb;
  border: 1px solid #eef1f6;
  padding: 10px 12px;
  border-radius: 12px;
  cursor: pointer;
  text-align: left;
}

.user-pill:hover {
  background: #eef3fb;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #dbeafe, #ecfdf5);
  border: 1px solid #e5e7eb;
}

.user-meta {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 700;
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #374151;
  text-transform: capitalize;
}

.user-role {
  font-size: 11px;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-pill i {
  color: #6b7280;
  transition: transform 0.2s ease;
}

.user-pill i.rotate {
  transform: rotate(180deg);
}

/* Dropdown */
.user-menu {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 8px);
  background: #ffffff;
  border: 1px solid #eef1f6;
  border-radius: 12px;
  box-shadow: 0 16px 40px rgba(17, 24, 39, 0.12);
  padding: 6px;
  z-index: 20;
}

.user-menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 10px;
  border-radius: 10px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: #374151;
  font-weight: 600;
  text-align: left;
}

.user-menu-item:hover {
  background: #f6f8fd;
}

.user-menu-item.danger {
  color: #b42318;
}

.user-menu-item.danger:hover {
  background: #fff1f2;
}

/* MENU */
.menu {
  margin-top: 2px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  overflow: auto;
  padding-right: 4px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 10px;
  border-radius: 12px;
  color: #374151;
  cursor: pointer;
  user-select: none;
  position: relative;
}

.menu-item i {
  color: #6b7280;
}

.menu-item:hover {
  background: #f6f8fd;
}

.menu-item.active {
  background: #e9f5ef;
  color: #0f3d2e;
}

.menu-item.active i {
  color: #0f3d2e;
}

.menu-section {
  margin-top: 4px;
}

/* chevron */
.chevron {
  margin-left: auto;
  display: flex;
  align-items: center;
  transition: transform 0.25s ease;
}

.chevron.open {
  transform: rotate(90deg);
}

/* SUBMENU */
.submenu {
  margin-left: 34px;
  margin-top: 6px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.submenu-item {
  padding: 8px 10px;
  border-radius: 10px;
  font-size: 13px;
  color: #6b7280;
  cursor: pointer;
}

.submenu-item:hover {
  background: #f6f8fd;
}

.submenu-item.current {
  color: #0f3d2e;
  background: #f0fbf6;
  font-weight: 700;
}

/* FOOTER */
.sidebar-footer {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.version {
  font-size: 12px;
  color: #9ca3af;
  padding: 0 4px;
}

/* accordion animation */
.accordion-enter-active,
.accordion-leave-active {
  transition:
    max-height 0.25s ease,
    opacity 0.25s ease;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  max-height: 500px;
  opacity: 1;
}

/* mobile open/close */
.sidebar-handle {
  display: none;
}

.close-btn {
  background: transparent;
  color: #444;
  border: none;
  font-size: 2em;
  cursor: pointer;
  align-self: flex-end;
  display: none;
}

.invert {
  display: inline-block;
  transform: rotate(180deg) scale(1.2);
  font-weight: 900;
  color: #0f3d2e;
}

@media (max-width: 1300px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: -100%;
    width: 78%;
    max-width: 320px;
    z-index: 1000;
    transition: left 0.3s ease;
  }

  .sidebar.open {
    left: 0;
  }

  .close-btn {
    display: block;
  }

  .sidebar-handle {
    display: flex;
    position: fixed;
    top: 50%;
    left: 0;
    transform: translateY(-100%);
    background-color: #06834d;
    width: 20px;
    height: 100px;
    z-index: 1002;
    align-items: center;
    color: white;
    justify-content: center;
    border-radius: 0 10px 10px 0;
    cursor: pointer;
  }
}
</style>
