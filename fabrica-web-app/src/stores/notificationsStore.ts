import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notificacoes', {
  state: () => ({
    lista: [] as any[]
  }),

  actions: {
    add(notif: any) {
      this.lista.push(notif)
    },
    remover(id: number) {
      this.lista = this.lista.filter(n => n.id !== id)
    },
    limparPedido(pedidoId: number) {
      this.lista = this.lista.filter(n => n.pedido_id !== pedidoId)
    }
  }
})
