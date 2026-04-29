<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/services/axios.ts' // ajuste o caminho do seu axios configurado
import Swal from 'sweetalert2'

const router = useRouter()
const email = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  if (!email.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Ops...',
      text: 'Digite seu e-mail.',
    })
    return
  }

  loading.value = true
  try {
    await axios.post('/api/auth/forgot-password', { email: email.value })
    Swal.fire({
      icon: 'success',
      title: 'E-mail enviado',
      text: 'Se este e-mail estiver cadastrado, enviaremos as instruções de recuperação.',
      confirmButtonText: 'Ok'
    }).then(() => {
      router.push('/')
    })
  } catch (error: any) {
    console.error(error.response?.data)

    if (error.response?.status === 422) {
      // mostra mensagens de validação
      const errors = error.response.data.errors
      if (errors && errors.email) {
        Swal.fire({
          icon: 'error',
          title: 'Erro de validação',
          text: errors.email[0],
        })
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Erro',
          text: error.response.data.message || 'Erro de validação.',
        })
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Erro ao solicitar recuperação de senha.',
      })
    }
  } finally {
    loading.value = false
  }
}
</script>


<template>
  <div class="reset-page d-flex justify-content-center align-items-center bg-light">
    <div class="container">
      <div class="row justify-content-center text-center mb-4">
        <h2 class="fw-bold text-dark">Recuperação de Senha</h2>
      </div>

      <div class="row justify-content-between align-items-center">
        <div class="col-sm-6 py-2" id="text-helper">
          <h4 class="h-white font-weight-lighter my-2" style="color: #000">
            Não se preocupe que iremos te ajudar!<br />Digite ao lado o e-mail que você cadastrou
            <br />
            no nosso sistema para recuperar sua senha.
          </h4>
          <div class="d-flex justify-content-center my-3 mr-5">
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
        </div>

        <div class="col-md-6">
          <form @submit.prevent="handleSubmit">
            <div class="form-group mb-3">
              <input
                v-model="email"
                type="email"
                class="form-control py-3"
                placeholder="E-mail"
                required
              />
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <router-link to="/" class="small text-fab">Retornar ao Login</router-link>
              <button class="btn btn-success" type="submit" :disabled="loading">
                {{ loading ? 'Enviando...' : 'Recuperar Senha' }}
              </button>
            </div>
          </form>

          <div class="text-center mt-3">
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
  height: 100vh;
  width: 100vw;
}
</style>
