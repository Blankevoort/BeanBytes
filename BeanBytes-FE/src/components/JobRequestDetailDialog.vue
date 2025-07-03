<template>
  <q-card
    class="q-pa-md"
    style="width: 500px"
    :class="$q.dark.isActive ? 'bg-dark text-grey-2' : 'bg-white text-black'"
  >
    <q-card-section>
      <div class="text-h6">{{ job.title }}</div>
      <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-grey-3' : 'text-grey-7'">
        Type: {{ job.type_label }}
      </div>
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

      <p>
        <strong>Status:</strong>
        <span :class="$q.dark.isActive ? 'text-grey-3' : 'text-grey-7'">
          {{ job.status_label }}
        </span>
      </p>

      <p :class="$q.dark.isActive ? 'text-grey-3' : 'text-black'">
        <strong>Skills Required:</strong>
      </p>
      <div
        v-if="job.job_request?.skills?.length"
        :class="$q.dark.isActive ? 'text-grey-2' : 'text-grey-7'"
      >
        <span v-for="(skill, index) in job.job_request.skills" :key="skill.id">
          {{ skill.name }}<span v-if="index !== job.job_request.skills.length - 1">, </span>
        </span>
      </div>
      <div v-else :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">
        No skills specified.
      </div>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn label="Apply" color="primary" @click="applyJob" />
      <q-btn
        label="Close"
        flat
        @click="$emit('close')"
        :color="$q.dark.isActive ? 'grey-3' : 'grey-7'"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { defineProps } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const props = defineProps({ job: Object })

const applyJob = async () => {
  try {
    const { data } = await api.post(`/api/service/${props.job.id}/apply`)
    $q.notify({ message: data.message, color: 'positive' })
  } catch (err) {
    $q.notify({
      message: err.response?.data?.message || 'Failed to apply for job',
      color: 'negative',
    })
  }
}
</script>
