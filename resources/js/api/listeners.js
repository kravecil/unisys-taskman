import { Notify } from 'quasar'

import { echo } from '@/api/echo'
import { user } from '@/api/auth'
import { tasks, documents } from '@/api/store'
import { getCounters, getTask } from '@/api/util'
import { route } from 'quasar/wrappers'

export const config = {
    router: null,
    route: null,
}

export const disable = () => {
    echo.leave(`events.${user.value.id}`)
}

export const enable = () => {
    echo.private(`events.${user.value.id}`)
        // .listen('TaskRemainder', (e) => {
        //     console.log(e);
        // })
        .listen('TaskShown', (e) => {
            // console.log(e);
            getCounters()
        })
        .listen('TaskModified', (e) => {
            // console.log(e);
            getCounters()

            // console.log(e.task);
            // console.log(config.route);

            /** обновим отображенные задачи */
            let actualView = false
            if (e.task.creators.some(item => item.id == user.value.id)
                && (e.task.executors?.length ?? 0) > 0
                && config.route.name == 'create'
                ) actualView = true;
            if (e.task.executors.some(item => item.id == user.value.id) && config.route.name == 'execute') actualView = true;
            if (e.task.coexecutors.some(item => item.id == user.value.id) && config.route.name == 'coexecute') actualView = true;
            if (e.task.controllers.some(item => item.id == user.value.id) && config.route.name == 'control') actualView = true;
            if (e.task.creators.some(item => item.id == user.value.id)
                && (e.task.executors?.length ?? 0) == 0
                && config.route.name == 'notes'
                ) actualView = true;
            if (config.route.name == 'project' && config.route.params.id == e.task.project?.id) actualView = true

            if (actualView/*  && e.task.action == 'show' && e.task.user.id == user.value.id */) {
                const pos = tasks.value.findIndex(item => item.id == e.task.id)

                if (pos < 0) {
                    if (!e.task.closed_at) {
                        if (e.user.id != user.value.id)e.task.is_unread = true
                        tasks.value.unshift(e.task)
                    }
                }
                else {
                    // e.task.is_unread = tasks.value[pos].is_unread
                    e.task.is_unread = true
                    tasks.value[pos] = e.task
                    if (e.task.closed_at) tasks.value.splice(pos, 1)
                }

                if (e.action == 'destroyed') tasks.value.splice(pos, 1)
            }

            /** обновим открытую задачу */
            if (config.route.name == 'task' && config.route.params.id == e.task.id && e.userId != user.value.id) {
                Notify.create({
                    type: 'info',
                    message: 'Данная задача обновлена!',
                    caption: 'Пожалуйста, обновите страницу',
                    timeout: 0,
                })
            }
        })
        .listen('DocumentModified', (e) => {
            // console.log('Document modified!');
            // console.log(e);
            // console.log(config.route.name);

            if (config.route.name == e.document.type.name_plural) {
                e.document.is_unread = true
                documents.value.unshift(e.document)
            }
            
            getCounters()
        })
}

// const notify = (task, message) => {
//     Notify.create({
//         icon: 'task',
//         message: message,
//         caption: task.title,
//         position: 'top',
//         color: 'bg-primary',
//         timeout: 0,
//         closeBtn: true,
//         actions: [
//             { label: 'Перейти', handler: () => {
//                 if (config.router) config.router.push({ name: 'task', params: {id: task.id}})
//             }, color: 'white' }
//         ]
//     })
    
//     // getCounters()
//     const foundedTaskToUpdate = tasks.value.findIndex((element) => element.id === task.id)
//     if (foundedTaskToUpdate >= 0)tasks.value[foundedTaskToUpdate].is_unread = true

//     if (config.route.name == 'task') getTask(task.id)
// }