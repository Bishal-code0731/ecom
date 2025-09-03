import axios from 'axios'

// Base Axios instance for non-API requests (like CSRF cookie)
export const baseAxios = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// Axios instance for API calls (with /api prefix)
const api = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// CSRF helper (call before login/register)
export const getCsrfCookie = async () => {
  try {
    await baseAxios.get('/sanctum/csrf-cookie')
  } catch (err) {
    console.error('CSRF cookie fetch failed:', err)
    throw err
  }
}

// Request interceptor: attach Bearer token + XSRF-TOKEN
api.interceptors.request.use(
  (config) => {
    // Add Bearer token if exists
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Add XSRF-TOKEN header if present in cookies
    const xsrfToken = document.cookie
      .split('; ')
      .find((row) => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1]

    if (xsrfToken) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken)
    }

    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor: auto-logout on 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
