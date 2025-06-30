<template>
  <q-layout view="lHr lpR fFf">
    <q-header class="text-white bg-dark">
      <q-toolbar>
        <q-btn
          v-if="$q.screen.lt.lg"
          dense
          flat
          round
          icon="menu"
          @click="mergedDrawerOpen = !mergedDrawerOpen"
        />

        <div class="col relative-position">
          <q-input
            borderless
            dark
            input-class="text-grey-6"
            v-model="searchInput"
            placeholder="Search"
            style="height: 55px"
          >
            <template v-slot:prepend>
              <q-icon class="cursor-pointer" color="grey-6" name="search" />
            </template>
          </q-input>

          <q-menu
            v-model="isDropdownOpen"
            auto-close
            anchor="bottom left"
            self="top left"
            transition-show="scale"
            transition-hide="scale"
          >
            <q-card class="bg-dark text-white q-pa-md" style="min-width: 250px">
              <div v-if="users.length">
                <div class="text-bold text-grey-4">Users</div>
                <q-separator dark class="q-my-sm" />
                <q-list dense>
                  <q-item
                    clickable
                    v-for="user in users"
                    :key="'user-' + user"
                    @click="goToUser(user.name)"
                  >
                    <q-item-section avatar>
                      <q-icon name="person" color="blue" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ user.username }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>

              <div v-if="tags.length">
                <div class="text-bold text-grey-4 q-mt-md">Tags</div>
                <q-separator dark class="q-my-sm" />
                <q-list dense>
                  <q-item clickable v-for="tag in tags" :key="'tag-' + tag" @click="goToTag(tag)">
                    <q-item-section avatar>
                      <q-icon name="label" color="green" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>#{{ tag }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>

              <!-- No Results -->
              <div v-if="users.length === 0 && tags.length === 0" class="text-center text-grey-5">
                No results found
              </div>
            </q-card>
          </q-menu>
        </div>
      </q-toolbar>

      <q-separator dark />
    </q-header>

    <q-drawer
      v-if="$q.screen.gt.md"
      show-if-above
      behavior="desktop"
      v-model="leftDrawerOpen"
      side="left"
      class="bg-dark text-secondary shadow-2"
    >
      <SideBar />
    </q-drawer>

    <q-drawer
      v-if="$q.screen.gt.md"
      show-if-above
      behavior="desktop"
      v-model="rightDrawerOpen"
      side="right"
      class="bg-dark text-secondary shadow-2 relative-position"
    >
      <TrendingTopics />

      <q-btn
        v-if="$q.screen.gt.md && rightDrawerOpen"
        dense
        round
        :icon="rightDrawerOpen ? 'chevron_left' : 'chevron_right'"
        @click="rightDrawerOpen = !rightDrawerOpen"
        class="fixed-top-right q-ma-sm z-max absolute q-ma-md"
        style="left: 0; right: inherit"
        color="primary"
      />
    </q-drawer>

    <q-btn
      v-if="$q.screen.gt.md && !rightDrawerOpen"
      dense
      round
      :icon="rightDrawerOpen ? 'chevron_left' : 'chevron_right'"
      @click="rightDrawerOpen = !rightDrawerOpen"
      class="fixed-top-right q-ma-sm z-max q-ma-md"
      color="primary"
    />

    <q-drawer
      v-if="$q.screen.lt.lg"
      v-model="mergedDrawerOpen"
      side="left"
      class="bg-dark text-secondary"
    >
      <q-tabs v-model="mergedTab" dense class="text-white">
        <q-tab name="menu" label="Menu" />
        <q-tab name="trending" label="Trending" />
      </q-tabs>

      <q-tab-panels v-model="mergedTab" animated class="bg-dark text-white">
        <q-tab-panel name="menu">
          <SideBar />
        </q-tab-panel>

        <q-tab-panel name="trending">
          <TrendingTopics />
        </q-tab-panel>
      </q-tab-panels>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { debounce } from 'quasar'
import { api } from 'src/boot/axios'

import SideBar from 'src/components/Layout/LayoutSidebar.vue'
import TrendingTopics from 'src/components/Layout/TrendingTopics.vue'
import { useRouter } from 'vue-router'

const searchInput = ref('')
const leftDrawerOpen = ref(true)
const rightDrawerOpen = ref(true)
const mergedDrawerOpen = ref(false)
const mergedTab = ref('menu')
const router = useRouter()
const users = ref([])
const tags = ref([])
const isDropdownOpen = ref(false)

const fetchSearchResults = debounce(async () => {
  if (!searchInput.value) {
    users.value = []
    tags.value = []
    isDropdownOpen.value = false
    return
  }

  try {
    const { data } = await api.get(`/api/search/${searchInput.value}`)
    users.value = data.user || []
    tags.value = data.tag || []

    isDropdownOpen.value = users.value.length > 0 || tags.value.length > 0
  } catch (error) {
    console.error('Search failed:', error)
    isDropdownOpen.value = false
  }
}, 500)

watch(searchInput, fetchSearchResults)

const goToUser = (name) => {
  router.push(`/user/${name}`)
}

const goToTag = (tag) => {
  router.push(`/tag/${tag}`)
}
</script>
