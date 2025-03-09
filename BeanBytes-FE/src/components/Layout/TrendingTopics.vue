<template>
  <div>
    <div class="row justify-between q-gutter-y-sm" :style="{ height: quasar.screen.gt.md ? '63px' : '' }">
      <q-btn flat label="Discuss" no-caps />

      <q-btn flat label="Discover" no-caps />

      <q-btn flat label="Hackathons" no-caps />
    </div>

    <q-separator dark />

    <div class="text-bold q-py-md" style="font-size: 24px">Trending Topics</div>

    <div class="row text-grey-6 text-bold">
      <div class="col- q-pa-xs" v-for="n in 8" :key="n">
        <div style="border: 1px solid grey; padding: 5px 15px; border-radius: 8px">
          #tag {{ n }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useQuasar } from 'quasar'

export default {
  setup() {
    const quasar = useQuasar() // Rename $q to 'quasar'

    return {
      quasar, // Use 'quasar' instead of $q
    }
  },
}
</script>
