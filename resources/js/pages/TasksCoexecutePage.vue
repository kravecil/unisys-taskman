<template>
  <q-page>
      <TaskFilter />
      <TaskList view="coexecute" />
  </q-page>
</template>

<script setup>
import TaskFilter from '@/components/TaskFilter.vue'
import TaskList from '@/components/TaskList.vue'
</script>