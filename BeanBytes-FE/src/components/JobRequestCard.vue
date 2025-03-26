<template>
  <div>
    <q-card class="q-mb-md q-pa-md">
      <q-item>
        <q-item-section>
          <q-item-label class="text-h6">{{ job.title }}</q-item-label>

          <q-item-label caption>{{ job.description.substring(0, 100) }}...</q-item-label>

          <q-item-label caption>
            <strong>Type:</strong> {{ job.type }} &nbsp; | <strong>Status:</strong> {{ job.status }}
          </q-item-label>
        </q-item-section>

        <q-item-section side>
          <q-icon
            class="cursor-pointer"
            :name="expandable ? (expanded ? 'expand_less' : 'expand_more') : 'chevron_right'"
            @click="expandable ? toggleExpand() : openJobDetail()"
          />
        </q-item-section>
      </q-item>

      <q-slide-transition>
        <div v-show="expanded" class="q-pa-md bg-grey-9">
          <div v-if="job.applications?.length">
            <div
              v-for="application in job.applications"
              :key="application.id"
              class="q-mb-sm row items-center justify-between"
            >
              <div
                class="row items-center cursor-pointer"
                @click="router.push('/user/' + application.user.name)"
              >
                <q-avatar size="40px">
                  <img :src="fixedImage(application.user?.profile?.profile_image?.path)" />
                </q-avatar>

                <div class="q-ml-sm">
                  <div class="text-bold text-white">{{ application.user.name }}</div>
                  <div class="text-grey-5">
                    {{ application.user.profile?.bio || 'No bio available' }}
                  </div>
                </div>
              </div>

              <div class="row q-gutter-sm">
                <q-btn
                  label="Accept"
                  color="positive"
                  icon="check"
                  dense
                  @click.stop="acceptApplicant(job.id, application.id)"
                />
                <q-btn
                  label="Decline"
                  color="negative"
                  icon="close"
                  dense
                  @click.stop="rejectApplicant(job.id, application.id)"
                />
              </div>
            </div>
          </div>

          <div v-else class="text-grey-5 text-center">No applications yet.</div>
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

const router = useRouter()
const $q = useQuasar()
const jobDialog = ref(false)
const expanded = ref(false)
const defaultAvatar = '/images/default-avatar.png'

const openJobDetail = () => {
  jobDialog.value = true
}

const toggleExpand = () => {
  expanded.value = !expanded.value
}

const fixedImage = (path) => {
  if (!path) return defaultAvatar
  return path.startsWith('http')
    ? path
    : `http://127.0.0.1:8000/storage/${path.replace(/\\/g, '/')}`
}

const acceptApplicant = async (jobRequestId, interactionId) => {
  try {
    await api.post(`/api/job-requests/${jobRequestId}/accept/${interactionId}`)
    $q.notify({ type: 'positive', message: 'Applicant accepted.' })
  } catch (error) {
    console.error(error)
    $q.notify({ type: 'negative', message: 'Failed to accept applicant.' })
  }
}

const rejectApplicant = async (jobRequestId, interactionId) => {
  try {
    await api.post(`/api/job-requests/${jobRequestId}/reject/${interactionId}`)
    $q.notify({ type: 'warning', message: 'Applicant rejected.' })
  } catch (error) {
    console.error(error)
    $q.notify({ type: 'negative', message: 'Failed to reject applicant.' })
  }
}
</script>

<style scoped>
.text-bold {
  font-weight: bold;
}
</style>
