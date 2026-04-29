<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/axios.ts'
import { entrarNoAbly } from '@/plugins/ablyPresence'
import { useAuthStore } from '@/stores/session'

const router = useRouter()
const auth = useAuthStore()

const email = ref(localStorage.getItem('saved_email') || '')
const password = ref('')
const error = ref('')
const showPassword = ref(false)
const showBlockedModal = ref(false)
const rememberEmail = ref(!!email.value)
const attemptCount = ref(0)
const isLoading = ref(false)

const login = async () => {
  error.value = ''
  isLoading.value = true

  try {
    const { data } = await api.post('/api/auth/login', {
      email: email.value,
      password: password.value,
    })

    if (data.message === 'bloqueado') {
      showBlockedModal.value = true
      return
    }

    const user = data.user
    if (!user) throw new Error('Usuário não retornou da API')

    if (!user.email_verified_at) {
      error.value = 'Verifique seu e-mail antes de fazer login.'
      return
    }

    // 1) salva token + user (mínimo)
    auth.setSession({
      user: {
        id: user.id,
        nome: user.nome ?? user.name,
        email: user.email,
        departamento_id: user.departamento_id,
        departamento_nome: user.departamento_nome,
      },
      token: data.token,
    })

    // 2) carrega roles/perms do backend
    await auth.fetchMe()

    // 3) Ably
    entrarNoAbly({
      id: user.id,
      nome: user.nome ?? user.name,
      email: user.email,
      departamento_id: user.departamento_id,
      departamento_nome: user.departamento_nome,
    })

    // 4) lembrar email
    if (rememberEmail.value) localStorage.setItem('saved_email', email.value)
    else localStorage.removeItem('saved_email')

    router.push(data.redirect_to || '/painel-atividade')
    
  } catch (err: any) {
    attemptCount.value++

    if (err.response?.status === 429) {
      error.value = 'Muitas tentativas. Aguarde 5 minutos.'
      showBlockedModal.value = true
      return
    }

    error.value = err.response?.data?.message || err.message || 'Erro ao fazer login. Tente novamente.'
  } finally {
    isLoading.value = false
  }
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>


<template>
  <div class="login-page d-flex justify-content-center align-items-center bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-sm col col-lg-5 top-15">
          <div class="row justify-content-center mb-6 mt-auto p-0">
            <img alt="fabrica logo" class="logo" src="@/assets/logofab.svg" width="125" height="125" />
          </div>

          <div class="card-body mt-5">
            <div class="form-group">
              <input v-model="email" class="form-control py-3 mb-3" type="text" placeholder="E-mail/Usuário" />
            </div>

            <div class="input-group mb-3">
              <input v-model="password" :type="showPassword ? 'text' : 'password'" class="form-control py-3"
                placeholder="Senha" @keyup.enter="login" />
              <button @click="togglePassword" class="input-group-text" id="basic-addon2">
                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="rememberEmail" v-model="rememberEmail" />
              <label class="form-check-label" for="rememberEmail"> Lembrar meu e-mail </label>
            </div>

            <div class="d-flex justify-content-between">
              <router-link to="/" class="btn btn-outline-dark">Início</router-link>
              <button class="btn btn-success" @click="login" :disabled="isLoading">
                {{ isLoading ? 'Entrando...' : 'Entrar' }}
              </button>
            </div>

            <div v-if="error" class="alert alert-danger text-center mt-4">
              {{ error }}
            </div>

            <div class="d-flex justify-content-center mt-4">
              <router-link to="/user-register" class="btn btn-outline-secondary">
                Não tem uma conta? Cadastre-se
              </router-link>
            </div>

            <div class="text-center mt-3">
              <router-link to="/reset-password" class="text-fab">Esqueceu sua senha? Recuperar</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Usuário Bloqueado -->
    <div v-if="showBlockedModal" class="modal-backdrop">
      <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="blockedModalTitle">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 id="blockedModalTitle" class="modal-title">Usuário Bloqueado</h5>
            </div>
            <div class="modal-body">
              <p>
                Detectamos algumas atividades suspeitas e sua conta foi bloqueada por 5 minutos.
                Caso ache que tenha havido algum engano, entre em contato conosco.
              </p>
              <button class="btn btn-secondary" @click="showBlockedModal = false">Fechar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  height: 100vh;
  width: 100vw;
}

.text-fab {
  color: #007a5a !important;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1050;
}

.btn-close {
  border: none;
  background: transparent;
  font-size: 1.5rem;
  cursor: pointer;
  color: #000;
}
</style>
