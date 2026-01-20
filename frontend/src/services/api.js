import axios from 'axios'

const API_BASE_URL = 'http://localhost:8000/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Handle 401 responses
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Auth APIs
export const login = async (email, password) => {
  const response = await api.post('/login', { email, password })
  return response.data
}

export const logout = async () => {
  const response = await api.post('/logout')
  return response.data
}

export const getMe = async () => {
  const response = await api.get('/me')
  return response.data
}

// Employee APIs
export const getEmployees = async () => {
  const response = await api.get('/employees')
  return response.data
}

export const getEmployee = async (id) => {
  const response = await api.get(`/employees/${id}`)
  return response.data
}

export const createEmployee = async (data) => {
  const response = await api.post('/employees', data)
  return response.data
}

export const updateEmployee = async (id, data) => {
  const response = await api.put(`/employees/${id}`, data)
  return response.data
}

export const deactivateEmployee = async (id) => {
  const response = await api.patch(`/employees/${id}/deactivate`)
  return response.data
}

export const activateEmployee = async (id) => {
  const response = await api.patch(`/employees/${id}/activate`)
  return response.data
}

export const deleteEmployee = async (id) => {
  const response = await api.delete(`/employees/${id}`)
  return response.data
}

// Role APIs
export const getRoles = async () => {
  const response = await api.get('/roles')
  return response.data
}

export const createRole = async (data) => {
  const response = await api.post('/roles', data)
  return response.data
}

export const updateRole = async (id, data) => {
  const response = await api.put(`/roles/${id}`, data)
  return response.data
}

export const deleteRole = async (id) => {
  const response = await api.delete(`/roles/${id}`)
  return response.data
}

export default api
