<template>
<div class="row">
  <!-- <div
    class="row col absolute-center text-grey-5"
    v-if="documents?.length == 0">
    Список документов пуст
  </div> -->

  <!-- <q-scroll-area class="col row"> -->
  <q-infinite-scroll
    class="q-gutter-y-sm col"
    @load="onScrollLoad"
    ref="infiniteScrollRef"
    :offset="200">

    <q-item v-for="document in documents" :key="document.id"
      class="rounded-borders q-pa-md list-item"
      clickable v-ripple
      :to="{name: 'document', params: {id: document.id}}">

      <!-- НОМЕР ДОКУМЕНТА -->
      <q-item-section side class="col-1" style="word-break: break-all;">{{ document.number}}</q-item-section>
      <q-separator spaced vertical />

      <!-- ДАТА, РЕГИСТРАТОР, ОПИСАНИЕ -->
      <q-item-section class="col">
        <q-item-label caption>
          <b>{{ document.issued_at}}</b><!-- &nbsp;|&nbsp;{{ document.issuer?.shortname }} -->
        </q-item-label>
        <q-item-label v-if="document.body" :lines="2" class="text-deep-orange-10">{{ document.body }}</q-item-label>
        <q-item-label v-if="!document.body" caption>&lt;Описание отсутствует&gt;</q-item-label>
      </q-item-section>

      <!-- СВЕДЕНИЯ -->
      <q-item-section class="col">
        <q-item-label v-if="document.kind" caption :lines="2">
          {{ document.kind }}
        </q-item-label>
        <q-item-label v-if="document.sent_at || document.sent_by" caption :lines="2">
          Отправлено: <i>{{ document.sent_by}} {{ date.formatDate(document.sent_at ?? '', 'DD.MM.YYYY') }}</i>
        </q-item-label>
        <q-item-label v-if="document.partner" caption :lines="2">
          {{ document.partner }}
        </q-item-label>
        <q-item-label v-if="document.is_kadr_salary" caption :lines="2" class="text-brown-10 text-weight-bold">
          к/зарпл
        </q-item-label>
        <q-item-label v-if="document.executor" caption :lines="2">
          {{ document.type?.name=='poa'? 'Представление интересов:':'Исполнитель:'}}
          <i>{{ JSON.parse(document.executor)?.fullname}}</i>
        </q-item-label>
        <q-item-label v-if="document.signer" caption :lines="2">
          Подписант: <i>{{ JSON.parse(document.signer)?.fullname}}</i>
        </q-item-label>
        <q-item-label v-if="document.signer_manual" caption :lines="2">
          Подписант: <i>{{ document.signer_manual }}</i>
        </q-item-label>
      </q-item-section>

      <!-- ВЛОЖЕНИЯ -->
      <q-item-section class="col-1" side :inset-level="1">
        <q-icon v-if="document.attachments.length > 0" name="attach_file" size="xs" />
      </q-item-section>

      <q-badge v-if="document.is_unread"
        rounded color="deep-orange-10" class="absolute-top-left" label="!"/>
    </q-item>  

    <template v-slot:loading>
      <div class="text-center q-my-md">
        <q-spinner-dots color="orange-10" size="40px" />
      </div>
    </template>
  </q-infinite-scroll>
  <!-- </q-scroll-area> -->
</div>

</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { date } from 'quasar'
// import { api } from 'src/boot/axios'
import { documents, filterDocuments } from '@/api/store'

const props = defineProps(['view'])

const infiniteScrollRef = ref(null)

onMounted(() => resetData())

let page = 1
const onScrollLoad = (index, done) => {
  axios.get('/api/documents/?page=' + String(page),
  {
    params: {
      view: props.view,
      search: filterDocuments.value.search,
      showRead: filterDocuments.value.showRead,
      dateFrom: filterDocuments.value.dateFrom,
      dateTo: filterDocuments.value.dateTo,
      sortBy: filterDocuments.value.sortBy,
      sortHow: filterDocuments.value.sortHow,
    }
  })
    .then((response) => {
      if (response.data.length > 0) {
        for (const item of response.data) documents.value.push(item)
        page++
      } else infiniteScrollRef.value?.stop()

      done()
    })
}
const resetData = () => {
  documents.value = []
  page = 1

  infiniteScrollRef.value.reset()
  infiniteScrollRef.value.poll()
  infiniteScrollRef.value.resume()
}

let interval
watch(filterDocuments, () => {
  clearInterval(interval)
  interval = setTimeout(() => resetData(), 500)
}, {deep: true})
</script>

<style lang="sass" scoped>
.list-item
  border: 1px solid #eee
  background-color: #fffb
</style>