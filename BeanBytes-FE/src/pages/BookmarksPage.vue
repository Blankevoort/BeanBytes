<template>
  <q-page class="bg-dark row justify-center">
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="q-mt-lg q-gutter-y-md">
        <!-- Posts -->

        <div v-if="posts">
          <PostCard v-for="post in posts" :key="post.id" :post="post" />
        </div>

        <div v-else>Your have`nt saved any Posts.</div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { api } from 'src/boot/axios'
import PostCard from 'src/components/PostCard.vue'
import { onMounted, ref } from 'vue'

export default {
  components: {
    PostCard,
  },

  setup() {
    const posts = ref()

    async function fetchPosts() {
      try {
        const r = await api.get('/api/user/bookmarks')
        posts.value = r.data
      } catch (error) {
        console.error('Error fetching posts:', error)
      }
    }

    onMounted(() => {
      fetchPosts()
    })

    return {
      posts,
    }
  },
}
</script>
