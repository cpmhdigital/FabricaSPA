import { defineStore } from 'pinia'
import api from '@/services/axios'
import { kanbanChannel } from '@/services/ably'
import Swal from 'sweetalert2'
import { useNotificationsStore } from '@/stores/notificationsStore'

export const useKanbanStore = defineStore('kanban', {
  state: () => ({
    pedidos: [] as any[],
    carregado: false,
  }),

  actions: {
    async carregarKanban() {
      const { data } = await api.get('/api/kanban')
      this.pedidos = data
      this.carregado = true
    },

    atualizarOuInserirPedido(p) {
      const index = this.pedidos.findIndex((x) => x.pedido_id === p.pedido_id)
      if (index >= 0) {
        this.pedidos[index] = { ...p }
      } else {
        this.pedidos.push({ ...p })
      }
    },

    ouvirAtualizacoesKanban() {
      if (!kanbanChannel) return

      kanbanChannel.subscribe('update', (msg) => {
        this.atualizarOuInserirPedido(msg.data)
      })

      kanbanChannel.subscribe('pedido-reprovado', (msg) => {
        if (!msg.data) return
        this.showSweetReprovado(msg.data)
      })
    },
  },
})
