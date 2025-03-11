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
        :name="post.saved ? 'sym_o_bookmark' : 'bookmark'"
        size="24px"
        class="cursor-pointer"
        @click="savePost(post.id)"
      />
    </div>

    <p>{{ post.content }}</p>

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
          <pre><code class="language-javascript">{{ post.fullCode }}</code></pre>
        </q-card-section>
      </q-card>
    </div>

    <div v-if="post.assets" class="q-py-md">
      <q-img
        v-for="asset in post.assets"
        :key="asset.id"
        :src="asset.path"
        style="max-height: 500px"
      />
    </div>

    <div class="q-pt-md q-gutter-y-sm">
      <div
        v-for="tag in post.tags"
        :key="tag.id"
        class="cursor-pointer"
        style="padding-left: 15px"
        @click="search(tag.name)"
      >
        #{{ tag.name }}
      </div>

      <div class="row q-gutter-x-md">
        <div class="post-actionButtons">
          <q-icon name="thumb_up" size="16px" /> {{ post.likes_count }}
        </div>

        <div class="post-actionButtons">
          <q-icon name="chat" size="16px" /> {{ post.comments_count }}
        </div>

        <div class="post-actionButtons">
          <q-icon name="share" size="16px" /> {{ post.shares_count }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

const $q = useQuasar()
defineProps({
  post: Object,
})

function savePost(postId) {
  const postData = new FormData()
  postData.append('post_id', postId)

  api
    .post('/api/post/add-save', postData)
    .then((response) => {
      console.log('Post saved successfully:', response.data)
    })
    .catch((error) => {
      console.error('Error saving post:', error.response.data)
    })
}

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

function copyCode(code) {
  navigator.clipboard.writeText(code)
  $q.notify({ message: 'Copied to clipboard!', color: 'green' })
}
</script>
