<template>
  <q-card class="q-pa-md" style="width: 500px">
    <q-card-section>
      <div class="text-h6">{{ job.title }}</div>
      
      <div class="text-subtitle1">Type: {{ job.type }}</div>
    </q-card-section>

    <q-card-section>
      <p>{{ job.description }}</p>
      <p>
        <strong>Budget:</strong>
        {{ job.budget ? '$' + job.budget : 'N/A' }}
      </p>
      <p>
        <strong>Hourly Rate:</strong>
        {{ job.hourly_rate ? '$' + job.hourly_rate : 'N/A' }}
      </p>
      <p><strong>Status:</strong> {{ job.status }}</p>
      <p>
        <strong>Skills Required:</strong>
      </p>

      <div v-if="job.skills && job.skills.length">
        <span v-for="(skill, index) in job.skills" :key="skill.id">
          {{ skill.name }}<span v-if="index !== job.skills.length - 1">, </span>
        </span>
      </div>

      <span v-else>No skills specified.</span>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn label="Apply" color="primary" @click="applyJob" />
      <q-btn label="Close" flat @click="$emit('close')" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { defineProps } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const props = defineProps({
  job: Object,
})

const applyJob = async () => {
  try {
    const { data } = await api.post(`/api/job-requests/${props.job.id}/apply`)
    $q.notify({ message: data.message, color: 'green' })
  } catch (error) {
    console.error('Error applying for job:', error)
    $q.notify({ message: 'Failed to apply for job', color: 'red' })
  }
}
</script>
