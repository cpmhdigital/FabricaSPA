import Echo from '@ably/laravel-echo'
import { createAblyClient } from '@/services/ably'

const ablyClient = createAblyClient()

const echo = ablyClient
  ? new Echo({
      broadcaster: 'ably',
      key: import.meta.env.VITE_ABLY_KEY,
      client: ablyClient,
    })
  : null

window.Echo = echo

export default echo
