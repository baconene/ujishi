export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    await authStore.fetchUser()
  }

  if (!authStore.isAuthenticated) {
    return navigateTo(`/auth/login?redirect=${to.path}`)
  }
})
