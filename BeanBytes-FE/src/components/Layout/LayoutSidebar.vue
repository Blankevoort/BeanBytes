<template>
  <div class="sidebar-container">
    <div class="left-sidebar">
      <div class="q-px-lg flex items-center " style="height: 55px">
        <q-avatar>
          <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg" />
        </q-avatar>
      </div>

      <q-separator dark />

      <div class="sidebar-content">
        <q-list class="q-px-md">
          <q-item clickable v-ripple to="/">
            <q-item-section avatar><q-icon name="home" /></q-item-section>
            <q-item-section>My Feed</q-item-section>
          </q-item>

          <q-item clickable disable v-ripple to="/groups">
            <q-item-section avatar><q-icon name="groups" /></q-item-section>
            <q-item-section>Groups</q-item-section>
          </q-item>

          <q-item clickable disable v-ripple to="/messages">
            <q-item-section avatar><q-icon name="message" /></q-item-section>
            <q-item-section>Messages</q-item-section>

            <div>
              <q-badge color="orange" label="1" rounded />
            </div>
          </q-item>

          <q-item clickable v-ripple to="/bookmarks">
            <q-item-section avatar><q-icon name="bookmark" /></q-item-section>
            <q-item-section>Bookmarks</q-item-section>
          </q-item>

          <q-separator color="grey-9" class="q-my-sm" />

          <q-item clickable disable v-ripple to="/notifications">
            <q-item-section avatar><q-icon name="notifications" /></q-item-section>
            <q-item-section>Notifications</q-item-section>

            <div>
              <q-badge color="grey" label="3" rounded />
            </div>
          </q-item>

          <q-item clickable v-ripple to="/settings/account">
            <q-item-section avatar><q-icon name="settings" /></q-item-section>
            <q-item-section>Settings</q-item-section>
          </q-item>

          <q-separator color="grey-9" class="q-my-sm" />

          <q-item clickable v-ripple>
            <q-item-section avatar>
              <q-avatar><img src="profile.jpg" /></q-avatar>
            </q-item-section>
            <q-item-section>Robert J.</q-item-section>
          </q-item>
        </q-list>
      </div>

      <div class="sidebar-footer">
        <q-separator color="" class="q-my-sm" dark />

        <div class="row justify-between items-center">
          Dark Theme
          <q-toggle v-model="darkMode" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const darkMode = ref(true)
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
