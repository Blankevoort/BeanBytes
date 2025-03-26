<template>
  <q-page class="bg-dark row justify-center">
    <div class="col-12 col-md-8 text-grey-6">
      <div class="q-py-md text-h6">Job Requests</div>

      <q-tabs
        v-model="jobsTab"
        active-color="primary"
        indicator-color="primary"
        class="q-mb-md"
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
  </q-page>
</template>

<script setup>
import { ref, watchEffect } from 'vue'
import { api } from 'src/boot/axios'
import JobRequestCard from 'src/components/JobRequestCard.vue'

const jobRequests = ref([])
const loading = ref(false)
const jobsTab = ref('allJobs')

const fetchJobRequests = async () => {
  loading.value = true
  try {
    const endpoint = jobsTab.value === 'myJobs' ? '/api/my-job-requests/' : '/api/job-requests'
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
