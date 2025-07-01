<template>
  <q-page class="bg-dark text-grey-6 row justify-center">
    <q-page class="row justify-center items-center">
      <q-card
        bordered
        dark
        class="q-pa-lg q-px-xl bg-dark shadow-0"
        style="width: 400px; border-radius: 16px"
      >
        <q-form @submit="handleSubmit">
          <div class="q-my-lg text-center row items-center text-center">
            <q-btn
              dense
              v-if="step !== 'enterEPhone'"
              flat
              color="grey-7"
              class="col-"
              @click="goBack"
            >
              <q-icon name="arrow_back" />
            </q-btn>
            <div class="text-h6 col">
              <span class="text-bold">{{ title }}</span>
            </div>

            <div v-if="step !== 'enterEPhone'" class="q-my-md full-width">{{ emailPhone }}</div>
          </div>

          <q-input
            v-if="step === 'enterEPhone'"
            v-model="emailPhone"
            outlined
            dark
            :rules="[(val) => !!val || 'Required!']"
            label="Enter Phone or Email"
          />
          <q-input
            v-if="step === 'enterPassword'"
            v-model="password"
            outlined
            dark
            :type="isPwd ? 'password' : 'text'"
            label="Password"
            :rules="[(val) => !!val || 'Required!', val && val.length > 6]"
          >
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility' : 'visibility_off'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>
          <q-input
            v-if="step === 'registerPhoneSent'"
            v-model="email"
            outlined
            dark
            type="email"
            label="Enter your Email"
            :rules="[(val) => !!val || 'Required!']"
          />
          <q-input
            v-if="step === 'registerEmailSent'"
            v-model="registerPhone"
            outlined
            dark
            type="tel"
            label="Enter your Phone Number"
            :rules="[(val) => !!val || 'Required!']"
          />
          <q-input
            v-if="step === 'registerPhoneSaved'"
            v-model="registerPassword"
            outlined
            dark
            :type="isPwd ? 'password' : 'text'"
            label="Choose Password"
            :rules="[(val) => !!val || 'Required!']"
          >
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility' : 'visibility_off'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>
          <q-input
            v-if="step === 'registerPasswordSaved'"
            v-model="registerEmailConfirm"
            outlined
            dark
            label="Confirm your Email"
            :rules="[(val) => !!val || 'Required!']"
          />
          <q-input
            v-if="step === 'registerPassword'"
            v-model="registerPassword"
            outlined
            dark
            :type="isPwd ? 'password' : 'text'"
            label="Choose a Password"
            :rules="[
              (val) => !!val || 'Required!',
              (val) => val.length >= 6 || 'Must be at least 6 characters',
            ]"
          >
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility' : 'visibility_off'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>

          <q-btn class="full-width q-py-md cart-btn q-mt-md" type="submit">
            <div class="q-py-sm text">{{ buttonText }}</div>
          </q-btn>
        </q-form>
      </q-card>
    </q-page>
  </q-page>
</template>

<script>
import { ref, computed } from 'vue'
import { api } from 'src/boot/axios'
import { useUser } from 'src/composables/useUser'
import { useErrorHandling } from 'src/composables/useErrorHandling'

export default {
  setup() {
    const { login } = useUser()
    const { handleApiError } = useErrorHandling()

    const step = ref('enterEPhone')
    const emailPhone = ref('')
    const password = ref('')
    const email = ref('')
    const registerPhone = ref('')
    const registerPassword = ref('')
    const registerEmailConfirm = ref('')
    const isPwd = ref(true)

    const title = computed(
      () =>
        ({
          enterEPhone: 'Login | Register',
          enterPassword: 'Enter your Password',
          registerPhoneSent: 'Enter your Email',
          registerEmailSent: 'Enter your Phone Number',
          registerPhoneSaved: 'Choose Password',
          registerPasswordSaved: 'Confirm your Email',
        })[step.value],
    )

    const buttonText = computed(() => (step.value === 'enterPassword' ? 'Login' : 'Next'))

    const goBack = () => {
      step.value = 'enterEPhone'
    }

    const handleSubmit = async () => {
      try {
        if (step.value === 'enterEPhone') {
          const { data } = await api.post('/api/check', { emailPhone: emailPhone.value })

          if (data.status === 'login') {
            step.value = 'enterPassword'
          } else if (data.phone == '') {
            step.value = 'registerPhoneSent'
          } else if (data.email == '') {
            step.value = 'registerEmailSent'
          }
        } else if (step.value === 'registerPhoneSent') {
          step.value = 'registerPassword'
        } else if (step.value === 'registerEmailSent') {
          step.value = 'registerPassword'
        } else if (step.value === 'registerPassword') {
          const r = await api.post('/api/register', {
            email: email.value || emailPhone.value,
            phone: registerPhone.value || emailPhone.value,
            password: registerPassword.value,
          })
          if (r.data?.token) login(r.data.user, r.data.token)
          window.location.href = '/'
        } else if (step.value === 'enterPassword') {
          const r = await api.post('/api/login', {
            emailPhone: emailPhone.value,
            password: password.value,
          })
          if (r.data?.token) login(r.data.user, r.data.token)
          window.location.href = '/'
        }
      } catch (error) {
        handleApiError(error)
      }
    }

    return {
      step,
      emailPhone,
      password,
      email,
      registerPhone,
      registerPassword,
      registerEmailConfirm,
      isPwd,
      title,
      buttonText,
      goBack,
      handleSubmit,
    }
  },
}
</script>
