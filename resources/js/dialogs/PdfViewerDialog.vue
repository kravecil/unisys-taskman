<template>
<transition appear enter-active-class="animated fadeIn slow">
  <div class="fixed z-max backdrop">
    <q-page-sticky position="top-right" :offset="[30, -10]">
      <q-btn fab round size="sm" icon="close" color="white" text-color="black" @click="emit('close')"/>
    </q-page-sticky>
    <iframe :src="source" width="100%" height="100%" frameborder="0" />
  </div>
</transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps(['src'])
const emit = defineEmits(['close'])

let source = URL.createObjectURL(new Blob([props.src], {type: 'application/pdf'}))

onMounted(() => { source = URL.createObjectURL(new Blob([props.src], {type: 'application/pdf'})) })
onUnmounted(() => { URL.revokeObjectURL(source) })

</script>

<style lang="sass" scoped>
.backdrop
  top: 0
  bottom: 0
  left: 0
  right: 0
  padding: 5em 5em 5em 5em
  background: #222c
  // backdrop-filter: grayscale(80%)
</style>