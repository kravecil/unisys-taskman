<template>
  <q-page>
      <TaskFilter />
      <TaskList view="notes" />
  </q-page>
</template>

<script setup>
import TaskList from '@/components/TaskList.vue'
import TaskFilter from '@/components/TaskFilter.vue'
</script>