<script setup lang="ts">
import BaseLayout from '@/components/BaseLayout.vue'

const adicionarSetor = () => {
  console.log('Novo setor adicionado!')
}
</script>

<template>
  <BaseLayout
    titulo="Gerenciamento dos Setores"
    textoBotao="Novo Setor"
    iconeBotao="bi bi-plus"
    espacamento="grande"
    @novo="adicionarSetor"
  >
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Nome</th>
            <th class="text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Anodização</td>
            <td class="text-center">
              <button type="button" class="btn btn-outline-primary btn-sm me-2" title="Editar">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button type="button" class="btn btn-outline-danger btn-sm" title="Excluir">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <!-- +++ Outras linhas aqui +++ -->
        </tbody>
      </table>
    </div>
  </BaseLayout>
</template>
