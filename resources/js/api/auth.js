import { Cookies } from 'quasar'
import hosts from '@/api/hosts.js'
import { ref } from 'vue'
import * as listeners from '@/api/listeners'

export const authenticated = ref(false)
export const user = ref({})

export const authenticate = async() => {
    console.log('Authenticating...')
    
    const token = Cookies.get('token')
    if (!token)  {
        unauthenticate()
        return Promise.reject()
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    authenticated.value = true

    axios.get(hosts.auth + 'api/user')
    .then(response => {
        user.value = response.data
        listeners.enable()
        return Promise.resolve()
    })
    .catch(error => {
        console.log(error)
        return Promise.reject()
    })
}

export const unauthenticate = () => {
    console.log('authenticated')
    Cookies.remove('token', { domain: hosts.auth.hostname}) 
    delete axios.defaults.headers.common['Authorization']

    listeners.disable()

    authenticated.value = false

    window.location = hosts.home
}

export function can(abilities) {
    if (user.value.permissions?.some(item => item.name == 'administration')) return true

    // console.log(user.value);
    for (const ability of abilities)
        if (user.value.permissions?.some(item => item.name == ability)) return true

    return false
}