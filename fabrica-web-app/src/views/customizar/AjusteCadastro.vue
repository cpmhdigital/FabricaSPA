<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUsuarios } from '@/composables/useUsuarios'
import api from '@/services/axios.ts'

const { perfil, carregarPerfil } = useUsuarios()

const senhaAtual = ref('')
const senha = ref('')
const confirmeSenha = ref('')

const mostrarSenhaAtual = ref(false)
const mostrarNovaSenha = ref(false)
const mostrarConfirmeSenha = ref(false)

onMounted(async () => {
  await carregarPerfil()
})

const alterarSenha = async () => {
  if (senha.value.length < 6) {
    alert('A nova senha deve ter no mínimo 6 caracteres.')
    return
  }

  if (senha.value !== confirmeSenha.value) {
    alert('As senhas não conferem!')
    return
  }

  try {
    const token = localStorage.getItem('token')

    await api.post(
      '/api/perfil/alterar-senha',
      {
        senha_atual: senhaAtual.value,
        nova_senha: senha.value,
        nova_senha_confirmation: confirmeSenha.value,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    )

    alert('Senha alterada com sucesso!')
    senha.value = ''
    confirmeSenha.value = ''
    senhaAtual.value = ''
  } catch (error: any) {
    console.error('Erro ao alterar senha:', error.response?.data)

    const mensagem = error.response?.data?.message
    const erros = error.response?.data?.errors

    let textoErro = mensagem || 'Erro ao alterar a senha.'

    if (erros) {
      textoErro += '\n\n' + Object.values(erros).flat().join('\n')
    }

    alert(textoErro)
  }
}
</script>

<template>
  <div class="container-fluid min-vh-100 py-4">
    <h1 class="lead text-secondary fs-4">Meu Perfil</h1>

    <div class="text-center" v-if="perfil">
      <h4 class="fw-bold text-dark mt-5">
        <i class="bi bi-person text-success fs-4"></i>
        {{ perfil.name || '-' }}
      </h4>
      <p class="text-muted">{{ perfil.email || '-' }}</p>
    </div>

    <div class="row justify-content-center mt-4">
      <div class="col-12 col-md-10">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="d-flex justify-content-center" v-if="perfil">
            <div class="col-md-6">
              <h5 class="text-muted mb-3 text-center">Redefinir Senha</h5>

              <form @submit.prevent="alterarSenha">
                <div class="form-group mb-3 position-relative">
                  <label class="form-label text-black">Senha Atual</label>
                  <input
                    :type="mostrarSenhaAtual ? 'text' : 'password'"
                    class="form-control"
                    v-model="senhaAtual"
                    required
                  />
                  <i
                    class="bi"
                    :class="mostrarSenhaAtual ? 'bi-eye-slash' : 'bi-eye'"
                    @click="mostrarSenhaAtual = !mostrarSenhaAtual"
                    style="position: absolute; right: 10px; top: 40px; cursor: pointer"
                  ></i>
                </div>

                <div class="form-group mb-3 position-relative">
                  <label class="form-label text-black">Nova Senha</label>
                  <input
                    :type="mostrarNovaSenha ? 'text' : 'password'"
                    class="form-control"
                    v-model="senha"
                    required
                  />
                  <i
                    class="bi"
                    :class="mostrarNovaSenha ? 'bi-eye-slash' : 'bi-eye'"
                    @click="mostrarNovaSenha = !mostrarNovaSenha"
                    style="position: absolute; right: 10px; top: 40px; cursor: pointer"
                  ></i>
                </div>

                <div class="form-group mb-3 position-relative">
                  <label class="form-label text-black">Confirme Senha</label>
                  <input
                    :type="mostrarConfirmeSenha ? 'text' : 'password'"
                    class="form-control"
                    v-model="confirmeSenha"
                    required
                  />
                  <i
                    class="bi"
                    :class="mostrarConfirmeSenha ? 'bi-eye-slash' : 'bi-eye'"
                    @click="mostrarConfirmeSenha = !mostrarConfirmeSenha"
                    style="position: absolute; right: 10px; top: 40px; cursor: pointer"
                  ></i>
                </div>

                <div class="d-flex justify-content-center mt-3">
                  <button type="submit" class="btn btn-success">Alterar Senha</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
