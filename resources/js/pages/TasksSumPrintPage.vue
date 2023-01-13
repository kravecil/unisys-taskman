<template>
<q-layout>
  <q-page padding>
    <div class="text-h4 text-right">Общее количество неисполненных поручений</div>
    <div class="text-h6 text-right q-mb-xl">на {{ fDatetime ?? date.formatDate(Date.now(), 'DD.MM.YYYY') }}</div>
    <!-- <canvas id="chartjs" class="chart"></canvas> -->
    <div v-for="item in items" :key="item.user.shortname"
      class="row q-my-sm"
    >
      <div class="col-2">{{ item.user.shortname }}</div>
      <div class="col row items-center">
        <!-- <div class="bg-black text-white" :style="'width: '+(item.tasks * 100 / maxCounter )+'%'">&nbsp;</div> -->
        <svg :width="(item.tasks * 100 / maxCounter - 5) + '%'" height="10px">
          <rect x="0" y="0" width="100%" height="100%" />
        </svg>
        <div class="q-ml-md">{{item.tasks}}</div>
      </div>
      
    </div>
    <div class="q-mt-xl text-italic">{{ date.formatDate(Date.now(), 'DD.MM.YYYY') }}</div>
    <div class="text-italic">Начальник отдела 233</div>
  </q-page>
  <q-page-sticky :offset="[30,30]">
    <div class="print-hide bg-deep-orange-10 q-pa-md text-white rounded-borders shadow-10 q-gutter-x-md">
      <q-icon name="settings" size="md" />
      <q-btn flat dense no-caps label="Быбрать дату">
        <q-popup-proxy>
          <div class="q-pa-md row q-gutter-md">
            <q-input v-model="fDatetime" filled dense autofocus label="введите дату" />
            <q-btn label="Показать" no-caps dense v-close-popup @click="updateList({datetime: fDatetime})" />
          </div>
        </q-popup-proxy>
      </q-btn>
      <q-checkbox v-model="hideClosed" label="без исполненных" />
      <q-btn flat dense no-caps label="Отобразить всех" @click="updateList()" />
    </div>
  </q-page-sticky>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { date, Loading } from 'quasar'
// import Chart from 'chart.js/auto'
// import ChartDataLabels from 'chartjs-plugin-datalabels'

// import { api } from 'src/boot/axios'
// import { filter } from 'src/api/store'

// import UserSelect from '@/components/UserSelect.vue'

// Chart.register(ChartDataLabels)

const items = ref([])
const fDatetime = ref(null)
const maxCounter = ref(0)
const hideClosed = ref(true)
// let ctx

const updateList = ({datetime} = {}) => {
  items.value = []
  fDatetime.value = datetime
  // chart.value?.destroy()
  Loading.show({message: 'Формируем отчёт...'})
  axios.get('/api/report/tasks', {params: { is_sum: true, datetime: fDatetime.value, hide_closed: hideClosed.value }})
  .then(response => {
    items.value = response.data.result
    maxCounter.value = response.data.max_counter
    // chart.value.destroy()
    // chart.value = new Chart(ctx, {
    //   type: 'bar',
    //   data: {
    //     datasets: [{
    //       data: response.data.map(item => ({ y: item.user.shortname, x: item.tasks }))
    //     }],
    //   },
    //   options: {
    //     indexAxis: 'y',
    //     responsive: true,
    //     maintainAspectRatio: true,
    //     scales: {
    //       x: {
    //         display: false
    //       },
    //       y: {
    //         grid: {
    //           display: false,
    //         },
    //         ticks: {
    //           // stepSize: 100,
    //           // autoSkip: true,
    //         }
    //       }
    //     },
    //     elements: {
    //       bar: {
    //         backgroundColor: 'grey',
    //         // inflateAmount: 100,

    //       }
    //     },
    //     plugins: {
    //       legend: false,
    //       title: {
    //         display: true,
    //       },
    //       tooltip: {
    //         enabled: false,
    //       },
    //       pluginDataLabels: {
    //         display: true,
    //       },
    //       datalabels: {
    //         display: true,
    //         align: 'end',
    //         anchor: 'end',
    //         labels: {
    //           value: {}
    //         },
    //         formatter: (value, context) => value.x
    //       }
    //     }
    //   },
    // })
  })
  .finally(() => Loading.hide())
}

// const ctx = 'chartjs'
// const chart = ref(null)

// document.addEventListener('beforeprint', () => {
//   for (let id in Chart.instances) {
//         Chart.instances[id].resize(600,600);
//     }
// })

onMounted(() => {
  // ctx = document.getElementById('chartjs').getContext('2d')
  updateList()}
)

watch(hideClosed, () => updateList({datetime: fDatetime.value}))
</script>

// <style lang="sass" scoped>
// .chart
//   // position: relative
//   // width: 100%
//   // height: 80vh
// </style>