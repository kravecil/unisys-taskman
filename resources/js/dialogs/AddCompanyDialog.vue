<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="bg-grey-4 q-pa-xl" style="">
      <div class="text-h6">Добавить контрагента</div>
      <q-card-section>
        <q-input v-model="name" type="text" label="Наименование" :rules="[val => !!val || 'Обязательное поле']" />
        <q-input v-model="inn" type="text" label="ИНН" />
        <q-input v-model="address" type="textarea" label="Адрес" />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn color="primary" label="Добавить" @click="onOKClick" />
        <q-btn color="primary" label="Отмена" @click="onDialogCancel" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent } from 'quasar'

// import { api } from 'src/boot/axios'

defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const name = ref('')
const inn = ref('')
const address = ref('')

function onOKClick () {
    axios.put('/api/partners', {
      name: name.value,
      inn: inn.value,
      address: address.value
    })
    .then(response => onDialogOK(response.data))
}
</script>