<script setup lang="ts">
import { ref } from 'vue'

const hover = ref(false)

const infoCabecalho = {
  num_pedido: '12345',
  produto: 'Produto Exemplo',
  doutor: 'Dr. João',
  paciente: 'Maria',
  lote: 'L123',
  data_aceite: '2025-07-10',
  data_confirmacao: '2025-07-11',
  conferido_por_nome: 'José',
}

function formatarData(dataStr: string) {
  if (!dataStr) return '—'
  const data = new Date(dataStr)
  return data.toLocaleDateString('pt-BR')
}
</script>

<template>
  <div class="container-fluid">
    <div class="row py-4 d-flex justify-content-center">
      <div class="col-sm-10">
        <div class="row mb-4">
          <div class="col-12">
            <h5 class="text-muted text-uppercase font-monospace fw-bold">
              registro das etapas de produção
            </h5>
            <small class="text-muted text-capitalize">descontinuado, pois será como o visualizar</small>
          </div>
        </div>
        <hr />
      </div>

      <div
        class="card shadow border-0 mb-5 bg-white text-dark rounded"
        @mouseover="hover = true"
        @mouseout="hover = false"
        :class="{ 'shadow-lg': hover }"
      >
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="badge bg-warning text-white fs-6"
              >Pedido #{{ infoCabecalho.num_pedido }}</span
            >
          </div>

          <div class="row gy-4 small text-secondary">
            <div class="col-md-2">
              <div class="fw-semibold text-dark">Produto</div>
              {{ infoCabecalho.produto }}
            </div>
            <div class="col-md-2">
              <div class="fw-semibold text-dark">Doutor</div>
              {{ infoCabecalho.doutor }}
            </div>
            <div class="col-md-2">
              <div class="fw-semibold text-dark">Paciente</div>
              {{ infoCabecalho.paciente }}
            </div>
            <div class="col-md-2">
              <div class="fw-semibold text-dark">Lote</div>
              {{ infoCabecalho.lote }}
            </div>
            <div class="col-md-2">
              <div class="fw-semibold text-dark">Dt Aceite</div>
              {{ formatarData(infoCabecalho.data_aceite) }}
            </div>
            <div class="col-md-2">
              <div class="fw-semibold text-dark mt-2">Resp. Abertura</div>
              {{ infoCabecalho.conferido_por_nome || '—' }}
              <hr />
              <div class="fw-semibold text-dark">Data</div>
              {{ formatarData(infoCabecalho.data_confirmacao) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
