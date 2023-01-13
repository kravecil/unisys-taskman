import URL from '@/api/hosts'

import Echo from 'laravel-echo'

import Pusher from 'pusher-js'

export const echo = new Echo({
  broadcaster: 'pusher',
  key: 'PUSHER_APP_KEY',
  // wsHost: window.location.host,
  wsHost: URL.ws.host,
  cluster: 'mt1',
  wsPort: 6001,
  forceTLS: false,
  disableStats: true,
  // auth: {
  //   headers: {
  //       Authorization: 'Bearer 2|um7AC33FncOgCJseYMrbmJmqaP7DwLDQ0E455lKx'
  //   },
  // },
//   authEndpoint: URL.api + '/broadcasting/auth',
  authorizer: (channel, options) => {
    return {
        authorize: (socketId, callback) => {
            axios.post(URL.ws.origin + '/broadcasting/auth', {
            // api.post(URL.api + '/broadcasting/auth', {
                socket_id: socketId,
                channel_name: channel.name
            })
            .then(response => {
                callback(false, response.data);
            })
            .catch(error => {
                callback(true, error);
            });
        }
    };
  },
})
