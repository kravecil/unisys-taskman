import { ref } from 'vue'
import _ from 'lodash'

export const counters = ref({})

export const tasks = ref([])
export const histories = ref([])
export const comments = ref([])
export const task = ref({})

export const documents = ref(null)
export const projects = ref(null)
export const outgoingDocuments = ref([])

export const filterDefaults = {
    showAllTasks:false,
    showInProgress: true,
    showExpired: true,
    search:null,
    filterUser:null,
    importance:null,
    // filterProject:null,
}
export const filter = ref(_.cloneDeep(filterDefaults))

export const filterDocumentsDefaults = {
    search: null,
    showRead: true,
    dateFrom: null,
    dateTo: null,
    sortBy: 'issued_at',
    sortHow: null,
}
export const filterDocuments = ref(_.cloneDeep(filterDocumentsDefaults))
export const periodDocuments = ref(null)

// filter.value = Object.assign({}, filterDefaults)
