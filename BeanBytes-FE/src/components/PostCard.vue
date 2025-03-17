<template>
  <div class="q-pa-md q-ml-sm q-my-md post-container">
    <!-- User Info -->
    <div class="row justify-between">
      <div class="cursor-pointer row q-gutter-x-md">
        <q-avatar size="32px">
          <img :src="post.user.profile_picture || 'default-profile.jpg'" />
        </q-avatar>

        <div>
          <p>{{ post.user.username }}</p>
          <p class="time-text">{{ formatDate(post.created_at) }}</p>
        </div>
      </div>

      <q-icon
        :name="post.isBookmarked ? 'bookmark' : 'sym_o_bookmark'"
        size="24px"
        class="cursor-pointer"
        @click="savePost(post.id)"
      />
    </div>

    <!-- Post Content -->

    <p>{{ cleanedContent }}</p>

    <!-- Code Snippet -->

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

        <q-card-section>
          <pre><code class="language-javascript">{{ truncatedCode }}</code></pre>
        </q-card-section>

        <q-expansion-item expand-separator v-model="isExpanded" label="Show Full Code">
          <q-card-section>
            <pre><code class="language-javascript">{{ post.fullCode }}</code></pre>
          </q-card-section>
        </q-expansion-item>
      </q-card>
    </div>

    <div v-if="post.assets.length" class="q-py-md image-grid">
      <img
        v-for="asset in fixedAssets"
        :key="asset.id"
        :src="asset.url"
        class="image-item cursor-pointer"
        @click="openImage(asset.url)"
      />
    </div>

    <!-- Tags -->

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

    <q-dialog v-model="imageDialog">
      <img :src="selectedImage" class="full-image" />
    </q-dialog>
  </div>
</template>

<script setup>
import { computed, defineProps, ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import 'highlight.js/styles/github-dark.min.css'
import hljs from 'highlight.js'

import { api } from 'src/boot/axios'

const $q = useQuasar()
const imageDialog = ref(false)
const selectedImage = ref('')
const isExpanded = ref(false)

const props = defineProps({
  post: Object,
})

const fixedAssets = computed(() => {
  return props.post.assets.map((asset) => {
    const fixedUrl = 'http://127.0.0.1:8000/storage/' + asset.url.replace(/\\/g, '/')

    return {
      ...asset,
      url: fixedUrl,
    }
  })
})

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

function openImage(url) {
  selectedImage.value = url
  console.log(selectedImage.value)
  imageDialog.value = true
}

onMounted(() => {
  hljs.highlightAll()
})
</script>

<style scoped>
.post-container {
  border: 2px solid grey;
  border-radius: 4px;
  padding: 16px;
  margin-right: 12px;
}

.time-text {
  font-size: 12px;
}

.image-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.image-item {
  max-height: 300px;
  width: auto;
  max-width: 100%;
  border-radius: 6px;
  transition: transform 0.2s ease-in-out;
}

.image-item:hover {
  transform: scale(1.05);
}

.full-image {
  max-width: 90vw;
  max-height: 90vh;
}
</style>
