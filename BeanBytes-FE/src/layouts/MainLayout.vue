<template>
  <q-layout view="hhh lpR lFr">

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { ref } from 'vue'

export default {
  setup() {
    const searchInput = ref()

    function search() {}

    return {
      search,
      searchInput,
    }
  },
}
</script>
