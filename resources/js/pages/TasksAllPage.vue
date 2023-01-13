<script setup>
import TaskFilter from '@/components/TaskFilter.vue'
import TaskList from '@/components/TaskList.vue'
</script>

<template>
<q-page>
  <TaskFilter />
  <q-toolbar class="bg-grey-8 q-mb-sm text-white">
    <div>Версия для печати (на контроле):</div>&nbsp;
    <router-link class="rl" :to="{name: 'tasksPrint'}">неисполненные по дате</router-link>&nbsp;&vert;&nbsp;
    <router-link class="rl" :to="{name: 'tasksSumPrint'}">неисполненные сумма</router-link>
  </q-toolbar>
  <TaskList view="all" />
</q-page>
</template>

<style lang="sass" scoped>
.rl
  color: $grey-5
</style>