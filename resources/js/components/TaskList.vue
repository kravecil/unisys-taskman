<template>
  <div class="q-px-lg col">
    <div class="WAL q-mx-auto column col">
      <!-- <div
        class="row col absolute-center text-grey-5"
        v-if="tasks?.length == 0"
      >
        Список поручений пуст
      </div> -->
      <!-- <q-scroll-area style="height: 200px"> -->
        <q-infinite-scroll
          class="q-gutter-y-sm"
          @load="onScrollLoad"
          ref="infiniteScrollRef"
          :offset="200"
        >
          <q-markup-table
            v-if="tasks?.length > 0"
            :wrap-cells="true" class="bg-transparent">
            <thead class="bg-brown-5 text-white">
              <tr>
                <th class="" style="width:1%"></th>
                <th class="" style="width:30%">Поручение</th>
                <th class="" style="width:18%">Срок</th>
                <th class="">Постановщик</th>
                <th class="">Исполнители</th>
                <th class="">Соисполнители</th>
                <th class="">Наблюдатели</th>
                <th class="" style="width:1%"></th>
              </tr>
            </thead>
            <tbody>
              <TaskItem
                v-for="(task) in tasks"
                :key="task.id"
                :task="task"
                type='executor'
              />
            </tbody>
          </q-markup-table>
          <template v-slot:loading>
            <div class="text-center q-my-md">
              <q-spinner-dots color="orange-10" size="40px" />
            </div>
          </template>
        </q-infinite-scroll>
      <!-- </q-scroll-area> -->
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted, watch, toRefs } from 'vue'
// import { api } from 'src/boot/axios'
import { tasks, filter } from '@/api/store'

import TaskItem from '@/components/TaskItem.vue'

export default defineComponent({
  name: 'TaskList',
  props: ['view'],
  components: { TaskItem }  ,
  setup(props) {
    const { view } = toRefs(props)
    const infiniteScrollRef = ref(null)
    let page = 1
    let interval;

    const onScrollLoad = (index, done) => {
      axios.get('/api/tasks/?page=' + String(page),
      {
        params: {
          search: filter.value.search,
          view: view.value,
          showClosed: filter.value.showAllTasks,
          showInProgress: filter.value.showInProgress,
          showExpired: filter.value.showExpired,
          // showMode: filter.value.showMode,
          user: filter.value.filterUser,
          importance: filter.value.importance,
          // project: filter.value.filterProject,
        }
      })
        .then((response) => {
          if (response.data.length > 0) {
            for (const item of response.data) tasks.value.push(item)
            page++
          } else infiniteScrollRef.value?.stop()

          done()
        })
    }

    const resetData = () => {
      tasks.value = []
      page = 1

      infiniteScrollRef.value.reset()
      infiniteScrollRef.value.poll()
      infiniteScrollRef.value.resume()
    }

    onMounted(() => resetData())

    watch(filter, () => {
      clearInterval(interval)
      interval = setTimeout(() => resetData(), 500)
    }, {deep: true})
    // watch (showAllTasks, (value) => {
    //   resetData()
    // })
    // watch(filterUser, () => {
    //   clearInterval(interval)
    //   interval = setTimeout(() => resetData(), 1000)
    // })
    // watch(filterProject, () => {
    //   clearInterval(interval)
    //   interval = setTimeout(() => resetData(), 1000)
    // })

    return {
      tasks,
      onScrollLoad,
      infiniteScrollRef,
    }
  }
})
</script>
<style lang="scss" scoped>
// .WAL {
//   max-width: 1170px;
// }
</style>