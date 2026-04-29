<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProducao } from '@/composables/useProducao'

import PedidoHeader from '@/components/pedido-producao/PedidoHeader.vue'
import ItemCard from '@/components/pedido-producao/ItemCard.vue'
import ModalChecklist from '@/components/modals/ModalChecklist.vue'
import ModalPausa from '@/components/modals/ModalPausa.vue'
import ProgressoProducao from '@/components/pedido-producao/ProgressoProducao.vue'

const route = useRoute()

const modalChecklistRef = ref<InstanceType<typeof ModalChecklist> | null>(null)
const modalPausaRef = ref<InstanceType<typeof ModalPausa> | null>(null)

const {
  pedido,
  itens,
  carregarPedido,
  iniciar,
  parar,
  finalizar,
  reprovar,
  loading,
} = useProducao({
  onAbrirChecklist: (item, tipo) => modalChecklistRef.value?.abrir(item, tipo),
  onAbrirPausa: (item) => modalPausaRef.value?.abrir(item),
})

onMounted(() => {
  const id = history.state?.id || route.params.id
  if (id) carregarPedido(id)
})
</script>

<template>
  <div v-if="!loading" class="container py-4">
    <PedidoHeader :pedido="pedido" />

    <div class="row g-4 mt-3">
      <!-- ✅ UM ÚNICO v-for (sem duplicação) -->
      <div v-for="it in itens" :key="it.pedido_item_unidade_id" class="col-lg-6 col-xl-4">
        <ItemCard
          :item="it"
          :pedido="pedido"
          @iniciar="({ pedido_item_unidade_id }) => iniciar(pedido_item_unidade_id)"
          @parar="({ pedido_item_unidade_id }) => parar(pedido_item_unidade_id)"
          @finalizar="({ pedido_item_unidade_id }) => finalizar(pedido_item_unidade_id)"
          @reprovar="({ pedido_item_unidade_id }) => reprovar(pedido_item_unidade_id)"
        />
      </div>
    </div>

    <ModalChecklist ref="modalChecklistRef" />
    <ModalPausa ref="modalPausaRef" />
    <ProgressoProducao :pedido-id="pedido.id" />
  </div>

  <div v-else class="text-center py-5">
    <div class="spinner-border text-primary"></div>
    <p class="mt-3 text-muted">Carregando dados...</p>
  </div>
</template>
