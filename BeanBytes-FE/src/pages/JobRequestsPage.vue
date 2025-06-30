<template>
  <q-page class="bg-dark">
    <div class="row justify-center">
      <div class="col-12 col-md-8 text-grey-6">
        <q-tabs
          v-model="jobsTab"
          class="q-mb-lg text-h6 lt-md"
          :align="$q.screen.gt.sm ? 'justify' : 'left'"
          active-color="primary"
          indicator-color="primary"
          no-caps
        >
          <q-tab name="allJobs" label="All Jobs" />
          <q-tab name="myJobs" label="My Jobs" />
        </q-tabs>

        <div class="gt-sm q-py-sm">
          <q-select
            filled
            v-model="model"
            use-input
            hide-selected
            fill-input
            input-debounce="0"
            :options="mobileJobsTab"
            @filter="filterTabs"
            style="width: 250px; padding-bottom: 32px"
            class="full-width"
          >
            <template v-slot:no-option>
              <q-item>
                <q-item-section class="text-grey"> No results </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>

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
import { ref, watchEffect, watch } from 'vue'
import { api } from 'src/boot/axios'

import JobRequestCard from 'src/components/JobRequestCard.vue'


const jobsTab = ref('allJobs')
const jobRequests = ref([])
const loading = ref(false)

const mobileJobsTab = ['All Jobs', 'My Jobs']
const filteredTabs = ref([...mobileJobsTab])
const model = ref('All Jobs')

const labelToTab = {
  'All Jobs': 'allJobs',
  'My Jobs': 'myJobs',
}

const tabToLabel = {
  allJobs: 'All Jobs',
  myJobs: 'My Jobs',
}

watch(jobsTab, (val) => {
  model.value = tabToLabel[val]
})

watch(model, (val) => {
  if (labelToTab[val]) {
    jobsTab.value = labelToTab[val]
  }
})

const filterTabs = (val, update) => {
  update(() => {
    const needle = val.toLowerCase()
    filteredTabs.value = mobileJobsTab.filter((v) => v.toLowerCase().includes(needle))
  })
}

const fetchJobRequests = async () => {
  loading.value = true
  try {
    const endpoint = jobsTab.value === 'myJobs' ? '/api/my-services' : '/api/services'
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
