<script setup>
import { ref, toRefs } from 'vue'
import { useRouter } from 'vue-router'
import { Notify, date, Loading } from 'quasar'
// import { api } from 'src/boot/axios'
import { projects } from '@/api/store'
import * as policy from '@/api/policy'

import UserExpansionItem from '@/components/UserExpansionItem.vue'
import EditDeadline from '@/components/EditDeadline.vue'

  // name: 'PageName',
const props = defineProps(['projectSelected', 'taskParent'])
const emit = defineEmits(['done', 'close'])
const router = useRouter()

const formRef = ref(null)

const body = ref('')
const datetime = ref('')
const creators = ref([])
const executors = ref([])
const coexecutors = ref([])
const controllers = ref([])
const attachments = ref([])
const isIndividualTask = ref(false)
const isCoexecutors = ref(false)
const project = ref(props.projectSelected ?? null)
const projectsList = ref([])
const importance = ref(0)

const bodyTemplates = [
  'Для подготовки ответа',
  'Для подготовки приказа',
  'Для руководства в работе',
  'Для предложений',
  'Для исполнения',
  'Для сведения',
  'Для принятия необходимых мер',
  'Для рассмотрения в установленном порядке',
  'Прошу переговорить',
]

const onSubmit = () => {
  if (!validate()) return

  const formData = new FormData()

  formData.append('body', body.value)
  formData.append('is_individual', isIndividualTask.value)
  formData.append('is_coexecutors', isCoexecutors.value)
  formData.append('importance', importance.value)

  if (props.taskParent) formData.append('task_id', props.taskParent.id)

  if (datetime.value) formData.append('deadline_at', datetime.value)
  if (project.value) formData.append('project_id', project.value.id)

  // console.log(project.value); return

  if (creators.value.length > 0)
      for (const creator of creators.value)
        formData.append('creators[]', creator.id)
  if (executors.value.length > 0)
      for (const executor of executors.value)
        formData.append('executors[]', executor.id)
  if (coexecutors.value.length > 0)
      for (const coexecutor of coexecutors.value)
        formData.append('coexecutors[]', coexecutor.id)
  if (controllers.value.length > 0)
      for (const controller of controllers.value)
        formData.append('controllers[]', controller.id)
  if (attachments.value.length > 0)
      for (const attachment of attachments.value)
        formData.append('attachments[]', attachment)

  Loading.show({message: 'Создание поручений...'})
  axios.post('/api/tasks', formData, { headers: {'Content-Type': 'multipart/form-data'}})
  .then((response) => {
    Notify.create({
      type: 'positive',
      message: 'Успех',
      caption: 'Задача добавлена',
    })
    // router.push({name: 'task', params: { id: response.data.id}})
    // if (dialogmode.value == false) router.back()
    // emit('updated')
    emit('done', response.data)

    /* Обновим список проектов  */
    const pos = projects.value?.findIndex(item => item.id == project.value?.id)
    if (pos >= 0) projects.value[pos].tasks_count++

    // router.push({
    //   name: executor.value? 'outgoing' : 'notes'
    // })
  })
  .finally(() => Loading.hide())
}
const onReset = () => {
  body.value = null
  datetime.value = null
  creators.value = []
  executors.value = []
  coexecutors.value = []
  controllers.value = []
  attachments.value = []
  isIndividualTask.value = false
  isCoexecutors.value = false
  importance.value = 0
  
  formRef.value.resetValidation()
}

const validate = () => {
  if (executors.value?.length == 0 && (coexecutors.value?.length > 0 || controllers.value?.length > 0)) {
    Notify.create({
      type: 'negative',
      message: 'Ошибка валидации',
      caption: 'Не указаны ответственные исполнители'
    })
    return false
  }
  // if (project.value && executors.value?.length == 0) {
  //   Notify.create({
  //     type: 'negative',
  //     message: 'Ошибка валидации',
  //     caption: 'Задан проект, но не указаны исполнители'
  //   })
  //   return false
  // }

  return true
}

const loadProjects = (val, update, abort) => {
  axios.get('/api/projects')
  .then(response => update(() => { projectsList.value = response.data}))
}
</script>

<template>
<div class="WAL q-mx-auto col q-pa-xl bg-grey-4">
  <q-form
    ref="formRef"
    @submit="onSubmit"
    @reset="onReset"
    class="q-gutter-md"
  >
    <q-toolbar class="q-pa-xs">
      <q-toolbar-title class="text-caption gt-sm">
        Добавить поручение
      </q-toolbar-title>
      <q-btn label="Добавить" size="sm" type="submit" color="deep-orange-10"/>
      <q-btn label="Сброс" size="sm" type="reset" color="grey-10" flat class="q-ml-sm" />
      <q-btn flat rounded dense color="primary" icon="close" class="q-ml-lg" @click="emit('close')" />
    </q-toolbar>
    <!-- На основании родительского поручения -->
    <q-item v-if="taskParent">
      <q-item-section avatar>
        <q-icon name="warning" color="yellow-10" />
      </q-item-section>
      <q-item-section>
        <q-item-label caption>Поручение на основании</q-item-label>
        <q-item-label class="text-weight-bold">{{ taskParent.body }}</q-item-label>
      </q-item-section>
    </q-item>
    
    <q-separator spaced />

    <!-- текст задачи -->
    <div class="col row">
      <q-btn-dropdown flat dense no-caps push size="xs" icon="article" class="self-start">
        <q-list>
          <q-item v-for="(bodyTemplate,index) in bodyTemplates" :key="index"
            clickable v-close-popup @click="body = bodyTemplate + body">
            <q-item-section>
              <q-item-label>{{ bodyTemplate }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
      <q-input
        class="col q-mx-sm"
        filled
        v-model="body"
        type="textarea"
        label="Текст задачи"
        autofocus
        lazy-rules
        :rules = "[val => !!val || 'Обязательное поле']"
      />
      <q-file
        class="col-4"
        v-model="attachments"
        multiple
        filled
        append
        max-files="10"
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
    
    <!-- срок выполнения -->
    <q-expansion-item
      class="q-ma-none q-mt-md"
      expand-separator>
      <EditDeadline v-model="datetime" />
      <template v-slot:header>
        <q-item class="col">
          <q-item-section avatar>
            <q-icon name="event" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Срок выполнения</q-item-label>
            <q-item-label caption>{{datetime? date.formatDate(datetime, 'D MMMM YYYY HH:mm') : 'не задан'}}</q-item-label>
          </q-item-section>
        </q-item>
      </template>
    </q-expansion-item>

    <!-- ответственные исполнители -->
    <UserExpansionItem v-model:users="executors" label="Ответственные исполнители" />

    <!-- соисполнители -->
    <UserExpansionItem v-model:users="coexecutors" label="Соисполнители" :disable="isIndividualTask" />

    <!-- контроль -->
    <UserExpansionItem v-model:users="controllers" label="Наблюдатели" />

    <!-- постановщики -->
    <UserExpansionItem v-model:users="creators" label="Постановщики" :disable="!policy.can('taskman_modify_creators')" />

    <!-- важность -->
    <div class="row bg-grey-5 q-pa-md items-center q-gutter-x-md">
      <span style="font-size:.8em" class="text-weight-bolder">Важность:</span>
      <q-btn-toggle
        :disable="!policy.isDocumentologist.value"
        v-model="importance"
        no-caps
        color="grey-2"
        text-color="black"
        size="sm"
        :options="[{label: 'Обычная', value: 0},{label: 'Высокая (К)', value: 1},{label: 'Очень высокая (КУ)', value: 2},]"
      />
    </div>

    <!-- проект -->
    <q-select v-model="project" :options="projectsList"
      option-value option-label="title" label="Проект" clearable filled @filter="loadProjects" />

    <q-separator spaced inset />
    
    <q-item>
      <q-item-section avatar>
        <q-checkbox left-label v-model="isIndividualTask"
          :disable="coexecutors?.length > 0" />
      </q-item-section>
      <q-item-section>
        <q-item-label>Индивидуальные поручения</q-item-label>
        <q-item-label caption>
          Каждому ответственному исполнителю в системе будет создано своё индивидуальное поручение, которое исполнитель
          сможет выполнить независимо от других участников. В противном случае любой из исполнителей сможет
          выполнить поручение.
        </q-item-label>
        <q-item-label v-if="coexecutors?.length > 0" class="text-red" caption>
          Назначены соисполнители
        </q-item-label>
      </q-item-section>
    </q-item>
    <q-item>
      <q-item-section avatar>
        <q-checkbox left-label v-model="isCoexecutors" />
      </q-item-section>
      <q-item-section>
        <q-item-label>Ответственные соисполнители</q-item-label>
        <q-item-label caption>
          Соисполнители имеют возможность отправлять поручение на проверку.
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-form>
</div>
</template>

<style lang="scss" scoped>
.WAL {
  max-width: 1170px;
}
</style>