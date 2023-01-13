<template>
<div class="row items-center q-py-sm no-warp">
  <!-- <pdf /> -->
  <q-icon size="xs" name="attach_file" />
  <q-btn flat dense round size="xs" icon="download" @click="onDownload" />
  <q-btn v-if="isPDF" flat dense round size="xs" icon="search" color="deep-orange-10" @click="onDownload(false)" />
  <div class="text-caption ellipsis" style="width:200px">
    <!-- {{ String(attachment.name).substring(0,30) }}... -->
    {{ attachment.name }}
    
    <q-tooltip>
      {{attachment.name}}
    </q-tooltip>
  </div>
  <!-- <q-btn dense flat icon="search" size="sm" /> -->
  <pdf-viewer-dialog v-if="blob" :src="blob" @close="blob = undefined" />
</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog } from 'quasar'
// import { api } from 'src/boot/axios'
import * as func from '@/api/util'
// import pdf from 'pdfjs'
// import pdf from 'vue-pdf'

import PdfViewerDialog from '@/dialogs/PdfViewerDialog.vue'

const props = defineProps(['taskId', 'documentId', 'attachment'])

const blob = ref(null)

const isPDF = computed(() => {
  const res = String(props.attachment.path).toLowerCase().match(/^.*\.(\w+)$/)
  return res? res[1] == 'pdf' : false
})

const onDownload = (download = true) => {
  let path
  if (props.taskId) path = `/api/tasks/${props.taskId}/attachments/${props.attachment.id}`
  if (props.documentId) path = `/api/documents/${props.documentId}/attachments/${props.attachment.id}`

  axios.get(path, {responseType: 'blob'})
  .then(response => {
    if (download) func.forceFileDownload(response.data, props.attachment.name)
    else {
      blob.value = response.data
    }
  })
}
</script>
