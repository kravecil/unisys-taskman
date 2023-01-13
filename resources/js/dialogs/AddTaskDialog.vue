<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent } from 'quasar'

// import { api } from 'src/boot/axios'

import AddTask from '@/components/AddTask.vue'

const props = defineProps(['project' ,'task'])
defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const name = ref('')
const description = ref('')

const onOKClick = (e) => {
    // api.put('/api/companies', {name: name.value, description: description.value })
    // .then(response => onDialogOK(response.data))
    onDialogOK(e)
}
const onCancelClick = () => { onDialogCancel() }
</script>

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
    <AddTask @close="onCancelClick" @done="onOKClick" :projectSelected="props.project" :taskParent="task" />
  </q-dialog>
</template>