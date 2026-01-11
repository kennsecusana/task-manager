import { useRuntimeConfig } from 'nuxt/app'
import type { ApiError } from '../types'

export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = config.public.apiBase as string

  const getToken = (): string | null => {
    if (import.meta.client) {
      return localStorage.getItem('auth_token')
    }
    return null
  }

  const request = async <T>(
    url: string,
    options: Record<string, any> = {}
  ): Promise<T> => {
    try {
      const token = getToken()

      const response = await $fetch<T>(url, {
        baseURL,
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
          ...options.headers
        },
        ...options
      })

      return response
    }
    catch (error: any) {
      const apiError: ApiError = {
        message: error?.data?.message || error?.message || 'An error occurred',
        errors: error?.data?.errors || {}
      }
      throw apiError
    }
  }

  const get = <T>(url: string): Promise<T> =>
    request<T>(url, { method: 'GET' })

  const post = <T>(url: string, body: any): Promise<T> =>
    request<T>(url, { method: 'POST', body })

  const put = <T>(url: string, body: any): Promise<T> =>
    request<T>(url, { method: 'PUT', body })

  const patch = <T>(url: string, body: any): Promise<T> =>
    request<T>(url, { method: 'PATCH', body })

  const del = <T>(url: string): Promise<T> =>
    request<T>(url, { method: 'DELETE' })

  return {
    get,
    post,
    put,
    patch,
    del,
    request
  }
}