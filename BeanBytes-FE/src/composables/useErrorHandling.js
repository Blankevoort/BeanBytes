import { ref } from 'vue'
import { useQuasar } from 'quasar'

export function useErrorHandling() {
  const $q = useQuasar()
  const error = ref('')

  function handleApiError(err) {
    if (err.response) {
      if (err.response.status === 400) {
        error.value = 'اطلاعات وارد شده معتبر نیستند.'
      } else if (err.response.status === 401) {
        error.value = 'اطلاعات وارد شده معتبر نیستند.'
      } else if (err.response.status === 403) {
        error.value = 'دسترسی غیرمجاز.'
      } else {
        error.value = 'خطای سمت سرور: درخواست نامعتبر.'
      }
    } else if (err.request) {
      error.value = 'خطای سمت سرور: درخواست ارسال نشد.'
    } else {
      error.value = 'خطای سمت سرور: خطای نامشخص رخ داد.'
    }
    triggerError()
  }

  function triggerError() {
    $q.notify({
      position: 'top-left',
      type: 'negative',
      message: error.value,
      badgeStyle: 'opacity: 0',
    })
  }

  function triggerSuccess(message) {
    $q.notify({
      position: 'top-left',
      type: 'positive',
      message: message,
      badgeStyle: 'opacity: 0',
    })
  }

  return {
    handleApiError,
    triggerError,
    triggerSuccess,
    error,
  }
}
