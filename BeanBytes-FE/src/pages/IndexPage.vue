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
              <button class="icon-btn">
                <q-icon name="code" size="24px" color="grey-6" />
              </button>

              <button class="icon-btn">
                <q-icon name="photo_camera" size="24px" color="grey-6" />
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
          <div class="flex items-center cursor-pointer" @click="postsTabs = 'following'">
            <q-icon size="24px" name="favorite" :color="postsTabs == 'following' ? 'red' : ''" />

            <span class="q-pl-sm">Following</span>
          </div>

          <div class="flex items-center cursor-pointer q-ml-lg" @click="postsTabs = 'featured'">
            <q-icon
              size="24px"
              name="local_fire_department"
              :color="postsTabs == 'featured' ? 'orange' : ''"
            />

            <span class="q-pl-sm">Featured</span>
          </div>

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

        <div v-if="posts">
          <PostCard v-for="post in posts" :key="post.id" :post="post" />
        </div>

        <div v-else>No posts found.</div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from 'src/boot/axios'
import PostCard from 'src/components/PostCard.vue'

const postsTabs = ref('following')
const content = ref()
const posts = ref([])

async function fetchPosts() {
  try {
    const r = await api.get('/api/get-posts')
    posts.value = r.data
  } catch (error) {
    console.error('Error fetching posts:', error.r?.data || error.message)
  }
}

function addPost() {
  const postData = new FormData()
  postData.append('content', content.value)

  api
    .post('/api/add-post', postData)
    .then((response) => {
      console.log('Post created successfully:', response.data)
    })
    .catch((error) => {
      console.error('Error creating post:', error.response.data)
    })
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
