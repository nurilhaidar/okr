import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { logout, getMe, getPendingApprovals, approveCheckIn, rejectCheckIn, deleteCheckIn } from '../services/api'

const Dashboard = () => {
  const navigate = useNavigate()
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const [pendingApprovals, setPendingApprovals] = useState([])
  const [loadingApprovals, setLoadingApprovals] = useState(true)
  const [currentUser, setCurrentUser] = useState(null)
  const [showModal, setShowModal] = useState(false)
  const [modalConfig, setModalConfig] = useState({
    title: '',
    message: '',
    onConfirm: null,
    type: 'danger' // 'danger' or 'info'
  })

  const showConfirmModal = (title, message, onConfirm, type = 'danger') => {
    setModalConfig({ title, message, onConfirm, type })
    setShowModal(true)
  }

  const showErrorModal = (title, message) => {
    setModalConfig({ title, message, onConfirm: () => setShowModal(false), type: 'info' })
    setShowModal(true)
  }

  useEffect(() => {
    const loadUser = async () => {
      try {
        const storedUser = localStorage.getItem('user')
        if (storedUser) {
          setUser(JSON.parse(storedUser))
        }

        const response = await getMe()
        if (response.success) {
          setUser(response.data.user)
          setCurrentUser(response.data.employee)
          localStorage.setItem('user', JSON.stringify(response.data.user))
        }
      } catch (err) {
        console.error('Failed to fetch user data:', err)
      } finally {
        setLoading(false)
      }
    }

    loadUser()
  }, [])

  useEffect(() => {
    const fetchPendingApprovals = async () => {
      try {
        setLoadingApprovals(true)
        const response = await getPendingApprovals()
        setPendingApprovals(response.data || [])
      } catch (err) {
        console.error('Failed to fetch pending approvals:', err)
        setPendingApprovals([])
      } finally {
        setLoadingApprovals(false)
      }
    }

    if (currentUser) {
      fetchPendingApprovals()
    }
  }, [currentUser])

  const handleApprove = (checkInId) => {
    showConfirmModal(
      'Approve Check-In',
      'Are you sure you want to approve this check-in?',
      async () => {
        try {
          await approveCheckIn(checkInId)
          // Refresh pending approvals
          const response = await getPendingApprovals()
          setPendingApprovals(response.data || [])
          setShowModal(false)
          // Show success message
          showErrorModal('Success', 'Check-in approved successfully!')
        } catch (err) {
          console.error('Failed to approve check-in:', err)
          setShowModal(false)
          showErrorModal('Error', err.response?.data?.message || 'Failed to approve check-in')
        }
      }
    )
  }

  const handleReject = (checkInId) => {
    showConfirmModal(
      'Reject Check-In',
      'Are you sure you want to reject this check-in?',
      async () => {
        try {
          await rejectCheckIn(checkInId)
          // Refresh pending approvals
          const response = await getPendingApprovals()
          setPendingApprovals(response.data || [])
          setShowModal(false)
          // Show success message
          showErrorModal('Success', 'Check-in rejected successfully!')
        } catch (err) {
          console.error('Failed to reject check-in:', err)
          setShowModal(false)
          showErrorModal('Error', err.response?.data?.message || 'Failed to reject check-in')
        }
      }
    )
  }

  const handleDelete = async (checkInId) => {
    showConfirmModal(
      'Delete Check-In',
      'Are you sure you want to delete this check-in?',
      async () => {
        try {
          await deleteCheckIn(checkInId)
          // Refresh pending approvals
          const response = await getPendingApprovals()
          setPendingApprovals(response.data || [])
          setShowModal(false)
        } catch (err) {
          console.error('Failed to delete check-in:', err)
          showErrorModal('Error', err.response?.data?.message || 'Failed to delete check-in')
        }
      }
    )
  }

  const canDeleteCheckIn = () => {
    // Allow delete if user is admin
    return user?.role === 'admin' || currentUser?.role === 'admin'
  }

  const handleLogout = async () => {
    try {
      await logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      navigate('/login')
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-red-50">
      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Welcome Section */}
        <div className="mb-8">
          <h2 className="text-3xl font-bold text-gray-900">Welcome back, {user?.name}!</h2>
          <p className="text-gray-600 mt-2">Here's your overview</p>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div className="bg-white rounded-2xl shadow-sm p-6 border-2 border-gray-100 hover:border-primary/30 transition-all duration-200">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-semibold text-gray-500">Role</p>
                <p className="text-2xl font-bold text-gray-900 mt-1">{user?.role || 'N/A'}</p>
              </div>
              <div className="w-12 h-12 bg-gradient-to-br from-primary to-primary-light rounded-xl flex items-center justify-center shadow-md">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 border-2 border-gray-100 hover:border-primary/30 transition-all duration-200">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-semibold text-gray-500">Position</p>
                <p className="text-2xl font-bold text-gray-900 mt-1">{user?.position || 'N/A'}</p>
              </div>
              <div className="w-12 h-12 bg-gradient-to-br from-primary-light to-primary rounded-xl flex items-center justify-center shadow-md">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 border-2 border-gray-100 hover:border-accent/30 transition-all duration-200">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-semibold text-gray-500">Rank</p>
                <p className="text-2xl font-bold text-gray-900 mt-1">{user?.rank || 'N/A'}</p>
              </div>
              <div className="w-12 h-12 bg-gradient-to-br from-accent to-accent-light rounded-xl flex items-center justify-center shadow-md">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        {/* Pending Approvals Section */}
        <div className="bg-white rounded-2xl shadow-sm p-6 border-2 border-gray-100 mb-8">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-semibold text-gray-900">Pending Check-In Approvals</h3>
            <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
              {pendingApprovals.length} Pending
            </span>
          </div>
          {loadingApprovals ? (
            <div className="flex items-center justify-center py-8">
              <div className="animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
            </div>
          ) : pendingApprovals.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <svg className="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p className="text-sm">No pending approvals</p>
            </div>
          ) : (
            <div className="space-y-3">
              {pendingApprovals.map((checkIn) => (
                <div key={checkIn.id} className="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="font-semibold text-gray-900">Value: {checkIn.current_value}</span>
                        <span className="text-xs text-gray-500">
                          {new Date(checkIn.date).toLocaleDateString()}
                        </span>
                      </div>
                      <div className="text-sm text-gray-600">
                        <p><span className="font-medium">Objective:</span> {checkIn.objective?.description || 'N/A'}</p>
                        <p><span className="font-medium">Tracker:</span> {checkIn.objective?.tracker_employee?.name || 'N/A'}</p>
                        {checkIn.comments && (
                          <p className="mt-2 text-gray-500 italic">"{checkIn.comments}"</p>
                        )}
                      </div>
                    </div>
                    <div className="flex items-center gap-2 ml-4">
                      <button
                        onClick={() => handleApprove(checkIn.id)}
                        className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                      >
                        <svg className="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                        Approve
                      </button>
                      <button
                        onClick={() => handleReject(checkIn.id)}
                        className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                      >
                        <svg className="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reject
                      </button>
                      {canDeleteCheckIn() && (
                        <button
                          onClick={() => handleDelete(checkIn.id)}
                          className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                        >
                          <svg className="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Delete
                        </button>
                      )}
                    </div>
                  </div>
                  {checkIn.evidence_path && (
                    <div className="mt-3">
                      <a
                        href={checkIn.evidence_path}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-xs text-primary hover:underline flex items-center"
                      >
                        <svg className="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        View Evidence
                      </a>
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>

        {/* User Details Card */}
        <div className="bg-white rounded-2xl shadow-sm p-6 border-2 border-gray-100 mb-8">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">User Details</h3>
          <dl className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="bg-gradient-to-r from-blue-50 to-blue-100/50 rounded-xl p-4">
              <dt className="text-sm font-medium text-gray-500">Full Name</dt>
              <dd className="mt-1 text-base font-semibold text-gray-900">{user?.name}</dd>
            </div>
            <div className="bg-gradient-to-r from-blue-50 to-blue-100/50 rounded-xl p-4">
              <dt className="text-sm font-medium text-gray-500">Email</dt>
              <dd className="mt-1 text-base font-semibold text-gray-900">{user?.email}</dd>
            </div>
            <div className="bg-gradient-to-r from-red-50 to-red-100/50 rounded-xl p-4">
              <dt className="text-sm font-medium text-gray-500">Username</dt>
              <dd className="mt-1 text-base font-semibold text-gray-900">{user?.username}</dd>
            </div>
            <div className="bg-gradient-to-r from-red-50 to-red-100/50 rounded-xl p-4">
              <dt className="text-sm font-medium text-gray-500">Employee ID</dt>
              <dd className="mt-1 text-base font-semibold text-gray-900">#{user?.id}</dd>
            </div>
          </dl>
        </div>

        {/* Quick Actions */}
        <div>
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <button className="flex items-center justify-center px-6 py-4 bg-white border-2 border-gray-200 rounded-xl hover:border-primary hover:bg-blue-50 transition-all duration-200 group shadow-sm hover:shadow-md">
              <svg className="w-6 h-6 text-primary mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002 2v3m0 0v-3a2 2 0 012 2h2a2 2 0 012 2v3m0 0c0 1.5.448 0 002.684 0m0 0c0 1.5.448 0 2.684 0" />
              </svg>
              <span className="font-medium text-gray-700">View OKRs</span>
            </button>

            <button className="flex items-center justify-center px-6 py-4 bg-white border-2 border-gray-200 rounded-xl hover:border-primary hover:bg-blue-50 transition-all duration-200 group shadow-sm hover:shadow-md">
              <svg className="w-6 h-6 text-primary mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0h6" />
              </svg>
              <span className="font-medium text-gray-700">Create Objective</span>
            </button>

            <button className="flex items-center justify-center px-6 py-4 bg-white border-2 border-gray-200 rounded-xl hover:border-accent hover:bg-red-50 transition-all duration-200 group shadow-sm hover:shadow-md">
              <svg className="w-6 h-6 text-accent mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span className="font-medium text-gray-700">View Reports</span>
            </button>
          </div>
        </div>
      </main>

      {/* Confirm/Error Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6">
              <div className="flex items-center mb-4">
                {modalConfig.type === 'danger' ? (
                  <svg className="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                ) : (
                  <svg className="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                )}
                <h3 className="text-xl font-bold text-gray-900">{modalConfig.title}</h3>
              </div>
              <p className="text-gray-700 mb-6">{modalConfig.message}</p>
              <div className="flex justify-end gap-3">
                {modalConfig.type === 'danger' ? (
                  <>
                    <button
                      onClick={() => setShowModal(false)}
                      className="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      onClick={() => {
                        if (modalConfig.onConfirm) {
                          modalConfig.onConfirm()
                        }
                      }}
                      className="px-6 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors"
                    >
                      Confirm
                    </button>
                  </>
                ) : (
                  <button
                    onClick={() => {
                      setShowModal(false)
                      if (modalConfig.onConfirm) {
                        modalConfig.onConfirm()
                      }
                    }}
                    className="px-6 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors"
                  >
                    OK
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Dashboard
