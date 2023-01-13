import { computed } from 'vue'

import { user } from '@/api/auth'
import { task } from '@/api/store'

export const can = (ability) => {
    return user.value?.permissions?.some(item => item.name == ability || item.name == 'administration')
}

export const isCreator = computed(() => {
    return task.value?.creators?.some(item => item.id == user.value.id)
})

export const isExecutor = computed(() => {
    return task.value?.executors?.some(item => item.id == user.value.id)
})

export const isCoexecutor = computed(() => {
    return task.value?.coexecutors?.some(item => item.id == user.value.id)
})

export const isController = computed(() => {
    return task.value?.controllers?.some(item => item.id == user.value.id)
})

export const isInTeam = computed(() => isCreator.value || isExecutor.value || isCoexecutor.value || isController.value || false)

export const isDocumentologist = computed(() => {
    return can('taskman_modify_documents')
})

export const canComplete = computed(() => {
    return isExecutor.value ||
        (task.value?.is_coexecutors && isCoexecutor.value) ||
        can('taskman_modify_tasks')
})

export const canBack = computed(() => {
    return isCreator.value ||
        can('taskman_modify_tasks')
})

export const canClose = computed(() => {
    // console.log(isCreator.value);
    return isCreator.value ||
        can('taskman_modify_tasks')
})

export const canDelete = computed(() => {
    return isCreator.value ||
        can('taskman_modify_tasks')
})

export const canConfirmDeadline = computed(() => {
    return isCreator.value ||
        can('taskman_modify_tasks')
})

export const canEditUsers = computed(() => {
    return isCreator.value ||
        can('taskman_modify_tasks') ||
        (task.value?.is_coexecutors && isCoexecutor.value)
})