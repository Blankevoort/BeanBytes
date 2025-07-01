<template>
  <q-page class="bg-dark row justify-center">
    <div
      class="text-grey-6 col-xs-12 col-sm-12 col-md-10 col-lg-8 col-xl-6 q-pa-sm"
      :class="{ 'shadow-4': $q.screen.gt.sm }"
    >
      <div v-if="loading" class="text-center q-my-md">
        <q-spinner color="primary" size="40px" />
        <div class="q-mt-sm">Loading posts...</div>
      </div>

      <div v-else>
        <div v-if="posts.length">
          <PostCard
            v-for="post in posts"
            :key="post.id"
            :post="post"
            @updateFollowStatus="toggleFollow"
          />
        </div>

        <div v-else class="text-center text-grey-5 q-mt-xl">
          No posts found with tag "<strong>{{ route.params.tag }}</strong>".
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'src/boot/axios'
import PostCard from 'src/components/PostCard.vue'

const route = useRoute()
const posts = ref([])
const loading = ref(true)

const fetchPostsByTag = async () => {
  loading.value = true
  try {
    const tag = route.params.tag
    const response = await api.get(`/api/tag/${tag}/posts`)
    posts.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch posts for tag:', route.params.tag, error)
  } finally {
    loading.value = false
  }
}

const toggleFollow = ({ userId }) => {
  posts.value = posts.value.map((post) => {
    if (post.user.id === userId) {
      return {
        ...post,
        isFollowed: !post.isFollowed,
      }
    }
    return post
  })
}

onMounted(fetchPostsByTag)

watch(() => route.params.tag, fetchPostsByTag)
</script>
