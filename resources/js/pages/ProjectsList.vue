<template>
<q-page padding>
  <q-tabs
    v-model="tab"
    class="bg-grey-3"
    align="left" no-caps>
    <q-tab name="create" label="Мои проекты" />
    <q-tab name="execute" label="Выполняю проекты" />
  </q-tabs>
  <q-tab-panels v-model="tab" animated class="bg-page">
    <q-tab-panel name="create">
      <ProjectsListTable :isMine="true" />
    </q-tab-panel>
    <q-tab-panel name="execute">
      <ProjectsListTable :isMine="false" />
    </q-tab-panel>
  </q-tab-panels>
  
</q-page>
</template>

<script setup>
import { ref } from 'vue'
import ProjectsListTable from '@/components/ProjectsListTable.vue'

const tab = ref('create')
</script>
