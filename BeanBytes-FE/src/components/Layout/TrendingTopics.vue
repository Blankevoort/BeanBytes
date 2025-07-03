<template>
  <div class="q-px-sm" :class="$q.dark.isActive ? 'bg-dark text-grey-3' : 'bg-white text-grey-8'">
    <div
      class="text-bold q-py-md text-right"
      :class="$q.dark.isActive ? 'text-grey-1' : 'text-black'"
      style="font-size: 20px"
    >
      Trending Topics
    </div>

    <div class="row q-gutter-sm">
      <div
        v-for="tag in tags"
        :key="tag"
        class="cursor-pointer"
        @click="router.push('/tag/' + tag)"
      >
        <q-chip outline :color="$q.dark.isActive ? 'grey-4' : 'grey-8'"> #{{ tag }} </q-chip>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { api } from 'src/boot/axios'

const $q = useQuasar()
const router = useRouter()
const tags = ref([])

const fetchTags = async () => {
  try {
    const { data } = await api.get('/api/trending-tags')
    tags.value = data
  } catch (err) {
    console.error('Failed to fetch tags', err)
  }
}

onMounted(fetchTags)
</script>

<style scoped>
.text-bold {
  font-weight: bold;
}
</style>
