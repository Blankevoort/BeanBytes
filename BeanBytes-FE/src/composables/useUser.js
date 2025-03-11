import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'

export function useUser() {
  const user = ref(null)
  const loggedIn = ref(false)
  const $q = useQuasar()
  const token = ref($q.cookies.get('token') || null)

  function fetchUserData() {
    if (!token.value) return

    api
      .get('/api/user', {
        headers: { Authorization: `Bearer ${token.value}` },
      })
      .then((r) => {
        if (r.data) {
          user.value = r.data
          loggedIn.value = true
        }
      })
      .catch((error) => {
        console.error('Error fetching user data:', error)
        logout()
      })
  }

  function login(userData, newToken) {
    user.value = userData
    loggedIn.value = true
    token.value = newToken

    $q.cookies.set('token', newToken, { expires: 360 })

    fetchUserData()
  }

  function logout() {
    user.value = null
    loggedIn.value = false
    token.value = null

    api.post('api/logout').then($q.cookies.remove('token'), location.reload())
  }

  onMounted(() => {
    if (token.value) fetchUserData()
  })

  return {
    user,
    loggedIn,
    fetchUserData,
    login,
    logout,
  }
}
