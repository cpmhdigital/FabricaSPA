<template>
  <div class="row g-3 cards-row">
    <div
      class="col-12 col-sm-6 col-md-4 col-lg-3"
      v-for="(item, index) in cards"
      :key="index"
    >
      <div class="card-item p-3 h-100 d-flex flex-row align-items-center gap-3">
        <!-- Ícone -->
        <div
          class="d-flex align-items-center justify-content-center rounded-circle"
          :style="{
            backgroundColor: item.iconBg || '#f8f9fa',
            width: '50px',
            height: '50px'
          }"
        >
          <i :class="item.icon" :style="{ color: item.iconColor, fontSize: '22px' }"></i>
        </div>

        <!-- Texto -->
        <div class="text-start card-text">
          <div class="fw-bold fs-5 lh-1">{{ item.value }}</div>
          <div class="text-muted small card-line">{{ item.label }}</div>
          <div v-if="item.sub" class="text-secondary small fst-italic card-line">
            {{ item.sub }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Card {
  label: string
  value: string | number
  sub?: string
  icon?: string
  iconColor?: string
  iconBg?: string
}

defineProps<{
  cards: Card[]
}>()
</script>

<style scoped>
/* Evita o “estouro” do grid em containers flex */
.cards-row {
  min-width: 0;
}

/* Mantém o card alinhado e sem overflow */
.card-item {
  min-width: 0;
}

/* Texto pode encolher sem estourar */
.card-text {
  min-width: 0;
}

/* Desktop: deixa com ellipsis para não quebrar layout */
.card-line {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Mobile: permite quebrar linha e ocupar largura */
@media (max-width: 576px) {
  .card-line {
    max-width: 100%;
    white-space: normal;
  }
}
</style>
