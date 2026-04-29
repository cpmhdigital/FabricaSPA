<script setup lang="ts">
import SelectPillPaineis from '@/components/SelectPillPaineis.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

import { useRoute } from 'vue-router'

const route = useRoute()

const breadcrumb = computed(() => {
  const bc = route.meta.breadcrumb as [string, string] | undefined
  return bc ?? ['—', '—']
})

const breadcrumbLeft = computed(() => breadcrumb.value[0])
const breadcrumbRight = computed(() => breadcrumb.value[1])

defineProps<{
  title: string
}>()

/** ===== Notificações/Ajuda (popover) ===== */
const notifOpen = ref(false)
const helpOpen = ref(false)

type Notificacao = {
  texto: string
  createdAt?: string
  type?: 'info' | 'warning' | 'success' | 'danger'
}

// simulação (pode vir da API depois)
const notificacoes = ref<Notificacao[]>([]) // vazio = nenhuma notificação

const hasNotif = computed(() => notificacoes.value.length > 0)

const closeAll = () => {
  notifOpen.value = false
  helpOpen.value = false
}

const toggleNotif = () => {
  helpOpen.value = false
  notifOpen.value = !notifOpen.value
}

const toggleHelp = () => {
  notifOpen.value = false
  helpOpen.value = !helpOpen.value
}

// fecha ao clicar fora
const onClickOutside = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.topbar-pop')) closeAll()
}

onMounted(() => {
  document.addEventListener('click', onClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside)
})

/** ===== Painéis do SelectPill ===== */
type PainelItem = {
  modulo: string
  label: string
  to: string
  icon?: string
}

const paineis: PainelItem[] = [
  {
    modulo: 'Atividade',
    label: 'Painel Atividade',
    to: '/painel-atividade',
    icon: 'bi-grid',
  },
  {
    modulo: 'Produção',
    label: 'Painel Produção',
    to: '/ordem-producao/painel-producao',
    icon: 'bi-kanban',
  },
  {
    modulo: 'Manutenção',
    label: 'Painel Manutenção',
    to: '/ordem-manutencao/painel-manutencao',
    icon: 'bi-wrench-adjustable-circle',
  },
  {
    modulo: 'Serviço',
    label: 'Painel de Serviço',
    to: '/ordem-servico/painel-servico',
    icon: 'bi-clipboard-check',
  },
  {
    modulo: 'Manutenção Operacional',
    label: 'Painel Manutenção',
    to: '/manutencao-operacional/painel-manutencao',
    icon: 'bi-gear',
  },
  {
    modulo: 'Configurações',
    label: 'Dashboard',
    to: '/customizar/dashboard',
    icon: 'bi-sliders',
  },
]
</script>


<template>
  <div class="app-shell">
    <!-- MAIN -->
    <main class="main">
      <!-- TOPBAR -->
      <header class="topbar">
        <div class="topbar-left">
          <div class="breadcrumb">
            <span>{{ breadcrumbLeft }}</span>
            <i class="bi bi-chevron-right"></i>
            <span class="active">{{ breadcrumbRight }}</span>
          </div>

        </div>

        <div class="topbar-right topbar-pop">
          <SelectPillPaineis :paineis="paineis" />

          <!-- NOTIFICAÇÕES -->
          <div class="icon-wrap">
            <button class="icon-btn" type="button" @click.stop="toggleNotif">
              <i class="bi bi-bell"></i>
              <span v-if="hasNotif" class="dot"></span>
            </button>

            <div v-if="notifOpen" class="popover">
              <div class="popover-title">Notificações</div>

              <div v-if="!hasNotif" class="popover-empty">
                <i class="bi bi-bell-slash"></i>
                <span>Nenhuma notificação no momento</span>
              </div>

              <div v-else class="popover-list">
                <div v-for="(n, i) in notificacoes" :key="i" class="popover-item">
                  {{ n.texto }}
                </div>
              </div>
            </div>
          </div>

          <!-- AJUDA -->
          <div class="icon-wrap">
            <button class="icon-btn" type="button" @click.stop="toggleHelp">
              <i class="bi bi-question-circle"></i>
            </button>

            <div v-if="helpOpen" class="popover">
              <div class="popover-title">Ajuda</div>

              <div class="popover-help">
                <p><strong>Bem-vindo ao Sistemas Fábrica.</strong></p>

                <ul>
                  <li>Use o <b>menu lateral</b> para navegar entre módulos.</li>
                  <li>No topo, você pode trocar rapidamente entre os <b>painéis</b>.</li>
                  <li>Pedidos <b>a serem encaminhamento para produção</b> ficam na tabela principal.</li>
                  <li>Pedidos <b>aprovados e já em produção</b> aparecem no acompanhamento.</li>
                </ul>

                <p class="help-footer">
                  Em caso de dúvidas técnicas, entre em contato com o administrador do sistema.
                </p>
              </div>
            </div>
          </div>
        </div>

      </header>

      <!-- CONTENT -->
      <section class="content">
        <div class="cards-wrap">
          <slot name="cards" />
        </div>

        <div class="grid-2">
          <div class="panel">
            <slot name="acompanhamento" />
          </div>
          
          <div class="panel">
            <slot name="grafico" />
          </div>

        </div>

        <div class="panel panel-table">
          <slot name="tabela" />
        </div>
      </section>
    </main>
  </div>
</template>
<style scoped>
.app-shell {
  min-height: 100vh;
  background: #f5f7fb;
  display: flex;
  font-family:
    system-ui,
    -apple-system,
    Segoe UI,
    Roboto,
    Arial,
    'Helvetica Neue',
    Helvetica,
    sans-serif;
  color: #1f2a37;
}

/* MAIN */
.main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  /* CRÍTICO: evita overflow quando tem grid/tabelas */
}

/* TOPBAR */
.topbar {
  height: 74px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 22px;
  background: transparent;
  gap: 12px;
}

.topbar-left {
  min-width: 0;
  /* evita estourar com título grande */
}

.page-title {
  margin: 0;
  font-size: 24px;
  font-weight: 900;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #9ca3af;
  flex-wrap: wrap;
  /* responsivo */
}

.breadcrumb .active {
  color: #374151;
  font-weight: 700;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  /* responsivo */
  justify-content: flex-end;
}

.icon-wrap {
  position: relative;
}

/* Popover */
.popover {
  position: absolute;
  right: 0;
  top: calc(100% + 10px);
  width: 320px;
  max-width: calc(100vw - 28px);
  /* evita estourar no mobile */
  background: #ffffff;
  border: 1px solid #eef1f6;
  border-radius: 14px;
  box-shadow: 0 20px 60px rgba(17, 24, 39, 0.12);
  padding: 10px;
  z-index: 50;
}

.popover-title {
  font-weight: 900;
  font-size: 13px;
  padding: 8px 10px;
  border-bottom: 1px solid #eef1f6;
  margin-bottom: 8px;
}

.popover-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 20px 10px;
  color: #6b7280;
  font-size: 13px;
  text-align: center;
}

.popover-empty i {
  font-size: 22px;
  color: #9ca3af;
}

.popover-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.popover-item {
  padding: 8px 10px;
  border-radius: 10px;
  font-size: 13px;
  background: #f6f8fd;
}

.popover-help {
  font-size: 13px;
  color: #374151;
  padding: 4px 6px;
}

.popover-help ul {
  padding-left: 16px;
  margin: 8px 0;
}

.popover-help li {
  margin-bottom: 6px;
}

.help-footer {
  font-size: 12px;
  color: #6b7280;
  margin-top: 10px;
}

/* Select pill (seu componente já tem o próprio estilo, mas mantemos base) */
.select-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #eef1f6;
  font-size: 13px;
  color: #6b7280;
  max-width: 100%;
}

.select-pill strong {
  color: #374151;
}

/* ícones do topo */
.icon-btn {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #eef1f6;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  cursor: pointer;
}

.icon-btn i {
  color: #6b7280;
  font-size: 18px;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 99px;
  background: #ef4444;
  position: absolute;
  top: 8px;
  right: 9px;
  border: 2px solid #ffffff;
}

/* CONTENT */
.content {
  padding: 0 22px 22px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-width: 0;
  /* CRÍTICO */
}

.cards-wrap {
  background: #ffffff;
  border: 1px solid #eef1f6;
  border-radius: 16px;
  padding: 14px;
  box-shadow: 0 10px 30px rgba(17, 24, 39, 0.05);
  min-width: 0;
}

/* GRID: gráfico + acompanhamento */
.grid-2 {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 16px;
  min-width: 0;
  /* evita overflow */
}

.panel {
  background: #ffffff;
  border: 1px solid #eef1f6;
  border-radius: 16px;
  padding: 14px;
  box-shadow: 0 10px 30px rgba(17, 24, 39, 0.05);
  min-width: 0;
  /* evita overflow com tabelas/gráficos */
}

.panel-table {
  padding: 14px 0 10px;
  min-width: 0;
}

/* ===== Breakpoints ===== */

/* 1) Até 1100px: duas colunas viram uma */
@media (max-width: 1100px) {
  .grid-2 {
    grid-template-columns: 1fr;
  }
}

/* 2) Até 900px: topbar e conteúdo comprimem corretamente */
@media (max-width: 900px) {
  .topbar {
    height: auto;
    padding: 12px 14px;
    flex-wrap: wrap;
    align-items: flex-start;
  }

  .page-title {
    font-size: 20px;
  }

  .content {
    padding: 0 14px 16px;
  }

  .popover {
    right: auto;
    left: 0;
    /* melhora em telas pequenas quando o botão está “quebrado” */
  }
}
</style>
