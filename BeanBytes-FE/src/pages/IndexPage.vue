<template>
  <q-page class="bg-dark row justify-center">
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <!-- Add new post -->

      <div class="q-pt-lg">
        <q-form @submit="addPost">
          <div class="post-input">
            <textarea placeholder="New Post" v-model="content"></textarea>
          </div>

          <div class="post-actions">
            <div class="action-left">
              <button type="button" class="icon-btn" @click="showDialog = true">
                <q-icon name="sym_o_image" size="24px" color="grey-6" />
              </button>
            </div>

            <button type="submit" class="post-btn">
              <q-icon name="send" size="24px" color="secondary" />
            </button>
          </div>
        </q-form>
      </div>

      <div class="q-mt-lg q-gutter-y-md">
        <!-- Tabs -->

        <div class="row q-ml-sm">
          <div class="flex items-center cursor-pointer q-mr-lg" @click="postsTabs = 'posts'">
            <q-icon
              size="24px"
              name="sym_o_receipt_long"
              :color="postsTabs == 'posts' ? 'primary' : ''"
            />

            <span class="q-pl-sm">All Posts</span>
          </div>

          <div class="flex items-center cursor-pointer" @click="postsTabs = 'following'">
            <q-icon size="24px" name="favorite" :color="postsTabs == 'following' ? 'red' : ''" />

            <span class="q-pl-sm">Following</span>
          </div>

          <!-- <div class="flex items-center cursor-pointer q-ml-lg" @click="postsTabs = 'featured'">
            <q-icon
              size="24px"
              name="local_fire_department"
              :color="postsTabs == 'featured' ? 'orange' : ''"
            />

            <span class="q-pl-sm">Featured</span>
          </div> -->

          <div class="flex items-center cursor-pointer q-ml-lg" @click="postsTabs = 'trends'">
            <q-icon
              size="24px"
              name="rocket_launch"
              :color="postsTabs == 'trends' ? 'yellow' : ''"
            />

            <span class="q-pl-sm">Trends</span>
          </div>
        </div>

        <!-- Posts -->

        <q-tab-panels v-model="postsTabs" animated>
          <q-tab-panel name="posts">
            <div v-if="posts.length === 0">
              <p class="text-grey">No posts.</p>
            </div>

            <div v-else>
              <PostCard
                v-for="post in posts"
                :key="post.id"
                :post="post"
                @update-follow-status="updateFollowStatus"
              />
            </div>
          </q-tab-panel>

          <q-tab-panel name="following">
            <div v-if="posts.length === 0">
              <p class="text-grey">No posts from followed users.</p>
            </div>

            <div v-else>
              <PostCard
                v-for="post in posts"
                :key="post.id"
                :post="post"
                @update-follow-status="updateFollowStatus"
              />
            </div>
          </q-tab-panel>

          <!-- <q-tab-panel name="featured">
            <div class="text-grey">Featured posts will be implemented later.</div>
          </q-tab-panel> -->

          <q-tab-panel name="trends">
            <div v-if="posts.length === 0">
              <p class="text-grey">No trending posts.</p>
            </div>

            <div v-else>
              <PostCard
                v-for="post in posts"
                :key="post.id"
                :post="post"
                @update-follow-status="updateFollowStatus"
              />
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>

    <q-dialog v-model="showDialog">
      <q-card style="width: 400px">
        <q-card-section class="row items-center justify-between">
          <div class="text-h6">Upload Image</div>
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <!-- File Input -->

        <q-card-section>
          <q-file
            v-model="imageFile"
            label="Choose an image"
            accept="image/*"
            filled
            @update:model-value="previewImage"
          />

          <!-- Image Preview -->

          <div v-if="imagePreview" class="q-mt-md">
            <q-img :src="imagePreview" style="max-height: 200px; max-width: 100%" />
          </div>
        </q-card-section>

        <q-separator />

        <!-- Upload Button -->

        <q-card-actions align="right">
          <q-btn label="Cancel" flat v-close-popup />
          <q-btn
            label="Upload"
            color="primary"
            @click="addPost"
            :disable="!content && !imageFile"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

import PostCard from 'src/components/PostCard.vue'

const postsTabs = ref('posts')
const content = ref()
const posts = ref([])
const $q = useQuasar()
const showDialog = ref(false)
const imageFile = ref(null)
const imagePreview = ref(null)

const fetchPosts = async () => {
  posts.value = []

  try {
    let response

    if (postsTabs.value === 'following') {
      response = await api.get('/api/posts/following')
    } else if (postsTabs.value === 'trends') {
      response = await api.get('/api/posts/trending')
    } else if (postsTabs.value === 'posts') {
      response = await api.get('/api/get-posts')
    } else {
      posts.value = []
      return
    }

    posts.value = response.data
  } catch (error) {
    console.error('Error fetching posts:', error)
  }
}

const updateFollowStatus = async ({ userId }) => {
  try {
    const response = await api.post('/api/user/follow', { user_id: userId })
    const updatedFollowState = response.data.isFollowed
    posts.value = posts.value.map((post) =>
      post.user.id === userId ? { ...post, isFollowed: updatedFollowState } : post,
    )
  } catch (error) {
    console.error('Error updating follow status:', error)
  }
}

async function addPost() {
  if (!content.value) return

  const postData = new FormData()
  postData.append('content', content.value || '')

  if (imageFile.value) {
    postData.append('assets[]', imageFile.value)
  }

  try {
    await api
      .post('/api/add-post', postData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      .then((r) => {
        if (r.data.status == 200) {
          content.value = ''
          imageFile.value = null
          imagePreview.value = null
          showDialog.value = false
          $q.notify({ message: 'Post created successfully!', color: 'green' })
        }
        fetchPosts()
      })
  } catch (error) {
    console.error('Error creating post:', error)
    $q.notify({ message: 'Failed to create post', color: 'red' })
  }
}

function previewImage(file) {
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

onMounted(fetchPosts)
</script>

<style scoped>
.left-sidebar {
  position: fixed;
  width: 318px;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.dark-mode-toggle {
  margin-top: auto;
  width: 100%;
}

.sidebar-content {
  flex-grow: 1;
  overflow-y: auto;
  padding-bottom: 60px;
}

.sidebar-footer {
  padding: 15px;
}

.new-post-box {
  width: 1100px;
  border-radius: 8px;
  margin: 1rem auto;
  color: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.post-input textarea {
  width: 100%;
  min-height: 120px;
  max-height: 240px;
  background-color: #2a2a2a;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
  padding: 0.75rem;
  color: #fff;
  font-size: 0.9rem;
  resize: vertical;
}

.post-input textarea::placeholder {
  color: #888;
}

.post-input textarea:focus {
  outline: 2px solid #555;
}

.post-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #1e1e1e;
  border-bottom-left-radius: 4px;
  border-bottom-right-radius: 4px;
  padding-left: 4px;
}

.action-left {
  display: flex;
  gap: 0.5rem;
}

.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: #fff;
  font-size: 0.9rem;
  cursor: pointer;
  border-radius: 4px;
  padding: 0.4rem 0.3rem;
  transition: background 0.2s;
}

.icon-btn:hover {
  background-color: #333;
}

.icon-btn img {
  height: 16px;
  margin-right: 0.3rem;
}

.post-btn {
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.post-actionButtons {
  padding: 15px;
  border-radius: 4px;
  transition: ease-in 0.3s;
}

.post-actionButtons:hover {
  color: #d9ac30;
  background: #7e600f;
}

.code-card {
  background: #1e1e1e;
  color: white;
  font-family: 'Fira Code', monospace;
}

.code-header {
  padding: 10px 15px;
  font-weight: bold;
}

.code-content {
  max-height: 150px;
  overflow: hidden;
  white-space: pre-wrap;
}
</style>
