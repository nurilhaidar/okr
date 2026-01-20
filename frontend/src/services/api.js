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

// OrgUnit APIs
export const getOrgUnits = async () => {
  const response = await api.get('/orgunits')
  return response.data
}

export const getOrgUnit = async (id) => {
  const response = await api.get(`/orgunits/${id}`)
  return response.data
}

export const createOrgUnit = async (data) => {
  const response = await api.post('/orgunits', data)
  return response.data
}

export const updateOrgUnit = async (id, data) => {
  const response = await api.put(`/orgunits/${id}`, data)
  return response.data
}

export const deactivateOrgUnit = async (id) => {
  const response = await api.patch(`/orgunits/${id}/deactivate`)
  return response.data
}

export const activateOrgUnit = async (id) => {
  const response = await api.patch(`/orgunits/${id}/activate`)
  return response.data
}

export const deleteOrgUnit = async (id) => {
  const response = await api.delete(`/orgunits/${id}`)
  return response.data
}

export const getOrgUnitMembers = async (id) => {
  const response = await api.get(`/orgunits/${id}/members`)
  return response.data
}

export const getOrgUnitAvailableRoles = async (id) => {
  const response = await api.get(`/orgunits/${id}/available-roles`)
  return response.data
}

export const addOrgUnitMember = async (id, data) => {
  const response = await api.post(`/orgunits/${id}/members`, data)
  return response.data
}

export const updateOrgUnitMemberRole = async (id, memberId, data) => {
  const response = await api.patch(`/orgunits/${id}/members/${memberId}`, data)
  return response.data
}

export const removeOrgUnitMember = async (id, memberId) => {
  const response = await api.delete(`/orgunits/${id}/members/${memberId}`)
  return response.data
}

export const getOrgUnitsDatatables = async (params) => {
  const response = await api.get('/orgunits/datatables', { params })
  return response.data
}

// OrgUnitType APIs
export const getOrgUnitTypes = async () => {
  const response = await api.get('/orgunit-types')
  return response.data
}

export const getOrgUnitType = async (id) => {
  const response = await api.get(`/orgunit-types/${id}`)
  return response.data
}

export const createOrgUnitType = async (data) => {
  const response = await api.post('/orgunit-types', data)
  return response.data
}

export const updateOrgUnitType = async (id, data) => {
  const response = await api.put(`/orgunit-types/${id}`, data)
  return response.data
}

export const deleteOrgUnitType = async (id) => {
  const response = await api.delete(`/orgunit-types/${id}`)
  return response.data
}

export const getOrgUnitTypesDatatables = async (params) => {
  const response = await api.get('/orgunit-types/datatables', { params })
  return response.data
}

// OrgUnitRole APIs
export const getOrgUnitRoles = async () => {
  const response = await api.get('/orgunit-roles')
  return response.data
}

export const getOrgUnitRole = async (id) => {
  const response = await api.get(`/orgunit-roles/${id}`)
  return response.data
}

export const createOrgUnitRole = async (data) => {
  const response = await api.post('/orgunit-roles', data)
  return response.data
}

export const updateOrgUnitRole = async (id, data) => {
  const response = await api.put(`/orgunit-roles/${id}`, data)
  return response.data
}

export const deleteOrgUnitRole = async (id) => {
  const response = await api.delete(`/orgunit-roles/${id}`)
  return response.data
}

export const getOrgUnitRolesDatatables = async (params) => {
  const response = await api.get('/orgunit-roles/datatables', { params })
  return response.data
}

export default api
