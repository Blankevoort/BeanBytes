<template>
  <q-page class="bg-dark row justify-center">
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="q-pa-md" v-if="user">
        <div class="row items-center q-mb-md">
          <q-avatar size="100px">
            <img
              v-if="user.profile?.profile_image"
              :src="fixedProfileImage"
              alt="Profile Picture"
            />
            <q-icon v-else name="person" size="100px" />
          </q-avatar>

          <div class="q-ml-md">
            <div class="text-h5 text-white">{{ user.name }}</div>
            <div class="text-subtitle1 text-grey">
              {{ user.profile?.job_title || 'No job title' }}
            </div>
            <div class="text-caption text-grey">
              {{ user.profile?.bio || 'No bio available' }}
            </div>
          </div>

          <div class="q-ml-auto row items-center">
            <q-btn
              v-if="isNotMe"
              :label="isFollowed ? 'Unfollow' : 'Follow'"
              :color="isFollowed ? 'negative' : 'primary'"
              @click="toggleFollow({ userId: user.id, isFollowed })"
            />
          </div>
        </div>

        <div class="q-my-md text-secondary text-h5 text-bold">Posts</div>

        <div v-if="posts.length">
          <PostCard
            v-for="post in posts"
            :key="post.id"
            :post="post"
            @updateFollowStatus="toggleFollow"
          />
        </div>

        <div v-else class="text-center text-grey-5">No posts available</div>
      </div>

      <q-spinner v-else color="primary" size="50px" />
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'src/boot/axios'
import { useAuthStore } from 'src/stores/auth'
import PostCard from 'src/components/PostCard.vue'

const route = useRoute()
const authStore = useAuthStore()

const user = ref(null)
const posts = ref([])
const isFollowed = ref(false)

const fixedProfileImage = computed(() => {
  if (user.value?.profile?.profile_image) {
    const imagePath = user.value.profile.profile_image.path
    return imagePath.startsWith('http')
      ? imagePath
      : `http://127.0.0.1:8000/storage/${imagePath.replace(/\\/g, '/')}`
  }
  return null
})

const fetchUser = async () => {
  try {
    const { data } = await api.get(`/api/user/${route.params.name}`)
    user.value = data.user
    posts.value = data.posts
    isFollowed.value = data.user.isFollowed
  } catch (error) {
    console.error('Error fetching user:', error)
  }
}

const toggleFollow = async ({ userId }) => {
  try {
    const response = await api.post('/api/user/follow', { user_id: userId })
    const updatedFollowState = response.data.isFollowed

    if (user.value.id === userId) {
      isFollowed.value = updatedFollowState
    }

    posts.value = posts.value.map((post) => {
      if (post.user.id === userId) {
        return { ...post, isFollowed: updatedFollowState }
      }
      return post
    })
  } catch (error) {
    console.error('Error updating follow status:', error)
  }
}

const isNotMe = computed(() => authStore.user?.id !== user.value?.id)

onMounted(fetchUser)
</script>
