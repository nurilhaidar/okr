import { useState, useEffect } from 'react'
import {
  getOrgUnits,
  createOrgUnit,
  updateOrgUnit,
  deactivateOrgUnit,
  activateOrgUnit,
  deleteOrgUnit,
  getOrgUnitMembers,
  getOrgUnitAvailableRoles,
  addOrgUnitMember,
  updateOrgUnitMemberRole,
  removeOrgUnitMember,
  getOrgUnitTypes,
  getEmployees
} from '../services/api'

const OrgUnits = () => {
  const [orgUnits, setOrgUnits] = useState([])
  const [orgUnitTypes, setOrgUnitTypes] = useState([])
  const [employees, setEmployees] = useState([])
  const [loading, setLoading] = useState(true)
  const [showModal, setShowModal] = useState(false)
  const [showMembersModal, setShowMembersModal] = useState(false)
  const [showAddMemberModal, setShowAddMemberModal] = useState(false)
  const [showDeactivateModal, setShowDeactivateModal] = useState(false)
  const [showActivateModal, setShowActivateModal] = useState(false)
  const [orgUnitToDeactivate, setOrgUnitToDeactivate] = useState(null)
  const [orgUnitToActivate, setOrgUnitToActivate] = useState(null)
  const [editingOrgUnit, setEditingOrgUnit] = useState(null)
  const [selectedOrgUnit, setSelectedOrgUnit] = useState(null)
  const [members, setMembers] = useState([])
  const [availableRoles, setAvailableRoles] = useState([])
  const [searchTerm, setSearchTerm] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [formData, setFormData] = useState({
    name: '',
    custom_type: '',
    orgunit_type_id: '',
    parent_id: '',
    is_active: true
  })
  const [memberFormData, setMemberFormData] = useState({
    employee_id: '',
    orgunit_role_id: ''
  })

  useEffect(() => {
    fetchAllData()
  }, [])

  const fetchAllData = async () => {
    try {
      const [orgUnitsRes, typesRes, employeesRes] = await Promise.all([
        getOrgUnits(),
        getOrgUnitTypes(),
        getEmployees()
      ])
      setOrgUnits(orgUnitsRes.data)
      setOrgUnitTypes(typesRes.data)
      setEmployees(employeesRes.data)
    } catch (error) {
      console.error('Error fetching data:', error)
    } finally {
      setLoading(false)
    }
  }

  const fetchMembers = async (orgUnitId) => {
    try {
      const response = await getOrgUnitMembers(orgUnitId)
      setMembers(response.data)
    } catch (error) {
      console.error('Error fetching members:', error)
    }
  }

  const fetchAvailableRoles = async (orgUnitId) => {
    try {
      const response = await getOrgUnitAvailableRoles(orgUnitId)
      setAvailableRoles(response.data.roles || [])
    } catch (error) {
      console.error('Error fetching available roles:', error)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      if (editingOrgUnit) {
        await updateOrgUnit(editingOrgUnit.id, formData)
      } else {
        await createOrgUnit(formData)
      }
      await fetchAllData()
      setShowModal(false)
      resetForm()
    } catch (error) {
      console.error('Error saving org unit:', error)
      alert(error.response?.data?.message || 'Error saving org unit')
    }
  }

  const handleEdit = (orgUnit) => {
    setEditingOrgUnit(orgUnit)
    setFormData({
      name: orgUnit.name,
      custom_type: orgUnit.custom_type || '',
      orgunit_type_id: orgUnit.orgunit_type_id || '',
      parent_id: orgUnit.parent_id || '',
      is_active: orgUnit.is_active
    })
    setShowModal(true)
  }

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this org unit?')) {
      try {
        await deleteOrgUnit(id)
        await fetchAllData()
      } catch (error) {
        console.error('Error deleting org unit:', error)
        alert(error.response?.data?.message || 'Error deleting org unit')
      }
    }
  }

  const handleToggleActive = async (orgUnit) => {
    if (orgUnit.is_active) {
      setOrgUnitToDeactivate(orgUnit)
      setShowDeactivateModal(true)
    } else {
      setOrgUnitToActivate(orgUnit)
      setShowActivateModal(true)
    }
  }

  const confirmDeactivate = async () => {
    if (!orgUnitToDeactivate) return

    try {
      await deactivateOrgUnit(orgUnitToDeactivate.id)
      await fetchAllData()
      setShowDeactivateModal(false)
      setOrgUnitToDeactivate(null)
    } catch (error) {
      console.error('Error deactivating org unit:', error)
      alert(error.response?.data?.message || 'Error deactivating org unit')
    }
  }

  const confirmActivate = async () => {
    if (!orgUnitToActivate) return

    try {
      await activateOrgUnit(orgUnitToActivate.id)
      await fetchAllData()
      setShowActivateModal(false)
      setOrgUnitToActivate(null)
    } catch (error) {
      console.error('Error activating org unit:', error)
      alert(error.response?.data?.message || 'Error activating org unit')
    }
  }

  const handleManageMembers = async (orgUnit) => {
    setSelectedOrgUnit(orgUnit)
    setShowMembersModal(true)
    await fetchMembers(orgUnit.id)
    await fetchAvailableRoles(orgUnit.id)
  }

  const handleAddMember = async (e) => {
    e.preventDefault()
    try {
      await addOrgUnitMember(selectedOrgUnit.id, memberFormData)
      await fetchMembers(selectedOrgUnit.id)
      await fetchAvailableRoles(selectedOrgUnit.id)
      setShowAddMemberModal(false)
      setMemberFormData({ employee_id: '', orgunit_role_id: '' })
    } catch (error) {
      console.error('Error adding member:', error)
      alert(error.response?.data?.message || 'Error adding member')
    }
  }

  const handleUpdateMemberRole = async (memberId, newRoleId) => {
    try {
      await updateOrgUnitMemberRole(selectedOrgUnit.id, memberId, { orgunit_role_id: newRoleId })
      await fetchMembers(selectedOrgUnit.id)
      await fetchAvailableRoles(selectedOrgUnit.id)
    } catch (error) {
      console.error('Error updating member role:', error)
      alert(error.response?.data?.message || 'Error updating member role')
    }
  }

  const handleRemoveMember = async (memberId) => {
    if (window.confirm('Are you sure you want to remove this member?')) {
      try {
        await removeOrgUnitMember(selectedOrgUnit.id, memberId)
        await fetchMembers(selectedOrgUnit.id)
        await fetchAvailableRoles(selectedOrgUnit.id)
      } catch (error) {
        console.error('Error removing member:', error)
        alert(error.response?.data?.message || 'Error removing member')
      }
    }
  }

  const resetForm = () => {
    setFormData({
      name: '',
      custom_type: '',
      orgunit_type_id: '',
      parent_id: '',
      is_active: true
    })
    setEditingOrgUnit(null)
  }

  const getAvailableEmployees = () => {
    const existingEmployeeIds = members.map(m => m.employee_id)
    return employees.filter(emp => !existingEmployeeIds.includes(emp.id))
  }

  const filteredOrgUnits = orgUnits.filter(orgUnit => {
    const matchesSearch = orgUnit.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (orgUnit.custom_type && orgUnit.custom_type.toLowerCase().includes(searchTerm.toLowerCase()))

    const matchesStatus = statusFilter === 'all' ||
      (statusFilter === 'active' && orgUnit.is_active) ||
      (statusFilter === 'inactive' && !orgUnit.is_active)

    return matchesSearch && matchesStatus
  })

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
      </div>
    )
  }

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
          <h1 className="text-3xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
            Org Units
          </h1>
          <p className="text-gray-600 mt-1">Manage organizational units and their members</p>
        </div>
        <button
          onClick={() => { setShowModal(true); resetForm() }}
          className="flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:shadow-lg transition-all duration-200"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          <span className="font-medium">Add Org Unit</span>
        </button>
      </div>

      <div className="bg-white rounded-2xl shadow-sm border-2 border-gray-100 p-4 mb-6 space-y-4">
        <div className="relative">
          <svg className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Search org units..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
          />
        </div>
        <div className="flex items-center space-x-2">
          <span className="text-sm font-medium text-gray-700">Status:</span>
          <div className="flex rounded-lg overflow-hidden border-2 border-gray-200">
            <button
              onClick={() => setStatusFilter('all')}
              className={`px-4 py-2 text-sm font-medium transition-colors ${
                statusFilter === 'all'
                  ? 'bg-primary text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              }`}
            >
              All
            </button>
            <button
              onClick={() => setStatusFilter('active')}
              className={`px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200 ${
                statusFilter === 'active'
                  ? 'bg-green-500 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              }`}
            >
              Active
            </button>
            <button
              onClick={() => setStatusFilter('inactive')}
              className={`px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200 ${
                statusFilter === 'inactive'
                  ? 'bg-red-500 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              }`}
            >
              Inactive
            </button>
          </div>
          <span className="text-sm text-gray-500 ml-2">
            ({filteredOrgUnits.length} {filteredOrgUnits.length === 1 ? 'unit' : 'units'})
          </span>
        </div>
      </div>

      <div className="grid gap-4">
        {filteredOrgUnits.length === 0 ? (
          <div className="bg-white rounded-2xl shadow-sm border-2 border-gray-100 p-12 text-center text-gray-500">
            <svg className="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p className="text-lg font-medium">No org units found</p>
            <p className="text-sm mt-1">Create a new org unit to get started</p>
          </div>
        ) : (
          filteredOrgUnits.map((orgUnit) => (
            <div key={orgUnit.id} className="bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
              <div className="p-6">
                <div className="flex items-start justify-between">
                  <div className="flex items-start space-x-4">
                    <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${orgUnit.is_active ? 'bg-gradient-to-br from-primary to-primary-light' : 'bg-gray-300'}`}>
                      <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <h3 className="text-lg font-semibold text-gray-900">{orgUnit.name}</h3>
                      <div className="flex items-center space-x-2 mt-1">
                        {orgUnit.type && (
                          <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {orgUnit.type.name}
                          </span>
                        )}
                        {orgUnit.custom_type && (
                          <span className="text-xs text-gray-500">• {orgUnit.custom_type}</span>
                        )}
                      </div>
                      {orgUnit.parent && (
                        <p className="text-xs text-gray-500 mt-1">
                          Parent: <span className="font-medium">{orgUnit.parent.name}</span>
                        </p>
                      )}
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
                      orgUnit.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}>
                      {orgUnit.is_active ? 'Active' : 'Inactive'}
                    </span>
                  </div>
                </div>
                <div className="mt-4 flex items-center justify-between">
                  <div className="flex items-center space-x-4 text-sm text-gray-500">
                    {orgUnit.children && orgUnit.children.length > 0 && (
                      <span className="flex items-center">
                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {orgUnit.children.length} sub-units
                      </span>
                    )}
                    {orgUnit.employees && orgUnit.employees.length > 0 && (
                      <span className="flex items-center">
                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {orgUnit.employees.length} members
                      </span>
                    )}
                  </div>
                  <div className="flex items-center space-x-2">
                    <button
                      onClick={() => handleManageMembers(orgUnit)}
                      className="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                      <span>Members</span>
                    </button>
                    <button
                      onClick={() => handleToggleActive(orgUnit)}
                      className={`p-2 rounded-lg transition-colors ${
                        orgUnit.is_active
                          ? 'text-amber-600 hover:bg-amber-100'
                          : 'text-green-600 hover:bg-green-100'
                      }`}
                      title={orgUnit.is_active ? 'Deactivate' : 'Activate'}
                    >
                      {orgUnit.is_active ? (
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      ) : (
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      )}
                    </button>
                    <button
                      onClick={() => handleEdit(orgUnit)}
                      className="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      onClick={() => handleDelete(orgUnit.id)}
                      className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Create/Edit OrgUnit Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-gray-200 sticky top-0 bg-white">
              <h2 className="text-xl font-bold text-gray-900">
                {editingOrgUnit ? 'Edit Org Unit' : 'Add Org Unit'}
              </h2>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="e.g., Engineering Department"
                  required
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Custom Type</label>
                <input
                  type="text"
                  value={formData.custom_type}
                  onChange={(e) => setFormData({ ...formData, custom_type: e.target.value })}
                  placeholder="e.g., Department"
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Org Unit Type</label>
                <select
                  value={formData.orgunit_type_id}
                  onChange={(e) => setFormData({ ...formData, orgunit_type_id: e.target.value })}
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                >
                  <option value="">Select Type</option>
                  {orgUnitTypes.map((type) => (
                    <option key={type.id} value={type.id}>{type.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Parent Org Unit</label>
                <select
                  value={formData.parent_id}
                  onChange={(e) => setFormData({ ...formData, parent_id: e.target.value })}
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                >
                  <option value="">No Parent (Root Level)</option>
                  {orgUnits
                    .filter(ou => !editingOrgUnit || ou.id !== editingOrgUnit.id)
                    .map((ou) => (
                      <option key={ou.id} value={ou.id}>{ou.name}</option>
                    ))}
                </select>
              </div>
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="isActive"
                  checked={formData.is_active}
                  onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                  className="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <label htmlFor="isActive" className="ml-3 text-sm font-medium text-gray-700">Active</label>
              </div>
              <div className="flex space-x-3 pt-4">
                <button
                  type="button"
                  onClick={() => { setShowModal(false); resetForm() }}
                  className="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="flex-1 px-4 py-3 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:shadow-lg transition-all duration-200 font-medium"
                >
                  {editingOrgUnit ? 'Update' : 'Create'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Members Management Modal */}
      {showMembersModal && selectedOrgUnit && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <div>
                <h2 className="text-xl font-bold text-gray-900">Manage Members</h2>
                <p className="text-sm text-gray-500">{selectedOrgUnit.name}</p>
              </div>
              <button
                onClick={() => { setShowMembersModal(false); setSelectedOrgUnit(null) }}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div className="p-6 border-b border-gray-200">
              <button
                onClick={() => setShowAddMemberModal(true)}
                className="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:shadow-lg transition-all duration-200"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                <span className="font-medium">Add Member</span>
              </button>
            </div>

            <div className="flex-1 overflow-y-auto p-6">
              {members.length === 0 ? (
                <div className="text-center py-12 text-gray-500">
                  <svg className="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <p className="text-lg font-medium">No members yet</p>
                  <p className="text-sm mt-1">Add members to this org unit</p>
                </div>
              ) : (
                <div className="grid gap-4">
                  {members.map((member) => (
                    <div key={member.id} className="bg-gray-50 rounded-xl p-4 flex items-center justify-between">
                      <div className="flex items-center space-x-4">
                        <div className="w-10 h-10 bg-gradient-to-br from-primary to-primary-light rounded-full flex items-center justify-center text-white font-semibold">
                          {member.employee_name.charAt(0)}
                        </div>
                        <div>
                          <p className="font-medium text-gray-900">{member.employee_name}</p>
                          <p className="text-sm text-gray-500">{member.employee_email}</p>
                          <p className="text-xs text-gray-400">{member.employee_position}</p>
                        </div>
                      </div>
                      <div className="flex items-center space-x-3">
                        <div className="relative">
                          <select
                            value={member.orgunit_role_id}
                            onChange={(e) => handleUpdateMemberRole(member.id, parseInt(e.target.value))}
                            className={`appearance-none px-4 py-2 pr-8 rounded-lg text-sm font-medium border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none cursor-pointer ${
                              member.is_exclusive
                                ? 'bg-amber-50 border-amber-300 text-amber-800'
                                : 'bg-blue-50 border-blue-300 text-blue-800'
                            }`}
                          >
                            {availableRoles.map((role) => (
                              <option
                                key={role.id}
                                value={role.id}
                                disabled={!role.available && role.id !== member.orgunit_role_id}
                              >
                                {role.name} {!role.available && '(taken)'}
                              </option>
                            ))}
                          </select>
                          <svg className="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                        </div>
                        <button
                          onClick={() => handleRemoveMember(member.id)}
                          className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                          title="Remove member"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Add Member Modal */}
      {showAddMemberModal && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">Add Member</h2>
            </div>
            <form onSubmit={handleAddMember} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Employee *</label>
                <select
                  value={memberFormData.employee_id}
                  onChange={(e) => setMemberFormData({ ...memberFormData, employee_id: e.target.value })}
                  required
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                >
                  <option value="">Select Employee</option>
                  {getAvailableEmployees().map((emp) => (
                    <option key={emp.id} value={emp.id}>
                      {emp.name} ({emp.email})
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                <select
                  value={memberFormData.orgunit_role_id}
                  onChange={(e) => setMemberFormData({ ...memberFormData, orgunit_role_id: e.target.value })}
                  required
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                >
                  <option value="">Select Role</option>
                  {availableRoles
                    .filter(role => role.available)
                    .map((role) => (
                      <option key={role.id} value={role.id}>
                        {role.name} {role.is_exclusive && '(exclusive)'}
                      </option>
                    ))}
                </select>
                {!availableRoles.filter(role => role.available).length && (
                  <p className="text-xs text-amber-600 mt-1">All exclusive roles are taken. Only Member role is available.</p>
                )}
              </div>
              <div className="flex space-x-3 pt-4">
                <button
                  type="button"
                  onClick={() => { setShowAddMemberModal(false); setMemberFormData({ employee_id: '', orgunit_role_id: '' }) }}
                  className="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="flex-1 px-4 py-3 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:shadow-lg transition-all duration-200 font-medium"
                >
                  Add Member
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Deactivate Confirmation Modal */}
      {showDeactivateModal && orgUnitToDeactivate && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6 text-center">
              <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.532-3L5.008 5.25C5.426 4.045 7.096 3 9 3h6c1.904 0 3.574 1.045 3.992 2.25l1.593 6.75c.97 1.333 2.502 3 4.068 3H19c1.566 0 3.098-1.667 4.068-3L19.992 5.25C20.504 4.045 22.074 3 24 3h-2c-1.904 0-3.574 1.045-3.992 2.25L15.415 12c-.97 1.333-2.502 3-4.068 3H9z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Deactivate Org Unit</h3>
              <p className="text-gray-600 mb-6">
                Are you sure you want to deactivate <span className="font-semibold text-gray-900">"{orgUnitToDeactivate.name}"</span>?
              </p>
              <p className="text-sm text-red-600 bg-red-50 rounded-lg p-3 mb-6">
                This will prevent the org unit from being used in the system.
              </p>
              <div className="flex space-x-3">
                <button
                  onClick={() => { setShowDeactivateModal(false); setOrgUnitToDeactivate(null) }}
                  className="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  onClick={confirmDeactivate}
                  className="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 font-medium"
                >
                  Deactivate
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Activate Confirmation Modal */}
      {showActivateModal && orgUnitToActivate && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6 text-center">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Activate Org Unit</h3>
              <p className="text-gray-600 mb-6">
                Are you sure you want to activate <span className="font-semibold text-gray-900">"{orgUnitToActivate.name}"</span>?
              </p>
              <p className="text-sm text-green-600 bg-green-50 rounded-lg p-3 mb-6">
                This will make the org unit available for use in the system.
              </p>
              <div className="flex space-x-3">
                <button
                  onClick={() => { setShowActivateModal(false); setOrgUnitToActivate(null) }}
                  className="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  onClick={confirmActivate}
                  className="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 font-medium"
                >
                  Activate
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default OrgUnits
