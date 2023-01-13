import './bootstrap';

import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import { Quasar, Loading, Dialog, Notify, Cookies } from 'quasar'

import '../css/app.sass'
import 'animate.css'

import '@quasar/extras/material-icons/material-icons.css'
import 'quasar/src/css/index.sass'
import quasarLang from 'quasar/lang/ru'

import App from '@/App.vue'
import routes from '@/routes.js'
import { authenticate, authenticated, unauthenticate } from '@/api/auth.js'

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

import { config } from '@/api/listeners'
router.beforeEach((to) => {
  if (!authenticated.value) unauthenticate()

  config.route = to
})

authenticate()
.then(() => {
  createApp(App)
    .use(Quasar, {
      plugins: {
        Loading, Notify, Dialog, Cookies
      },
      lang: quasarLang
    })
    .use(router)
    .mount('#app')
})
