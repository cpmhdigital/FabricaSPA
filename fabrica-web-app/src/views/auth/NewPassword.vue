
<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import api from '@/services/axios.ts'

const route = useRoute()
const router = useRouter()
const token = (route.query.token as string) || ''
const emailFromQuery = (route.query.email as string) || ''

const form = ref({
  email: '',
  password: '',
  password_confirmation: '',
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

onMounted(() => {
  if (emailFromQuery) {
    form.value.email = emailFromQuery
  }
})

const submit = async () => {
  try {
    const response = await api.post('/api/auth/reset-password', {
      token,
      ...form.value,
    })

    await Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: response.data.message,
      confirmButtonColor: '#007A5A',
    })

    router.push('/new-password')
  }catch (err) {
    let message = 'Erro ao redefinir a senha'

    if (axios.isAxiosError(err)) {
      message = err.response?.data?.message || message
    }

    await Swal.fire({
      icon: 'error',
      title: 'Erro',
      text: message,
      confirmButtonColor: '#d33',
    })
  }
}
</script>
<template>
  <div class="reset-page d-flex justify-content-center align-items-center bg-light">
    <div class="container">
      <div class="row justify-content-center text-center mb-5">
        <h3 class="fw-semibold text-success mb-1">Recuperação de Senha</h3>
      </div>

      <div class="row align-items-center">
        <div class="col-md-6 d-none d-md-flex justify-content-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="108.908"
            height="116.399"
            viewBox="0 0 108.908 116.399"
          >
            <g id="Grupo_829" data-name="Grupo 829" transform="translate(762.59 -2343.84)">
              <g id="Grupo_827" data-name="Grupo 827" transform="translate(-762.59 2343.84)">
                <path
                  id="Caminho_2152"
                  data-name="Caminho 2152"
                  d="M-736.831,2394.255h60.452a2.931,2.931,0,0,0,2.931-2.931v-2.345a2.931,2.931,0,0,0-2.931-2.931h-6.829v-14.475c0-14.937-11.819-27.448-26.755-27.729a27.305,27.305,0,0,0-27.8,27.272v14.954a25.789,25.789,0,0,0-24.827,25.736v19.585a25.759,25.759,0,0,0,25.759,25.759H-689a2.931,2.931,0,0,0,2.931-2.931v-2.345a2.931,2.931,0,0,0-2.931-2.931h-47.83a17.552,17.552,0,0,1-17.552-17.552v-19.585A17.552,17.552,0,0,1-736.831,2394.255Zm7.275-23.138a19.09,19.09,0,0,1,18.736-19.067c10.67-.184,19.4,8.774,19.4,19.446v14.552h-38.14Z"
                  transform="translate(762.59 -2343.84)"
                  fill="#007A5A"
                />
              </g>
              <g id="Grupo_828" data-name="Grupo 828" transform="translate(-688.899 2401.996)">
                <path
                  id="Caminho_2153"
                  data-name="Caminho 2153"
                  d="M-609.667,2520.31a5.691,5.691,0,0,0-4.347,1.961,6.594,6.594,0,0,0-1.635,4.475,6.785,6.785,0,0,0,1.579,4.411,5.6,5.6,0,0,0,4.4,2.1,5.7,5.7,0,0,0,4.411-2.071,6.71,6.71,0,0,0,1.635-4.44,6.6,6.6,0,0,0-1.636-4.477A5.761,5.761,0,0,0-609.667,2520.31Z"
                  transform="translate(628.099 -2475.015)"
                  fill="#007A5A"
                />
                <path
                  id="Caminho_2154"
                  data-name="Caminho 2154"
                  d="M-606.593,2447.194c-3.161-2.753-7.368-4.15-12.5-4.15-6.9,0-12.463,2.289-16.527,6.8a4.886,4.886,0,0,0-1.253,3.588,4.895,4.895,0,0,0,1.676,3.418,4.9,4.9,0,0,0,6.8-.314,11.7,11.7,0,0,1,8.983-3.524,8.331,8.331,0,0,1,5.821,1.814,5.81,5.81,0,0,1,1.9,4.635,7.11,7.11,0,0,1-.922,3.786,28.213,28.213,0,0,1-3.72,4.492,48.082,48.082,0,0,0-3.542,3.949,17.486,17.486,0,0,0-2.548,4.648v0a15.806,15.806,0,0,0-.824,3.252,4.967,4.967,0,0,0,1.152,3.974,4.908,4.908,0,0,0,3.717,1.7h.187a4.941,4.941,0,0,0,4.871-4.249,7.112,7.112,0,0,1,.876-2.666,27.847,27.847,0,0,1,3.758-4.705,50.391,50.391,0,0,0,3.495-4.016,17.833,17.833,0,0,0,2.5-4.674,18.174,18.174,0,0,0,1.033-6.315A14.5,14.5,0,0,0-606.593,2447.194Z"
                  transform="translate(636.887 -2443.044)"
                  fill="#007A5A"
                />
              </g>
              <path
                id="Caminho_2155"
                data-name="Caminho 2155"
                d="M-674.138,2464.3a9.931,9.931,0,0,0-9.931-9.931A9.932,9.932,0,0,0-694,2464.3a9.928,9.928,0,0,0,5.241,8.754v5.315a4.69,4.69,0,0,0,4.69,4.69,4.69,4.69,0,0,0,4.69-4.69v-5.316A9.926,9.926,0,0,0-674.138,2464.3Z"
                transform="translate(-28.381 -45.734)"
                fill="#007A5A"
              />
            </g>
          </svg>
        </div>

        <div class="col-md-6">
          <form @submit.prevent="submit" class="shadow p-4 bg-white rounded">
            <div class="mb-3">
              <input
                v-model="form.email"
                type="email"
                placeholder="Email"
                disabled
                class="form-control"
              />
            </div>
            <div class="mb-3 position-relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Nova senha"
                required
                class="form-control"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2"
                tabindex="-1"
                :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
              >
                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <div class="mb-3 position-relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="Confirmar senha"
                required
                class="form-control"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2"
                tabindex="-1"
                :aria-label="showConfirmPassword ? 'Ocultar senha' : 'Mostrar senha'"
              >
                <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold">Salvar nova senha</button>
          </form>

          <div class="d-flex justify-content-between mt-3 gap-3">
            <router-link to="/login" class="text-decoration-none text-success small">
              Ir para minha conta
            </router-link>

            <router-link to="/user-register" class="text-fab small">
              Não tem conta? Cadastre-se já
            </router-link>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reset-page {
  min-height: 100vh;
  width: 100vw;
  padding: 2rem 0;
  background-color: #f8f9fa;
}

form input.form-control {
  font-size: 1rem;
  padding: 0.75rem 1rem;
}

form button[type='submit'] {
  font-size: 1.1rem;
  padding: 0.75rem;
}

.text-fab {
  color: #007a5a;
  text-decoration: none;
}

.text-fab:hover {
  text-decoration: underline;
}
</style>
