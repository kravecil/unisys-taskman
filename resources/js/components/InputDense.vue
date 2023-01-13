<script setup>
import { computed } from 'vue'

const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue'])

const model = computed({
  get() {
    return props.modelValue
  },
  set(val) {
    emit('update:modelValue', val)
  }
})
</script>

<template>
<input
  v-model="model"
  type="text"
  class="el text-dense"
/>
</template>

<style scoped lang="sass">
@import 'quasar/src/css/variables.sass'
.el
  background-color: $grey-6
  outline: none
  border: 0
  box-shadow: none
  padding: 0 1em

  &::placeholder
    color: $grey-3
</style>