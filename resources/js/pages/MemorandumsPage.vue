<template>
<q-page class="column">
  <DocumentFilter class="" />
  <DocumentsList view="memorandums" class="q-pa-md col" />
</q-page>
</template>

<script setup>
import DocumentFilter from '@/components/DocumentFilter.vue'
import DocumentsList from '@/components/DocumentsList.vue'
</script>
