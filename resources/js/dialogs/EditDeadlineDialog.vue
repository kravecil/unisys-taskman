<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="bg-grey-4" style="max-width: 800px; width: 800px;">
      <q-card-section class="bg-grey-8 text-white">
        <div class="text-h5">Выбор срока поручения</div>
      </q-card-section>
      <q-card-section class="align-center">
        <EditDeadline v-model="deadline" />
      </q-card-section>
      <q-card-actions align="right" class="bg-grey-5">
        <q-btn color="primary" label="Применить именения" @click="onOKClick" />
        <q-btn color="primary" label="Отмена" @click="onDialogCancel" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent } from 'quasar'

import EditDeadline from '@/components/EditDeadline.vue'

defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const deadline = ref('')


function onOKClick () {
    if (deadline.value) onDialogOK ( deadline.value )
}
</script>