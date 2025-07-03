<template>
  <q-page
    class="row justify-center"
    :class="[$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black']"
  >
    <div class="text-grey-6 col-xs-12 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div v-if="posts.length">
        <PostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
          @bookmark-changed="onBookmarkChanged"
        />
      </div>

      <div v-else class="text-center text-grey q-mt-lg">You haven’t saved any posts.</div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

import PostCard from 'src/components/PostCard.vue'

const $q = useQuasar()
const posts = ref([])
let lastRemoved = null

async function fetchPosts() {
  try {
    const r = await api.get('/api/bookmarked-posts')
    posts.value = r.data
  } catch (err) {
    console.error(err)
  }
}

function onBookmarkChanged({ postId, isBookmarked }) {
  if (!isBookmarked) {
    const idx = posts.value.findIndex((p) => p.id === postId)
    if (idx !== -1) {
      lastRemoved = { post: posts.value[idx], index: idx }
      posts.value.splice(idx, 1)

      $q.notify({
        message: 'Post removed from bookmarks',
        color: 'negative',
        timeout: 5000,
        actions: [
          {
            label: 'Undo',
            color: 'white',
            handler: () => {
              if (lastRemoved) {
                posts.value.splice(lastRemoved.index, 0, lastRemoved.post)
                lastRemoved = null
                $q.notify({ message: 'Bookmark restored', color: 'positive' })
              }
            },
          },
        ],
      })
    }
  }
}

fetchPosts()
</script>
