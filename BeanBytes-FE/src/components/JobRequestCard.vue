<template>
  <div>
    <q-card
      class="q-mb-md"
      :class="$q.dark.isActive ? 'bg-grey-11 text-grey-2' : 'bg-white text-black'"
    >
      <q-item>
        <q-item-section>
          <q-item-label class="text-h6">
            {{ job.title }}
          </q-item-label>
          <q-item-label caption> {{ job.description.substring(0, 100) }}... </q-item-label>
          <q-item-label caption>
            <strong>Type:</strong> {{ job.type_label }}
            &nbsp;|&nbsp;
            <strong>Status:</strong> {{ job.status_label }}
          </q-item-label>
        </q-item-section>

        <q-item-section v-if="job.status != 'in_progress'" side>
          <q-icon
            class="cursor-pointer"
            :name="expandable ? (expanded ? 'expand_less' : 'expand_more') : 'chevron_right'"
            :color="$q.dark.isActive ? 'grey-2' : 'grey-8'"
            @click="expandable ? toggleExpand() : openJobDetail()"
          />
        </q-item-section>
      </q-item>

      <q-slide-transition>
        <div
          v-show="expanded"
          class="q-pa-md"
          :class="$q.dark.isActive ? 'bg-grey-10' : 'bg-grey-2'"
        >
          <div v-if="job.type === 'hiring' && job.job_request?.applications?.length">
            <div
              v-for="application in job.job_request.applications"
              :key="application.id"
              class="q-mb-sm row items-center justify-between"
            >
              <div class="row items-center cursor-pointer" @click="goToUser(application.user)">
                <q-avatar size="40px">
                  <img :src="fixedImage(application.user?.profile?.profile_image?.path)" />
                </q-avatar>

                <div class="q-ml-sm" :class="$q.dark.isActive ? 'text-grey-1' : 'text-black'">
                  {{ application.user?.name }}
                </div>
              </div>

              <div class="row q-gutter-sm">
                <q-btn
                  dense
                  round
                  icon="check"
                  :color="$q.dark.isActive ? 'positive' : 'positive'"
                  @click.stop="acceptApplicant(job.id, application.user_id)"
                />
                <q-btn
                  dense
                  round
                  icon="close"
                  color="negative"
                  @click.stop="rejectApplicant(job.id, application.user_id)"
                />
              </div>
            </div>
          </div>

          <div v-else :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-6'" class="text-center">
            No applications yet.
          </div>
        </div>
      </q-slide-transition>
    </q-card>

    <q-dialog v-model="jobDialog">
      <JobRequestDetailDialog :job="job" @close="jobDialog = false" />
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, defineProps } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'
import JobRequestDetailDialog from 'src/components/JobRequestDetailDialog.vue'

defineProps({
  job: Object,
  expandable: { type: Boolean, default: false },
})

const $q = useQuasar()
const router = useRouter()
const jobDialog = ref(false)
const expanded = ref(false)

function toggleExpand() {
  expanded.value = !expanded.value
}
function openJobDetail() {
  jobDialog.value = true
}

function fixedImage(path) {
  if (!path) return '/images/default-avatar.png'
  return path.startsWith('http')
    ? path
    : `http://127.0.0.1:8000/storage/${path.replace(/\\/g, '/')}`
}

function goToUser(user) {
  if (user) {
    router.push(`/user/${user.username || user.name}`)
  }
}

async function acceptApplicant(serviceId, applicantUserId) {
  try {
    await api.post(`/api/service/${serviceId}/applicants/${applicantUserId}/accept`)
    $q.notify({ type: 'positive', message: 'Applicant accepted.' })
  } catch {
    $q.notify({ type: 'negative', message: 'Failed to accept applicant.' })
  }
}

async function rejectApplicant(serviceId, applicantUserId) {
  try {
    await api.post(`/api/service/${serviceId}/applicants/${applicantUserId}/reject`)
    $q.notify({ type: 'warning', message: 'Applicant rejected.' })
  } catch {
    $q.notify({ type: 'negative', message: 'Failed to reject applicant.' })
  }
}
</script>

<style scoped>
.text-bold {
  font-weight: bold;
}
</style>
