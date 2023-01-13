// import { api } from 'src/boot/axios'
import * as store from '@/api/store'

export const getCounters = () => {
    axios.get('/api/counters', {params: { showClosed: store.filter.value.showAllTasks }})
        .then((response) => {
            store.counters.value = response.data
        })
}

export const getTask = (taskId) => {
    return new Promise((resolve, reject) => {
        store.task.value = {}
        axios.get('/api/tasks/' + taskId)
            .then((response) => {
                store.task.value = response.data
                getComments(taskId)
                getHistories(taskId)
                // markTaskAsRead(taskId)
                resolve(response)
            })
            .catch(error => reject(error))
    })
}

export const getHistories = (taskId) => {
    return new Promise((resolve, reject) => {
        store.histories.value = []
        axios.get(`/api/tasks/${taskId}/histories`)
            .then((response) => {
                store.histories.value = response.data
                resolve(response)
            })
            .catch(error => reject(error))
            
    })
}

export const getComments = (taskId) => {
    return new Promise((resolve, reject) => {
        store.comments.value = []
        axios.get(`/api/tasks/${taskId}/comments`)
            .then((response) => {
                store.comments.value = response.data
                resolve(response)
            })
            .catch(error => reject(error))
            
    })
}

export const markTaskAsRead = (taskId) => {
    axios.post(`/api/tasks/${taskId}/markAsRead`)
        .then(() => getCounters())
}

export const forceFileDownload = (data, title) => {
    const url = URL.createObjectURL(new Blob([data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', title)
    document.body.appendChild(link)
    link.click()
    URL.revokeObjectURL(url);
}

export const icons = {
    'create': 'edit',
    'back': 'reply',
    'complete': 'help_center',
    'close': 'done',
    'comment': 'chat',
}