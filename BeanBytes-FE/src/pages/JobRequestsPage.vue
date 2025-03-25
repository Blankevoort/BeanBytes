<template>
  <q-page class="bg-dark row justify-center">
    <div class="col-12 col-md-8 text-grey-6">
      <div class="q-py-md text-h6">Job Requests</div>

      <q-list v-if="!loading && jobRequests.length">
        <q-item v-for="job in jobRequests" :key="job.id" clickable @click="openJobDetail(job)">
          <q-item-section>
            <q-item-label class="text-h6">{{ job.title }}</q-item-label>
            <q-item-label caption> {{ job.description.substring(0, 100) }}... </q-item-label>
            <q-item-label caption>
              <strong>Type:</strong> {{ job.type }} &nbsp;

              <strong>Status:</strong> {{ job.status }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-icon name="chevron_right" />
          </q-item-section>
        </q-item>
      </q-list>
      
      <div v-else-if="!loading" class="q-pa-md text-center text-grey">
        No job requests available.
      </div>
      <q-spinner v-if="loading" color="primary" size="50px" />

      <q-dialog v-model="jobDialog">
        <JobRequestDetailDialog :job="selectedJob" @close="jobDialog = false" />
      </q-dialog>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import JobRequestDetailDialog from 'src/components/JobRequestDetailDialog.vue'

const jobRequests = ref([])
const loading = ref(false)
const jobDialog = ref(false)
const selectedJob = ref(null)

const fetchJobRequests = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/api/job-requests')
    jobRequests.value = data.data || data
  } catch (error) {
    console.error('Error fetching job requests:', error)
  } finally {
    loading.value = false
  }
}

const openJobDetail = (job) => {
  selectedJob.value = job
  jobDialog.value = true
}

onMounted(fetchJobRequests)
</script>
