<template>
<q-input v-model="model" dense outlined clearable @update:model-value="update">
  <q-menu ref="popupRef" no-parent-event no-focus>
    <q-list>
      <q-item v-for="item in options" :key="item.id"
        clickable @click="clicked(item)">
        <q-item-section>{{ caption? item[caption] : item }}</q-item-section>
      </q-item>
    </q-list>
  </q-menu>
</q-input>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
// import { api } from 'src/boot/axios'

const props = defineProps([
  'modelValue',
  'api-path',
  'caption'
])
const emit = defineEmits([
  'update:modelValue'
])

const options = ref([])
const popupRef = ref(null)

const model = computed({
  get: () => props.modelValue,
  set: (val) => { 
    emit('update:modelValue', val)
  },
})

let interval
// watch(model, (val) => {
//   clearInterval(interval)
//   if (!!val) {
//     interval = setTimeout(() => {
//       axios.get(props.apiPath, {params: { search: val}})
//     .then(response => {
//       options.value = response.data
//       popupRef.value.show()
//     })
//     }, 500)
//   } else popupRef.value.hide()
// })
const update = (val) => {
  if (!props.apiPath) return
  
  clearInterval(interval)
  if (!!val) {
    interval = setTimeout(() => {
      axios.get(props.apiPath, {params: { search: val}})
    .then(response => {
      options.value = response.data
      popupRef.value?.show()
    })
    }, 500)
  } else popupRef.value.hide()
}

const clicked = (item) => {
  model.value = (props.caption? item[props.caption] : item)
  popupRef.value?.hide()
}
</script>