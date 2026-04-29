<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/axios.ts'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()

interface Usuario {
  id?: number
  name?: string
  email?: string
  status?: string
  departamento_id?: number | string
  departamento?: Departamento
  roles?: Role | Role[]
  permissoes?: Permissao[]
  tipo_acesso?: 'padrao' | 'personalizado'
  email_verified_at?: string | null
  created_at?: string
}

interface Departamento {
  id: number
  nome: string
}

interface Permissao {
  id: number
  name: string
  url?: string
  descricao?: string
}

interface Role {
  id: number
  name: string
}

const usuario = ref<Usuario>({})
const departamento = ref<Departamento[]>([])
const permissoes = ref<Permissao[]>([])
const roles = ref<Role[]>([])
const statusOpcoes = ref<string[]>([])

const carregarDepartamento = async () => {
  try {
    const { data } = await api.get('/api/departamento')
    departamento.value = data.departamento
  } catch (err) {
    console.error('Erro ao carregar departamento', err)
  }
}

const carregarPermissoes = async () => {
  try {
    const { data } = await api.get('/api/permissoes')
    permissoes.value = data.data
  } catch (err) {
    console.error('Erro ao carregar permissões', err)
  }
}

const carregarStatusOpcoes = async () => {
  try {
    const { data } = await api.get('/api/status-opcoes')
    statusOpcoes.value = data.status
  } catch (err) {
    console.error('Erro ao carregar status', err)
  }
}

const carregarRoles = async () => {
  try {
    const { data } = await api.get('/api/roles')
    roles.value = data.data
  } catch (err) {
    console.error('Erro ao carregar roles', err)
  }
}

const carregarUsuario = async () => {
  try {
    const encodedId = route.params.id as string
    const id = parseInt(atob(encodedId))

    const { data } = await api.get(`/api/usuarios/${id}`)
    /* console.log('📦 Dados recebidos do backend:', data) */

    usuario.value = {
      ...data,
      setor_id: data.setor_id ?? data.setor?.id,
    }

    if (Array.isArray(data.roles) && data.roles.length > 0) {
      const userRoleId = data.roles[0].id

      usuario.value.roles = roles.value.find((r) => r.id === userRoleId) || {
        id: userRoleId,
        name: data.roles[0].name,
      }

      usuario.value.tipo_acesso = 'padrao'
      return
    }

    if (Array.isArray(data.permissoes_diretas) && data.permissoes_diretas.length > 0) {
      const permissoesIds = data.permissoes_diretas.map((p: any) =>
        typeof p === 'object' ? p.id : p,
      )

      usuario.value.permissoes = permissoes.value.filter((p) => permissoesIds.includes(p.id))

      usuario.value.tipo_acesso = 'personalizado'
      return
    }

    usuario.value.tipo_acesso = 'padrao'
  } catch (err) {
    console.error('Erro ao carregar usuário', err)
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Erro ao carregar usuário.',
    })
    router.push('/usuarios')
  }
}

const salvar = async () => {
  try {
    const encodedId = route.params.id as string
    const id = parseInt(atob(encodedId))

    if (usuario.value.status === 'aprovado' && !usuario.value.email_verified_at) {
      Swal.fire({
        icon: 'warning',
        title: 'Atenção!',
        text: 'Não é possível aprovar um usuário sem e-mail verificado.',
      })
      return
    }

    const payload: any = {
      status: usuario.value.status,
      departamento_id: usuario.value.departamento_id,
      roles: [],
      permissoes: [],
    }

    if (usuario.value.tipo_acesso === 'padrao') {
      const role = usuario.value.roles as Role
      payload.roles = role ? [role.id] : []
    }

    if (usuario.value.tipo_acesso === 'personalizado') {
      payload.permissoes = (usuario.value.permissoes || []).map((p) => p.id)
    }

    await api.put(`/api/usuarios/${id}`, payload)

    Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Atualizado com sucesso!',
      timer: 2000,
      showConfirmButton: false,
    })
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Erro ao salvar usuário.',
    })
    console.error(err)
  }
}

onMounted(async () => {
  await carregarDepartamento()
  await carregarPermissoes()
  await carregarRoles()
  await carregarStatusOpcoes()
  await carregarUsuario()
})
</script>
<template>
  <div class="container py-5">
    <a
      @click="$router.back()"
      class="d-flex align-items-center gap-2 mb-5 text-decoration-none"
      :class="'btn btn-link text-secondary p-0'"
    >
      <i class="bi bi-arrow-left-circle" style="font-size: 1.2rem"></i>
      <span class="ms-2" style="font-size: 1rem">Voltar</span>
    </a>

    <div class="mb-5 text-center">
      <h4 class="fw-bold text-dark">
        <i class="bi bi-person-bounding-box text-success fs-4"></i>
        Detalhes do Usuário(a)
      </h4>
      <p class="text-muted mb-0">
        <span class="fw-semibold text-decoration-underline text-capitalize">{{
          usuario.name || 'Carregando...'
        }}</span>
        — Edite as informações e permissões deste usuário.
      </p>
    </div>

    <div class="row justify-content-center mt-5">
      <div class="col-12 col-md-10">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row gx-4 gy-3 border-bottom pb-4 mb-4">
            <div class="col-md-6">
              <small class="text-muted text-uppercase">Criado em</small>
              <p class="mb-0 fw-medium">
                {{ usuario.created_at ? new Date(usuario.created_at).toLocaleString() : '-' }}
              </p>
            </div>
            <div class="col-md-6">
              <small class="text-muted text-uppercase">Verificação de e-mail</small>
              <p class="mb-0 fw-medium">
                {{
                  usuario.email_verified_at
                    ? new Date(usuario.email_verified_at).toLocaleString()
                    : 'Não verificado'
                }}
              </p>
            </div>
            <div class="col-md-6">
              <small class="text-muted text-uppercase">Nome</small>
              <p class="mb-0 fw-medium">{{ usuario.name || '-' }}</p>
            </div>
            <div class="col-md-6">
              <small class="text-muted text-uppercase">Email</small>
              <p class="mb-0 fw-medium">{{ usuario.email || '-' }}</p>
            </div>
          </div>

          <div class="row gy-3 gx-4 mb-4">
            <div class="col-md-6">
              <label for="statusSelect" class="form-label text-secondary">
                <i class="bi bi-info-circle-fill me-2 text-primary"></i>Status
              </label>
              <select v-model="usuario.status" class="form-select shadow-sm" id="statusSelect">
                <option disabled value="">Selecione um status</option>
                <option
                  v-for="status in statusOpcoes"
                  :key="status"
                  :value="status"
                  :disabled="status === 'aprovado' && !usuario.email_verified_at"
                >
                  {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                </option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="setorSelect" class="form-label text-secondary">
                <i class="bi bi-building me-2 text-primary"></i>Departamento
              </label>
              <select
                v-model="usuario.departamento_id"
                class="form-select shadow-sm"
                id="setorSelect"
              >
                <option disabled value="">Selecione um departamento</option>
                <option
                  v-for="departamento in departamento"
                  :key="departamento.id"
                  :value="departamento.id"
                >
                  {{ departamento.nome }}
                </option>
              </select>
            </div>
          </div>

          <!-- Tipo de Acesso -->
          <div class="mb-4">
            <label class="form-label text-secondary d-block">
              <i class="bi bi-key me-2 text-primary"></i>Tipo de Acesso
            </label>

            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                id="acessoPadrao"
                value="padrao"
                v-model="usuario.tipo_acesso"
              />
              <label class="form-check-label" for="acessoPadrao">Padrão (Papéis)</label>
            </div>

            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                id="acessoPersonalizado"
                value="personalizado"
                v-model="usuario.tipo_acesso"
              />
              <label class="form-check-label" for="acessoPersonalizado"
                >Personalizado (Permissões)</label
              >
            </div>
          </div>

          <!-- Papéis / Permissões -->
          <div v-if="usuario.tipo_acesso === 'padrao'" class="mb-4">
            <label class="form-label text-secondary">Papéis</label>
            <multiselect
              v-model="usuario.roles"
              :options="roles"
              track-by="id"
              label="name"
              placeholder="Selecione um papel"
              :multiple="false"
            />
          </div>

          <div v-if="usuario.tipo_acesso === 'personalizado'" class="mb-4">
            <label class="form-label text-secondary">Permissões personalizadas</label>
            <multiselect
              v-model="usuario.permissoes"
              :options="permissoes"
              track-by="id"
              label="name"
              placeholder="Selecione permissões"
              :multiple="true"
            />
          </div>

          <div class="d-flex justify-content-end">
            <button
              @click="salvar"
              class="btn btn-success d-flex align-items-center gap-2 px-4 py-2 shadow-sm"
            >
              <i class="bi bi-check-circle-fill"></i> Salvar Alterações
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
