<template>
  <q-layout view="lHr lpR fFf">
    <q-header class="text-white bg-dark">
      <q-input
        borderless
        dark
        input-class="text-grey-6 col"
        v-model="searchInput"
        placeholder="Search"
        style="height: 55px"
      >
        <template v-slot:prepend>
          <q-icon class="cursor-pointer" color="grey-6" name="search" @click="search" />
        </template>
      </q-input>

      <q-separator dark />
    </q-header>

    <q-drawer show-if-above v-model="leftDrawerOpen" side="left" class="bg-dark text-secondary">
      <div class="sidebar-container">
        <div class="left-sidebar">
          <div class="q-px-lg flex items-center" style="height: 55px">
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

                <div class="flex items-center">
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

                <div class="flex items-center">
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
    </q-drawer>

    <q-drawer
      show-if-above
      behavior="desktop"
      v-model="rightDrawerOpen"
      side="right"
      class="bg-dark text-secondary"
    >
      <div class="row justify-between" style="height: 55px">
        <q-btn flat label="Discuss" no-caps />

        <q-btn flat label="Discover" no-caps />

        <q-btn flat label="Hackatons" no-caps />
      </div>

      <q-separator dark />

      <div class="text-bold q-py-md" style="font-size: 24px">Trending Topics</div>

      <div class="row text-grey-6 text-bold">
        <div class="col- q-pa-xs" v-for="n in 8" :key="n">
          <div style="border: 1px solid grey; padding: 5px 15px; border-radius: 8px">
            #tag {{ n }}
          </div>
        </div>
      </div>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { ref } from 'vue'

export default {
  setup() {
    const search = ref()
    const darkMode = ref(true)

    return {
      search,
      darkMode,
      rightDrawerOpen: true,
      leftDrawerOpen: true,
    }
  },
}
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
