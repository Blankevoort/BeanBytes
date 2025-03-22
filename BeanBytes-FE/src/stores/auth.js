import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isFetching: false,
  }),
  actions: {
    async fetchUser() {
      if (this.user || this.isFetching) return

      this.isFetching = true

      try {
        const { data } = await api.get('/api/user')
        this.user = data
      } catch (error) {
        console.error('Error fetching user:', error)
      } finally {
        this.isFetching = false
      }
    },

    setUser(updatedUser) {
      this.user = updatedUser
    },
  },
})
