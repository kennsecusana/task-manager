export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.server) return

  const token = localStorage.getItem('auth_token')

  if (token && to.path === '/login') {
    return navigateTo('/', { replace: true })
  }

  if (!token && to.path !== '/login') {
    return navigateTo('/login', { replace: true })
  }
})