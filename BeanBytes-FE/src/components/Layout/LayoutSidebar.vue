<template>
  <div
    class="sidebar-container"
    :class="$q.dark.isActive ? 'bg-dark text-grey-2' : 'bg-white text-grey-8'"
  >
    <div class="left-sidebar">
      <div class="sidebar-content">
        <q-list class="q-px-md">
          <q-item clickable v-ripple to="/">
            <q-item-section avatar>
              <q-icon name="home" :color="$q.dark.isActive ? 'grey-2' : 'grey-8'" />
            </q-item-section>
            <q-item-section>Feed</q-item-section>
          </q-item>

          <q-item clickable v-ripple to="/requests">
            <q-item-section avatar>
              <q-icon name="work" :color="$q.dark.isActive ? 'grey-2' : 'grey-8'" />
            </q-item-section>
            <q-item-section>Job Requests</q-item-section>
          </q-item>

          <q-separator :color="$q.dark.isActive ? 'grey-9' : 'grey-4'" class="q-my-sm" />

          <q-item clickable v-ripple :to="user ? '/bookmarks' : '/account'">
            <q-item-section avatar>
              <q-icon name="bookmark" :color="$q.dark.isActive ? 'grey-2' : 'grey-8'" />
            </q-item-section>
            <q-item-section>Bookmarks</q-item-section>
          </q-item>

          <q-item clickable v-ripple :to="user ? '/notifications' : '/account'">
            <q-item-section avatar>
              <q-icon name="notifications" :color="$q.dark.isActive ? 'grey-2' : 'grey-8'" />
            </q-item-section>
            <q-item-section>Notifications</q-item-section>
          </q-item>

          <q-item clickable v-ripple :to="user ? '/settings/account' : '/account'">
            <q-item-section avatar>
              <q-icon name="settings" :color="$q.dark.isActive ? 'grey-2' : 'grey-8'" />
            </q-item-section>
            <q-item-section>Settings</q-item-section>
          </q-item>

          <q-separator :color="$q.dark.isActive ? 'grey-9' : 'grey-4'" class="q-my-sm" />

          <q-item clickable v-ripple :to="user ? `/user/${user.name}` : '/account'">
            <q-item-section avatar>
              <q-avatar>
                <img :src="profilePicture" />
              </q-avatar>
            </q-item-section>
            <q-item-section>{{ user?.username || 'Guest' }}</q-item-section>
            <q-item-section v-if="user" side>
              <q-icon name="logout" color="negative" @click="logout" class="cursor-pointer" />
            </q-item-section>
          </q-item>
        </q-list>
      </div>

      <div
        class="sidebar-footer"
        :class="$q.dark.isActive ? 'bg-dark text-grey-3' : 'bg-grey-1 text-grey-7'"
      >
        <div class="row justify-between items-center">
          Dark Theme
          <q-toggle v-model="darkMode" @update:model-value="toggleDarkMode" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'src/stores/auth'
import { useUser } from 'src/composables/useUser'

const $q = useQuasar()
const authStore = useAuthStore()
const { logout } = useUser()

const user = computed(() => authStore.user)

const profilePicture = computed(() => {
  const path = user.value?.profile?.profile_image?.path
  if (!path) return 'default-profile.jpg'
  return path.startsWith('http')
    ? path
    : `http://127.0.0.1:8000/storage/${path.replace(/\\/g, '/')}`
})

const darkMode = ref(localStorage.getItem('darkMode') === 'true')
$q.dark.set(darkMode.value)
const toggleDarkMode = () => {
  $q.dark.set(darkMode.value)
  localStorage.setItem('darkMode', darkMode.value)
}

onMounted(() => {
  authStore.fetchUser()
})
</script>

<style scoped>
.sidebar-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.left-sidebar {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.sidebar-content {
  flex-grow: 1;
  overflow-y: auto;
  padding-bottom: 16px;
}

.sidebar-footer {
  padding: 16px;
  position: sticky;
  bottom: 0;
}
</style>
