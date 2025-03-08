<template>
  <q-page class="bg-dark text-secondary">
    <div class="row justify-between bg-dark text-white text-grey-6">
      <div class="sidebar-container col-2 text-secondary">
        <div class="left-sidebar">
          <div class="q-px-lg">
            <q-avatar style="height: 55px">
              <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg" />
            </q-avatar>
          </div>

          <q-separator dark />

          <div class="sidebar-content">
            <q-list class="q-px-lg">
              <q-item clickable v-ripple to="/feed">
                <q-item-section avatar><q-icon name="home" /></q-item-section>
                <q-item-section>My Feed</q-item-section>
              </q-item>

              <q-item clickable v-ripple to="/groups">
                <q-item-section avatar><q-icon name="groups" /></q-item-section>
                <q-item-section>Groups</q-item-section>
              </q-item>

              <q-item clickable v-ripple to="/messages">
                <q-item-section avatar><q-icon name="message" /></q-item-section>
                <q-item-section>Messages</q-item-section>
                <q-badge color="orange" label="1" rounded />
              </q-item>

              <q-item clickable v-ripple to="/bookmarks">
                <q-item-section avatar><q-icon name="bookmark" /></q-item-section>
                <q-item-section>Bookmarks</q-item-section>
              </q-item>

              <q-separator color="grey-9" class="q-my-sm" />

              <q-item clickable v-ripple to="/notifications">
                <q-item-section avatar><q-icon name="notifications" /></q-item-section>
                <q-item-section>Notifications</q-item-section>
                <q-badge color="grey" label="3" rounded />
              </q-item>

              <q-item clickable v-ripple to="/settings/account">
                <q-item-section avatar><q-icon name="settings" /></q-item-section>
                <q-item-section>Settings</q-item-section>
              </q-item>

              <q-separator color="grey-9" class="q-my-sm" />

              <q-item clickable v-ripple to="/profile">
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

      <div class="col">
        <!-- Search in developer or post tags -->

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

        <!-- Add new post -->

        <div class="q-mt-lg">
          <div class="new-post-box">
            <div class="post-input">
              <textarea placeholder="New Post"></textarea>
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

              <button class="post-btn">
                <q-icon name="send" size="24px" color="secondary" />
              </button>
            </div>
          </div>
        </div>

        <div class="q-mt-lg q-gutter-y-md">
          <!-- Tabs -->

          <div class="row q-ml-sm">
            <div class="flex items-center" @click="postsTabs = 'following'">
              <q-icon size="24px" name="favorite" :color="postsTabs == 'following' ? 'red' : ''" />

              <span class="q-pl-sm">Following</span>
            </div>

            <div class="flex items-center q-ml-lg" @click="postsTabs = 'featured'">
              <q-icon
                size="24px"
                name="local_fire_department"
                :color="postsTabs == 'featured' ? 'orange' : ''"
              />

              <span class="q-pl-sm">Featured</span>
            </div>

            <div class="flex items-center q-ml-lg" @click="postsTabs = 'trends'">
              <q-icon
                size="24px"
                name="rocket_launch"
                :color="postsTabs == 'trends' ? 'yellow' : ''"
              />

              <span class="q-pl-sm">Trends</span>
            </div>
          </div>

          <div
            class="q-pa-md q-ml-sm"
            style="border: 2px solid grey; border-radius: 4px; margin-right: 12px"
          >
            <div class="cursor-pointer row items-center q-gutter-x-md">
              <q-avatar size="32px"><img src="profile.jpg" /></q-avatar>

              <div>
                <p>Robert J.</p>

                <p style="font-size: 12px">2 hours ago</p>
              </div>
            </div>

            Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur dolor
            saepe quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus,
            debitis repellendus harum quas nam.

            <br />
            <br />

            Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur dolor
            saepe quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus,
            debitis repellendus harum quas nam. Lorem ipsum dolor sit amet consectetur adipisicing
            elit. Id quo debitis pariatur dolor saepe quas sed provident ab quasi ducimus. Sit quae
            reiciendis amet voluptatibus, debitis repellendus harum quas nam.

            <q-img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg" />
          </div>
        </div>
      </div>

      <div class="col-3">
        <div class="row justify-between col-2" style="height: 55px">
          <q-btn flat label="Discuss" />

          <q-btn flat label="Discover" />

          <q-btn flat label="Hackatons" />
        </div>

        <q-separator dark />

        <div class="q-mt-lg"></div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { ref } from 'vue'

export default {
  setup() {
    const search = ref()
    const darkMode = ref(true)
    const postsTabs = ref('following')

    return {
      search,
      darkMode,
      postsTabs,
    }
  },
}
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
</style>
