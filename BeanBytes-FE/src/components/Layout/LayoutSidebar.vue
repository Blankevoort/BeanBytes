<template>
  <div class="sidebar-container">
    <div class="left-sidebar">
      <div class="sidebar-content">
        <q-list class="q-px-md">
          <q-item clickable v-ripple to="/">
            <q-item-section avatar><q-icon name="home" /></q-item-section>
            <q-item-section>Feed</q-item-section>
          </q-item>

          <q-item clickable v-ripple to="/requests">
            <q-item-section avatar><q-icon name="work" /></q-item-section>
            <q-item-section>Job Requests</q-item-section>
          </q-item>

          <q-separator color="grey-9" class="q-my-sm" />

          <q-item clickable v-ripple :to="user ? '/bookmarks' : '/account'">
            <q-item-section avatar><q-icon name="bookmark" /></q-item-section>
            <q-item-section>Bookmarks</q-item-section>
          </q-item>

          <q-item clickable v-ripple :to="user ? '/notifications' : '/account'">
            <q-item-section avatar><q-icon name="notifications" /></q-item-section>
            <q-item-section>Notifications</q-item-section>
          </q-item>

          <q-item clickable v-ripple :to="user ? '/settings/account' : '/account'">
            <q-item-section avatar><q-icon name="settings" /></q-item-section>
            <q-item-section>Settings</q-item-section>
          </q-item>

          <q-separator color="grey-9" class="q-my-sm" />

          <q-item clickable v-ripple :to="user ? `/user/${user.name}` : '/account'">
            <q-item-section avatar>
              <q-avatar>
                <img alt="profile image" :src="profilePicture" />
              </q-avatar>
            </q-item-section>

            <q-item-section>{{ user?.username || 'Guest' }}</q-item-section>

            <q-item-section v-if="user" side
              ><q-icon color="red" size="20px" name="logout" @click="logout"
            /></q-item-section>
          </q-item>
        </q-list>
      </div>

      <div class="sidebar-footer">
        <q-separator color="grey-9" class="q-my-sm" />

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

onMounted(async () => {
  await authStore.fetchUser()
})
</script>

<style>
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
