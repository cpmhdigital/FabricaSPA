<script setup lang="ts">
import PainelBase from '@/components/PainelBase.vue'
import BaseCards from '@/components/CardsBase.vue'
import PainelAcompanhamento from '@/components/PainelAcompanhamento.vue'
</script>

<template>
  <PainelBase title="Painel da Ordem de Serviço">
    <template #cards>
      <BaseCards
        :cards="[
          {
            label: 'Pausados',
            value: 8,
            sub: 'Em espera',
            icon: 'bi bi-pause-circle',
            iconColor: '#cd9a02',
            iconBg: '#fff3cd',
          },
          {
            label: 'Em andamento',
            value: 12,
            sub: 'Em execução',
            icon: 'bi bi-arrow-repeat',
            iconColor: '#132fef',
            iconBg: '#e7f1ff',
          },
          {
            label: 'Concluídos',
            value: 3,
            sub: 'Finalizados',
            icon: 'bi bi-clock-history',
            iconColor: '#42ad00',
            iconBg: '#eaf7ea',
          },
        ]"
      />
    </template>
    <template #acompanhamento>
      <PainelAcompanhamento />
    </template>
  </PainelBase>
</template>
