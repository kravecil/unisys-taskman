<script setup>
import { filter, filterDefaults } from '@/api/store'
import InputDense from '@/components/InputDense.vue'

const reset = () => {
  filter.value = Object.assign({}, filterDefaults)
}
</script>

<template>
<q-toolbar class="bg-grey-9 q-pa-sm text-white toolbar__dense" style="position: sticky; top: 50px; z-index: 1;">
  <div class="row col justify-end q-gutter-md">
    <!-- <q-icon name="filter_alt" size="md" />
    <div class="text-h6">Фильтр</div> -->

    <q-input v-model="filter.filterUser" type="text" label="исполнитель" dense filled color="white" input-class="text-white"
      :bg-color="filter.filterUser? 'deep-orange-10' : 'grey-8'" label-color="grey-5" square clearable style="width: 150px" />

    <q-input v-model="filter.search" type="text" label="текст поручения" dense filled color="white" input-class="text-white"
      :bg-color="!!filter.search? 'deep-orange-10' : 'grey-8'" label-color="grey-5" square clearable style="width: 150px" />

    <!-- <input-dense v-model="filter.search" placeholder="текст поручения" /> -->
    
    <q-toggle v-model="filter.showAllTasks" unchecked-icon="visibility_off" size="xs" class="text-dense"
      checked-icon="visibility" label="Закрытые поручения" dense />
    <q-toggle v-model="filter.showInProgress" unchecked-icon="visibility_off" size="xs" class="text-dense"
      checked-icon="visibility" label="В работе" dense />
    <q-toggle v-model="filter.showExpired" unchecked-icon="visibility_off" size="xs" class="text-dense"
      checked-icon="visibility" label="Просроченные" dense />
    <q-btn-toggle
      v-model="filter.importance"
      color="grey-7"
      no-caps
      size="sm"
      toggle-color="primary"
      :options="[
        {label: 'Все', value: null},
        {label: 'Обычные', value: 0},
        {label: 'Важные (К)', value: 1},
        {label: 'Очень важные (КУ)', value: 2},
      ]"
    />

    <q-btn outline size="sm" color="white" label="Сброс" no-caps @click="reset()" />
  </div>
</q-toolbar>
</template>