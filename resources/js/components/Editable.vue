<template>
  <div class="editable row items-center">
    <div>
      {{ label? label:'&lt;пусто&gt;' }}
      <q-popup-proxy
        ref="popupRef"
        no-parent-event
        class="q-pa-lg"
        @before-show="labelRaw=label"
      >
        <form class="row q-gutter-md" @submit="submit">
          <!-- <q-input
            v-model="labelRaw"
            clearable
            outlined
          /> -->
          <input-filter
            v-model="labelRaw"
            :api-path="apiPathFilter"
            clearable
            outlined
            autofocus
          />
          <q-btn v-if="innerNumber" icon="cached" flat dense @click="getLastInnerNumber"/>
          <q-btn
            icon="done"
            color="deep-orange-10"
            unelevated
            type="submit"
          />
        </form>
      </q-popup-proxy>
    </div>
    <q-btn
      v-if="editable"
      class="editable__button"
      flat
      dense
      round
      icon="edit"
      size="sm"
      @click="popupRef?.show()"
    />
  </div>
</template>

<script setup>
/* ***************************************************************************************************** */
import { ref } from 'vue'
import { Notify } from 'quasar'
// import { api } from 'src/boot/axios'

import InputFilter from '@/components/InputFilter.vue'

const popupRef = ref(null)
const labelRaw = ref(null)

const props = defineProps({
  label: {
    type: [String, Number],
  },
  editable: {
    type: Boolean,
    default: false,
  },
  innerNumber: {
    type: Boolean,
    default: false,
  },
  column: {
    type: String
  },
  apiPath: {
    type: String
  },
  apiPathFilter: {
    type: String
  }
})
const emit = defineEmits(['done'])

const getLastInnerNumber = () => {
  axios.get('/api/documents/last-inner-number')
  .then(response => labelRaw.value = response.data)
}

const submit = () => {
  axios.post(props.apiPath, { column: props.column, value: labelRaw.value})
  .then(() => {
    popupRef.value?.hide()
    emit('done')
    Notify.create({message: 'Значение успешно изменено'})
  })
}
</script>

<style lang="sass" scope>
/* **************************************************************************************************** */
.editable
  &:hover
    .editable__button
      visibility: visible

.editable__button
  visibility: hidden
</style>