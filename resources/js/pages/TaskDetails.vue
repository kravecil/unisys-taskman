<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Dialog, Notify, Loading } from 'quasar'
// import { api } from 'src/boot/axios'
// import { user } from 'src/api/auth'
import { markTaskAsRead, getTask, getComments, getHistories, icons } from '@/api/util'
import { task, comments, histories } from '@/api/store'
import * as policy from '@/api/policy'

import Attachment from '@/components/Attachment.vue'
import EditUserSelect from '@/components/EditUserSelect.vue'
import EditDeadlineDialog from '@/dialogs/EditDeadlineDialog.vue'
import AddTaskDialog from '@/dialogs/AddTaskDialog.vue'
import TaskItem from '@/components/TaskItem.vue'
import SetRepeatDialog from '@/dialogs/SetRepeatDialog.vue'
import axios from 'axios'

// const emit = defineEmits(['updated'])
const route = useRoute()
const router = useRouter()

const loading = ref(false)
const loadingHistories = ref(false)
const loadingComments = ref(false)
const commentary = ref('')
const attachments = ref([])
const tab = ref('comments')

const project = ref({})
const projectsList = ref([])

const importances = [
  { label: 'Обычное', class: 'text-grey-7'},
  { label: 'Важное (К)', class: 'text-deep-orange-6'},
  { label: 'Очень важное (КУ)', class: 'text-deep-orange-10 text-weight-bolder'},
]

const updateTask = () => {
  loading.value = true
  getTask(route.params.id)
  .then(() => {
    project.value = task.value.project?.title ?? null
  })
  .finally(() => loading.value = false)
}

const updateComments = () => {
  loadingHistories.value = true

  getComments(route.params.id)
    .finally(() => loadingComments.value = false)
}

const updateHistories = () => {
  loadingHistories.value = true

  getHistories(route.params.id)
    .finally(() => loadingHistories.value = false)
}

onMounted(() => {
  updateTask()
  tab.value = 'comments'
})
watch(() => route.params.id, (newVal) => { if (newVal && route.name=='task') updateTask() })

const deleteTask = () => {
  Dialog.create({
    title: 'Удаление поручения',
    message: 'Вы действительно хотите удалить данное поручение?',
    cancel: true,
    html: true,
  })
    .onOk(() => {
      axios.delete('/api/tasks/' + task.value.id)
        .then(() => {
          Notify.create({
            type: 'positive',
            message: 'Успех',
            caption: 'Сообщение удалено',
          })
          // emit('updated')
          // router.back()
          router.push({name: 'taskDeleted'})
        })
    })
}

const createChild = () => {
  Dialog.create({component: AddTaskDialog, componentProps: {task: task.value}})
    .onOk(e => router.push({name: 'task', params: {id: e[0]?.id}})) 
}

const complete = () => {
  axios.post(`/api/tasks/${task.value.id}/complete`)
    .then(() => {
      Notify.create({
          type: 'positive',
          message: 'Успех',
          caption: 'Поручение отправлено на проверку',
        })
      updateTask()
    })
}

const back = () => {
  axios.post(`/api/tasks/${task.value.id}/back`)
    .then(() => {
      Notify.create({
          type: 'positive',
          message: 'Успех',
          caption: 'Поручение возвращено',
        })
      updateTask()
    })
}

const comment = () => {
  if (commentary.value || attachments.value.length > 0) {
    const formData = new FormData()
    
    if (commentary.value) formData.append('comment', commentary.value)
    if (attachments.value.length > 0)
      for (const attachment of attachments.value)
        formData.append('attachments[]', attachment)

    Loading.show('Отправка комментария...')
    axios.post(`/api/tasks/${task.value.id}/comment`, formData, {headers: {'Content-Type': 'multipart/form-data'}})
      .then(() => {
        Notify.create({
            type: 'positive',
            message: 'Успех',
            caption: 'Комментарий добавлен',
          })
        commentary.value = null
        attachments.value = []
        updateComments()
        updateHistories()
      })
      .finally(() => Loading.hide())
  }
}

const close = () => {
  axios.delete(`/api/tasks/${task.value.id}/close`)
    .then(() => {
      Notify.create({
          type: 'positive',
          message: 'Успех',
          caption: 'Задача закрыта',
        })
      updateTask()
    })
}

const dialogEditUserSelect = (view) => {
    Dialog.create({
      component: EditUserSelect,
      componentProps: {
        view: view,
        users: task.value[view],
      }
    })
    .onOk((payload) => {
      if (payload.view == 'creators') {
        if (!(payload.users?.length > 0)) {
          Notify.create({type: 'negative', message: 'Ошибка',
          caption: 'Список постановщиков не может быть пустым'})
          return
        }
      } else if (payload.view == 'executors') {
        if (!(payload.users?.length > 0) && !(task.value.coexecutors.length == 0 && task.value.controllers.length == 0)) {
          Notify.create({type: 'negative', message: 'Ошибка',
          caption: 'Список исполнителей не может быть пустым, если заданы соисполнители или контролирующие'})
          return
        }
      }

      const bufView = task.value[view] /// TEMP исправить эти две строки, выделить в копию объекта
      task.value[view] = payload.users

      axios.put(`/api/tasks/${task.value.id}/users`, {
        view: payload.view,
        creators: task.value.creators.map(item => item.id),
        executors: task.value.executors.map(item => item.id),
        coexecutors: task.value.coexecutors.map(item => item.id),
        controllers: task.value.controllers.map(item => item.id),
      })
      .then(() => {
        // task.value[view] = payload.users
        updateTask()

        Notify.create({
          type: 'positive',
          message: 'Успех',
          caption: 'Список сотрудников изменён',
        })
      })
      .catch(() => {
        task.value[view] = bufView
      })
    })
  }

const dialogEditDeadline = () => {
    Dialog.create({
      component: EditDeadlineDialog,
    })
    .onOk((payload) => {
      axios.put(`/api/tasks/${task.value.id}/deadline`, {
        request: true,
        request_deadline_at: payload,
      })
      .then(() => {
        updateTask()

        Notify.create({
          type: 'positive',
          message: 'Успех',
          caption: 'Запрос на перенос срока отправлен',
        })
      })
    })
}

const acceptRequestDeadline = () => {
  axios.put(`/api/tasks/${task.value.id}/deadline`, {
    request: false,
    accept: true,
  })
  .then(() => {
    updateTask()

    Notify.create({
      type: 'positive',
      message: 'Успех',
      caption: 'Запрос на изменение срока принят',
    })
  })
}
const declineRequestDeadline = () => {
  axios.put(`/api/tasks/${task.value.id}/deadline`, {
    request: false,
    accept: false,
  })
  .then(() => {
    updateTask()

    Notify.create({
      type: 'positive',
      message: 'Успех',
      caption: 'Запрос на изменение срока отменён',
    })
  })
}

const loadProjects = (val, update, abort) => {
  axios.get('/api/projects')
  .then(response => update(() => { projectsList.value = response.data}))
}
const attachProject = () => {
  axios.post(`/api/tasks/${task.value.id}/attach/${project.value?.id ?? ''}`)
  .then(() => updateTask())
}
const updateImportance = (val) => {
  axios.post(`/api/tasks/${task.value.id}/update-importance`, { importance: val})
  .then(() => updateTask())
}

/**
 * Установить интервал повторяемости поручения
 */
const setRepeat = () => {
  Dialog.create({ component: SetRepeatDialog })
  .onOk((e) => {
    console.log(e)
    axios.post(`/api/tasks/${task.value.id}/repeat`, { repeat_in: e})
  })
}
</script>

<template>
  <q-page class="bg-page">
    <div class="WAL q-mx-auto col q-pa-xl relative-position">
      <div class="column col">
        <q-toolbar class="q-pa-xs">
          <!-- <q-btn
            flat
            rounded
            dense
            color="primary"
            icon="arrow_back"
            @click="router.back()"
          >
            <q-tooltip>Назад</q-tooltip>
          </q-btn> -->
          <q-toolbar-title class="text-caption gt-sm">
            Поручение
            <span
              :class="[importances[task.importance]?.class, policy.isDocumentologist.value? 'cursor-pointer': '']"
            >
              [ {{ importances[task.importance]?.label }} ]
              <q-menu v-if="policy.isDocumentologist.value">
                <q-list dense>
                  <q-item clickable v-close-popup @click="updateImportance(0)">
                    <q-item-section>Обычное</q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="updateImportance(1)">
                    <q-item-section>Важное (К)</q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="updateImportance(2)">
                    <q-item-section>Очень важное (КУ)</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </span>
          </q-toolbar-title>
          <div
            class="text-caption text-weight-thin"
            v-if="task.closed_at">
            Поручение закрыто
          </div>

          <!-- <q-btn v-if="policy.isCreator.value"
              flat label="Повтор" size="sm" @click="setRepeat()" />
          <q-separator spaced vertical inset /> -->
          <div v-if="!task.closed_at">
            <q-btn flat label="Создать подзадачу" size="sm" @click="createChild()" color="brown-6" />
            <q-btn v-if="policy.canComplete.value && task.state?.name != 'complete'"
              flat label="На проверку" size="sm" @click="complete()" />
            <q-btn v-if="policy.canBack.value && task.state?.name == 'complete'"
              flat label="На доработку" size="sm" @click="back()" />
            <q-btn v-if="policy.canClose.value"
              flat label="Подтвердить выполнение" size="sm" @click="close()" />
            <q-btn v-if="policy.canDelete.value"
              flat label="Удалить задачу" size="sm" color="red" @click="deleteTask()" />
          </div>
          
          
        </q-toolbar>
        
        <q-separator spaced />

        <div class="row q-my-lg q-gutter-x-md">
          <!-- текст задачи -->
          <div class="col column q-py-md q-gutter-y-md">
            <div class="text-grey-9" style="white-space: pre-line">
              <!-- {{ String(task.body).replace(/(?:\r\n|\r|\n)/g, '<br>') }} -->
              {{ task.body }}
            </div>

            <!-- на основании поручения -->
            <q-item
              v-if="task.task"
              :to="{name: 'task', params: {id: task.task.id}}"
              class="shadow-1 bg-white"
              clickable v-ripple
            >
              <q-item-section avatar>
                <q-icon name="info" color="red-8" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Является подзадачей к поручению</q-item-label>
                <q-item-label caption :lines="2">{{ task.task?.body }}</q-item-label>
              </q-item-section>
            </q-item>

            <!-- на основании документа -->
            <q-item
              v-if="task.document"
              :to="{name: 'document', params: {id: task.document_id}}"
              class="shadow-1 bg-white"
              clickable v-ripple
            >
              <q-item-section avatar>
                <q-icon name="info" color="red-8" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Поручение на основании документа</q-item-label>
                <q-item-label caption :lines="2">{{ task.document?.body }}</q-item-label>
              </q-item-section>
            </q-item>

            <!-- на основании проекта -->
            <q-item
              v-if="task.project"
              :to="{name: 'project', params: {id: task.project.id }}"
              class="shadow-1 bg-white"
              clickable v-ripple
            >
              <q-item-section avatar>
                <q-icon name="info" color="red-8" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Поручение относится к проекту</q-item-label>
                <q-item-label caption :lines="2">{{ task.project?.title }}</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <q-separator v-if="task.attachments?.length > 0" vertical />
          <!-- вложения к задаче -->
          <div v-if="task.attachments?.length > 0"
            class="col-4 column q-pa-md q-gutter-y-md">
            <div class="text-subtitle1">Вложения к задаче</div>
            <div class="column col">
              <attachment
                v-for="attachment in task.attachments" :key="attachment.id"
                :taskId="task.id"
                :attachment="attachment"
              />
            </div>
          </div>
        </div>
        
        <q-separator spaced />

        <div class="row col q-my-md">
          <!-- поручение: даты -->
          <div class="column col">
            <!-- создано  -->
            <div class="row">
              <div class="col-4">Создано</div>
              <div class="col text-caption text-grey-8">{{ task.created_at }}</div>
            </div>
            <!-- срок -->
            <div v-if="task.deadline_at" class="row"
              :class="{'text-red text-weight-bold' : task.deadline_is_close == 3 && !task.closed_at}">
              <div class="col-4">Срок</div>
              <div class="col column text-caption" :class="{'text-grey-8' : task.deadline_is_close != 3}">
                <div>{{ task.deadline_at }}</div>
                <div class="text-black" v-if="task.deadline_is_close != 3 && !task.closed_at">
                  осталось дней: {{ task.days_till_deadline > 0 ?task.days_till_deadline : 'менее суток' }}
                </div>
              </div>
            </div>
            <!-- запрос срока -->
            <div class="row" v-if="!task.closed_at">
                <div class="col-4 text-weight-bold">
                  <span v-if="task.request_deadline_at && policy.isInTeam">Запрос переноса</span>
                </div>
                <div class="col column text-caption text-grey-8">
                  {{ task.request_deadline_at }}
                  <q-btn
                    v-if="policy.isInTeam.value"
                    outline no-caps size="sm" color="deep-orange-10" icon-right="edit" label="Новый срок"
                    class="self-start q-my-sm"
                    @click="dialogEditDeadline()" />
                    <!-- принять отклонить перенос срока -->
                  <q-btn-group v-if="policy.canConfirmDeadline.value"
                    outline class="q-my-sm self-start">
                    <q-btn no-caps outline size="sm" color="deep-orange-10" label="Принять"
                      v-if="task.request_deadline_at"
                      class="self-start"
                      @click="acceptRequestDeadline()" />
                    <q-btn no-caps outline size="sm" text-color="deep-orange-10" label="Отклонить"
                      v-if="task.request_deadline_at"
                      class="self-start"
                      @click="declineRequestDeadline()" />
                  </q-btn-group>
                </div>
            </div>
            <!-- закрыто -->
            <div v-if="task.closed_at" class="row">
              <div class="col-4 text-green-10">Закрыто</div>
              <div class="col text-caption text-grey-8">{{ task.closed_at }}</div>
            </div>
          </div>
          <!-- поручение: участники -->
          <div class="column col q-gutter-y-md">
            <div class="column">
              <div class="row q-gutter-x-sm">
                <div class="">Постановщик</div>
                <q-btn v-if="policy.canEditUsers.value && !task.closed_at"
                  :disable="!policy.can('taskman_modify_creators')"
                  flat dense round size="xs" color="grey-7" icon="edit" @click="dialogEditUserSelect('creators')">
                  <q-tooltip>Изменить</q-tooltip>
                </q-btn>
              </div>
              <div class="row q-gutter-sm text-caption text-grey-8">
                <!-- {{ task.creators?.map(item => item.shortname).join(',') }} -->
                <div v-for="creator in task.creators" :key="creator.id" class="row items-center">
                  {{ creator.shortname }}
                </div>
              </div>
            </div>
            <div class="column">
              <div class="row q-gutter-x-sm">
                <div class="">Исполнители</div>
                <q-btn v-if="policy.canEditUsers.value && !task.closed_at"
                  flat dense round size="xs" color="grey-7" icon="edit" @click="dialogEditUserSelect('executors')">
                  <q-tooltip>Изменить</q-tooltip>
                </q-btn>
              </div>
              <div class="row q-gutter-sm text-caption text-grey-8">
                <!-- {{ task.executors?.map(item => item.shortname).join(',') }} -->
                <div v-for="executor in task.executors" :key="executor.id" class="row items-center">
                  <q-icon v-if="executor.pivot?.read_at" name="visibility" color="green-8" class="q-mr-xs" />
                  {{ executor.shortname }}
                </div>
              </div>
            </div>
            <div class="column">
              <div class="row q-gutter-x-sm">
                <div class="">Соисполнители</div>
                <q-btn v-if="(policy.canEditUsers.value || policy.isExecutor.value) && !task.closed_at"
                  flat dense round size="xs" color="grey-7" icon="edit" @click="dialogEditUserSelect('coexecutors')">
                  <q-tooltip>Изменить</q-tooltip>
                </q-btn>
              </div>
              <div class="row q-gutter-sm text-caption text-grey-8">
                <!-- {{ task.coexecutors?.map(item => item.shortname).join(',') }} -->
                <div v-for="coexecutor in task.coexecutors" :key="coexecutor.id" class="row items-center">
                  <q-icon v-if="coexecutor.pivot?.read_at" name="visibility" color="green-8" class="q-mr-xs" />
                  {{ coexecutor.shortname }}
                </div>
              </div>
            </div>
            <div class="column">
              <div class="row q-gutter-x-sm">
                <div class="">Наблюдатели</div>
                <q-btn v-if="policy.canEditUsers.value && !task.closed_at"
                  flat dense round size="xs" color="grey-7" icon="edit" @click="dialogEditUserSelect('controllers')">
                  <q-tooltip>Изменить</q-tooltip>
                </q-btn>
              </div>
              <div class="row q-gutter-sm text-caption text-grey-8">
                <!-- {{ task.controllers?.map(item => item.shortname).join(',') }} -->
                <div v-for="controller in task.controllers" :key="controller.id" class="row items-center">
                  <q-icon v-if="controller.pivot?.read_at" name="visibility" color="green-8" class="q-mr-xs" />
                  {{ controller.shortname }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- подзадачи -->
      <q-expansion-item
        v-if="task.user_child_tasks?.length>0"
        label="Подзадачи"
        header-class="bg-deep-orange-10 text-white text-weight-bold"
        expand-icon-class="text-white"
      >
        <q-markup-table>
          <TaskItem
            v-for="t in task.user_child_tasks" :key="t.id"
            :task="t"
            type="executor"
          />
        </q-markup-table>
      </q-expansion-item>

      <!-- привзять к проекту -->
      <div v-if="policy.isCreator.value && !task.closed_at"
        class="row q-gutter-sm q-mt-md">
        <q-select v-model="project" :options="projectsList" class="col"
          option-value option-label="title" clearable filled dense label="Привязать к проекту"
          @filter="loadProjects" />
        <q-btn outline color="primary" label="Применить" @click="attachProject" />
      </div>

      <q-separator spaced />

      <div>
        <q-tabs
          v-model="tab"
          class="text-black"
          align="left" >
          <q-tab name="comments" label="Комментарии" />
          <q-tab name="history" label="История активности" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated class="relative-position bg-transparent">
          <!-- панель комментариев -->
          <q-tab-panel name="comments" class="q-gutter-y-sm">
            <div v-if="!(comments?.length > 0)" class="text-caption text-grey-8">Комментарии отсутствуют...</div>
            <q-item v-for="comment in comments" :key="comment.id"
              class="bg-grey-5 q-pa-md" style="border: 1px solid #999999">
              <q-item-section>
                <q-item-label caption>
                  <span class="text-weight-bold">{{ comment.user.fullname}}</span>
                  <q-badge color="grey-8 q-ml-sm">{{ comment.created_at }}</q-badge>
                </q-item-label>
                <q-item-label caption>{{ comment.body }}</q-item-label>
                <q-item-label class="row">
                  <attachment
                    v-for="attachment in comment.attachments" :key="attachment.id"
                    :taskId="task.id"
                    :attachment="attachment"
                  />
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-tab-panel>
          <!-- панель истории активности -->
          <q-tab-panel name="history">
            <q-timeline color="secondary" layout="dense">
              <q-timeline-entry
                v-for="item in histories" :key="item.id"
                :icon="icons[item.history_types.name]"
              >
                <div class="text-grey-8">{{ item.history_types.title }}</div>
                <template v-slot:subtitle>
                  <div class="text-caption">{{ item.created_at }}</div>
                </template>
                <template v-slot:title>
                  <div class="text-caption ">{{ item.user.fullname }}</div>
                </template>
              </q-timeline-entry>
            </q-timeline>
          </q-tab-panel>
          <q-inner-loading :showing="loadingHistories || loadingComments">
            <q-spinner-gears size="50px" color="primary" />
          </q-inner-loading>
        </q-tab-panels>
      </div>

      <q-separator spaced />

      <div class="row q-gutter-x-md flex align-stretch"
        v-if="!task.closed_at && policy.isInTeam.value"
      >
        <div class="column col">
          <div class="row col q-gutter-x-md">
            <q-input class="col" filled v-model="commentary" type="textarea" label="Оставьте комментарий" />
            <q-file
              class="col-4"
              v-model="attachments"
              multiple
              filled
              append
              max-files="5"
              max-total-size="50000000"
              counter
              label="Прикрепите вложения..."
            >
              <template v-slot:file="{index, file}">
                <q-chip
                  class="bg-grey-5 full-width text-caption text-weight-light"
                  square
                  icon="attach_file"
                  :label="file.name"
                  removable
                  @remove="attachments.splice(index, 1)"
                />
              </template>
            </q-file>
          </div>
          
          <q-btn
            class="q-mt-lg"
            color="primary"
            label="Отправить комментарий"
            @click="comment()"
        />
        
        </div>
      </div>

      <q-inner-loading :showing="loading">
        <q-spinner-gears size="50px" color="primary" />
      </q-inner-loading>
    </div>
  </q-page>
</template>

<style lang="scss" scoped>
.WAL {
  max-width: 1170px;
}
</style>