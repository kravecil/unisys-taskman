<template>
<q-page>
  <div v-if="outgoingDocuments.length == 0" class="absolute-center text-white">Зарегистрированные исходящие отсутствуют</div>
  <q-toolbar class="bg-grey-9 text-white q-py-md q-px-lg">
    <q-input v-model="search" dense label="быстрый поиск" type="text" filled square class="col-5"
      bg-color="grey-8" color="white" input-class="text-white" label-color="grey-5" />
  </q-toolbar>
  
  <q-infinite-scroll
    class="q-gutter-y-sm col q-px-lg"
    @load="onScrollLoad"
    ref="infiniteScrollRef"
    :offset="200">

    <q-item v-for="document in outgoingDocuments" :key="document.id"
      class="bg-grey-3 rounded-borders flex">
      <q-item-section side class="text-grey-10" style="width: 60px">{{ document.number}}</q-item-section>
      <q-separator vertical spaced />
      <q-item-section>
        <q-item-label lines="2" class="text-deep-orange-10">{{ document.description }}</q-item-label>
        <q-item-label v-if="document.receiver" caption lines="2">Кому: {{ document.receiver }}</q-item-label>
      </q-item-section>
      <q-item-section>
        <q-item-label caption>{{ document.signer?.shortname}}</q-item-label>
        <q-item-label v-if="document.executor" caption>Исп.: {{ document.executor?.shortname}}</q-item-label>
      </q-item-section>
      <q-item-section side>
        <q-item-label caption class="text-italic">{{ document.created_at }}</q-item-label>
      </q-item-section>
      <!-- <q-item-section side>
        <q-btn flat dense rounded icon="delete" @click="deleteDocument(document)" />
      </q-item-section> -->
    </q-item>

    <template v-slot:loading>
      <div class="text-center q-my-md">
        <q-spinner-dots color="orange-10" size="40px" />
      </div>
    </template>
  </q-infinite-scroll>
</q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Dialog } from 'quasar'
// import { api } from 'src/boot/axios'


import { outgoingDocuments } from '@/api/store'

const infiniteScrollRef = ref(null)
let page = 1
let interval
const search = ref(null)

onMounted(() => resetData())

const showDrawer = () => {
  if (drawerMini.value) {
    formData.value = _.cloneDeep(formDataDefaults)
    axios.get('/api/outgoing-documents/last-number')
    .then(response => formData.value.number = response.data)
    drawerMini.value = false
  }
}

const resetData = () => {
  outgoingDocuments.value = []
  page = 1

  infiniteScrollRef.value.reset()
  infiniteScrollRef.value.poll()
  infiniteScrollRef.value.resume()
}

const onScrollLoad = (index, done) => {
  axios.get('/api/outgoing-documents?page=' + String(page), { params: { search: search.value } })
  .then(response => {
    if (response.data.length > 0) {
        for (const item of response.data) outgoingDocuments.value.push(item)
        page++
      } else infiniteScrollRef.value?.stop()

      done()
  })
}

const deleteDocument = (document) => {
  Dialog.create({
    title: 'Удаление исходящего документа',
    message: `Удалить документ номер <b>${document.number}</b>?`,
    html: true,
    cancel: true,
  })
  .onOk(() => {
    axios.delete(`/api/outgoing-documents/${document.id}`)
    .then(() => {
      const pos = outgoingDocuments.value.findIndex(item => item.id == document.id)
      // console.log(pos); return
      if (pos >= 0) outgoingDocuments.value.splice(pos, 1)
    })
  })
}

watch(search, (val, old) => {
  clearInterval(interval)
  interval = setTimeout(() => resetData(), 500)
})
</script>
