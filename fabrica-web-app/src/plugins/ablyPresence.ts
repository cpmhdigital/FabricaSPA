// src/plugins/ablyPresence.ts
import { ref } from 'vue'
import { createAblyClient } from '@/services/ably'

export const usuariosOnline = ref<
  {
    id: number
    nome: string
    email: string
    departamento_id?: number
    departamento_nome?: string
  }[]
>([])

let ablyClient: ReturnType<typeof createAblyClient> = null
let channel: NonNullable<ReturnType<NonNullable<typeof ablyClient>['channels']['get']>> | null = null

export function entrarNoAbly(usuario: {
  id: number
  nome: string
  email: string
  departamento_id?: number
  departamento_nome?: string
}) {
  ablyClient = createAblyClient({
    clientId: `user-${usuario.id}`,
  })
  if (!ablyClient) return

  channel = ablyClient.channels.get('usuarios-online')

  // Entra no canal de presença
  channel.presence.enter(
    {
      nome: usuario.nome,
      email: usuario.email,
      departamento_id: usuario.departamento_id,
      departamento_nome: usuario.departamento_nome ?? 'Sem Departamento',
      status: 'online',
    },
    (err) => {
      if (err) console.error('Erro ao entrar no canal de presença', err)
    }
  )

  // Lista inicial
  channel.presence.get((err, members) => {
    if (err) return console.error(err)

    usuariosOnline.value = members.map((m) => ({
      id: parseInt(m.clientId.replace('user-', '')),
      nome: m.data?.nome,
      email: m.data?.email,
      departamento_id: m.data?.departamento_id,
      departamento_nome: m.data?.departamento_nome ?? 'Sem Departamento',
    }))
  })

  // Entrada de novos usuários
  channel.presence.subscribe('enter', (member) => {
    const id = parseInt(member.clientId.replace('user-', ''))
    if (!usuariosOnline.value.find((u) => u.id === id)) {
      usuariosOnline.value.push({
        id,
        nome: member.data?.nome,
        email: member.data?.email,
        departamento_id: member.data?.departamento_id,
        departamento_nome: member.data?.departamento_nome ?? 'Sem Departamento',
      })
    }
  })

  // Saída de usuários
  channel.presence.subscribe('leave', (member) => {
    const id = parseInt(member.clientId.replace('user-', ''))
    usuariosOnline.value = usuariosOnline.value.filter((u) => u.id !== id)
  })
}

export function sairDoAbly() {
  if (channel) channel.presence.leave()
  if (ablyClient) ablyClient.close()

  usuariosOnline.value = []
}

// Reconectar após refresh
const usuarioSalvo = localStorage.getItem('usuario')
if (usuarioSalvo) {
  const user = JSON.parse(usuarioSalvo)
  if (user?.id && user?.nome) entrarNoAbly(user)
}
