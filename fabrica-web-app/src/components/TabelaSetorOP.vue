<script setup lang="ts">
import { ref } from 'vue'
import { AgGridVue } from 'ag-grid-vue3'
import 'ag-grid-community/styles/ag-theme-alpine.css'

const emit = defineEmits(['submit'])

const rowData = ref([
  { id: 12885, produto: 'PC-353-2', lote: '1256', chegada: '2025-09-05', taxa: 'não' },
  { id: 12878, produto: 'PC-353-2', lote: '1265', chegada: '2025-09-07', taxa: 'não' },
  { id: 25596, produto: 'PC-353-2', lote: '1265', chegada: '2025-09-10', taxa: 'não' },
  { id: 25600, produto: 'PC-354-1', lote: '1265', chegada: '2025-09-12', taxa: 'não' },
  { id: 25601, produto: 'PC-355-1', lote: '1265', chegada: '2025-09-14', taxa: 'não' },
  { id: 25602, produto: 'PC-356-1', lote: '1265', chegada: '2025-09-15', taxa: 'não' },
])

const columnDefs = ref([
  { headerName: 'Pedido', field: 'id', sortable: true, filter: true },
  { headerName: 'Produto', field: 'produto', sortable: true, filter: true },
  { headerName: 'Lote', field: 'lote', sortable: true, filter: true },
  { headerName: 'Chegada', field: 'chegada', sortable: true, filter: true },
  { headerName: 'Taxa', field: 'taxa', sortable: true, filter: true },
])

const defaultColDef = ref({
  resizable: true,
  flex: 1,
  minWidth: 120,
  sortable: true,
  filter: true,
})

const searchText = ref('')
</script>

<template>
  <div>
    <input
      type="text"
      placeholder="Pesquisar..."
      v-model="searchText"
      class="mb-2 p-1 border rounded"
    />

    <AgGridVue
      class="ag-theme-alpine"
      style="width: 100%; height: 405px"
      :rowData="rowData"
      :columnDefs="columnDefs"
      :defaultColDef="defaultColDef"
      :quickFilterText="searchText"
      :pagination="true"
      :paginationPageSize="5"
    />
  </div>
</template>
