<template>
  <div class="mb-4">
    <label class="fw-semibold d-block mb-2">{{ titulo }}</label>
    <div v-for="item in itens" :key="item.id" class="form-check ms-2">
      <input
        class="form-check-input"
        type="checkbox"
        :id="`${titulo}-${item.id}`"
        :value="item.id"
        v-model="modeloInterno"
      />
      <label class="form-check-label" :for="`${titulo}-${item.id}`">
        {{ item[itemLabel] }}
      </label>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  titulo: string
  itens: any[]
  modelValue: number[]
  itemLabel: string
}>()

const emit = defineEmits(['update:modelValue'])
const modeloInterno = computed({
  get: () => props.modelValue,
  set: (valor) => emit('update:modelValue', valor),
})
</script>
