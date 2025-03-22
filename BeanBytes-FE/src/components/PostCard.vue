<template>
  <div class="q-pa-md q-ml-sm q-my-md post-container">
    <!-- User Info -->

    <div class="row justify-between">
      <div class="cursor-pointer row q-gutter-x-md">
        <q-avatar size="32px" @click="router.push('user/' + user.name)">
          <img :src="post.user.profile_picture || 'default-profile.jpg'" />
        </q-avatar>

        <div>
          <p>
            <span @click="router.push('user/' + post.user.name)">{{ post.user.username }}</span>

            <q-btn
              v-if="localPost.user.id !== authUserId"
              :label="localPost.isFollowed ? 'Following' : 'Follow'"
              :color="localPost.isFollowed ? 'grey' : 'primary'"
              flat
              dense
              no-caps
              @click="toggleFollow(localPost.user.id)"
            />
          </p>

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

    <p v-html="cleanedContent"></p>

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

    <!-- Images -->

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
        <div class="cursor-pointer" @click="toggleLike(post.id)">
          <q-icon :name="localPost.isLiked ? 'thumb_up' : 'sym_o_thumb_up'" size="16px" />
          {{ localPost.likes_count }}
        </div>

        <div class="cursor-pointer" @click="openCommentDialog">
          <q-icon name="chat" size="16px" />
          {{ localPost.comments_count }}
        </div>

        <div class="cursor-pointer" @click="sharePost(post.id)">
          <q-icon :name="localPost.isShared ? 'share' : 'sym_o_share'" size="16px" />
          {{ localPost.shares_count }}
        </div>
      </div>

      <div v-if="showCommentInput" class="q-mt-sm row q-gutter-x-sm">
        <q-input
          v-model="newComment"
          filled
          dense
          placeholder="Write a comment..."
          class="col-grow"
        />

        <q-btn label="Send" color="primary" @click="addComment(post.id)" />
      </div>
    </div>

    <q-dialog v-model="imageDialog">
      <img :src="selectedImage" class="full-image" />
    </q-dialog>

    <q-dialog v-model="shareDialog">
      <q-card class="q-pa-md" style="width: 500px">
        <q-card-section>
          <div class="text-h6">Share Post</div>
        </q-card-section>

        <q-card-section>
          <q-input v-model="postShareLink" readonly>
            <template v-slot:append>
              <q-btn icon="content_copy" flat @click="copyPostLink" />
            </template>
          </q-input>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="commentDialog">
      <q-card class="q-pa-md comment-dialog" style="width: 500px">
        <q-card-section>
          <div class="text-h6">Comments</div>
        </q-card-section>

        <q-card-section class="row q-gutter-x-sm">
          <q-input
            v-model="newComment"
            filled
            dense
            placeholder="Write a comment..."
            class="col-grow"
          />
          <q-btn label="Send" color="primary" @click="addComment(post.id)" />
        </q-card-section>

        <q-separator />

        <q-card-section class="comments-list">
          <q-list v-if="comments.length">
            <q-item v-for="comment in comments" :key="comment.id" class="q-mt-sm">
              <q-item-section avatar>
                <q-avatar size="24px">
                  <img :src="comment.user.profile_picture || 'default-profile.jpg'" />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ comment.user.username }}</q-item-label>
                <q-item-label caption>{{ comment.content }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>

          <div v-else class="text-center text-grey q-mt-md">No comments yet.</div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { computed, defineProps, ref, onMounted, reactive, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { api } from 'src/boot/axios'

import { useAuthStore } from 'src/stores/auth'
import 'highlight.js/styles/github-dark.min.css'
import hljs from 'highlight.js'

const $q = useQuasar()
const authStore = useAuthStore()
const authUserId = computed(() => authStore.user?.id || null)
const imageDialog = ref(false)
const selectedImage = ref('')
const isExpanded = ref(false)
const showCommentInput = ref(false)
const newComment = ref('')
const shareDialog = ref(false)
const postShareLink = ref('')
const commentDialog = ref(false)
const comments = ref([])
const router = useRouter()

const props = defineProps({
  post: Object,
})

const localPost = reactive({
  ...props.post,
})

watch(() => props.post, (newPost) => {
  Object.assign(localPost, newPost)
}, { deep: true })

const fixedAssets = computed(() => {
  return props.post.assets.map((asset) => {
    const fixedUrl = 'http://127.0.0.1:8000/storage/' + asset.url.replace(/\\/g, '/')
    return { ...asset, url: fixedUrl }
  })
})

const cleanedContent = computed(() => {
  return props.post.content
    .replace(/(\r\n|\r|\n)/g, '<br>')
    .replace(/#([\p{L}0-9_]+)/gu, '')
    .trim()
})

const truncatedCode = computed(() => {
  return props.post.fullCode.split('\n').slice(0, 4).join('\n') + '\n...'
})

function formatDate(date) {
  const now = new Date()
  const past = new Date(date)

  const diffInSeconds = Math.floor((now.getTime() - past.getTime()) / 1000)
  if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`
  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) return `${diffInMinutes} minutes ago`
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours} hours ago`
  const diffInDays = Math.floor(diffInHours / 24)
  return diffInDays < 30 ? `${diffInDays} days ago` : new Date(date).toLocaleDateString()
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

const emit = defineEmits(['update-follow-status'])

const toggleFollow = async (userId) => {
  try {
    const response = await api.post('/api/user/follow', { user_id: userId })
    const isFollowed = response.data.isFollowed ?? false

    emit('update-follow-status', { userId, isFollowed })

    $q.notify({
      message: response.data.message,
      color: isFollowed ? 'positive' : 'negative',
    })
  } catch (error) {
    console.error('Follow error:', error.response?.data || error.message)
    $q.notify({ message: 'Failed to update follow status', color: 'negative' })
  }
}

async function toggleLike(postId) {
  try {
    const response = await api.post('/api/add-post/like', { post_id: postId })
    localPost.likes_count = response.data.likes_count
    localPost.isLiked = !localPost.isLiked

    $q.notify({
      message: response.data.message,
      color: response.data.message === 'Post liked' ? 'green' : 'red',
    })
  } catch (error) {
    console.error('Error liking post:', error.response?.data || error.message)
    $q.notify({ message: 'Failed to like the post!', color: 'red' })
  }
}

async function sharePost(postId) {
  if (localPost.isShared) {
    postShareLink.value = `${window.location.origin}/post/${postId}`
    shareDialog.value = true
    return
  }

  try {
    await api.post('/api/add-post/share', { post_id: postId })

    const updatedPost = await api.get(`/api/get-post/${postId}`)
    localPost.shares_count = updatedPost.data.shares_count
    localPost.isShared = updatedPost.data.isShared

    postShareLink.value = `${window.location.origin}/post/${postId}`
    shareDialog.value = true

    $q.notify({ message: 'Post shared successfully!', color: 'green' })
  } catch (error) {
    console.error('Error sharing post:', error.response?.data || error.message)
    $q.notify({ message: 'Failed to share the post!', color: 'red' })
  }
}

function copyCode(code) {
  navigator.clipboard.writeText(code)
  $q.notify({ message: 'Copied to clipboard!', color: 'green' })
}

function copyPostLink() {
  navigator.clipboard.writeText(postShareLink.value)
  $q.notify({ message: 'Copied to clipboard!', color: 'green' })
}

function openImage(url) {
  selectedImage.value = url
  imageDialog.value = true
}

async function addComment(postId) {
  if (!newComment.value.trim()) {
    $q.notify({ message: 'Comment cannot be empty!', color: 'red' })
    return
  }

  try {
    const response = await api.post('/api/add-post/comment', {
      post_id: postId,
      content: newComment.value,
    })

    localPost.comments_count++
    comments.value.unshift({
      id: response.data.comment.id,
      content: newComment.value,
      user: response.data.comment.user,
    })

    newComment.value = ''

    $q.notify({ message: 'Comment added successfully!', color: 'green' })
  } catch (error) {
    console.error('Error adding comment:', error.response?.data || error.message)
    $q.notify({ message: 'Failed to add comment!', color: 'red' })
  }
}

async function openCommentDialog() {
  commentDialog.value = true

  try {
    const response = await api.get(`/api/get-comments?post_id=${props.post.id}`)
    comments.value = response.data.comments
  } catch (error) {
    console.error('Error fetching comments:', error.response?.data || error.message)
    $q.notify({ message: 'Failed to load comments!', color: 'red' })
  }
}

const highlightCodeBlocks = () => {
  document.querySelectorAll('pre code').forEach((block) => {
    if (!block.dataset.highlighted) {
      hljs.highlightElement(block)
      block.dataset.highlighted = 'yes'
    }
  })
}

onMounted(() => {
  authStore.fetchUser()
  highlightCodeBlocks()
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

.hashtag {
  color: #1da1f2;
  cursor: pointer;
  font-weight: bold;
}

.comment-dialog {
  width: 100%;
}

.comments-list {
  max-height: 300px;
  overflow-y: auto;
}
</style>
