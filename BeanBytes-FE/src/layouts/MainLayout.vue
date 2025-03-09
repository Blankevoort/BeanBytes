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
      </q-toolbar>

      <q-separator dark />
    </q-header>

    <q-drawer
      v-if="$q.screen.gt.md"
      show-if-above
      v-model="leftDrawerOpen"
      side="left"
      class="bg-dark text-secondary"
    >
      <SideBar />
    </q-drawer>

    <q-drawer
      v-if="$q.screen.gt.md"
      show-if-above
      behavior="desktop"
      v-model="rightDrawerOpen"
      side="right"
      class="bg-dark text-secondary"
    >
      <TrendingTopics />
    </q-drawer>

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
import { ref } from 'vue'
import SideBar from 'src/components/Layout/LayoutSidebar.vue'
import TrendingTopics from 'src/components/Layout/TrendingTopics.vue'

const searchInput = ref('')
const leftDrawerOpen = ref(true)
const rightDrawerOpen = ref(true)
const mergedDrawerOpen = ref(false)
const mergedTab = ref('menu')

function search() {
  console.log('Searching:', searchInput.value)
}
</script>
