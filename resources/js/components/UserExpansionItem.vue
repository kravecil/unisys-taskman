<template>
  <q-expansion-item
    class="q-ma-none col"
    expand-separator>
    <div class="column q-py-md"
      style="height: 300px; max-height: 300px;">
      <user-select
        @select="selected($event)"/>
    </div>
    <template v-slot:header class="">
      <q-item class="col">
        <q-item-section avatar>
          <q-icon name="person" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ label }}</q-item-label>
          <q-item-label>
            <q-chip v-for="(executor,index) in model" :key="executor.id"
              class="q-mx-xs q-px-md"
              color="deep-orange-10"
              text-color="white"
              removable
              square
              size="sm"
              :label="executor.shortname"
              @remove="model.splice(index, 1)" />
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </q-expansion-item>
</template>

<script setup>
import { computed } from 'vue'
import UserSelect from '@/components/UserSelect.vue'

const props = defineProps(['users', 'label'])
const emit = defineEmits(['update:users'])

const model = computed({
  get() {
    return props.users
  },
  set (val) {
    emit('update:users', val)
  }
})
  
const selected = (e) => {
  if (!model.value.some(item => item.id == e.id)) model.value.push(e)
}
</script>
