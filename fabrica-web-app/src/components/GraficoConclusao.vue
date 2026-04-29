<script setup lang="ts">
import { ref } from 'vue'
import { use } from 'echarts/core'
import VChart from 'vue-echarts'
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
} from 'echarts/components'
import { BarChart } from 'echarts/charts'
import { CanvasRenderer } from 'echarts/renderers'

use([TitleComponent, TooltipComponent, LegendComponent, GridComponent, BarChart, CanvasRenderer])

interface MesData {
  mes: string
  noPrazo: number
  atrasado: number
}

const dadosMensais: MesData[] = [
  { mes: 'Ago/2025', noPrazo: 20, atrasado: 10 },
  { mes: 'Set/2025', noPrazo: 35, atrasado: 5 },
  { mes: 'Out/2025', noPrazo: 10, atrasado: 15 },
  { mes: 'Nov/2025', noPrazo: 9, atrasado: 10 },
  { mes: 'Dez/2025', noPrazo: 55, atrasado: 2 },
  { mes: 'jan/2026', noPrazo: 15, atrasado: 15 },
]

// Configuração do gráfico
const option = ref({
  tooltip: {
    trigger: 'axis',
    axisPointer: { type: 'shadow' },
  },
  legend: {
    top: '5%',
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true,
  },
  xAxis: {
    type: 'category',
    data: dadosMensais.map((d) => d.mes),
  },
  yAxis: {
    type: 'value',
  },
  series: [
    {
      name: 'No Prazo',
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#CAE0BC' },
      data: dadosMensais.map((d) => d.noPrazo),
    },
    {
      name: 'Atrasado',
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#FFD66B' },
      data: dadosMensais.map((d) => d.atrasado),
    },
  ],
})
</script>

<template>
  <div class="container">
    <v-chart class="chart" :option="option" autoresize />
    <div class="text-center mt-3">
      <button class="btn btn-primary btn-sm">Ver Detalhes</button>
    </div>
  </div>
</template>

<style scoped>
.chart {
  height: 400px;
  width: 100%;
}
</style>
