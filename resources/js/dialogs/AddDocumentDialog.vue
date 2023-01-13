<template>
  <q-dialog ref="dialogRef" persistent @hide="onDialogHide">
    <!-- <q-card class="bg-grey-4 q-pa-xl" style="">
      <div class="text-h6">Добавить организацию</div>
      <q-card-section>
        <q-input v-model="name" type="text" label="Наименование" />
        <q-input v-model="description" type="textarea" label="Описание" />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn color="primary" label="Добавить" @click="onOKClick" />
        <q-btn color="primary" label="Отмена" @click="onDialogCancel" />
      </q-card-actions>
    </q-card> -->
    <AddDocument @close="onCancelClick" @done="onOKClick" @cancel="onDialogCancel"/>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useDialogPluginComponent } from 'quasar'

import AddDocument from '@/components/AddDocument.vue'

const router = useRouter()

defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const onOKClick = (e) => {
    // api.put('/api/companies', {name: name.value, description: description.value })
    // .then(response => onDialogOK(response.data))
    // router.push({name: 'document', params: {id: e.id}})
    onDialogOK(e)
}
const onCancelClick = () => { onDialogCancel() }
</script>