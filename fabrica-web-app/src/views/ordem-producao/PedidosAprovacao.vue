<script setup lang="ts">
const pedidos = [
  { id: '20250722-01', produto: 'Sonda XYZ', status: 'Pendente' },
  { id: '20250722-02', produto: 'Kit Esterilização', status: 'Pendente' }
]
</script>

<template>
  <div class="pedidos-aprovacao">
    <h2>Pedidos para Aprovação do Gestor</h2>
    <table>
      <thead>
        <tr>
          <th>Ordem</th>
          <th>Produto</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="pedido in pedidos" :key="pedido.id">
          <td>{{ pedido.id }}</td>
          <td>{{ pedido.produto }}</td>
          <td>{{ pedido.status }}</td>
          <td><button>Aprovar</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.pedidos-aprovacao {
  margin-top: 30px;
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  color: #333;
}
</style>
