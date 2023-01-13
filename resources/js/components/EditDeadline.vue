<template>
  <div>
    <div class="row q-mt-sm q-gutter-xs">
      <q-chip v-for="(interval, index) in dateIntervals" :key="index"
        clickable size="sm" text-color="white" color="deep-orange-10" :label="interval.label"
        @click="interval.click()" />
    </div>
    <div class="flex q-gutter-md q-py-md items-stretch">
      <q-date
        v-model="datetime"
        mask="YYYY-MM-DD HH:mm"
        today-btn
        flat
        color="deep-orange-10"
      />
      <q-time
        v-model="datetime"
        mask="YYYY-MM-DD HH:mm"
        now-btn
        flat
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { date } from 'quasar'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})
const emit = defineEmits(['update:modelValue', 'close'])

const dateFormat = 'YYYY-MM-DD HH:mm:ss'

const datetime = computed({
  get () {
    return props.modelValue
  },
  set(val) {
    emit('update:modelValue', val)
  }
})

const dateIntervals = [
  {
    label: 'через час',
    click: () => {
      const dateValue = date.addToDate(new Date(), {hours: 1})
      datetime.value = date.formatDate(dateValue, dateFormat)
    }
  },
  {
    label: 'через день',
    click: () => {
      const dateValue = date.addToDate(new Date(), {days: 1})
      datetime.value = date.formatDate(dateValue, dateFormat)
    }
  },
  {
    label: 'завтра до обеда',
    click: () => {
      const dateValue = date.adjustDate(date.addToDate(new Date(), {days: 1}), {hours: 12, minutes: 0})
      datetime.value = date.formatDate(dateValue, dateFormat)
    }
  },
  {
    label: 'в следующий понедельник',
    click: () => {
      const dateInWeek = date.addToDate(new Date(), {days: 7})
      const dayOfWeek = date.getDayOfWeek(dateInWeek)
      const dateValue = date.subtractFromDate(dateInWeek, {days: dayOfWeek - 1})
      datetime.value = date.formatDate(dateValue, dateFormat)
    }
  },
  {
    label: 'через неделю',
    click: () => {
      const dateValue = date.addToDate(new Date(), {days: 7})
      datetime.value = date.formatDate(dateValue, dateFormat)
    }
  },
  {
    label: 'через месяц',
    click: () => {
      const dateValue = date.addToDate(new Date(), {months: 1})
      datetime.value = date.formatDate(dateValue, dateFormat)
    }
  },
]
</script>
