<template>
  <q-page class="bg-dark row justify-center">
    <div class="text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <q-list separator>
        <q-item-label class="text-h6 text-white q-py-md">Notifications</q-item-label>

        <q-item v-if="isLoading">
          <q-item-section class="text-grey">Loading...</q-item-section>
        </q-item>

        <q-item v-for="notification in notifications" :key="notification.id" clickable v-ripple>
          <q-item-section class="text-secondary">
            <q-item-label>
              {{ notification.message }}
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-btn
              flat
              dense
              round
              icon="delete"
              color="red"
              @click="removeNotification(notification.id)"
            />
          </q-item-section>
        </q-item>
      </q-list>

      <q-item v-if="!isLoading && notifications.length === 0">
        <q-item-section class="text-grey">No notifications yet</q-item-section>
      </q-item>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'

const notifications = ref([])
const isLoading = ref(false)

const fetchNotifications = async () => {
  if (isLoading.value) return
  isLoading.value = true

  try {
    const { data } = await api.get('/api/notifications')
    notifications.value = data || []
  } catch (error) {
    console.error('Error fetching notifications:', error)
    notifications.value = []
  } finally {
    isLoading.value = false
  }
}

const removeNotification = async (id) => {
  notifications.value = notifications.value.filter((n) => n.id !== id)

  try {
    await api.delete(`/api/notifications/${id}`)
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

onMounted(fetchNotifications)
</script>
