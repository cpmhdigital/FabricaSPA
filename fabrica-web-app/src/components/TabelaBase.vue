<script setup lang="ts" generic="T = any">
import { ref } from 'vue'
import { AgGridVue } from 'ag-grid-vue3'
import type { ColDef, GridApi, GridReadyEvent, DomLayoutType } from 'ag-grid-community'

const {
  rowData,
  columnDefs,
  components,
  pagination,
  paginationPageSize,
  domLayout
} = defineProps<{
  rowData: T[]
  columnDefs: ColDef[]
  components?: Record<string, unknown>
  pagination?: boolean
  paginationPageSize?: number
  domLayout?: DomLayoutType
}>()

const searchText = ref('')
const gridApi = ref<GridApi | null>(null)

const onGridReady = (params: GridReadyEvent) => {
  gridApi.value = params.api
}
</script>

<template>
  <div class="mb-3">
    <input
      type="text"
      class="form-control border-bottom custom-width"
      v-model="searchText"
      placeholder="Pesquisar"
    />
  </div>

  <div class="ag-theme-alpine w-100" style="width: 100%">
    <AgGridVue
      class="styled-grid"
      :rowData="rowData"
      :columnDefs="columnDefs"
      :components="components"
      :pagination="pagination ?? true"
      :paginationPageSize="paginationPageSize ?? 10"
      :domLayout="domLayout || 'autoHeight'"
      :quickFilterText="searchText"
      @grid-ready="onGridReady"
    />
  </div>
</template>

<style scoped>
.styled-grid .ag-header-cell-label {
  font-weight: 600;
  color: #333;
}

.styled-grid .ag-cell {
  font-size: 14px;
}

.custom-width {
  width: 15%;
}
</style>
