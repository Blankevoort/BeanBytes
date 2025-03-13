<template>
  <div
    class="q-pa-md q-ml-sm q-my-md"
    style="border: 2px solid grey; border-radius: 4px; margin-right: 12px"
  >
    <div class="row justify-between">
      <div class="cursor-pointer row q-gutter-x-md">
        <q-avatar size="32px">
          <img :src="post.user.profile_picture || 'default-profile.jpg'" />
        </q-avatar>

        <div>
          <p>{{ post.user.username }}</p>
          <p style="font-size: 12px">{{ formatDate(post.created_at) }}</p>
        </div>
      </div>

      <q-icon
        :name="post.isBookmarked ? 'bookmark' : 'sym_o_bookmark'"
        size="24px"
        class="cursor-pointer"
        @click="savePost(post.id)"
      />
    </div>

    <p>{{ cleanedContent }}</p>

    <!-- Scrollable Code Snippet -->

    <div v-if="post.fullCode" class="q-py-md">
      <q-card class="bg-dark text-white">
        <q-card-section class="row justify-between">
          <div class="text-bold">Code Snippet</div>
          <q-icon
            name="content_copy"
            @click="copyCode(post.fullCode)"
            class="cursor-pointer"
            size="16px"
          />
        </q-card-section>

        <q-separator dark />

        <!-- Shortened Code Preview -->

        <q-card-section class="scrollable-x">
          <pre><code class="language-javascript">{{ truncatedCode }}</code></pre>
        </q-card-section>

        <!-- Expandable Full Code -->

        <q-expansion-item expand-separator v-model="isExpanded" label="Show Full Code">
          <q-card-section class="scrollable-x">
            <pre><code class="language-javascript">{{ post.fullCode }}</code></pre>
          </q-card-section>
        </q-expansion-item>
      </q-card>
    </div>

    <!-- Image Assets -->

    <div v-if="post.assets" class="q-py-md scrollable-x">
      <q-img v-for="asset in post.assets" :key="asset.id" :src="asset.path" class="image-asset" />
    </div>

    <!-- Post Tags -->

    <div class="q-pt-md q-gutter-y-sm">
      <div class="row q-gutter-x-sm">
        <div
          v-for="tag in post.tags"
          :key="tag"
          class="cursor-pointer text-secondary"
          @click="search(tag)"
        >
          #{{ tag }}
        </div>
      </div>

      <!-- Post Actions -->

      <div class="row q-gutter-x-md">
        <div class="cursor-pointer">
          <q-icon name="thumb_up" size="16px" /> {{ post.likes_count }}
        </div>

        <div class="cursor-pointer">
          <q-icon name="chat" size="16px" /> {{ post.comments_count }}
        </div>

        <div class="cursor-pointer">
          <q-icon name="share" size="16px" /> {{ post.shares_count }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps, ref } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

const $q = useQuasar()
const props = defineProps({
  post: Object,
})

const isExpanded = ref(false)

const cleanedContent = computed(() => {
  return props.post.content.replace(/#([\p{L}0-9_]+)/gu, '').trim()
})

const truncatedCode = computed(() => {
  return props.post.fullCode.split('\n').slice(0, 4).join('\n') + '\n...'
})

function formatDate(date) {
  const now = new Date()
  const past = new Date(date)

  const diffInSeconds = Math.floor((now.getTime() - past.getTime()) / 1000)

  if (diffInSeconds < 0) {
    return 'Just now'
  }

  if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`

  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) return `${diffInMinutes} minutes ago`

  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours} hours ago`

  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 30) return `${diffInDays} days ago`

  const diffInMonths = Math.floor(diffInDays / 30)
  if (diffInMonths < 12) return `${diffInMonths} months ago`

  const diffInYears = Math.floor(diffInMonths / 12)
  return `${diffInYears} years ago`
}

function savePost(postId) {
  const postData = new FormData()
  postData.append('post_id', postId)

  api
    .post('/api/post/save', postData)
    .then((response) => {
      window.location.reload()
      console.log('Post saved successfully:', response.data)
    })
    .catch((error) => {
      console.error('Error saving post:', error.response.data)
    })
}

function copyCode(code) {
  navigator.clipboard.writeText(code)
  $q.notify({ message: 'Copied to clipboard!', color: 'green' })
}
</script>

<style scoped>
.scrollable-x {
  overflow-x: auto;
  white-space: nowrap;
}

pre {
  margin: 0;
  padding: 8px;
  font-size: 14px;
  overflow-x: auto;
  background: #1e1e1e;
  color: #fff;
  border-radius: 4px;
}

.image-asset {
  max-height: 500px;
  max-width: 100%;
  display: inline-block;
  margin-right: 10px;
}
</style>
