<script setup lang="ts">
import { ref, computed } from 'vue'
import PainelBase from '@/components/PainelBase.vue'

/**
 * Perfil: pode vir do localStorage/claims/token
 * - gestor: vê números globais
 * - colaborador: vê números filtrados (ex.: itens atribuídos)
 * - (adicione outros: 'admin', 'qualidade', 'pcp', etc.)
 */
type Perfil = 'gestor' | 'colaborador' | 'qualidade' | 'pcp'
const perfilUsuario = ref<Perfil>('gestor')

/** ===== KPIs (exemplo) ===== */
const kpis = computed(() => {
  // Ajuste conforme seu backend
  const base = {
    aguardando: 5,
    aprovados: 12,
    emExecucao: 8,
    atrasados: 3,
  }

  // Ex.: colaborador enxerga menor volume (apenas atribuídos)
  if (perfilUsuario.value === 'colaborador') {
    return { aguardando: 2, aprovados: 4, emExecucao: 3, atrasados: 1 }
  }

  return base
})

/** ===== Atividades recentes (timeline) ===== */
type Atividade = {
  id: number
  when: string
  title: string
  description: string
  tag: 'Produção' | 'Manutenção' | 'Serviço' | 'Sistema'
  level: 'info' | 'success' | 'warning' | 'danger'
}

const atividades = ref<Atividade[]>([
  {
    id: 1,
    when: 'Hoje, 09:32',
    title: 'Pedido #1029 aprovado',
    description: 'Encaminhado para Produção e liberado para planejamento.',
    tag: 'Produção',
    level: 'success',
  },
  {
    id: 2,
    when: 'Hoje, 08:55',
    title: 'Ordem de Manutenção criada',
    description: 'Máquina: SLM-02 • Prioridade: Média • Responsável: Equipe Técnica.',
    tag: 'Manutenção',
    level: 'info',
  },
  {
    id: 3,
    when: 'Ontem, 17:18',
    title: 'Atraso detectado em item',
    description: 'Item associado ao pedido #1011 ultrapassou o prazo definido.',
    tag: 'Produção',
    level: 'warning',
  },
  {
    id: 4,
    when: 'Ontem, 14:40',
    title: 'OS finalizada',
    description: 'Ordem de Serviço concluída e registrada no histórico.',
    tag: 'Serviço',
    level: 'success',
  },
])

/** ===== Pendências / tarefas rápidas ===== */
type Pendencia = {
  id: number
  title: string
  sub: string
  due?: string
  status: 'Aberta' | 'Em andamento' | 'Bloqueada' | 'Concluída'
}

const pendencias = ref<Pendencia[]>([
  { id: 1, title: 'Revisar pedido #1030', sub: 'Validar dados e anexos antes do encaminhamento', due: 'Hoje', status: 'Aberta' },
  { id: 2, title: 'Checar fila de impressão', sub: 'Priorizar itens com prazo próximo', due: 'Amanhã', status: 'Em andamento' },
  { id: 3, title: 'Atualizar status do item #778', sub: 'Registrar etapa concluída no sistema', status: 'Bloqueada' },
])

/** ===== Ações rápidas por perfil ===== */
const acoesRapidas = computed(() => {
  // Ajuste rotas reais
  const common = [
    { label: 'Abrir Produção', to: '/ordem-producao/painel-producao', icon: 'bi-kanban' },
    { label: 'Abrir Manutenção', to: '/ordem-manutencao/painel-manutencao', icon: 'bi-wrench-adjustable-circle' },
    { label: 'Abrir Serviço', to: '/ordem-servico/painel-servico', icon: 'bi-clipboard-check' },
  ]

  if (perfilUsuario.value === 'gestor' || perfilUsuario.value === 'pcp') {
    return [
      ...common,
      { label: 'Criar Pedido', to: '/ordem-producao/novo-pedido', icon: 'bi-plus-circle' },
    ]
  }

  if (perfilUsuario.value === 'qualidade') {
    return [
      ...common,
      { label: 'Relatórios', to: '/customizar/dashboard', icon: 'bi-graph-up' },
    ]
  }

  return common
})

/** ===== Filtro de atividade (global para todos) ===== */
const filtroTag = ref<'Todos' | Atividade['tag']>('Todos')

const atividadesFiltradas = computed(() => {
  if (filtroTag.value === 'Todos') return atividades.value
  return atividades.value.filter((a) => a.tag === filtroTag.value)
})
</script>

<template>
  <PainelBase title="Painel de Atividade">
    <!-- ===== CARDS ===== -->
    <template #cards>
      <div class="kpi-grid">
        <div class="kpi">
          <div class="kpi-icon ok"><i class="bi bi-inboxes"></i></div>
          <div class="kpi-meta">
            <div class="kpi-value">{{ kpis.aguardando }}</div>
            <div class="kpi-label">Aguardando</div>
            <div class="kpi-sub">Entrada / PCP</div>
          </div>
        </div>

        <div class="kpi">
          <div class="kpi-icon info"><i class="bi bi-check2-circle"></i></div>
          <div class="kpi-meta">
            <div class="kpi-value">{{ kpis.aprovados }}</div>
            <div class="kpi-label">Aprovados</div>
            <div class="kpi-sub">Liberados</div>
          </div>
        </div>

        <div class="kpi">
          <div class="kpi-icon warn"><i class="bi bi-tools"></i></div>
          <div class="kpi-meta">
            <div class="kpi-value">{{ kpis.emExecucao }}</div>
            <div class="kpi-label">Em execução</div>
            <div class="kpi-sub">Em andamento</div>
          </div>
        </div>

        <div class="kpi">
          <div class="kpi-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
          <div class="kpi-meta">
            <div class="kpi-value">{{ kpis.atrasados }}</div>
            <div class="kpi-label">Em atraso</div>
            <div class="kpi-sub">Atenção</div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== GRAFICO (aqui usamos como "Ações rápidas") ===== -->
    <template #grafico>
      <div class="panel-title-row">
        <div class="panel-title">Ações rápidas</div>
        <div class="panel-sub">Acesso direto aos módulos</div>
      </div>

      <div class="quick-grid">
        <router-link
          v-for="a in acoesRapidas"
          :key="a.to"
          :to="a.to"
          class="quick-btn"
        >
          <i class="bi" :class="a.icon"></i>
          <span>{{ a.label }}</span>
          <i class="bi bi-arrow-up-right"></i>
        </router-link>
      </div>
    </template>

    <!-- ===== ACOMPANHAMENTO (Timeline + Pendências) ===== -->
    <template #acompanhamento>
      <div class="panel-title-row">
        <div class="panel-title">Atividades recentes</div>

        <div class="filter-row">
          <span class="filter-label">Filtro</span>
          <select v-model="filtroTag" class="filter-select">
            <option value="Todos">Todos</option>
            <option value="Produção">Produção</option>
            <option value="Manutenção">Manutenção</option>
            <option value="Serviço">Serviço</option>
            <option value="Sistema">Sistema</option>
          </select>
        </div>
      </div>

      <div class="timeline">
        <div class="tl-item" v-for="a in atividadesFiltradas" :key="a.id">
          <div class="tl-dot" :class="a.level"></div>

          <div class="tl-body">
            <div class="tl-top">
              <div class="tl-title">{{ a.title }}</div>
              <span class="tl-when">{{ a.when }}</span>
            </div>
            <div class="tl-desc">{{ a.description }}</div>
            <span class="tl-tag">{{ a.tag }}</span>
          </div>
        </div>
      </div>

      <div class="divider"></div>

      <div class="panel-title-row">
        <div class="panel-title">Pendências</div>
        <div class="panel-sub">Itens que exigem ação</div>
      </div>

      <div class="tasks">
        <div class="task" v-for="t in pendencias" :key="t.id">
          <div class="task-main">
            <div class="task-title">{{ t.title }}</div>
            <div class="task-sub">{{ t.sub }}</div>
          </div>

          <div class="task-right">
            <span class="task-status" :class="t.status.replace(' ', '-')">
              {{ t.status }}
            </span>
            <span v-if="t.due" class="task-due">{{ t.due }}</span>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== TABELA (Resumo por módulo) ===== -->
    <template #tabela>
      <div class="table-head">
        <div>
          <div class="panel-title">Resumo por módulo</div>
          <div class="panel-sub">Visão unificada para qualquer perfil</div>
        </div>

        <button class="mini-btn" type="button">
          <i class="bi bi-arrow-repeat me-1"></i>
          Atualizar
        </button>
      </div>

      <div class="simple-table-wrap">
        <table class="simple-table">
          <thead>
            <tr>
              <th>Módulo</th>
              <th style="width: 120px;">Aguardando</th>
              <th style="width: 120px;">Em execução</th>
              <th style="width: 120px;">Aprovados</th>
              <th style="width: 120px;">Atrasos</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Produção</td>
              <td>{{ kpis.aguardando }}</td>
              <td>{{ kpis.emExecucao }}</td>
              <td>{{ kpis.aprovados }}</td>
              <td class="td-warn">{{ kpis.atrasados }}</td>
            </tr>
            <tr>
              <td>Manutenção</td>
              <td>1</td>
              <td>2</td>
              <td>—</td>
              <td class="td-warn">0</td>
            </tr>
            <tr>
              <td>Serviço</td>
              <td>0</td>
              <td>1</td>
              <td>—</td>
              <td class="td-warn">0</td>
            </tr>
            <tr>
              <td>Manutenção Operacional</td>
              <td>0</td>
              <td>1</td>
              <td>—</td>
              <td class="td-warn">0</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </PainelBase>
</template>

<style scoped>
/* ===== Cards KPI (mesma pegada clean do seu layout) ===== */
.kpi-grid{
  display:grid;
  grid-template-columns: repeat(4, 1fr);
  gap:12px;
}

.kpi{
  background:#ffffff;
  border:1px solid #eef1f6;
  border-radius:14px;
  padding:12px;
  display:flex;
  gap:10px;
  align-items:center;
}

.kpi-icon{
  width:42px;height:42px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid #eef1f6;
  background:#f6f8fd;
}
.kpi-icon i{ font-size:18px; color:#6b7280; }

.kpi-icon.ok{ background:#e9f5ef; border-color:#d7efe4; }
.kpi-icon.ok i{ color:#0f3d2e; }

.kpi-icon.info{ background:#eef2ff; border-color:#e0e7ff; }
.kpi-icon.warn{ background:#fff7ed; border-color:#ffedd5; }
.kpi-icon.danger{ background:#fff1f2; border-color:#ffe4e6; }

.kpi-meta{ min-width:0; }
.kpi-value{ font-weight:900; font-size:20px; color:#111827; line-height:1.1; }
.kpi-label{ font-weight:800; font-size:12px; color:#374151; }
.kpi-sub{ font-size:12px; color:#9ca3af; }

/* ===== Painel title row ===== */
.panel-title-row{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
  margin-bottom:10px;
}
.panel-title{ font-weight:900; color:#111827; }
.panel-sub{ font-size:12px; color:#9ca3af; margin-top:2px; }

/* ===== Quick actions ===== */
.quick-grid{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:10px;
}
.quick-btn{
  display:flex;
  align-items:center;
  gap:10px;
  padding:12px;
  border-radius:14px;
  border:1px solid #eef1f6;
  background:#ffffff;
  color:#374151;
  text-decoration:none;
  font-weight:800;
}
.quick-btn i{ color:#6b7280; }
.quick-btn:hover{ background:#f6f8fd; }
.quick-btn :last-child{ margin-left:auto; }

/* ===== Filter ===== */
.filter-row{
  display:flex; align-items:center; gap:8px;
}
.filter-label{ font-size:12px; color:#9ca3af; font-weight:800; }
.filter-select{
  height:34px;
  border-radius:12px;
  border:1px solid #eef1f6;
  background:#ffffff;
  padding:0 10px;
  font-size:12px;
  color:#374151;
}

/* ===== Timeline ===== */
.timeline{
  display:flex;
  flex-direction:column;
  gap:12px;
}
.tl-item{
  display:flex;
  gap:12px;
}
.tl-dot{
  width:12px;height:12px;border-radius:999px;
  margin-top:6px;
  border:2px solid #ffffff;
  box-shadow:0 0 0 2px #eef1f6;
}
.tl-dot.info{ background:#6366f1; }
.tl-dot.success{ background:#10b981; }
.tl-dot.warning{ background:#f59e0b; }
.tl-dot.danger{ background:#ef4444; }

.tl-body{
  flex:1;
  border:1px solid #eef1f6;
  background:#ffffff;
  border-radius:14px;
  padding:10px 12px;
}
.tl-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.tl-title{ font-weight:900; color:#111827; font-size:13px; }
.tl-when{ font-size:12px; color:#9ca3af; white-space:nowrap; }
.tl-desc{ font-size:12px; color:#6b7280; margin-top:4px; }
.tl-tag{
  display:inline-flex;
  margin-top:8px;
  font-size:11px;
  font-weight:900;
  color:#0f3d2e;
  background:#e9f5ef;
  border:1px solid #d7efe4;
  padding:4px 8px;
  border-radius:999px;
}

/* ===== Tasks ===== */
.divider{
  height:1px;
  background:#eef1f6;
  margin:14px 0;
}

.tasks{
  display:flex;
  flex-direction:column;
  gap:10px;
}

.task{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
  border:1px solid #eef1f6;
  background:#ffffff;
  border-radius:14px;
  padding:10px 12px;
}
.task-title{ font-weight:900; font-size:13px; color:#111827; }
.task-sub{ font-size:12px; color:#6b7280; margin-top:3px; }

.task-right{
  display:flex;
  flex-direction:column;
  align-items:flex-end;
  gap:6px;
  min-width:110px;
}
.task-status{
  font-size:11px;
  font-weight:900;
  padding:4px 8px;
  border-radius:999px;
  border:1px solid #eef1f6;
  color:#374151;
  background:#f6f8fd;
}
.task-status.Aberta{ background:#eef2ff; border-color:#e0e7ff; }
.task-status.Em-andamento{ background:#e9f5ef; border-color:#d7efe4; color:#0f3d2e; }
.task-status.Bloqueada{ background:#fff1f2; border-color:#ffe4e6; color:#b42318; }
.task-status.Concluída{ background:#f3f4f6; }

.task-due{ font-size:12px; color:#9ca3af; }

/* ===== Table ===== */
.table-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:12px 14px 6px;
}
.mini-btn{
  height:34px;
  border-radius:12px;
  border:1px solid #eef1f6;
  background:#ffffff;
  padding:0 12px;
  font-weight:900;
  font-size:12px;
  color:#374151;
}
.mini-btn:hover{ background:#f6f8fd; }

.simple-table-wrap{ padding: 0 14px 14px; }
.simple-table{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  font-size:13px;
}
.simple-table thead th{
  background:#f6f8fd;
  color:#6b7280;
  font-weight:900;
  padding:12px;
  border-top:1px solid #eef1f6;
  border-bottom:1px solid #eef1f6;
}
.simple-table thead th:first-child{
  border-left:1px solid #eef1f6;
  border-top-left-radius:12px;
}
.simple-table thead th:last-child{
  border-right:1px solid #eef1f6;
  border-top-right-radius:12px;
}
.simple-table tbody td{
  padding:12px;
  border-bottom:1px solid #eef1f6;
  background:#ffffff;
  color:#374151;
}
.simple-table tbody tr:hover td{ background:#fbfcff; }
.td-warn{ color:#b42318; font-weight:900; }

/* ===== Responsivo ===== */
@media (max-width: 1100px){
  .kpi-grid{ grid-template-columns: 1fr 1fr; }
  .quick-grid{ grid-template-columns: 1fr; }
}
</style>
