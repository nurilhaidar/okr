import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { logout, getMe } from '../services/api'

const Dashboard = () => {
  const navigate = useNavigate()
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

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
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h1 className="text-xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">OKR System</h1>
            </div>

            <div className="flex items-center space-x-4">
              <div className="hidden sm:flex items-center space-x-2">
                <div className="w-8 h-8 bg-gradient-to-br from-primary to-primary-light rounded-full flex items-center justify-center">
                  <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                  </svg>
                </div>
                <span className="text-sm font-medium text-gray-700">{user?.name}</span>
              </div>

              <button
                onClick={handleLogout}
                className="inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent to-accent-dark text-white text-sm font-medium rounded-lg hover:from-accent-dark hover:to-accent focus:outline-none focus:ring-4 focus:ring-accent/50 transition-all duration-200 shadow-md hover:shadow-lg"
              >
                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>

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
    </div>
  )
}

export default Dashboard
