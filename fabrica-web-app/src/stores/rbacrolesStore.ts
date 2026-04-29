import { defineStore } from 'pinia'
import axios from '@/services/axios.ts'

export const useRolesStore = defineStore('roles', {
  state: () => ({
    roles: [] as Array<any>,
  }),
  actions: {
    async loadRoles() {
      try {
        const response = await axios.get('/api/roles')
        this.roles = response.data.data
      } catch (error) {
        console.error('Erro ao carregar roles:', error)
      }
    },
    async createRole(name: string) {
      const { data } = await axios.post('/api/roles', { name })
      this.roles.push(data)
    },

    async updateRole(id: number, name: string) {
      await axios.put(`/api/roles/${id}`, { name })
    },

    async deleteRole(id: number) {
      await axios.delete(`/api/roles/${id}`)
      this.roles = this.roles.filter((r) => r.id !== id)
    },
  },
})
