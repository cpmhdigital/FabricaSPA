import { defineStore } from 'pinia'
import api from '@/services/axios'

type User = {
  id: number
  nome: string
  email: string
  departamento_id?: number
  departamento_nome?: string
}

type StoredAuth = {
  user: User | null
  roles: string[]
  permissions: string[]
  token: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    roles: [] as string[],
    permissions: [] as string[],
    token: '' as string,
    loaded: false as boolean,
  }),

  getters: {
    isLogged: (s) => !!s.token,
    can: (s) => (perm: string) => s.permissions.includes(perm),
    hasRole: (s) => (role: string) => s.roles.includes(role),
  },

  actions: {
    setSession(payload: { user: User; token: string; roles?: string[]; permissions?: string[] }) {
      this.user = payload.user
      this.token = payload.token
      this.roles = payload.roles ?? []
      this.permissions = payload.permissions ?? []

      const toStore: StoredAuth = {
        user: this.user,
        roles: this.roles,
        permissions: this.permissions,
        token: this.token,
      }

      localStorage.setItem('auth', JSON.stringify(toStore))
      this.loaded = true
    },

    loadFromStorage() {
      if (this.loaded) return

      try {
        const raw = localStorage.getItem('auth')
        if (!raw) {
          this.loaded = true
          return
        }

        const parsed = JSON.parse(raw) as StoredAuth
        this.user = parsed.user
        this.roles = parsed.roles ?? []
        this.permissions = parsed.permissions ?? []
        this.token = parsed.token ?? ''
      } catch {
        this.clear()
      } finally {
        this.loaded = true
      }
    },

    async fetchMe() {
      const { data } = await api.get('/api/me')

      this.user = data.user
      this.roles = data.roles || []
      this.permissions = data.permissions || []

      // mantém o token atual e persiste tudo
      const raw = localStorage.getItem('auth')
      const token = raw ? (JSON.parse(raw)?.token ?? this.token) : this.token
      this.token = token

      if (this.user) {
        this.setSession({
          user: this.user,
          token: this.token,
          roles: this.roles,
          permissions: this.permissions,
        })
      }
    },

    clear() {
      this.user = null
      this.roles = []
      this.permissions = []
      this.token = ''
      this.loaded = true
      localStorage.removeItem('auth')
      localStorage.removeItem('usuario') // legado
      localStorage.removeItem('token') // legado
    },
  },
})
