<template>
<q-file
  class="col-4"
  v-model="model"
  multiple
  filled
  append
  max-files="10"
  max-total-size="50000000"
  counter
  label="Прикрепите вложения..."
>
  <template v-slot:file="{index, file}">
    <q-chip
      class="bg-grey-5 full-width text-caption text-weight-light"
      square
      icon="attach_file"
      :label="file.name"
      removable
      @remove="model.splice(index, 1)"
    />
  </template>
</q-file>
</template>

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
  }
)
</script>
