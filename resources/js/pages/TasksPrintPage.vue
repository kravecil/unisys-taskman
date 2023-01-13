<template>
<q-layout>
  <q-page padding>
    <div class="text-h4 text-right">Неисполненные поручения</div>
    <div class="text-h6 text-right q-mb-xl">на {{ fDatetime ?? date.formatDate(Date.now(), 'DD.MM.YYYY') }}</div>
    <!-- <q-separator class="q-mb-lg" /> -->

    <div v-if="items?.length == 0" class="text-center text-h6 text-grey-6">Не найдено неисполненных поручений</div>
    <div v-for="(item,index) in items" :key="index">
      <q-item class="q-my-md">
        <q-item-section class="text-h6 items-end">
          Исполнитель
        </q-item-section>
        <q-separator vertical spaced/>
        <q-item-section>
          <q-item-label>{{ item.user.fullname }}</q-item-label>
          <q-item-label caption>{{ item.user.department.number_title }}</q-item-label>
        </q-item-section>
      </q-item>
      <q-markup-table flat square bordered separator="cell" :wrap-cells="true" class="q-mb-xl">
        <thead class="bg-grey-7 text-white">
          <tr>
            <th class="" style="width:35%">Поручение</th>
            <th class="" style="width:35%">Документ</th>
            <th class="" style="width:10%">Срок</th>
            <th class="" style="width:15%">Постановщики</th>
            <th class="" style="width:5%">Подпись</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in item.tasks" :key="task.id">
            <td>{{task.body}}</td>
            <td>{{task.document?.body}}</td>
            <td class="text-center">
              {{task.deadline_at}}
              <div v-if="task.request_deadline_at" class="text-weight-bolder">
                Запрос на: {{ task.request_deadline_at }}
              </div>
            </td>
            <td>
              <div v-for="creator in task.creators" :key="creator.id" class="text-center">
                {{ creator.shortname}}
              </div>
            </td>
            <td></td>
          </tr>
        </tbody>
      </q-markup-table>
    </div>
    
    <div class="q-mt-xl text-italic">{{ date.formatDate(Date.now(), 'DD.MM.YYYY') }}</div>
    <div class="text-italic">Начальник отдела 233</div>
  </q-page>
  <q-page-sticky :offset="[30,30]">
    <div class="print-hide bg-deep-orange-10 q-pa-md text-white rounded-borders shadow-10 q-gutter-x-md">
      <q-icon name="settings" size="md" />
      <q-btn flat dense no-caps label="Быбрать исполнителя">
        <q-popup-proxy>
          <user-select
            @select="updateList({executor: $event, datetime: fDatetime})"
            :hide-groups="true" style="width: 600px; height: 500px"
          />
        </q-popup-proxy>
      </q-btn>
      <q-btn flat dense no-caps label="Быбрать дату">
        <q-popup-proxy>
          <div class="q-pa-md row q-gutter-md">
            <q-input v-model="fDatetime" filled dense autofocus label="введите дату" />
            <q-btn label="Показать" no-caps dense v-close-popup @click="updateList({executor: fExecutor, datetime: fDatetime})" />
            
          </div>
        </q-popup-proxy>
      </q-btn>
      <q-checkbox v-model="hideClosed" label="без исполненных" />
      <q-btn flat dense no-caps label="Отобразить всех" @click="updateList()" />
    </div>
  </q-page-sticky>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { date, Loading } from 'quasar'

// import { api } from 'src/boot/axios'
// import { filter } from 'src/api/store'

import UserSelect from '@/components/UserSelect.vue'

const fExecutor = ref(null)
const fDatetime = ref(null)
const hideClosed = ref(true)

const items = ref(null)
const updateList = ({executor, datetime} = {}) => {
  items.value = null

  fExecutor.value = executor
  fDatetime.value = datetime

  Loading.show({message: 'Формируем отчёт...'})
  axios.get('/api/report/tasks', {
    params: {
      executor_id: fExecutor.value?.id,
      datetime: fDatetime.value,
      hide_closed: hideClosed.value
    }})
  .then(response => items.value = response.data)
  .finally(() => Loading.hide())
}

onMounted(() => updateList())

watch(hideClosed, () => updateList({executor: fExecutor.value, datetime: fDatetime.value}))
</script>
