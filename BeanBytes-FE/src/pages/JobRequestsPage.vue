<template>
  <q-page class="bg-dark">
    <div class="row items-center q-gutter-x-md q-px-sm lt-md">
      <q-icon name="arrow_back" @click="router.push('/')" size="20px" />

      <div class="text-white q-py-md" style="font-size: 20px">Job Requests</div>
    </div>

    <div class="row justify-center">
      <div class="col-12 col-md-8 text-grey-6">
        <q-tabs
          v-model="jobsTab"
          :class="$q.screen.gt.sm ? 'q-mb-lg text-h6' : 'q-mb-md'"
          :align="$q.screen.gt.sm ? 'justify' : 'left'"
          active-color="primary"
          indicator-color="primary"
          no-caps
        >
          <q-tab name="allJobs" label="All Jobs" />
          <q-tab name="myJobs" label="My Jobs" />
        </q-tabs>

        <q-list v-if="!loading && jobRequests.length">
          <JobRequestCard
            v-for="job in jobRequests"
            :key="job.id"
            :job="job"
            :expandable="jobsTab === 'myJobs'"
          />
        </q-list>

        <div v-else-if="!loading" class="q-pa-md text-center text-grey">
          No job requests available.
        </div>

        <q-spinner v-if="loading" color="primary" size="50px" />
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, watchEffect } from 'vue'
import { api } from 'src/boot/axios'
import { useRouter } from 'vue-router'

import JobRequestCard from 'src/components/JobRequestCard.vue'

const jobRequests = ref([])
const loading = ref(false)
const jobsTab = ref('allJobs')
const router = useRouter()

const fetchJobRequests = async () => {
  loading.value = true
  try {
    const endpoint = jobsTab.value === 'myJobs' ? '/api/my-services/' : '/api/services'
    const { data } = await api.get(endpoint)
    jobRequests.value = data.data || data
  } catch (error) {
    console.error('Error fetching job requests:', error)
  } finally {
    loading.value = false
  }
}

watchEffect(fetchJobRequests)
</script>
