import { defineStore } from 'pinia'
import api from '@/services/axios'

export const usePermissionsStore = defineStore('permissions', {
  state: () => ({
    permissions: [] as Array<any>,
  }),
  actions: {
    async loadPermissions() {
      try {
        const response = await api.get('/api/permissoes')
        // ajuste aqui conforme seu backend retorna (data ou data.data)
        this.permissions = response.data.data ?? response.data
      } catch (error) {
        console.error('Erro ao carregar permissões:', error)
      }
    },

    async createPermission(name: string) {
      const { data } = await api.post('/api/permissoes', { name })
      this.permissions.push(data?.data ?? data)
    },

    async updatePermission(id: number, name: string) {
      await api.put(`/api/permissoes/${id}`, { name })
      const perm = this.permissions.find((p) => p.id === id)
      if (perm) perm.name = name
    },

    async deletePermission(id: number) {
      try {
        await api.delete(`/api/permissoes/${id}`)
        this.permissions = this.permissions.filter((p) => p.id !== id)
      } catch (error) {
        console.error('Erro ao excluir a permissão:', error)
      }
    },
  },
})
