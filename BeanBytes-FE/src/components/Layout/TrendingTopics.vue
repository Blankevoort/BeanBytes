<template>
  <div>
    <div
      class="row justify-between q-gutter-y-sm"
      :style="{ height: quasar.screen.gt.md ? '63px' : '' }"
    >
      <q-btn flat label="Discuss" no-caps />
      <q-btn flat label="Discover" no-caps />
      <q-btn flat label="Hackathons" no-caps />
    </div>

    <q-separator dark />

    <div class="text-bold q-py-md" style="font-size: 24px">Trending Topics</div>

    <div class="row text-grey-6 text-bold">
      <div class="col- q-pa-xs" v-for="tag in tags" :key="tag.name">
        <div style="border: 1px solid grey; padding: 5px 15px; border-radius: 8px">
          #{{ tag.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

export default {
  setup() {
    const quasar = useQuasar()
    const tags = ref([])

    const fetchTags = async () => {
      try {
        const { data } = await api.get('/api/random-tags')
        tags.value = data
      } catch (error) {
        console.error('Failed to fetch tags', error)
      }
    }

    onMounted(fetchTags)

    return {
      quasar,
      tags,
    }
  },
}
</script>
