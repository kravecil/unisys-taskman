import _ from 'lodash';
window._ = _;


import { unauthenticate } from '@/api/auth.js'
import { Notify } from 'quasar'
import axios from 'axios'
import hosts from '@/api/hosts.js'
window.axios = axios
window.axios.defaults.baseURL = String(hosts.base)
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
window.axios.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    console.log(error)
    if (error.response?.status === 401) unauthenticate();
    // if (error.response.status === 403) unauthenticate();

    Notify.create({
      type: "negative",
      message: `<strong>Ошибка: ${error.response?.status}</strong><br>${error.response?.data?.message}`,
      html: true,
    });

    console.log(error)

    return Promise.reject(error)
  }
);