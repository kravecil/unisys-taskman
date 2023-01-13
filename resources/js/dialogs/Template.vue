<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="dialog q-pa-lg">
      <q-card-section>
      </q-card-section>
      <q-separator inset />
      <q-card-section>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn unelevated label="" color="deep-orange-10" />
        <q-btn unelevated label="Отмена" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useDialogPluginComponent } from 'quasar'

const props = defineProps(['overtime'])
defineEmits([
  ...useDialogPluginComponent.emits
])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent()

function onOKClick () {
}
</script>

<style scoped lang="sass">
.dialog
    width: 800px
</style>