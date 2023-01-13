<template>
<div>
  <q-infinite-scroll
    class="q-gutter-y-sm"
    @load="onScrollLoad"
    ref="infiniteScrollRef"
    :offset="200">
    <q-markup-table :wrap-cells="true" class="bg-page" >
      <thead class="bg-brown-5 text-white">
        <tr>
          <th class="">Проект</th>
          <th class="">Поручений</th>
          <th class="" style="width: 1%"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="project in projects" :key="project.id">
          <td>
            {{project.title}}
          </td>
          <td class="text-center">
            <!-- <router-link class="text-grey-9"
              @click="onProjectClick(project.id)"
              :to="{name: isMine?'create':'execute'}">
              {{project.tasks_count}}
            </router-link> -->
            <router-link class="text-grey-9"
              :to="{name: 'project', params: {id: project.id}}">
              {{project.tasks_count}}
            </router-link>
          </td>
          <td>
            <div v-if="isMine" class="row">
              <q-btn flat dense size="sm" round icon="add" color="grey-8"
                @click="showAddTaskDialog(project)">
                <q-tooltip>Добавить поручение к проекту</q-tooltip>
              </q-btn>
              <q-btn flat dense size="sm" round icon="delete" color="red-8"
                @click="onDeleteProject(project)">
                <q-tooltip>Удалить проект</q-tooltip>
              </q-btn>
            </div>
          </td>
        </tr>
      </tbody>
    </q-markup-table>
    <template v-slot:loading>
      <div class="text-center q-my-md">
        <q-spinner-dots color="orange-10" size="40px" />
      </div>
    </template>
  </q-infinite-scroll>
</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Dialog } from 'quasar'

// import { api } from '/src/boot/axios'
import { projects, filter } from '@/api/store'

import AddTaskDialog from '@/dialogs/AddTaskDialog.vue'

const props = defineProps(['isMine'])

const infiniteScrollRef = ref(null)
let page = 1

const resetData = () => {
  projects.value = []
  page = 1

  infiniteScrollRef.value.reset()
  infiniteScrollRef.value.poll()
  infiniteScrollRef.value.resume()
}
onMounted(() => resetData())

const onScrollLoad = (index, done) => {
  axios.get(`/api/projects/?page=${page}`, {params: {isMine: props.isMine}})
  .then(response => {
    if (response.data.length > 0) {
      for (const item of response.data) projects.value.push(item)
      page++
    } else infiniteScrollRef.value?.stop()

    done()
  })
}
const showAddTaskDialog = (project) => {
  Dialog.create({
    component: AddTaskDialog,
    componentProps: { project: project},
  })
}
// const onProjectClick = (projectId) => {
//   filter.value.filterProject = `id:${projectId}`
//   // filter.value.showInProgress = true
//   // filter.value.showInProgress = true
// }
const onDeleteProject = (project) => {
  Dialog.create({
    message: `Удалить проект<br><b>${project.title}</b>?`,
    html: true,
    cancel: true,
  })
  .onOk(() => {
    axios.delete(`/api/projects/${project.id}`)
    .then(() => resetData())
  })
}
</script>
