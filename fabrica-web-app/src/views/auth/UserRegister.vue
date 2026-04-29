<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import axios from 'axios'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const departamento_id = ref('')
const departamento = ref<{ id: number; nome: string }[]>([])

const error = ref('')
const isLoading = ref(false)

const carregarDepartamento = async () => {
  try {
    const { data } = await axios.get('/api/departamento')
    departamento.value = data.departamento ?? data
  } catch (err) {
    console.error('Erro ao carregar departamento:', err)
    error.value = 'Erro ao carregar o departamento.'
  }
}

onMounted(async () => {
  await carregarDepartamento()

  Object.assign(document.body.style, {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100vh',
    margin: '0',
  })
})
const register = async () => {
  error.value = ''
  isLoading.value = true

  try {
    await axios.post('/api/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
      departamento_id: departamento_id.value,
    })

    await Swal.fire({
      title: 'Cadastro realizado!',
      text: 'Verifique seu e-mail antes de fazer login.',
      icon: 'success',
      confirmButtonText: 'Ir para login',
    })

    router.push('/login')
  } catch (err: any) {
    console.error('Erro no register:', err)

    let message = 'Erro inesperado ao registrar.'

    if (err.response?.data?.errors) {
      const messages = Object.values(err.response.data.errors).flat()
      message = messages.join('\n')
    } else if (err.response?.data?.message) {
      message = err.response.data.message
    }

    await Swal.fire({
      title: 'Erro ao cadastrar',
      text: message,
      icon: 'error',
      confirmButtonText: 'OK',
    })

    error.value = message
  } finally {
    isLoading.value = false
  }
}
</script>
<template>
  <div class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-sm-12">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="text-center mb-4">
              <h3 class="fw-semibold text-primary">Cadastro de Novo Usuário</h3>
              <p class="text-muted small">Seu acesso será validado por um administrador.</p>
            </div>

            <form @submit.prevent="register">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                  <input
                    v-model="name"
                    type="text"
                    class="form-control form-control-lg text-capitalize"
                    placeholder="Digite seu nome completo"
                    required
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">E-mail <span class="text-danger">*</span></label>
                  <input
                    v-model="email"
                    type="email"
                    class="form-control form-control-lg"
                    placeholder="exemplo@dominio.com"
                    required
                    pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
                    title="Por favor, insira um e-mail válido. Ex: exemplo@dominio.com"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Senha <span class="text-danger">*</span></label>
                  <input
                    v-model="password"
                    type="password"
                    class="form-control form-control-lg"
                    placeholder="********"
                    required
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label"
                    >Confirmar Senha <span class="text-danger">*</span></label
                  >
                  <input
                    v-model="password_confirmation"
                    type="password"
                    class="form-control form-control-lg"
                    placeholder="********"
                    required
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Departamento <span class="text-danger">*</span></label>
                  <select v-model="departamento_id" class="form-select form-select-lg" required>
                    <option value="">Selecione um departamento</option>
                    <option
                      v-for="departamento in departamento"
                      :key="departamento.id"
                      :value="departamento.id"
                    >
                      {{ departamento.nome }}
                    </option>
                  </select>
                </div>

                <div class="col-12 mb-4">
                  <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>
                      Após o cadastro, você receberá um e-mail para confirmar sua conta. O acesso
                      será liberado somente após essa verificação e aprovação do administrador.
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="error" class="alert alert-danger text-center mt-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ error }}
              </div>

              <button :disabled="isLoading" class="btn btn-success btn-lg w-100 mt-4" type="submit">
                {{ isLoading ? 'Cadastrando...' : 'Criar Conta' }}
              </button>
            </form>

            <div class="text-center mt-3">
              <router-link to="/login" class="text-decoration-none text-secondary small">
                Já tenho cadastro. Fazer login
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.card {
  transition: box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

label {
  font-weight: 500;
}

input::placeholder,
select {
  font-size: 0.95rem;
  color: #999;
}

.btn-success {
  background-color: #007a5a;
  border-color: #007a5a;
}

.btn-success:hover {
  background-color: #066747;
  border-color: #066747;
}

.text-primary {
  color: #007a5a !important;
}
</style>
