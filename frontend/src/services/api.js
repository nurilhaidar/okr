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
export const getEmployees = async (params = {}) => {
  const response = await api.get('/employees', { params })
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
export const getRoles = async (params = {}) => {
  const response = await api.get('/roles', { params })
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
export const getOrgUnitTypes = async (params = {}) => {
  const response = await api.get('/orgunit-types', { params })
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
export const getOrgUnitRoles = async (params = {}) => {
  const response = await api.get('/orgunit-roles', { params })
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

// OKR APIs
export const getOkrs = async (params) => {
  const response = await api.get('/okrs', { params })
  return response.data
}

export const getOkr = async (id) => {
  const response = await api.get(`/okrs/${id}`)
  return response.data
}

export const createOkr = async (data) => {
  const response = await api.post('/okrs', data)
  return response.data
}

export const updateOkr = async (id, data) => {
  const response = await api.put(`/okrs/${id}`, data)
  return response.data
}

export const deleteOkr = async (id) => {
  const response = await api.delete(`/okrs/${id}`)
  return response.data
}

export const activateOkr = async (id) => {
  const response = await api.patch(`/okrs/${id}/activate`)
  return response.data
}

export const deactivateOkr = async (id) => {
  const response = await api.patch(`/okrs/${id}/deactivate`)
  return response.data
}

export const getAvailableOwners = async () => {
  const response = await api.get('/okrs/available-owners')
  return response.data
}

export const getOkrsByEmployee = async (employeeId) => {
  const response = await api.get(`/okrs/by-employee/${employeeId}`)
  return response.data
}

// OKR Type APIs
export const getOkrTypes = async (params = {}) => {
  const response = await api.get('/okr-types', { params })
  return response.data
}

export const getOkrType = async (id) => {
  const response = await api.get(`/okr-types/${id}`)
  return response.data
}

export const createOkrType = async (data) => {
  const response = await api.post('/okr-types', data)
  return response.data
}

export const updateOkrType = async (id, data) => {
  const response = await api.put(`/okr-types/${id}`, data)
  return response.data
}

export const deleteOkrType = async (id) => {
  const response = await api.delete(`/okr-types/${id}`)
  return response.data
}

// Objective APIs
export const getObjectives = async (params) => {
  const response = await api.get('/objectives', { params })
  return response.data
}

export const getObjective = async (id) => {
  const response = await api.get(`/objectives/${id}`)
  return response.data
}

export const createObjective = async (data) => {
  const response = await api.post('/objectives', data)
  return response.data
}

export const updateObjective = async (id, data) => {
  const response = await api.put(`/objectives/${id}`, data)
  return response.data
}

export const deleteObjective = async (id) => {
  const response = await api.delete(`/objectives/${id}`)
  return response.data
}

export const getObjectivesByOkr = async (okrId) => {
  const response = await api.get(`/objectives/by-okr/${okrId}`)
  return response.data
}

export const getObjectivesByTracker = async (trackerId) => {
  const response = await api.get(`/objectives/by-tracker/${trackerId}`)
  return response.data
}

export const getObjectivesByApprover = async (approverId) => {
  const response = await api.get(`/objectives/by-approver/${approverId}`)
  return response.data
}

// CheckIn APIs
export const getCheckIns = async (params) => {
  const response = await api.get('/check-ins', { params })
  return response.data
}

export const getCheckIn = async (id) => {
  const response = await api.get(`/check-ins/${id}`)
  return response.data
}

export const createCheckIn = async (data) => {
  const response = await api.post('/check-ins', data)
  return response.data
}

export const updateCheckIn = async (id, data) => {
  const response = await api.put(`/check-ins/${id}`, data)
  return response.data
}

export const deleteCheckIn = async (id) => {
  const response = await api.delete(`/check-ins/${id}`)
  return response.data
}

export const getCheckInsByObjective = async (objectiveId) => {
  const response = await api.get(`/check-ins/by-objective/${objectiveId}`)
  return response.data
}

export const getCheckInsByTracker = async (trackerId) => {
  const response = await api.get(`/check-ins/by-tracker/${trackerId}`)
  return response.data
}

export const approveCheckIn = async (id) => {
  const response = await api.post(`/check-ins/${id}/approve`)
  return response.data
}

export const rejectCheckIn = async (id) => {
  const response = await api.post(`/check-ins/${id}/reject`)
  return response.data
}

export const getCheckInApprovalLogs = async (id) => {
  const response = await api.get(`/check-ins/${id}/approval-logs`)
  return response.data
}

export const getPendingApprovals = async () => {
  const response = await api.get('/check-ins/pending-approvals')
  return response.data
}

export default api
