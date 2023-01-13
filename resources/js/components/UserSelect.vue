<template>
  <div class="column col q-gutter-y-sm" style="min-width: 300px;">
    <q-input
      class="col-auto bg-grey-4" style="position: sticky; top:0; z-index: 100;"
      filled
      dense
      v-model="search"
      type="search"
      label="Быстрый поиск"
      autofocus
    >
      <template v-slot:prepend>
        <q-icon name="search" />
      </template>
      <template v-slot:append>
        <q-btn
          round
          dense
          flat
          icon="clear"
          size="sm"
          @click="search = null" />
      </template>
    </q-input>

    <div v-if="!hideGroups" class="row">
      <q-chip clickable label="все найденные" size="sm" color="deep-orange-10" text-color="white" @click="groupSelect('all')" />
      <q-chip clickable label="руководители" size="sm" color="deep-orange-10" text-color="white" @click="groupSelect('leaders')" />
      <q-chip clickable label="директоры" size="sm" color="deep-orange-10" text-color="white" @click="groupSelect('directors')" />
      <q-chip clickable label="ген дир" size="sm" color="deep-orange-10" text-color="white" @click="groupSelect('gendir')" />
      <q-chip clickable label="часто используемые" size="sm" color="deep-orange-10" text-color="white" @click="groupSelect('frequent')" />
    </div>

    <div class="col row relative-position">
      <q-virtual-scroll
        class="col"
        style="height: 100%;"
        :items="users">
        <template v-slot="{ item }">
          <q-item
            class="bg-grey-5 q-my-xs rounded-borders"
            dense
            clickable 
            v-ripple
            :key="item.id"
            @click="select(item)"
          >
            <q-item-section avatar>
              <q-icon name="person" :color="item.is_leader? 'orange-10':'grey-9'" />
            </q-item-section>
            <q-item-section class="q-py-xs">
              <q-item-label>{{ item.fullname }}</q-item-label>
              <q-item-label caption>
                {{ item.department?.number }} {{ item.department?.title }}
              </q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-virtual-scroll>

      <div
        class="col text-center text-caption"
        v-if="!loading && (users && users.length == 0)"
      >
        Поиск не дал результатов...
      </div>

      <q-inner-loading :showing="loading">
        <q-spinner-gears size="50px" color="primary" />
      </q-inner-loading>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
// import { api } from 'src/boot/axios'
// import URL from 'src/api/hosts'

const props = defineProps(['hideGroups'])
const emit = defineEmits(['select'])
const loading = ref(false)
const users = ref([])
const search = ref('')
let timer = null

const getUsers = () => {
  loading.value = true
  axios.get('/api/users', { params: { search: search.value}})
    .then((response) => {
      users.value = response.data
    })
    .finally(() => loading.value = false)
}
onMounted(() => getUsers())

const select = (a) => {
  emit('select', a)
}

const groupSelect = (mode) => {
  if (mode=='all') {
    for (const user of users.value)
        emit('select', user)
  }
  if (mode=='leaders') {
    for (const user of users.value)
      if (user.is_leader)
        emit('select', user)
  }
  if (mode=='directors') {
    for (const user of users.value)
        if (user.is_director)
        emit('select', user)
  }
  if (mode=='gendir') {
    emit('select', users.value.find(i => i.fullname=='Арсланов Ирек Наилович'))
    // for (const user of users.value)
    //     if (user.department?.department_id == null)
    //     emit('select', user)
  }
  if (mode=='frequent') {
    const grp = [91, 90, 89, 4, 93, 10, 186, 180, 5];
    for(const user of users.value)
        if (grp.some(i => i == user.id)) emit('select', user)
  }
}

watch(search, (val) => {
  clearInterval(timer)
  timer = setTimeout(() => {
    getUsers()
  }, 500)
})
</script>