<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide" persistent>
      <q-card class="bg-grey-4 q-pa-xl" style="max-width: 800px; width: 800px;">
        <q-card-section>
          <q-form
            @submit="onSubmit"
          >
          <div class="text-h5">Добавить проект</div>
          <q-input v-model="title" type="text" label="Наименование"
            :rules="[val => !!val || 'Обязательно поле']" />
          <!-- <q-input v-model="description" type="textarea" label="Описание" /> -->
          <div class="row q-pa-md q-gutter-md justify-end">
            <q-btn color="primary" type="submit" label="Добавить" />
            <q-btn color="primary" label="Отмена" @click="onDialogCancel" />
          </div>
          </q-form>
        </q-card-section>
      </q-card>
    
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent } from 'quasar'
// import { api } from 'src/boot/axios'
import { projects } from '@/api/store'

defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const title = ref('')
const description = ref('')

function onSubmit () {
  axios.post('/api/projects', { title: title.value, description: description.value })
  .then(response => {
    projects.value?.unshift(response.data)
    onDialogOK()
  })
}
</script>