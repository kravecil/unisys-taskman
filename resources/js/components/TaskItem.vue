<script setup>
import { useRouter } from 'vue-router'
import { icons } from '@/api/util'
import { filter } from '@/api/store'

defineProps({
    task: Object,
    type: String,
  })
const router = useRouter()

const MAX_USERS = 5

const onUserClick =  (userId) => { filter.value.filterUser = `id:${userId}`}
</script>

<template>
  <!-- <q-item clickable v-ripple
    class="relative-position q-pa-md"
    :class="{'bg-grey-5': !task.closed_at, 'bg-grey-4': task.closed_at}"
    :to="{path: '/task/' + task.id}"
  > -->
  <tr
    :class="{'tr-regular': !task.closed_at, 'tr-closed': task.closed_at, 'tr-expired': !task.closed_at && task.deadline_is_close == 3}">
    <!-- аватарка -->
    <td class="text-center">
      <q-icon v-if="task.importance==0" color="grey-9" name="task" />
      <span v-if="task.importance==1" class="text-deep-orange-6 text-weight-bolder">K</span>
      <span v-if="task.importance==2" class="text-deep-orange-10 text-weight-bolder">KУ</span>
    </td>

    <!-- тело и дата создания -->
    <td :class="{'cell-bg2':!task.closed_at && task.deadline_is_close != 3}">
      <div class="items-start column">
        <div class="text-caption" style="font-size: .7em">
          {{ task.created_at }}
          <q-badge v-if="task.is_unread && !task.closed_at"
            color="deep-orange-10" text-color="white" label="новые события" size="xs" style="font-size: 0.7em" />
        </div>

        <!-- сколько подзадач -->
         <div v-if="task.user_child_tasks_count>0" class="text-dense q-px-sm bg-grey-8 text-white rounded-borders">подзадачи: {{ task.user_child_tasks_count }}</div>

        <!-- <div class="link text-weight-bold cursor-pointer "
          @click="filter.filterProject=`id:${task.project?.id}`">
          {{task.project?.title}}
        </div> -->
        <router-link v-if="task.project" class="link text-weight-bold" :to="{name: 'project', params: {id: task.project.id}}">
          {{task.project?.title}}
        </router-link>
        
        <router-link :to="{name: 'task', params: {id: task.id}}" class="link text-deep-orange-10">
          <q-item-label
            class="text-weight-bold"
            :class="{'text-strike': task.closed_at}"
            lines="2">
            {{ task.body }}
          </q-item-label>
        </router-link>

        <div v-if="task.document" class="text-dense ellipsis-2-lines">
          <strong>документ:</strong> {{ task.document?.body}}
        </div>
      </div>
    </td>

    <!-- крайний срок -->
    <td class="text-center text-grey-8">
      <div v-if="task.deadline_at" class="td-text-deadline">
        <div class="row justify-center items-center">
          {{ task.deadline_at }}
          <q-icon v-if="task.request_deadline_at" name="schedule" size="1.2em" color="deep-orange-10">
            <q-tooltip>Запрос на изменение срока</q-tooltip>
          </q-icon>
        </div>
        <div v-if="task.deadline_is_close != 3">
          дней: <b>{{ task.days_till_deadline > 0 ?task.days_till_deadline : 'менее суток' }}</b>
        </div>
      </div>
      
    </td>

    <!-- постановщики -->
    <td class="text-center">
      <div class="column td-text-users">
        <div v-if="task.creators?.length < MAX_USERS">
          <div v-for="creator in task.creators" :key="creator.id">
            {{ creator.shortname }}
          </div>
        </div>
        <div v-else>
          всего {{ task.creators?.length }}
        </div>
      </div>
    </td>

    <!-- исполнители -->
    <td class="text-center">
      <div class="column td-text-users">
        <div v-if="task.executors?.length < MAX_USERS">
          <div v-for="executor in task.executors" :key="executor.id"
            :class="{'text-weight-bold' : !executor.pivot?.read_at}">
            <!-- <q-icon v-if="executor.pivot?.read_at" name="visibility" color="green-8" /> -->
            <!-- <span v-if="executor.pivot?.read_at" class="text-green-8 text-h6">&bull;</span> -->
            <div class="cursor-pointer link" 
              @click="onUserClick(executor.id)">{{ executor.shortname }}</div>
          </div>
        </div>
        <div v-else>
          всего {{ task.executors?.length }}
        </div>
      </div>
    </td>

    <!-- соисполнители -->
    <td class="text-center">
      <div class="column td-text-users">
        <div v-if="task.coexecutors?.length < MAX_USERS">
          <div v-for="coexecutor in task.coexecutors" :key="coexecutor.id"
            :class="{'text-weight-bold' : !coexecutor.pivot?.read_at}">
            <!-- <q-icon v-if="coexecutor.pivot?.read_at" name="visibility" color="green-8" /> -->
            <!-- <span v-if="coexecutor.pivot?.read_at" class="text-green-8 text-h6">&bull;</span> -->
            {{ coexecutor.shortname }}
          </div>
        </div>
        <div v-else>
          всего {{ task.coexecutors?.length }}
        </div>
      </div>
    </td>

    <!-- контроль -->
    <td class="text-center">
      <div class="column td-text-users">
        <div v-if="task.controllers?.length < MAX_USERS">
          <div v-for="controller in task.controllers" :key="controller.id"
            :class="{'text-weight-bold' : !controller.pivot?.read_at}">
            <!-- <q-icon v-if="controller.pivot?.read_at" name="visibility" color="green-8" /> -->
            <!-- <span v-if="controller.pivot?.read_at" class="text-green-8 text-h6">&bull;</span> -->
            {{ controller.shortname }}
          </div>
        </div>
        <div v-else>
          всего {{ task.controllers?.length }}
        </div>
      </div>
    </td>

    
    <td>
      <!-- внимание на срок исполнения -->
      <q-icon v-if="!task.closed_at && task.deadline_is_close == 1"
        color="grey-9" name="info">
        <q-tooltip>Обратите внимание на срок выполнения</q-tooltip>
      </q-icon>
      <q-icon v-if="!task.closed_at && task.deadline_is_close == 2"
        color="deep-orange-6" name="info">
        <q-tooltip>Срок выполнения подходит</q-tooltip>
      </q-icon>
      <q-icon v-if="!task.closed_at && task.deadline_is_close == 3"
        color="red-10" name="info">
        <q-tooltip>Задача просрочена</q-tooltip>
      </q-icon>
      <!-- состояние поручения -->
      <q-icon :name="icons[task.state.name]">
        <q-tooltip>{{ task.state.title }}</q-tooltip>
      </q-icon>
    </td>
    <!-- <q-badge
      v-if="task.is_unread"
      floating
      label="Новые события"
    /> -->
  </tr>
  <!-- </q-item> -->
</template>

<style lang="sass" scoped>
@import 'quasar/src/css/variables.sass'
.td-text-users
  font-size: 0.8em
.td-text-deadline
  font-size: 0.8em
.tr-regular
  // background-color: #fff6
  background-color: white
.tr-closed
  background-color: #ccc6
.tr-expired
  // background-color: #faa6
  background-color: $red-2
.cell-bg2
  // background-color: #f1aa9466
  background-color: $deep-orange-1
.link
  text-decoration: underline
  color: black
  &:hover
    color: #bf360c
</style>