import axios, { type AxiosError } from 'axios'
import router from '@/router'
import Swal from 'sweetalert2'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '',
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

function getStoredToken(): string | null {
  try {
    // prioridade: token isolado
    const token = localStorage.getItem('token')
    if (token) return token

    // fallback: objeto auth { token: '...' }
    const raw = localStorage.getItem('auth')
    if (!raw) return null
    const parsed = JSON.parse(raw)
    return parsed?.token ?? null
  } catch {
    return null
  }
}

// Request: injeta Bearer se houver token
api.interceptors.request.use((config) => {
  const token = getStoredToken()
  if (token) {
    config.headers = config.headers ?? {}
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response: trata erros
api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const status = error.response?.status

    if (status === 401) {
      localStorage.removeItem('auth')
      localStorage.removeItem('usuario')
      localStorage.removeItem('token')

      // Evita alert duplicado se estiver na tela de login
      if (router.currentRoute.value.path !== '/') {
        await Swal.fire({
          icon: 'warning',
          title: 'Sessão expirada',
          text: 'Faça login novamente.',
          confirmButtonColor: '#007A5A',
        })
      }

      router.replace({ path: '/' })
      return Promise.reject(error)
    }

    if (status === 403) {
      await Swal.fire({
        icon: 'warning',
        title: 'Acesso negado',
        text: (error.response?.data as any)?.message || 'Sem permissão.',
        confirmButtonColor: '#d33',
      })
      return Promise.reject(error)
    }

    if (status === 422) {
      await Swal.fire({
        icon: 'info',
        title: 'Atenção',
        text: 'Preencha os campos corretamente.',
        confirmButtonColor: '#007A5A',
      })
      return Promise.reject(error)
    }

    if (status === 429) {
      await Swal.fire({
        icon: 'error',
        title: 'Muitas tentativas',
        text: 'Aguarde alguns minutos para tentar novamente.',
        confirmButtonColor: '#d33',
      })
      return Promise.reject(error)
    }

    await Swal.fire({
      icon: 'error',
      title: 'Erro',
      text: 'Ocorreu um erro. Tente novamente.',
      confirmButtonColor: '#d33',
    })

    return Promise.reject(error)
  },
)

export default api
