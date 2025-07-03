<template>
  <q-page
    class="row justify-center"
    :class="[$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black']"
  >
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="text-center q-my-md">
        <q-avatar size="125px" v-if="profileImagePreview">
          <img :src="profileImagePreview" />
        </q-avatar>
      </div>

      <q-form @submit="updateUser">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="Full Name" v-model="name" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="Username" v-model="username" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="Email" v-model="email" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="Phone" v-model="phone" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="Job Title" v-model="jobTitle" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="Location" v-model="location" />
          </div>

          <div class="col-12 q-pa-sm">
            <q-input class="form-inputs" type="textarea" placeholder="Bio" v-model="bio" rows="3"  />
          </div>

          <div class="col-12 q-pa-sm">
            <q-file
              v-model="profileImage"
              label="Upload Profile Image"
              accept="image/*"
              :fill-color="$q.dark.isActive ? '#2a2a2a' : 'grey-2'"
              :color="$q.dark.isActive ? 'white' : 'black'"
            >
              <template v-slot:prepend>
                <q-icon name="cloud_upload" />
              </template>
            </q-file>
          </div>
        </div>

        <div class="col-12 q-px-sm q-gutter-x-md q-py-md">
          <q-btn class="cart-btn" label="Submit" type="submit" />
          <q-btn label="Reset" type="reset" />
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<script setup>
import { ref, watchEffect, computed } from 'vue'
import { api } from 'src/boot/axios'
import { useErrorHandling } from 'src/composables/useErrorHandling'
import { useAuthStore } from 'src/stores/auth'

const authStore = useAuthStore()
const { handleApiError } = useErrorHandling()

const name = ref('')
const username = ref('')
const phone = ref('')
const email = ref('')
const jobTitle = ref('')
const bio = ref('')
const location = ref('')
const profileImage = ref(null)

const profileImagePreview = computed(() => {
  const imagePath = authStore.user?.profile?.profile_image?.path

  if (imagePath) {
    return imagePath.startsWith('http')
      ? imagePath
      : `http://127.0.0.1:8000/storage/${imagePath.replace(/\\/g, '/')}`
  }

  return 'path/to/default/profile-image.png'
})

watchEffect(() => {
  if (authStore.user) {
    name.value = authStore.user.name
    username.value = authStore.user.username
    phone.value = authStore.user.phone
    email.value = authStore.user.email
    jobTitle.value = authStore.user.profile?.job_title || ''
    bio.value = authStore.user.profile?.bio || ''
    location.value = authStore.user.profile?.location || ''
  }
})

async function updateUser() {
  try {
    const formData = new FormData()
    formData.append('name', name.value)
    formData.append('username', username.value)
    formData.append('phone', phone.value)
    formData.append('email', email.value)
    formData.append('job_title', jobTitle.value)
    formData.append('bio', bio.value)
    formData.append('location', location.value)

    if (profileImage.value) {
      formData.append('profile_image', profileImage.value)
    }

    const { data } = await api.post('/api/user/update', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    authStore.setUser(data.user)

    if (data.profile_image) {
      authStore.user.profile.profile_image = {
        ...authStore.user.profile.profile_image,
        path: data.profile_image.startsWith('http')
          ? data.profile_image
          : `http://127.0.0.1:8000/storage/${data.profile_image.replace(/\\/g, '/')}`,
      }
    }
  } catch (err) {
    handleApiError(err)
  }
}
</script>

<style>
.form-inputs input,
.form-inputs textarea {
  width: 100%;
  padding: 0.75rem;
  font-size: 0.9rem;
  resize: vertical;
}

.form-inputs input::placeholder,
.form-inputs textarea::placeholder {
  color: #888;
}

.form-inputs input:focus,
.form-inputs textarea:focus {
  outline: 2px solid #555;
}

.form-inputs .q-field__control {
  font-size: 0.9rem;
}
</style>
