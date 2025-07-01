<template>
  <div class="q-px-sm">
    <div class="text-bold q-py-md text-right" style="font-size: 20px">Trending Topics</div>

    <div class="row text-grey-6 text-bold">
      <div class="col- q-pa-xs cursor-pointer" v-for="tag in tags" :key="tag.name">
        <div
          style="border: 1px solid grey; padding: 5px 15px; border-radius: 8px"
          @click="router.push('/tag/' + tag)"
        >
          #{{ tag }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'

import { api } from 'src/boot/axios'

export default {
  setup() {
    const quasar = useQuasar()
    const tags = ref([])
    const router = useRouter()

    const fetchTags = async () => {
      try {
        const { data } = await api.get('/api/trending-tags')
        tags.value = data
      } catch (error) {
        console.error('Failed to fetch tags', error)
      }
    }

    onMounted(fetchTags)

    return {
      tags,
      router,
      quasar,
    }
  },
}
</script>
