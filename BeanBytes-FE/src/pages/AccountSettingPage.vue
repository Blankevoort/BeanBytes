<template>
  <q-page class="bg-dark row justify-center">
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="text-bold q-py-md q-px-sm" style="font-size: 24px">Account</div>

      <q-form @submit="updateUser">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" placeholder="name" v-model="name" />
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
        </div>

        <div class="col-12 q-px-sm q-gutter-x-md q-py-md">
          <q-btn class="cart-btn" label="submit" type="submit" />

          <q-btn label="Reset" type="reset" />
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<script setup>
import { ref, watchEffect } from 'vue'
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

watchEffect(() => {
  if (authStore.user) {
    name.value = authStore.user.name
    username.value = authStore.user.username
    phone.value = authStore.user.phone
    email.value = authStore.user.email
    jobTitle.value = authStore.user.jobTitle
  }
})

async function updateUser() {
  try {
    const { data } = await api.put('api/user/update', {
      name: name.value,
      username: username.value,
      phone: phone.value,
      email: email.value,
      jobTitle: jobTitle.value,
    })

    authStore.setUser(data.user)
  } catch (err) {
    handleApiError(err)
  }
}
</script>

<style>
.form-inputs input {
  width: 100%;
  background-color: #2a2a2a;
  padding: 0.75rem;
  color: #fff;
  font-size: 0.9rem;
  resize: vertical;
}

.form-inputs input::placeholder {
  color: #888;
}

.form-inputs input:focus {
  outline: 2px solid #555;
}
</style>
