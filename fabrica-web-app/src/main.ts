// src/main.ts
import './assets/main.css'
import './plugins/echo'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import * as bootstrap from 'bootstrap'
;(window as any).bootstrap = bootstrap


import App from './App.vue'
import router from './router'

import { entrarNoAbly } from './plugins/ablyPresence'
import { ModuleRegistry, AllCommunityModule } from 'ag-grid-community'

// ----------------------
// AG GRID
// ----------------------
ModuleRegistry.registerModules([AllCommunityModule])

// ----------------------
// CONFIGURAR TOKEN
// ----------------------
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// ----------------------
// RECONEXÃO ABLY (após refresh)
// ----------------------
const usuarioLogado = JSON.parse(localStorage.getItem('usuario') || '{}')
if (usuarioLogado?.id && usuarioLogado?.name) {
  entrarNoAbly(usuarioLogado)
}

// ----------------------
// INICIALIZAR VUE
// ----------------------
createApp(App).use(createPinia()).use(router).mount('#app')
