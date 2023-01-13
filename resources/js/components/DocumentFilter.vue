<template>
<div class="bg-grey-9 text-white" style="position: sticky; top: 50px; z-index: 1;">
  <q-toolbar class="q-pa-md q-gutter-md">
    <div class="row self-stretch items-center q-px-md q-gutter-x-md rounded-borders" style="border: 1px solid grey">
      <div v-if="!periodDocuments" class="text-grey-6">выберите период</div>
      <div v-else class="text-weight-bold row">
        Начало:&nbsp;
        <div class="text-weight-light">{{ filterDocuments.dateFrom }}</div>
        &nbsp;-&nbsp;конец:&nbsp;
        <div class="text-weight-light">{{ filterDocuments.dateTo }}</div>
      </div>
      <q-btn v-if="periodDocuments" icon="close" dense round size="xs" unelevated @click="periodDocuments = null" color="grey-8" />
      <q-btn icon="event" dense flat>
        <q-popup-proxy><q-date v-model="periodDocuments" minimal range /></q-popup-proxy>
      </q-btn>
    </div>
    
    <q-input v-model="filterDocuments.search" type="text" label="быстрый поиск" dense filled color="white" input-class="text-white"
      :bg-color="!!filterDocuments.search? 'deep-orange-10' : 'grey-8'" label-color="grey-5" square clearable />
    
    <q-toggle v-model="filterDocuments.showRead" unchecked-icon="visibility_off"
      checked-icon="visibility" label="Показывать ознакомленные" dense />

    <q-btn outline color="white" label="Сброс" no-caps @click="reset()" />
  </q-toolbar>
  <q-toolbar inset>
    Отсортировать:
    <q-chip icon="expand_more" label="по номеру" dense clickable :class="{'bg-deep-orange-10': filterDocuments.sortBy=='number'}"
      @click="filterDocuments.sortBy='number'"/>
    <q-chip icon="expand_more" label="по дате" dense clickable :class="{'bg-deep-orange-10': filterDocuments.sortBy=='issued_at'}"
      @click="filterDocuments.sortBy='issued_at'" />
  </q-toolbar>
</div>

</template>

<script setup>
import { ref, watch } from 'vue'
import { filterDocuments, filterDocumentsDefaults, periodDocuments } from '@/api/store'
// import _ from 'lodash'

const sortHow = ref(null)

watch(periodDocuments, (val) => {
  filterDocuments.value.dateFrom = typeof(val)==='object'? val?.from : val
  filterDocuments.value.dateTo = typeof(val)==='object'? val?.to : val
})

const reset = () => {
  filterDocuments.value = _.cloneDeep(filterDocumentsDefaults)
  periodDocuments.value = null
}
</script>
