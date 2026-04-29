import { defineStore } from 'pinia'
import axios from '@/services/axios.ts'

export const useRolePermissionsStore = defineStore('rolePermissions', {
  state: () => ({
    rolePermissions: [] as Array<{ roleId: number; permissionIds: number[] }>,
  }),
  actions: {
    async loadRolePermissions() {
      try {
        const response = await axios.get('/api/roles-permissions')

        const rolesWithPerms = response.data

        this.rolePermissions = rolesWithPerms.map((role: any) => ({
          roleId: role.id,
          permissionIds: role.permissions ? role.permissions.map((p: any) => p.id) : [],
        }))
      } catch (error) {
        console.error('Erro ao carregar permissões dos papéis:', error)
      }
    },

    
    async assignPermission(roleId: number, permissionId: number) {
      await axios.post(`/api/roles/${roleId}/permissions`, {
        role_id: roleId,
        permission_id: permissionId,
      })
      this.loadRolePermissions()
    },

    async removePermission(roleId: number, permissionId: number) {
      await axios.delete(`/api/roles/${roleId}/permissions/${permissionId}`)
      this.loadRolePermissions()
    },
  },
})
