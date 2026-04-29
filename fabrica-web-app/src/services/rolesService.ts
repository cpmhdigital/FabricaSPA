import api from '@/services/axios.ts'

export default {
  getAll() {
    return api.get('/roles')
  },
  create(data: { name: string }) {
    return api.post('/roles', data)
  },
  update(id: number, data: { name: string }) {
    return api.put(`/roles/${id}`, data)
  },
  delete(id: number) {
    return api.delete(`/roles/${id}`)
  },
  getPermissions(roleId: number) {
    return api.get(`/roles/${roleId}/permissions`)
  },
  updatePermissions(roleId: number, permissions: string[]) {
    return api.post(`/roles/${roleId}/permissions`, { permissions })
  }
}
