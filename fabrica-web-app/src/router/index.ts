import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/session'

import UserRegister from '@/views/auth/UserRegister.vue'
import Layout from '@/components/layout/Layout.vue'
import Dashboard from '@/views/PainelAtividade.vue'
import DashboardManutencao from '@/views/ordem-manutencao/PainelManutencao.vue'
import NovoPedido from '@/views/ordem-producao/NovoPedido.vue'
import DashboardProducao from '@/views/ordem-producao/PainelProducao.vue'
import NovoRegistro from '@/views/ordem-manutencao/NovoRegistro.vue'
import NovoRegistroOs from '@/views/ordem-servico/NovoRegistro.vue'
import AprovarPedido from '@/views/ordem-producao/AprovarPedido.vue'
import SetorOp from '@/views/ordem-producao/SetorProducao.vue'
import SetorForm from '@/views/ordem-producao/SetorForm.vue'
import Its from '@/views/ordem-producao/ItseRevOverview.vue'
import ConfigProd from '@/views/ordem-producao/ConfigProducao.vue'
import Etapa from '@/views/ordem-producao/EtapaConfig.vue'
import Fluxo from '@/views/ordem-producao/FluxoConfig.vue'
import NovoFluxo from '@/views/ordem-producao/FluxoForm.vue.vue'
import SetorOs from '@/views/ordem-servico/SetorServico.vue'
import HistoricoItRev from '@/views/ordem-producao/HistoricoItRev.vue'
import AjusteCadastro from '@/views/customizar/AjusteCadastro.vue'
import ListarUsuarios from '@/views/customizar/ListarUsuarios.vue'
import RegistroMaquinas from '@/views/manutencao-operacional/RegistroMaquinas.vue'
import Produto from '@/views/ordem-producao/ListaProduto.vue'
import Login from '@/views/auth/UserLogin.vue'
import EditarUsuario from '@/views/customizar/EditarUsuario.vue'
import RolesView from '@/views/customizar/RolesView.vue'
import UsuariosOnline from '@/views/customizar/UsuariosOnline.vue'
import EmailConfirmado from '@/views/auth/EmailConfirmado.vue'
import ResetPassword from '@/views/auth/ResetPassword.vue'
import NewPassword from '@/views/auth/NewPassword.vue'
import PainelServico from '@/views/ordem-servico/PainelServico.vue'
import EtapaForm from '@/views/ordem-producao/EtapaForm.vue'
import Producao from '@/views/ordem-producao/PedidoProducao.vue'
import DetalhesPedido from '@/views/ordem-producao/DetalhesPedido.vue'
import DooMatrizes from '@/views/doo/DooMatrizes.vue'
import DooIntegracao from '@/views/doo/DooIntegracao.vue'
import Documentacao from '@/views/customizar/Doc.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/email-confirmado', name: 'EmailConfirmado', component: EmailConfirmado },
    { path: '/', name: 'Login', component: Login },
    { path: '/user-register', name: 'UserRegister', component: UserRegister },
    { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
    { path: '/new-password', name: 'NewPassword', component: NewPassword },
    { path: '/:pathMatch(.*)*', redirect: '/' },

    {
      path: '/',
      component: Layout,
      children: [
        {
          path: 'painel-atividade',
          component: Dashboard,
          meta: {
            requiresAuth: true,
            breadcrumb: ['Atividade', 'Painel'],
          },
        },

        {
          path: 'ordem-producao/painel-producao',
          component: DashboardProducao,
          meta: {
            requiresAuth: true,
            permission: 'menu painel producao',
            breadcrumb: ['Produção', 'Painel'],
          },
        },
        {
          path: 'ordem-producao/novo-pedido',
          component: NovoPedido,
          meta: { requiresAuth: true, permission: 'menu criar pedido' },
        },
        {
          path: 'ordem-producao/qualidade',
          component: AprovarPedido,
          meta: { requiresAuth: true, permission: ['inspecionar op', 'reprovar item qualidade', 'retornar etapas'] },
        },
        {
          path: 'ordem-producao/setor',
          component: SetorOp,
          meta: { requiresAuth: true, permission: ['menu config producao', 'operar producao setor'] },
        },
        {
          path: '/ordem-producao/setores/:id/editar',
          component: SetorForm,
          meta: { requiresAuth: true, permission: 'menu config producao' },
        },
        {
          path: 'ordem-producao/its',
          component: Its,
          meta: { requiresAuth: true, permission: 'menu its' },
        },
        {
          path: 'ordem-producao/historico-itrev/:id',
          name: 'HistoricoItRev',
          component: HistoricoItRev,
          props: true,
          meta: { requiresAuth: true, permission: ['menu its', 'modificar it'] },
        },
        {
          path: 'ordem-producao/etapa',
          component: Etapa,
          meta: { requiresAuth: true, permission: 'menu config producao' },
        },
        {
          path: 'ordem-producao/formulario-etapa',
          component: EtapaForm,
          meta: { requiresAuth: true, permission: 'menu config producao' },
        },
        {
          path: 'ordem-producao/configuracao-producao',
          component: ConfigProd,
          meta: { requiresAuth: true, permission: 'menu config producao' },
        },
        {
          path: 'ordem-producao/fluxo',
          component: Fluxo,
          meta: { requiresAuth: true, permission: 'menu config producao' },
        },
        {
          path: '/ordem-producao/novo-fluxo',
          component: NovoFluxo,
          meta: { requiresAuth: true, permission: 'menu config producao' },
        },
        {
          path: '/ordem-producao/pedido-detalhe',
          name: 'pedido-detalhe',
          component: DetalhesPedido,
          meta: { requiresAuth: true, permission: 'visualizar dados do pedido completo' },
        },
        {
          path: '/ordem-producao/pedido-producao',
          name: 'pedido-producao',
          component: Producao,
          meta: { requiresAuth: true, permission: 'visualizar producao completa do pedido' },
        },
        {
          path: 'ordem-producao/produto',
          component: Produto,
          meta: { requiresAuth: true, permission: 'menu produtos' },
        },

        {
          path: 'ordem-manutencao/painel-manutencao',
          component: DashboardManutencao,
          meta: {
            requiresAuth: true,
            permission: 'menu painel manutencao',
            breadcrumb: ['Manutenção', 'Painel'],
          },
        },
        {
          path: 'ordem-manutencao/novo-registro',
          component: NovoRegistro,
          meta: { requiresAuth: true, permission: 'menu nova om' },
        },

        {
          path: 'ordem-servico/novo-registro',
          component: NovoRegistroOs,
          meta: { requiresAuth: true, permission: 'menu nova os' },
        },
        {
          path: 'ordem-servico/setor',
          component: SetorOs,
          meta: { requiresAuth: true, permission: 'menu setor os' },
        },
        {
          path: 'ordem-servico/painel-servico',
          component: PainelServico,
          meta: {
            requiresAuth: true,
            permission: 'menu painel os',
            breadcrumb: ['Serviço', 'Painel'],
          },
        },
        {
          path: 'manutencao-operacional/registro-maquinas',
          component: RegistroMaquinas,
          meta: { requiresAuth: true, permission: 'menu registro maquinas' },
        },

        {
          path: 'doo/matrizes',
          name: 'DooMatrizes',
          component: DooMatrizes,
          meta: { requiresAuth: true, permission: 'menu doo matrizes' },
        },
        {
          path: 'doo/integracao',
          name: 'DooIntegracao',
          component: DooIntegracao,
          meta: { requiresAuth: true, permission: 'menu doo integracao' },
        },

        { path: 'customizar/perfil', component: AjusteCadastro, meta: { requiresAuth: true } },
        {
          path: 'customizar/lista-usuarios',
          component: ListarUsuarios,
          meta: { requiresAuth: true, permission: 'menu lista usuarios' },
        },
        {
          path: 'customizar/usuarios-online',
          component: UsuariosOnline,
          meta: { requiresAuth: true, permission: 'menu usuarios online' },
        },
        {
          path: 'customizar/documentacao',
          component: Documentacao,
          meta: { requiresAuth: true, permission: 'documentacao api' },
        },

        {
          path: '/papeis',
          name: 'roles',
          component: RolesView,
          meta: { requiresAuth: true, permission: 'menu lista usuarios' },
        },
        {
          path: 'lista-usuarios/editar/:id',
          name: 'editarUsuario',
          component: EditarUsuario,
          props: true,
          meta: { requiresAuth: true, permission: 'menu lista usuarios' },
        },
      ],
    },
  ],
})

function getDefaultAuthorizedRoute(auth: ReturnType<typeof useAuthStore>) {
  if (auth.can('menu painel producao')) return '/ordem-producao/painel-producao'
  if (auth.can('menu painel manutencao')) return '/ordem-manutencao/painel-manutencao'
  if (auth.can('menu painel os')) return '/ordem-servico/painel-servico'
  if (auth.can('menu doo matrizes')) return '/doo/matrizes'
  return '/painel-atividade'
}

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  // carrega 1x, não toda navegação
  auth.loadFromStorage()

  // 1) auth básico
  if (to.meta.requiresAuth && !auth.token) {
    return next({ path: '/' })
  }

  // 2) garante permissions carregadas ANTES de validar permissão
  // (isso resolve seu "roles:[] permissions:[] no console")
  if (auth.token && auth.permissions.length === 0) {
    try {
      await auth.fetchMe()
    } catch {
      auth.clear()
      return next({ path: '/' })
    }
  }

  // 3) bloqueio por permissão
  const perm = to.meta.permission as string | string[] | undefined
  const perms = Array.isArray(perm) ? perm : perm ? [perm] : []
  if (perms.length > 0 && !perms.some((item) => auth.can(item))) {
    return next({ path: getDefaultAuthorizedRoute(auth) })
  }

  return next()
})

export default router
