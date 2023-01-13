<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card style="max-width: 1170px; width: 800px;">
      <q-card-section class="bg-grey-8 text-white">
        <div class="text-h5">Выбор сотрудников</div>
        <div class="text-subtitle1">{{ title[view] }}</div>
      </q-card-section>
      <q-splitter
        v-model="splitterModel"
        style="height: 400px"
      >
        <template v-slot:before>
          <div class="column q-pa-md q-gutter-md" style="height: 100%">
            <q-btn label="Очистить список" @click="rawUsers=[]" outline color="deep-orange-10" />
            <q-scroll-area class="col column q-pa-md">
              <q-item v-for="(user,index) in rawUsers" :key="user.id"
                class="q-my-xs rounded-borders"
                style="border:1px solid #aaaaaa"
                dense>
                <q-item-section avatar>
                  <q-icon color="grey-9" name="person" />
                </q-item-section>
                <q-item-section class="q-py-xs">
                  <q-item-label>{{ user.fullname }}</q-item-label>
                  <q-item-label caption :lines="2">
                    {{ user.departments?.number }} {{ user.departments?.title }}
                  </q-item-label>
                </q-item-section>
                <q-item-section top side>
                  <q-btn dense flat round icon="close" size="sm"
                    @click="removeUser(index)"/>
                </q-item-section>
              </q-item>
            </q-scroll-area>
          </div>
        </template>
        <template v-slot:after>
          <div class="flex q-pa-md" style="height: 100%">
            <UserSelect @select="insertUser"/>
          </div>
        </template>
      </q-splitter>
      <q-card-actions align="right" class="bg-grey-5">
        <q-btn color="primary" label="Применить именения" @click="onOKClick" />
        <q-btn color="primary" label="Отмена" @click="onDialogCancel" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent, Notify } from 'quasar'
import UserSelect from '@/components/UserSelect.vue'

const props = defineProps({
  users: Array,
  view: String
})

defineEmits([
  ...useDialogPluginComponent.emits
])

const rawUsers = ref([])
for (const user of props.users) rawUsers.value.push(user)

const title = {
  'creators': 'Постановщики',
  'executors': 'Исполнители',
  'coexecutors': 'Соисполнители',
  'controllers': 'Наблюдатели',
  'mailing': 'Получатели рассылки',
}

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()
// dialogRef      - Vue ref to be applied to QDialog
// onDialogHide   - Function to be used as handler for @hide on QDialog
// onDialogOK     - Function to call to settle dialog with "ok" outcome
//                    example: onDialogOK() - no payload
//                    example: onDialogOK({ /*.../* }) - with payload
// onDialogCancel - Function to call to settle dialog with "cancel" outcome

const splitterModel = ref(50)

const insertUser = (user) => {
  const pos = rawUsers.value.findIndex(item => item.id == user.id)
  if (pos < 0) rawUsers.value.push(user)
}

const removeUser = (pos) => {
  rawUsers.value.splice(pos, 1)
}

function onOKClick () {
  // console.log(props.view);
  // console.log(rawUsers.value);
  // return
  // if (rawUsers.value?.length > 0) {
    onDialogOK({
      view: props.view,
      users: rawUsers.value
    })
  // } else {
  //   Notify.create({
  //     type: 'negative',
  //     message: 'Ошибка',
  //     caption: 'Выберите не менее одного пользователя'
  //   })
  // }
  
}
</script>