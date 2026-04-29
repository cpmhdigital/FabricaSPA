<template>
  <div class="modal fade" id="modalDetalhesItem" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            Detalhes do item: {{ item?.modelo }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <HistoricoRoteiro :roteiro="item?.roteiro ?? []" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import HistoricoRoteiro from './HistoricoRoteiro.vue'
defineProps<{ item: any | null }>()
</script>
