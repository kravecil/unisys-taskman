<template>
<q-page padding>
  <q-markup-table class="bg-page">
    <thead class="bg-brown-5 text-white">
      <tr>
        <th class="text-center">Сотрудник</th>
        <th class="text-center">Выполняет</th>
        <th class="text-center">Просрочено</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in data" :key="index">
        <td class="text-left">{{item.shortname}}</td>
        <td class="text-center">
          <router-link class="text-grey-9"
            :to="{name: 'create'}" @click="onUserClick(item.user_id, false)">
            {{item.executing}}
          </router-link>
        </td>
        <td class="text-center">
          <router-link :class="{'text-red text-weight-bold': item.expired, 'text-grey-9': !item.expired}"
            :to="{name: 'create'}" @click="onUserClick(item.user_id, true)">
            {{item.expired}}
          </router-link>
        </td>
      </tr>
    </tbody>
  </q-markup-table>
</q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
// import { api } from 'src/boot/axios'
import { filter } from '@/api/store'

const data = ref([])

onMounted(() => {
  axios.get('/api/tasks/report')
  .then(response => {
    data.value = response.data
  })
})
const onUserClick = (userId, expired = false) => {
  filter.value.filterUser = `id:${userId}`
  filter.value.showInProgress = !expired
  filter.value.showExpired = expired
}
</script>
