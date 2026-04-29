import * as Ably from 'ably'

const ablyKey = import.meta.env.VITE_ABLY_KEY?.trim()

let warnedMissingKey = false

function warnMissingAblyKey() {
  if (warnedMissingKey) return
  warnedMissingKey = true
  console.warn('[ably] VITE_ABLY_KEY nao configurada. Recursos realtime foram desativados.')
}

export function isAblyEnabled() {
  return Boolean(ablyKey)
}

export function createAblyClient(options: ConstructorParameters<typeof Ably.Realtime>[0] = {}) {
  if (!ablyKey) {
    warnMissingAblyKey()
    return null
  }

  return new Ably.Realtime({
    key: ablyKey,
    ...options,
  })
}

export const ably = createAblyClient()
export const kanbanChannel = ably?.channels.get('kanban') ?? null
export const presenceChannel = ably?.channels.get('usuarios-online') ?? null
