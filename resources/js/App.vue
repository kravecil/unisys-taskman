<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Dialog } from 'quasar'

import URL from '@/api/hosts'
import { user } from '@/api/auth'
// import { api } from 'src/boot/axios'
import { counters } from '@/api/store'
import { getCounters } from '@/api/util'
import * as policy from '@/api/policy'

import AddTaskDialog from '@/dialogs/AddTaskDialog.vue'
import AddProjectDialog from '@/dialogs/AddProjectDialog.vue'
import AddDocumentDialog from '@/dialogs/AddDocumentDialog.vue'
import RegisterOutgoingDialog from '@/dialogs/RegisterOutgoingDialog.vue'

onMounted(() => getCounters())

// watch (showAllTasks, () => getCounters())

const router = useRouter()

  const userName = computed(() => [user.value.lastname, user.value.firstname, user.value.middlename]
    .join(' '))
  const drawerLeft = ref(true)

  const goHome = () => window.location = URL.home
  const showAddTaskDialog = () => Dialog.create({component: AddTaskDialog})
    .onOk(e => router.push({name: 'task', params: {id: e[0]?.id}}))
  const showAddProjectDialog = () => Dialog.create({component: AddProjectDialog})
  const showAddDocumentDialog = () => Dialog.create({component: AddDocumentDialog})
    .onOk(e => router.push({name: 'document', params: {id: e.id}}))
  const showRegisterOutgoingDialog = () => Dialog.create({component: RegisterOutgoingDialog})
</script>

<template>
<q-layout view="hHh Lpr fFf">
  <q-header elevated class="bg-grey-10 print-hide">
      <q-toolbar>
        <q-btn class="lt-md" flat dense icon="menu" @click="drawerLeft = true" />

        <q-img src="images/logo.png" fit="contain" width="100px" />
        <q-toolbar-title class="gt-sm text-weight-thin justify-center">
          <a class="cursor-pointer text-weight-bold" @click="goHome()">
            <q-tooltip class="text-no-wrap">Домашняя страница</q-tooltip>
            ЕИС
          </a>
          Документы и поручения
          <q-badge color="deep-orange-10" text-color="white" label="0.4.12" align="top" /> <!-- ВЕРСИЯ -->
        </q-toolbar-title>

        <span class="text-deep-orange-6 text-weight-bold">{{ userName }}</span>
      </q-toolbar>
    </q-header>

    <q-drawer
      class="q-pa-md bg-grey-3 print-hide"
      side="left"
      v-model="drawerLeft"
      :width="300" >
      <q-list padding dense>
        <q-item-label header class="items-center row">
          <span class="col">Поручения</span>
          <div class="q-gutter-x-sm">
            <q-btn outline dense color="deep-orange-10" size="sm" icon="add" @click.prevent="showAddTaskDialog" round>
              <q-tooltip>Добавить поручение</q-tooltip>
            </q-btn>
            <q-btn outline dense color="deep-orange-10" size="sm" icon="post_add" @click.prevent="showAddProjectDialog" round>
              <q-tooltip>Создать проект</q-tooltip>
            </q-btn>
          </div>
          
        </q-item-label>

        <q-item clickable v-ripple
          active-class="bg-grey-4"
          :to="{name: 'create'}">
          <q-item-section>Назначил</q-item-section>
          <q-item-section side v-if="counters.creating?.total > 0">
            {{ counters.creating?.total }}
            <q-badge v-if="counters.creating?.unread > 0"
              floating transparent color="red">
              {{ counters.creating?.unread }}
            </q-badge>
          </q-item-section>
        </q-item>
        <q-item clickable v-ripple
          active-class="bg-grey-4"
          :to="{name: 'execute'}">
          <q-item-section>Выполняю</q-item-section>
          <q-item-section side v-if="counters.executing?.total > 0">
            {{ counters.executing?.total }}
            <q-badge v-if="counters.executing?.unread > 0"
              floating transparent color="red">
              {{ counters.executing?.unread }}
            </q-badge>
          </q-item-section>
        </q-item>
        <q-item clickable v-ripple
          active-class="bg-grey-4"
          :to="{name: 'coexecute'}">
          <q-item-section>Помогаю</q-item-section>
          <q-item-section side v-if="counters.coexecuting?.total > 0">
            {{ counters.coexecuting?.total }}
            <q-badge v-if="counters.coexecuting?.unread > 0"
              floating transparent color="red">
              {{ counters.coexecuting?.unread }}
            </q-badge>
          </q-item-section>
        </q-item>
        <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'control'}">
          <q-item-section>Наблюдаю</q-item-section>
          <q-item-section side v-if="counters.controlling?.total > 0">
            {{ counters.controlling?.total }}
            <q-badge v-if="counters.controlling?.unread > 0"
              floating transparent color="red">
              {{ counters.controlling?.unread }}
            </q-badge>
          </q-item-section>
        </q-item>
        <q-item clickable v-ripple class="text-deep-orange-10" active-class="bg-grey-4" :to="{name: 'projects'}">
          <q-item-section>Проекты</q-item-section>
        </q-item>
        <q-item clickable v-ripple class="text-deep-orange-10" active-class="bg-grey-4" :to="{name: 'executorsList'}">
          <q-item-section>Исполнители</q-item-section>
        </q-item>
        <q-item clickable v-ripple
          class="text-deep-orange-10"
          active-class="bg-grey-4"
          :to="{name: 'notes'}">
          <q-item-section>Заметки</q-item-section>
          <q-item-section side v-if="counters.notes?.total > 0">
            {{ counters.notes?.total }}
          </q-item-section>
        </q-item>
        <q-item v-if="policy.isDocumentologist.value"
          dense clickable v-ripple active-class="bg-grey-4" :to="{name: 'tasksAll'}">
          <q-item-section>Контроль поручений</q-item-section>
        </q-item>

        <q-separator spaced inset />
        
        <q-item-label header class="items-center row">
          <span class="col">Документы</span>
          <q-btn class="" v-if="policy.isDocumentologist.value" color="deep-orange-10"
            outline dense size="sm" icon="add" @click.prevent="showAddDocumentDialog" round>
            <q-tooltip>Зарегистрировать документ</q-tooltip>
          </q-btn>
        </q-item-label>

        <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'mails'}">
          <q-item-section>Входящие письма</q-item-section>
          <q-item-section side v-if="counters.mails?.total > 0">{{ counters.mails?.total }}</q-item-section>
        </q-item>
        <q-item v-if="policy.isDocumentologist.value" clickable v-ripple active-class="bg-grey-4" :to="{name: 'outgoingMails'}">
          <q-item-section>Исходящие письма</q-item-section>
        </q-item>
        <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'decrees'}">
          <q-item-section>Приказы</q-item-section>
          <q-item-section side v-if="counters.decrees?.total > 0">{{ counters.decrees?.total }}</q-item-section>
        </q-item>
        <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'ksDecrees'}">
          <q-item-section>Приказы КЗ</q-item-section>
          <q-item-section side v-if="counters.ksDecrees?.total > 0">{{ counters.ksDecrees?.total }}</q-item-section>
        </q-item>
        <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'orders'}">
          <q-item-section>Распоряжения</q-item-section>
          <q-item-section side v-if="counters.orders?.total > 0">{{ counters.orders?.total }}</q-item-section>
        </q-item>
        <q-item disable clickable v-ripple active-class="bg-grey-4" :to="{name: 'memorandums'}">
          <q-item-section>Служебные записки</q-item-section>
          <q-item-section side v-if="counters.memorandums?.total > 0">{{ counters.memorandums?.total }}</q-item-section>
        </q-item>
        <!-- <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'protocols'}">
          <q-item-section>Протоколы</q-item-section>
          <q-item-section side v-if="counters.protocols?.total > 0">{{ counters.protocols?.total }}</q-item-section>
        </q-item> -->
        <q-item disable clickable v-ripple active-class="bg-grey-4" :to="{name: 'docs???'}">
          <q-item-section>Договоры</q-item-section>
          <!-- <q-item-section side v-if="counters.notes?.total > 0">{{ counters.notes?.total }}</q-item-section> -->
        </q-item>
        <q-item v-if="policy.isDocumentologist.value" clickable v-ripple active-class="bg-grey-4" :to="{name: 'poas'}">
          <q-item-section>Доверенности</q-item-section>
        </q-item>
        <q-item clickable v-ripple active-class="bg-grey-4" :to="{name: 'miscdocuments'}">
          <q-item-section>Прочие документы</q-item-section>
          <q-item-section side v-if="counters.miscdocuments?.total > 0">{{ counters.miscdocuments?.total }}</q-item-section>
        </q-item>
      </q-list>

    </q-drawer>

    <!-- <q-img no-spinner
      class="fixed-center bg-image fit" src="images/bg-documents.jpg"
      fit="cover"
    /> -->
    
  <q-page-container class="print">
    <router-view v-slot="{ Component }" class="page-container">
      <transition enter-active-class="animate__animated animate__fadeIn">
        <component :is="Component" />
      </transition>
    </router-view>
  </q-page-container>
</q-layout>
</template>

<style lang="sass" scoped>
@import 'quasar/src/css/variables.sass'
.page-container
  background-color: $grey-4

.bg-image 
  z-index: -1
  filter: grayscale(100%)
@media print
  .print
    padding: 0 !important
  :deep(.q-drawer)
    display: none !important
</style>

<!-- <style scoped>
:deep(.q-drawer) {
  width: 10px !important
}
</style> -->