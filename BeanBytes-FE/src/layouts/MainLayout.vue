<template>
  <q-layout view="lHr lpR fFf">
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
import { ref } from 'vue'

import SideBar from 'src/components/Layout/LayoutSidebar.vue'
import TrendingTopics from 'src/components/Layout/TrendingTopics.vue'

const leftDrawerOpen = ref(true)
const rightDrawerOpen = ref(true)
const mergedDrawerOpen = ref(false)
const mergedTab = ref('menu')
</script>
