<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="bg-grey-4 q-pa-xl" style="width: 700px">
      <div class="text-h6">Зарегистрировать исходящий документ</div>
      <q-form ref="formRef" class="q-my-md q-gutter-md" @submit="submit">

      <q-card-section class="q-gutter-md">
        <q-input v-model="formData.number" dense outlined type="number" label="Номер документа"
          :rules="[val => !!val || 'Обязательное поле']"/>
        <q-input v-model="formData.description" autofocus dense outlined type="textarea" label="Описание" />
        <input-filter v-model="formData.executor_id" label="Исполнитель" api-path="/api/users" caption="shortname" />
        <input-filter v-model="formData.signer_id" label="Подписант" api-path="/api/users" caption="shortname" />
        <input-filter v-model="formData.receiver" label="Получатель" api-path="/api/outgoing-documents/receivers" />

      </q-card-section>
      <q-card-actions>
        <q-btn type="submit" outline label="Зарегистрировать" color="deep-orange-10" />
        <q-btn outline label="Отмена" @click="onCancelClick" />
      </q-card-actions>
    </q-form>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useDialogPluginComponent } from 'quasar'
// import _ from 'lodash'

// import { api } from 'src/boot/axios'
import { outgoingDocuments } from '@/api/store'

import InputFilter from '@/components/InputFilter.vue'

defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const formDataDefaults = {
  number: undefined,
  description: undefined,
  executor_id: undefined,
  signer_id: undefined,
  receiver: undefined,
}
const formData = ref(_.cloneDeep(formDataDefaults))

const formRef = ref(null)

onMounted(() => { axios.get('/api/outgoing-documents/last-number')
  .then(response => formData.value.number = response.data)
})

const submit = () => {
  formRef.value.validate()
  .then(() => {
    formData.value.executor_id = formData.value.executor_id?.id ?? undefined
    formData.value.signer_id = formData.value.signer_id?.id ?? undefined

    axios.put('/api/outgoing-documents', formData.value)
    .then(response => {
      outgoingDocuments.value.unshift(response.data)
      onDialogOK()
    })
  })
  
}

function onCancelClick () {
    onDialogCancel()
}
</script>