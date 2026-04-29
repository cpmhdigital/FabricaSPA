<script setup lang="ts">
import { defineProps, defineEmits, computed } from 'vue'

const props = defineProps<{
  title: string
  show: boolean
  size?: 'sm' | 'md' | 'lg'
}>()

const emit = defineEmits<{
  (e: 'save'): void
  (e: 'cancel'): void
  (e: 'update:show', value: boolean): void
}>()

const modalSize = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'modal-sm'
    case 'lg':
      return 'modal-lg'
    default:
      return 'modal-md'
  }
})

const closeModal = () => {
  emit('cancel')
  emit('update:show', false) // Isso vai fechar o modal
}
</script>

<template>
  <div
    v-if="props.show"
    class="modal fade show d-block"
    tabindex="-1"
    style="background-color: rgba(0, 0, 0, 0.5)"
  >
    <div :class="['modal-dialog', modalSize]">
      <div class="modal-content">
        <div class="d-flex justify-content-end">
          <button
            type="button"
            class="btn btn-outline-danger rounded-circle d-flex align-items-center justify-content-center m-3"
            @click="closeModal"
            style="width: 35px; height: 35px"
          >
            <span aria-hidden="true"><i class="bi bi-x-lg"></i></span>
          </button>
        </div>

        <div class="modal-body">
          <slot></slot>
        </div>
        <div class="modal-footer">
          <slot name="footer">
            <button type="button" class="btn btn-success" @click="$emit('save')">Salvar</button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>
