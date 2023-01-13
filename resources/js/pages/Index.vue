<template>
  <q-page class="flex flex-center">
    <transition appear
      enter-active-class="animated bounce slower repeat-3"
    >
      <img
        alt="Quasar logo"
        src="~assets/quasar-logo-vertical.svg"
        style="width: 200px; height: 200px"
      >
    </transition>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'PageIndex'
})
</script>
