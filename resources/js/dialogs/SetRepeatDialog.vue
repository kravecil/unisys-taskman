<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent } from 'quasar'

defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

const interval = ref(0)
const factor = ref({ label: 'дни', value: 1440 })

function onOKClick () {
  onDialogOK(interval.value * factor.value.value)
}
</script>

<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="dialog q-pa-lg">
      <q-card-section>
        <div class="text-h6">Установить интервал повторений</div>
      </q-card-section>
      <q-separator inset />
      <q-card-section>
        <div class="row q-gutter-x-md">
          <q-input
            v-model="interval"
            dense
            filled
            class="col"
          />
          <q-select
            v-model="factor"
            dense
            filled
            :options="[
              { label: 'минуты', value: 1 },
              { label: 'часы', value: 60 },
              { label: 'дни', value: 1440 },
              { label: 'недели', value: 10080 },
            ]"
            class="col-5"
          />
        </div>
        <div class="row items-baseline q-gutter-x-sm q-pt-md">
          <q-icon name="warning" color="orange-10" />
          <span class="text-grey-8 text-dense">Пустое значение либо "0" отключают повторения</span>
        </div>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn unelevated label="Применить" color="deep-orange-10" @click="onOKClick" />
        <q-btn unelevated label="Отмена" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<style scoped lang="sass">
.dialog
    // width: 800px
</style>