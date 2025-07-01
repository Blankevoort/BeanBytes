<template>
  <q-card class="q-pa-md" style="width: 500px">
    <q-card-section>
      <div class="text-h6">{{ job.title }}</div>

      <div class="text-subtitle1">Type: {{ job.type_label }}</div>
    </q-card-section>

    <q-card-section>
      <p>{{ job.description }}</p>

      <p>
        <strong>Budget:</strong>
        {{
          job.job_request?.budget
            ? '$' + parseFloat(job.job_request.budget).toLocaleString()
            : 'N/A'
        }}
      </p>

      <p>
        <strong>Hourly Rate:</strong>
        {{
          job.job_request?.hourly_rate
            ? '$' + parseFloat(job.job_request.hourly_rate).toLocaleString()
            : 'N/A'
        }}
      </p>

      <p><strong>Status:</strong> {{ job.status_label }}</p>

      <p><strong>Skills Required:</strong></p>
      <div v-if="job.job_request?.skills?.length">
        <span v-for="(skill, index) in job.job_request.skills" :key="skill.id">
          {{ skill.name }}<span v-if="index !== job.job_request.skills.length - 1">, </span>
        </span>
      </div>
      <div v-else>
        <span>No skills specified.</span>
      </div>
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
    const { data } = await api.post(`/api/service/${props.job.id}/apply`)
    $q.notify({ message: data.message, color: 'green' })
  } catch (error) {
    console.error('Error applying for job:', error)
    $q.notify({
      message: error.response?.data?.message || 'Failed to apply for job',
      color: 'red',
    })
  }
}
</script>
