<template>
<q-page class="bg-page">
  <TaskFilter />
  <div class="q-pa-lg column q-gutter-md">
    <div class="column">
      <div class="text-caption">Проект</div>
      <div class="text-h6">{{ project.title }}</div>
    </div>

    <q-infinite-scroll
      class="q-gutter-y-sm"
      @load="onScrollLoad"
      ref="infiniteScrollRef"
      :offset="200"
    >
      <q-markup-table
        v-if="tasks?.length > 0"
        :wrap-cells="true" class="bg-transparent">
        <thead class="bg-brown-5 text-white">
          <tr>
            <th class="" style="width:1%"></th>
            <th class="" style="width:30%">Поручение</th>
            <th class="" style="width:18%">Срок</th>
            <th class="">Постановщик</th>
            <th class="">Исполнители</th>
            <th class="">Соисполнители</th>
            <th class="">Наблюдатели</th>
            <th class="" style="width:1%"></th>
          </tr>
        </thead>
        <tbody>
          <TaskItem
            v-for="(task) in tasks"
            :key="task.id"
            :task="task"
            type='executor'
          />
        </tbody>
      </q-markup-table>
      <template v-slot:loading>
        <div class="text-center q-my-md">
          <q-spinner-dots color="orange-10" size="40px" />
        </div>
      </template>
    </q-infinite-scroll>
  </div>
  <!-- <TaskList view="project" /> -->
  
  
</q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

// import { api } from 'src/boot/axios'
import { tasks, filter } from '@/api/store'

import TaskFilter from '@/components/TaskFilter.vue'
// import TaskList from 'components/TaskList'
import TaskItem from '@/components/TaskItem.vue'

const route = useRoute()
const project = ref({})
// const tasks = ref([])

onMounted (() => {
  resetData()
  // tasks.value = []

  axios.get(`/api/projects/${route.params.id}`)
  .then(response => project.value = response.data)

  // axios.get(`/api/projects/${route.params.id}/tasks`)
  // .then(response => tasks.value = response.data)
})

let interval
watch(filter, () => {
  clearInterval(interval)
  interval = setTimeout(() => resetData(), 500)
}, {deep: true})

let page = 1;
const infiniteScrollRef = ref(null)
const onScrollLoad = (index, done) => {
  axios.get(`/api/projects/${route.params.id}/tasks/?page=` + String(page),
  {
    params: {
      search: filter.value.search,
      showClosed: filter.value.showAllTasks,
      showInProgress: filter.value.showInProgress,
      showExpired: filter.value.showExpired,
      // showMode: filter.value.showMode,
      user: filter.value.filterUser,
      // project: filter.value.filterProject,
    }
  })
    .then((response) => {
      if (response.data.length > 0) {
        for (const item of response.data) tasks.value.push(item)
        page++
      } else infiniteScrollRef.value?.stop()

      done()
    })
}

const resetData = () => {
  tasks.value = []
  page = 1

  infiniteScrollRef.value.reset()
  infiniteScrollRef.value.poll()
  infiniteScrollRef.value.resume()
}
</script>
