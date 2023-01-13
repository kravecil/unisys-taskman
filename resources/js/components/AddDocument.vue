<template>
<div class="WAL bg-white column">
  <!-- <div class="text-h6 q-mb-md col-auto">Зарегистрировать документ</div> -->
  <q-toolbar class="bg-grey-5"><q-toolbar-title class="text-center">Зарегистрировать документ</q-toolbar-title></q-toolbar>
  <div class="row no-wrap col q-pa-lg">
    <div class="col-9">
      <q-tabs v-model="tab" align="left" no-caps active-class="text-deep-orange-10">
        <q-tab name="1" label="Тип документа" />
        <q-tab name="2" label="Общие сведения" />
        <q-tab name="3" label="Дополнительные реквизиты"
          :disable="['decree','ksDecree','order','poa','miscdocument'].some(el=>el==docType)" />
        <q-tab name="4" label="Персонал" :disable="docType=='mail'"/>
        <q-tab name="5" label="Вложения" />
        <q-tab name="6" label="Рассылка" />
      </q-tabs>
      <q-tab-panels v-model="tab" animated>

        <!-- ТИП ДОКУМЕНТА -->
        <q-tab-panel name="1">
          <q-option-group v-model="docType" :options="docTypes"/>
        </q-tab-panel>

        <!-- НОМЕР И ДАТА -->
        <q-tab-panel name="2">
          <div class="row justify-between q-gutter-x-md">
            <div class="column q-gutter-md col">
              <div class="row">
                <q-input v-model="docNumber" class="col" label="Номер документа" type="text" clearable outlined />
                <q-btn flat icon="last_page" class="col-auto" @click="getLastNumber">
                  <q-tooltip>Получить последний номер</q-tooltip>
                </q-btn>
              </div>
              <q-input v-model="docBody" type="textarea" outlined :label="docType=='poa'?'Представл. интересов':'Описание'" />
              <q-input v-if="docType=='mail'"
                v-model="docInnerNumber"
                outlined
                dense
                label="Внутренний номер"
                :rules="[val => /^\d*$/.test(val) || 'Значение должно быть числовым']"
              >
                <template v-slot:append>
                  <q-icon name="cached" @click="getLastInnerNumber" />
                </template>
              </q-input>
            </div>
            <q-date landscape v-model="docDatetime" mask="YYYY-MM-DD" today-btn flat />
          </div>
        </q-tab-panel>

        <!-- ДОПОЛНИТЕЛЬНЫЕ РЕКВИЗИТЫ -->
        <q-tab-panel name="3">
          <div class="column q-gutter-md">
            <!-- <q-toggle v-if="docType?.name=='mail'" v-model="docIsOutgoing" label="Исходящее" />
            <q-toggle v-if="docType?.name=='decree'" v-model="docIsKadrSalary" label="Кадры/зарплата" /> -->
            
            <input-filter v-if="docType=='outgoingMail' || docType?.name=='miscdocument'" v-model="docKind"
              label="Вид документа" api-path="/api/documents/list-kinds" />
            <input-filter v-if="docType=='outgoingMail'" v-model="docSentBy"
              label="Вид отправки" api-path="/api/documents/list-sent-by" />
            <q-input v-if="docType=='outgoingMail'" v-model="docSentAt"
              dense outlined type="text" label="Дата отправки" clearable>
              <template v-slot:append>
                <q-icon name="event">
                  <q-popup-proxy><q-date v-model="docSentAt" minimal mask="YYYY-MM-DD" /></q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
            <div />
            <input-filter v-model="docPartner"
              dense label="Сведения об организации" hint="Наименование, город, адрес" api-path="/api/documents/list-partners" />
            <input-filter v-if="docType=='mail'" v-model="docSignerManual"
              label="Подписант" api-path="/api/documents/list-signers" />
          </div>
        </q-tab-panel>

        <!-- ПЕРСОНАЛ -->
        <q-tab-panel name="4">
          <div class="column q-gutter-md">
            <div v-if="docType!='poa' && docType?.name!='mail'"
              class="row items-center q-gutter-x-md" style="height: 4em"> <!-- подписант -->
              <div class=" col-2 ">Подписант</div>
              <div v-if="!docSigner" class="text-deep-orange-10 text-weight-bold cursor-pointer">
                &lt;выбрать&gt;
                <q-popup-proxy class="q-pa-md">
                  <user-select @select="docSigner=$event"/>
                </q-popup-proxy>
              </div>
              <q-item v-else><q-item-section>
                <q-item-label>{{ docSigner?.fullname }}</q-item-label>
                <q-item-label caption>{{ docSigner?.department?.number_title }}</q-item-label>
              </q-item-section></q-item>
              <q-btn dense flat round v-if="docSigner" icon="clear" @click="docSigner=null" />
            </div>
            <div class="row items-center q-gutter-x-md" style="height: 4em"> <!-- исполнитель -->
              <div class="col-2">
                {{ docType =='poa'? 'Доверенность на':'Исполнитель'}}
              </div>
              <div v-if="!docExecutor" class="text-deep-orange-10 text-weight-bold cursor-pointer">
                &lt;выбрать&gt;
                <q-popup-proxy class="q-pa-md">
                  <user-select @select="docExecutor=$event"/>
                </q-popup-proxy>
              </div>
              <q-item v-else><q-item-section>
                <q-item-label>{{ docExecutor?.fullname }}</q-item-label>
                <q-item-label caption>{{ docExecutor?.department?.number_title }}</q-item-label>
              </q-item-section></q-item>
              <q-btn dense flat round v-if="docExecutor" icon="clear" @click="docExecutor=null" />
            </div>
          </div>
        </q-tab-panel>

        <!-- ВЛОЖЕНИЯ -->
        <q-tab-panel name="5">
          <Attachments v-model="docAttachments" />
        </q-tab-panel>

        <!-- РАССЫЛКА -->
        <q-tab-panel name="6">
          <div class="row col">
            <div class="row col items-center justify-center text-grey-8 q-pa-md" v-if="docMailing?.length == 0">
              Выберите сотрудников для рассылки
            </div>
            <div v-if="docMailing?.length > 0" class="q-pa-md column col q-gutter-y-md">
              <q-btn label="Очистить список" @click="docMailing=[]" outline color="deep-orange-10" />
              <q-scroll-area class="col column">
                <q-item v-for="(user,index) in docMailing" :key="user.id"
                  class="q-my-xs rounded-borders bg-grey-4" dense>
                  <q-item-section avatar>
                    <q-icon color="grey-9" name="person" />
                  </q-item-section>
                  <q-item-section class="q-py-xs">
                    <q-item-label>{{ user.fullname }}</q-item-label>
                    <q-item-label caption :lines="2">
                      {{ user.departments?.number }} {{ user.departments?.title }}
                    </q-item-label>
                  </q-item-section>
                  <q-item-section top side>
                    <q-btn dense flat round icon="close" size="sm"
                      @click="removeUser(index)"/>
                  </q-item-section>
                </q-item>
              </q-scroll-area>
            </div>
            <div class="q-pa-md col" >
              <UserSelect @select="insertUser" style="height: 300px"/>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </div>

    <q-separator vertical class="q-mx-md" />
    <div class="col-3 column q-pa-md q-gutter-md">
      <div class="column col">
        <div><b>Тип:</b> {{ docType? docTypes.find(i => i.value == docType)?.label : 'не выбран!' }}</div>
        <div v-if="docNumber"><b>Номер документа:</b> {{ docNumber }}</div>
        <div v-if="docInnerNumber"><b>Внутренний номер:</b> {{ docInnerNumber }}</div>
        <div v-if="docBody"><b>Описание:</b> {{ docBody }}</div>
        <div v-if="docDatetime"><b>Дата документа:</b> {{ docDatetime }}</div>
        <div v-if="docKind"><b>Вид документа:</b> {{ docKind }}</div>
        <div v-if="docSentBy"><b>Вид отправки:</b> {{ docSentBy }}</div>
        <div v-if="docSentAt"><b>Дата отправки:</b> {{ docSentAt }}</div>
        <div v-if="docPartner"><b>Организация:</b> {{ docPartner }}</div>
        <div v-if="docSignerManual"><b>Подписант:</b> {{ docSignerManual }}</div>
        <div v-if="docSigner"><b>Подписант:</b> {{ docSigner.fullname }}</div>
        <div v-if="docExecutor"><b>{{docType =='poa'? 'Доверенность на':'Исполнитель'}}:</b> {{ docExecutor.fullname }}</div>
        <div v-if="docAttachments?.length>0"><b>Файлов прикреплено:</b> {{ docAttachments?.length }}</div>
        <div v-if="docMailing?.length>0"><b>Получателей рассылки:</b> {{ docMailing?.length }}</div>
      </div>
      <div class="row col-auto justify-between">
        <q-btn :disabled="!docType" label="Добавить"  color="primary" @click="submit" />
        <q-btn label="Отмена" color="grey-8" class="" @click="emit('cancel')" />
      </div>
    </div>

  </div>
</div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { date, Notify, Loading } from 'quasar'

// import { api } from 'src/boot/axios'

import InputFilter from '@/components/InputFilter.vue'
import UserSelect from '@/components/UserSelect.vue'
import Attachments from '@/components/Attachments.vue'

const emit = defineEmits(['done', 'cancel'])

const docTypes = [
  {value: 'mail', label: 'Письмо входящее'},
  {value: 'outgoingMail', label: 'Письмо исходящее'},
  {value: 'decree', label: 'Приказ'},
  {value: 'ksDecree', label: 'Приказ КЗ'},
  {value: 'order', label: 'Распоряжение'},
  {value: 'poa', label: 'Доверенность'},
  {value: 'miscdocument', label: 'Документ'},
]

const tab = ref('1')

const docType = ref('miscdocument')
const docNumber = ref('')
const docDatetime = ref('')
const docKind = ref('')
// const docIsOutgoing = ref(null)
// const docIsKadrSalary = ref(null)
const docBody = ref('')
const docSigner = ref(null)
const docExecutor = ref(null)
const docAttachments = ref([])
const docMailing = ref([])
const docSentBy = ref('')
const docSentAt = ref('')
const docPartner = ref('')
const docSignerManual = ref('')
const docInnerNumber = ref(null)

const getLastNumber = () => {
  if (docType.value) axios.get('/api/documents/last-number',
  {
    params: {
      type: docType.value,
      // is_outgoing: docIsOutgoing.value,
      // is_kadr_salary: docIsKadrSalary.value,
    }
  })
    .then(response => docNumber.value = response.data)
}
const getLastInnerNumber = () => {
  if (docType.value) axios.get('/api/documents/last-inner-number')
  .then(response => docInnerNumber.value = response.data)
}

const insertUser = (user) => {
  const pos = docMailing.value.findIndex(item => item.id == user.id)
  if (pos < 0) docMailing.value.push(user)
}

const removeUser = (pos) => {
  docMailing.value.splice(pos, 1)
}

const submit = () => {
  if (!docType.value) return

  const formData = new FormData()

  formData.append('type', docType.value)
  // if (docIsOutgoing.value != null) formData.append('is_outgoing', docIsOutgoing.value)
  // if (docIsKadrSalary.value != null) formData.append('is_kadr_salary', docIsKadrSalary.value)
  
  if (!!docNumber.value) formData.append('number', docNumber.value)
  if (!!docInnerNumber.value) formData.append('inner_number', docInnerNumber.value)
  if (!!docDatetime.value) formData.append('issued_at', docDatetime.value)
  if (!!docBody.value) formData.append('body', docBody.value)
  if (!!docKind.value) formData.append('kind', docKind.value)
  if (!!docSigner.value) formData.append('signer', JSON.stringify(
    { fullname: docSigner.value.fullname, department: docSigner.value.department?.number_title }
  ))
  if (!!docExecutor.value) formData.append('executor', JSON.stringify(
    { fullname: docExecutor.value.fullname, department: docExecutor.value.department?.number_title }
  ))
  if (!!docSentBy.value) formData.append('sent_by', docSentBy.value)
  if (!!docSentAt.value) formData.append('sent_at', docSentAt.value)
  if (!!docPartner.value) formData.append('partner', docPartner.value)
  if (!!docSignerManual.value) formData.append('signer_manual', docSignerManual.value)
  
  if (docMailing.value?.length > 0) for (const user of docMailing.value)
    formData.append('mailing_users[]', user.id)

  if (docAttachments.value.length > 0) for (const attachment of docAttachments.value)
    formData.append('attachments[]', attachment)

  Loading.show({message: 'Регистрация документа...'})
  axios.post('/api/documents', formData, { headers: {'Content-Type': 'multipart/form-data'}})
  .then(response => {
    // router.push({name: 'document', params: {id: response.data?.id}})
    Notify.create({
      type: 'positive',
      message: 'Документ добавлен',
    })
    emit('done', response.data)
  })
  .finally(() => Loading.hide())
}

</script>

<style lang="sass" scoped>
.WAL
  max-width: 1170px
  width: 100%
  max-height: 500px
  height: 100%
</style>
