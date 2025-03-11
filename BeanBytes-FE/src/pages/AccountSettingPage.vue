<template>
  <q-page class="bg-dark row justify-center">
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="text-bold q-py-md q-px-sm" style="font-size: 24px">Account</div>

      <q-form @submit="updateUser">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Fullname" v-model="fullname" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Username" v-model="username" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Email" v-model="email" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Phone" v-model="phone" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Job Title" v-model="jobTitle" />
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
import { ref } from 'vue'
import { api } from 'src/boot/axios'
import { useErrorHandling } from 'src/composables/useErrorHandling'

const fullname = ref()
const username = ref()
const phone = ref()
const email = ref()
const jobTitle = ref()
const { handleApiError } = useErrorHandling()

function updateUser() {
  api
    .put('api/user/update', {
      fullname: fullname.value,
      username: username.value,
      phone: phone.value,
      email: email.value,
      jobTitle: jobTitle.value,
    })
    .then(() => {
      window.reload()
    })
    .catch((err) => {
      handleApiError(err)
    })
}
</script>

<style>
.form-inputs input {
  width: 100%;
  background-color: #2a2a2a;
  border-radius: 4px;
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
