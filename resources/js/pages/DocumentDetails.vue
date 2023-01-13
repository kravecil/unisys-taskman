<template>
<q-page class="q-pa-xl bg-grey-6">
  <div class="q-gutter-md q-mx-auto" style="max-width: 1170px; width: 100%;">
    <q-banner v-if="document.is_unread"
      class="bg-red-10 text-white rounded-borders" inline-actions>
      Необходимо подтвердить ознакомление с документом
      <template v-slot:action>
        <q-btn flat color="white" label="Ознакомиться" @click="markAsRead" />
      </template>
    </q-banner>

    <!-- <q-banner v-if="document.is_approved == null"
      class="bg-red-10 text-white rounded-borders" inline-actions>
      Требуется согласовать документ
      <template v-slot:action>
        <div class="cursor-pointer q-mx-lg text-deep-orange-2">
          <span v-if="!approvalNote">(Замечание)</span>
          {{ approvalNote }}
          <q-popup-edit v-model="approvalNote" title="Оставьте замечание">
            <q-input v-model="approvalNote" dense autofocus />
          </q-popup-edit>
        </div>
        <q-btn flat color="white" label="Согласовать" @click="approve(true)" />
        <q-btn flat color="white" label="Отказать" @click="approve(false)" />
      </template>
    </q-banner> -->

    <div class="row text-h4 items-center">
      {{ document.type?.title }}
      &nbsp;<div v-if="document.number">№ <span class="text-deep-orange-10 text-weight-bold">{{ document.number }}</span></div>
      <q-btn v-if="policy.isDocumentologist.value" icon="edit" unelevated dense
        @click="showNumberDialog">
        <q-tooltip>Редактировать номер документа</q-tooltip>
      </q-btn>
      &nbsp;<div v-if="document.issued_at">от <span class="text-deep-orange-10 text-weight-bold">{{ document.issued_at}}</span></div>
      <q-btn v-if="policy.isDocumentologist.value" icon="edit" unelevated dense
        @click="showIssuedAtDialog">
        <q-tooltip>Редактировать дату документа</q-tooltip>
      </q-btn>
      <!-- <div v-if="policy.isDocumentologist.value" class="text-grey-8 text-weight-bold col row justify-end">
        ИН:{{ document.id }} [{{ document.created_at }}]
      </div> -->
      <!-- <q-btn v-if="policy.isDocumentologist.value" flat round dense icon="delete" @click="deleteDocument">
        <q-tooltip>Удалить документ</q-tooltip>
      </q-btn> -->
    </div>

    <!-- ВНУТРЕННИЙ НОМЕР -->
    <div v-if="policy.isDocumentologist.value && document?.type?.name=='mail'"
      class="row text-grey-8 text-h6">
      <div>Внутренний номер:</div>&nbsp;
      <editable
        :label="document.inner_number"
        :editable="policy.isDocumentologist.value"
        inner-number
        column="inner_number"
        :api-path="`/api/documents/${document.id}/update-fields`"
        @done="updateDocument()"
      />
    </div>

    <!-- <div v-if="policy.isDocumentologist.value" class="text-grey-8 text-weight-bold col column text-h5">
      <span>ИН:{{ (+document.id) + 22560 }}</span><span>{{ document.created_at }}</span>
    </div> -->

    <div class="text-caption">Зарегистрирован: <span class="text-italic text-grey-9">{{ document.issuer?.fullname}}</span></div>
    <div v-if="document.sender" class="text-caption">Отправитель: <span class="text-italic text-grey-9">{{ document.sender}}</span></div>
    <q-separator spaced inset />

    <div class="row q-gutter-x-md">
      <q-item v-if="document?.type?.name=='outgoingMail'" class="rounded-borders pin">
        <q-item-section avatar><q-icon name="local_shipping" /></q-item-section>
        <q-item-section>
          <q-item-label>{{ document.sent_by}}</q-item-label>
          <q-item-label caption>{{ document.sent_at}}</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-btn v-if="policy.isDocumentologist.value" icon="edit" unelevated dense class="text-black"
            @click="showSentDialog">
            <q-tooltip>Редактировать отправку</q-tooltip>
          </q-btn>
        </q-item-section>
      </q-item>
      <q-item v-if="document?.kind || policy.isDocumentologist.value" class="rounded-borders pin">
        <q-item-section avatar><q-icon name="article" /></q-item-section>
        <q-item-section>
          <q-item-label>
            <editable
              :label="document.kind"
              :editable="policy.isDocumentologist.value"
              column="kind"
              :api-path="`/api/documents/${document.id}/update-fields`"
              api-path-filter="/api/documents/list-kinds"
              @done="updateDocument()"
            />
          </q-item-label>
        </q-item-section>
      </q-item>
    </div>
    <q-separator spaced inset />

    <!-- КРАТКОЕ СОДЕРЖАНИЕ -->
    <!-- <q-card v-if="document.body">
      <q-card-section class="row items-center bg-card-title">
        <q-icon name="description" size="md" class="q-mr-sm" />
        <div class="text-h6">Краткое содержание</div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="text-caption">{{ document.body }}</div>
      </q-card-section>
    </q-card> -->
    <div class="row text-black q-my-lg">
      <editable
        :label="document.body"
        :editable="policy.isDocumentologist.value"
        column="body"
        :api-path="`/api/documents/${document.id}/update-fields`"
        @done="updateDocument()"
      />
    </div>
    <q-separator spaced inset />
    <div v-if="document.partner || policy.isDocumentologist.value"
      class="text-grey-8 text-italic row items-center">
      <b>Организация:</b>&nbsp;
      <editable
        :label="document.partner"
        :editable="policy.isDocumentologist.value"
        column="partner"
        :api-path="`/api/documents/${document.id}/update-fields`"
        api-path-filter="/api/documents/list-partners"
        @done="updateDocument()"
      />
    </div>
    <div v-if="document?.type?.name=='mail' && (document.signer_manual || policy.isDocumentologist.value)"
      class="text-grey-8 text-italic row items-center">
      <b>Подписант:</b>&nbsp;
      <editable
        :label="document.signer_manual"
        :editable="policy.isDocumentologist.value"
        column="signer_manual"
        :api-path="`/api/documents/${document.id}/update-fields`"
        api-path-filter="/api/documents/list-signers"
        @done="updateDocument()"
      />
    </div>
    <div v-if="document.signer" class="text-grey-8 text-italic"><b>Подписант:</b> {{ JSON.parse(document.signer ?? '')?.fullname }}</div>
    <div v-if="document.executor" class="text-grey-8 text-italic"><b>Исполнитель:</b> {{ JSON.parse(document.executor ?? '')?.fullname }}</div>
    
    <div class="row">
    <!-- ВЛОЖЕНИЯ -->
      <q-card v-if="document.attachments?.length > 0 || policy.isDocumentologist.value" flat square
        class="col q-mr-sm">
        <q-card-section class="row items-center bg-card-title">
          <q-icon name="file_present" size="md" class="q-mr-sm" />
          <div class="text-h6">Прикреплённые файлы</div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div v-for="attachment in document.attachments" :key="attachment.id" class="row">
            <Attachment :documentId="document.id" :attachment="attachment" />
            <q-btn v-if="policy.isDocumentologist.value"
              dense flat icon="delete" color="red" @click="deleteAttachment(attachment)" size="sm" />
          </div>
          <div v-if="policy.isDocumentologist.value" class="row col q-py-md q-gutter-md items-start">
            <attachments dense class="col-9" v-model="attachmentsToAdd"/>
            <q-btn v-if="attachmentsToAdd?.length > 0" label="добавить" @click="addAttachments" outline class="col-2" />
          </div>
        </q-card-section>
      </q-card>

      <!-- РАССЫЛКА -->
      <q-card v-if="document.mailing_users?.length > 0 || policy.isDocumentologist.value"
        flat square
        style="height: 250px;" class="column col q-ml-sm">
        <q-card-section class="row items-center col-auto bg-card-title">
          <q-icon name="people" size="md" class="q-mr-sm" />
          <div class="text-h6">Рассылка</div>
          <q-btn v-if="policy.isDocumentologist.value"
            flat dense icon="edit" @click="editMailingListDialog" />
        </q-card-section>
        <q-separator />
        <q-card-section class="row col ">
          <q-scroll-area class="col">
            <q-item v-for="user in document.mailing_users" :key="user.id" dense>
              <q-item-section>
                <q-item-label>{{ user.fullname }}</q-item-label>
                <q-item-label caption v-if="user.pivot.read_at"
                  class="text-deep-orange-4"
                >
                  ознакомление: {{ user.pivot.read_at }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-scroll-area>
        </q-card-section>
      </q-card>
    </div>
    
    <q-card flat square>
      <q-card-section class="row items-center col-auto bg-card-title">
        <q-icon name="task" size="md" class="q-mr-sm" />
        <div class="text-h6">Поручения по документу</div>
        <q-btn flat dense icon="add" @click="addTaskDialog" />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <q-markup-table
          :wrap-cells="true">
          <tbody>
            <TaskItem
              v-for="(task) in document.user_tasks"
              :key="task.id"
              :task="task"
              type='executor'
            />
          </tbody>
        </q-markup-table>
      </q-card-section>
    </q-card>

    <!-- <q-card>
      <q-card-section>
        <div class="text-h6">Отправители</div>
      </q-card-section>
      <q-card-section>
        <q-item v-for="user in document.senders" :key="user.id">
          <q-item-section avatar>
            <q-avatar color="primary" text-color="white" icon="bluetooth" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ user.fullname }}</q-item-label>
          </q-item-section>
          <q-item-section side top>
            <q-item-label caption>{{ user.pivot.read_at}}</q-item-label>
          </q-item-section>
        </q-item>
      </q-card-section>
    </q-card> -->

    <!-- <q-card>
      <q-card-section>
        <div class="text-h6">Этапы согласования</div>
      </q-card-section>
      <q-card-section>
        <q-item v-for="user in document.approval_users" :key="user.id">
          <q-item-section avatar>
            <q-icon v-if="user.pivot.is_approved != null" :name="user.pivot.is_approved == 1? 'done':'close'" />
            <q-icon v-if="user.pivot.is_approved == null" name="question_mark" />
          </q-item-section>
          <q-item-section>
            <q-item-label>
              {{ user.fullname }}
              <q-badge color="grey-10" text-color="white" :label="'Этап ' + (user.pivot.approval_order + 1)" />
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-item-label caption>{{ user.pivot.approval_note}}</q-item-label>
          </q-item-section>
          
        </q-item>
      </q-card-section>
    </q-card> -->
  </div>

  <!-- ДИАЛОГ: ИЗМЕНЕНИЕ НОМЕРА ДОКУМЕНТА -->
  <q-dialog v-model="numberDialog">
    <q-card style="min-width: 350px">
      <q-card-section><div class="text-h6">Изменение номера документа</div></q-card-section>
      <q-card-section>
        <div class="row">
          <q-input v-model.number="docNumber" class="col" label="Номер документа" type="text" clearable outlined />
          <q-btn flat icon="last_page" class="col-auto" @click="getLastNumber">
            <q-tooltip>Получить последний номер</q-tooltip>
          </q-btn>
        </div>
      </q-card-section>
      <q-card-actions>
        <q-btn flat label="Изменить" @click="updateNumber"/>
        <q-btn flat label="Отмена" v-close-popup/>
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- ДИАЛОГ: ИЗМЕНЕНИЕ ДАТЫ ДОКУМЕНТА -->
  <q-dialog v-model="issuedAtDialog">
    <q-card style="min-width: 350px">
      <q-card-section><div class="text-h6">Изменение даты документа</div></q-card-section>
      <q-card-section>
        <q-input v-model="docIssuedAt" outlined  type="text" label="Дата документа">
          <template v-slot:append>
            <q-icon name="event">
              <q-popup-proxy><q-date v-model="docIssuedAt" minimal mask="YYYY-MM-DD" /></q-popup-proxy>
            </q-icon>
          </template>
        </q-input>
      </q-card-section>
      <q-card-actions>
        <q-btn flat label="Изменить" @click="updateIssuedAt"/>
        <q-btn flat label="Отмена" v-close-popup/>
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- ДИАЛОГ: ИЗМЕНЕНИЕ ОТПРАВКИ -->
  <q-dialog v-model="sentDialog">
    <q-card style="min-width: 350px">
      <q-card-section><div class="text-h6">Изменение отправки</div></q-card-section>
      <q-card-section class="q-gutter-y-md">
        <input-filter v-model="docSentBy" label="Вид отправки" api-path="/api/documents/list-sent-by" />
        <q-input v-model="docSentAt" outlined  type="text" label="Дата документа">
          <template v-slot:append>
            <q-icon name="event">
              <q-popup-proxy><q-date v-model="docSentAt" minimal mask="YYYY-MM-DD" /></q-popup-proxy>
            </q-icon>
          </template>
        </q-input>
      </q-card-section>
      <q-card-actions>
        <q-btn flat label="Изменить" @click="updateSentAt"/>
        <q-btn flat label="Отмена" v-close-popup/>
      </q-card-actions>
    </q-card>
  </q-dialog>

  <q-inner-loading :showing="documentIsLoading">
    <q-spinner-gears size="50px" color="primary" />
  </q-inner-loading>
</q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Dialog, Notify, Loading } from 'quasar'
// import { api } from 'src/boot/axios'
import * as policy from '@/api/policy'
import { getCounters } from '@/api/util'

import Attachment from '@/components/Attachment.vue'
import Attachments from '@/components/Attachments.vue'
import TaskItem from '@/components/TaskItem.vue'
import AddTaskDialog from '@/dialogs/AddTaskDialog.vue'
import EditUserSelect from '@/components/EditUserSelect.vue'
import InputFilter from '@/components/InputFilter.vue'
import Editable from '@/components/Editable.vue'

const router = useRouter()
const route = useRoute()
const document = ref({is_unread: false})
const approvalNote = ref('')

const numberDialog = ref(false)
const issuedAtDialog = ref(false)
const sentDialog = ref(false)
const docNumber = ref('')
const docIssuedAt = ref('')
const docSentBy = ref(null)
const docSentAt = ref(null)
const showNumberDialog = () => { docNumber.value = document.value?.number; numberDialog.value = true}
const showIssuedAtDialog = () => { docIssuedAt.value = document.value?.issued_at_iso; issuedAtDialog.value = true}
const showSentDialog = () => {
  docSentBy.value = document.value?.sent_by
  docSentAt.value = document.value?.sent_at_iso
  sentDialog.value = true
}
const updateNumber = () => {
  Loading.show({message: 'Изменение номера документа'})
  axios.post(`/api/documents/${document.value.id}/update-number`, { number: docNumber.value})
  .then(response => {
    document.value.number = response.data?.number
    numberDialog.value = false
    Notify.create({type: 'positive', message: 'Номер успешно изменён'})
  })
  .finally(() => Loading.hide())
}
const updateIssuedAt = () => {
  Loading.show({message: 'Изменение даты документа'})
  axios.post(`/api/documents/${document.value.id}/update-issued-at`, { issued_at: docIssuedAt.value})
  .then(response => {
    document.value.issued_at = response.data?.issued_at
    document.value.issued_at_iso = response.data?.issued_at_iso
    issuedAtDialog.value = false
    Notify.create({type: 'positive', message: 'Дата документа успешно изменена'})
  })
  .finally(() => Loading.hide())
}
const updateSentAt = () => {
  Loading.show({message: 'Изменение отправки документа'})
  axios.post(`/api/documents/${document.value.id}/update-sent`, { sent_by: docSentBy.value, sent_at: docSentAt.value })
  .then(response => {
    document.value.sent_by = response.data?.sent_by
    document.value.sent_at = response.data?.sent_at
    document.value.sent_at_iso = response.data?.sent_at_iso
    sentDialog.value = false
    Notify.create({type: 'positive', message: 'Отправка успешно изменена'})
  })
  .finally(() => Loading.hide())
}

const documentIsLoading = ref(false)

onMounted(() => updateDocument())

const updateDocument = () => {
  documentIsLoading.value = true
  axios.get(`/api/documents/${route.params.id}`)
  .then(response => {
    document.value = response.data
  })
  .finally(() => documentIsLoading.value = false)
}
watch(() => route.params.id, (newVal) => { if (newVal && route.name == 'document') updateDocument() })

const markAsRead = () => {
  axios.post(`/api/documents/${route.params.id}/read`)
  .then(() => {
    getCounters()
    updateDocument()
})
}
const approve = (b) => {
  axios.post(`/api/documents/${route.params.id}/approve`, {approve: b, note: approvalNote.value})
  .then(() => updateDocument())
}
const addTaskDialog = () => {
  Dialog.create({component: AddTaskDialog })
  .onOk((e) => {
    // console.log(e);
    axios.post(`/api/documents/${document.value.id}/attach`, {tasks: e?.map(item => item.id)})
    .then(() => updateDocument())
  })
}
const editMailingListDialog = () => {
  Dialog.create({component: EditUserSelect, componentProps: {users: document.value.mailing_users, view: 'mailing'}})
  .onOk(e => {
    Loading.show({message: 'Изменение рассылки'})
    axios.post(`/api/documents/${document.value?.id}/update-mailing-users`, {mailing_users: e.users.map(i => i.id)})
    .then(() => {
      Notify.create({type: 'positive', message: 'Список рассылки успешно изменён'})
      updateDocument()
    })
    .finally(() => Loading.hide())
  })
}
const deleteDocument = () => {
  Dialog.create({
    title: 'Удаление',
    message: `Удалить документ <b>${document.value.fullname}</b>?`,
    cancel: true,
    html: true,
    options: {
      type: 'checkbox',
      model: [],
      items: [
        { label: 'удалить привязанные поручения', value: true }
      ],
    }
  })
  .onOk((data) => {
    // router.push({name: 'documentDeleted'})
    axios.delete(`/api/documents/${document.value.id}`, { data: { deleteTasks: data[0] ?? false} })
    .then(() => { 
      getCounters()
      router.push({name: 'documentDeleted'})
  })
  })
}

const getLastNumber = () => {
  axios.get('/api/documents/last-number',
  {
    params: {
      type: document.value?.type.name,
      is_outgoing: document.value?.is_outgoing,
      is_kadr_salary: document.value?.is_kadr_salary,
    }
  })
  .then(response => docNumber.value = response.data)
}

const attachmentsToAdd = ref([])
const addAttachments = () => {
  const formData = new FormData()

  if (attachmentsToAdd.value.length > 0) for (const attachment of attachmentsToAdd.value)
    formData.append('attachments[]', attachment)

  Loading.show({message: 'Загрузка прикреплённых файлов'})
  axios.post(`/api/documents/${document.value.id}/attachments`, formData, { headers: {'Content-Type': 'multipart/form-data'}})
  .then(response => {
    attachmentsToAdd.value = []
    updateDocument()
    Notify.create({
      message: 'Прикреплённые файлы дополнены',
    })
  })
  .finally(() => Loading.hide())
}
const deleteAttachment = (attachment) => {
  axios.delete(`/api/documents/${document.value.id}/attachments/${attachment.id}`)
  .then(() => {
    updateDocument()
    Notify.create({
      message: `Прикреплённый файл <b>${attachment.name}</b> удалён`,
      html: true,
    })
})
}
</script>

<style lang="sass" scoped>
.bg-card-title
  background: linear-gradient(to right, #eee, #fefefe)
.pin
  border: 1px solid $grey-9
  color: $grey-9
</style>