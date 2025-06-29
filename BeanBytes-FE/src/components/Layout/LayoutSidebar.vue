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

          <q-item clickable disable v-ripple to="/groups">
            <q-item-section avatar><q-icon name="groups" /></q-item-section>
            <q-item-section>Groups</q-item-section>
          </q-item>

          <!-- <q-item clickable disable v-ripple to="/messages">
            <q-item-section avatar><q-icon name="message" /></q-item-section>
            <q-item-section>Messages</q-item-section>

            <div>
              <q-badge color="orange" label="1" rounded />
            </div>
          </q-item> -->

          <q-item clickable v-ripple @click="handleBookmarksClick">
            <q-item-section avatar><q-icon name="bookmark" /></q-item-section>
            <q-item-section>Bookmarks</q-item-section>
          </q-item>

          <q-separator color="grey-9" class="q-my-sm" />

          <q-item clickable v-ripple @click="handleNotificationClick">
            <q-item-section avatar><q-icon name="notifications" /></q-item-section>
            <q-item-section>Notifications</q-item-section>
          </q-item>

          <q-item clickable v-ripple @click="handleSettingsClick">
            <q-item-section avatar><q-icon name="settings" /></q-item-section>
            <q-item-section>Settings</q-item-section>
          </q-item>

          <q-separator color="grey-9" class="q-my-sm" />

          <q-item clickable v-ripple>
            <q-item-section avatar>
              <q-avatar> <img :src="user?.avatar || 'profile.jpg'" /></q-avatar>
            </q-item-section>
            <q-item-section>{{ user?.username || 'Guest' }}</q-item-section>
          </q-item>
        </q-list>
      </div>

      <div class="sidebar-footer">
        <q-separator color="" class="q-my-sm" dark />

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
import { useRouter } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)

const darkMode = ref(localStorage.getItem('darkMode') === 'true')
$q.dark.set(darkMode.value)

const toggleDarkMode = () => {
  $q.dark.set(darkMode.value)
  localStorage.setItem('darkMode', darkMode.value)
}

const handleBookmarksClick = () => {
  if (!user.value) {
    router.push('/account')
  } else {
    router.push('/bookmarks')
  }
}

const handleSettingsClick = () => {
  if (!user.value) {
    router.push('/account')
  } else {
    router.push('/settings/account')
  }
}

const handleNotificationClick = () => {
  if (!user.value) {
    router.push('/account')
  } else {
    router.push('/notifications')
  }
}

onMounted(() => {
  authStore.fetchUser()
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
