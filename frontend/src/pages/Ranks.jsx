import { useState, useEffect } from 'react'
import { getRanks, createRank, updateRank, deleteRank } from '../services/api'

const Ranks = () => {
  const [ranks, setRanks] = useState([])
  const [loading, setLoading] = useState(true)
  const [showModal, setShowModal] = useState(false)
  const [editingRank, setEditingRank] = useState(null)
  const [searchTerm, setSearchTerm] = useState('')
  const [formData, setFormData] = useState({
    name: '',
    level: '',
    description: ''
  })

  useEffect(() => {
    fetchRanks()
  }, [])

  const fetchRanks = async () => {
    try {
      const response = await getRanks()
      setRanks(response.data)
    } catch (error) {
      console.error('Error fetching ranks:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      if (editingRank) {
        await updateRank(editingRank.id, formData)
      } else {
        await createRank(formData)
      }
      setShowModal(false)
      resetForm()
      fetchRanks()
    } catch (error) {
      alert(error.response?.data?.message || 'Error saving rank')
    }
  }

  const handleEdit = (rank) => {
    setEditingRank(rank)
    setFormData({
      name: rank.name,
      level: rank.level || '',
      description: rank.description || ''
    })
    setShowModal(true)
  }

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this rank?')) {
      try {
        await deleteRank(id)
        fetchRanks()
      } catch (error) {
        alert('Error deleting rank')
      }
    }
  }

  const resetForm = () => {
    setFormData({
      name: '',
      level: '',
      description: ''
    })
    setEditingRank(null)
  }

  const filteredRanks = ranks.filter(rank =>
    rank.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (rank.description && rank.description.toLowerCase().includes(searchTerm.toLowerCase()))
  )

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
      </div>
    )
  }

  return (
    <div className="p-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Ranks</h2>
          <p className="text-gray-600 mt-1">Manage employee ranks and levels</p>
        </div>
        <button
          onClick={() => { setShowModal(true); resetForm() }}
          className="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary transition-all duration-200 shadow-md hover:shadow-lg"
        >
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0h6" />
          </svg>
          Add Rank
        </button>
      </div>

      {/* Search */}
      <div className="mb-6">
        <input
          type="text"
          placeholder="Search ranks..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
        />
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredRanks.map((rank) => (
          <div key={rank.id} className="bg-white rounded-2xl shadow-sm border-2 border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div className="flex items-start justify-between mb-4">
              <div className="w-12 h-12 bg-gradient-to-br from-accent to-orange-400 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                {rank.level || rank.name.charAt(0).toUpperCase()}
              </div>
              <div className="flex gap-1">
                <button
                  onClick={() => handleEdit(rank)}
                  className="p-2 text-primary hover:bg-blue-100 rounded-lg transition-colors"
                  title="Edit"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  onClick={() => handleDelete(rank.id)}
                  className="p-2 text-accent hover:bg-red-100 rounded-lg transition-colors"
                  title="Delete"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
            <h3 className="text-lg font-semibold text-gray-900 mb-1">{rank.name}</h3>
            {rank.level && (
              <span className="inline-block px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full mb-2">
                Level {rank.level}
              </span>
            )}
            <p className="text-gray-600 text-sm mt-2">{rank.description || 'No description'}</p>
          </div>
        ))}
      </div>

      {filteredRanks.length === 0 && (
        <div className="text-center py-12 text-gray-500">No ranks found</div>
      )}

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl shadow-xl max-w-md w-full">
            <div className="p-6 border-b border-gray-200">
              <h3 className="text-xl font-bold text-gray-900">
                {editingRank ? 'Edit Rank' : 'Add New Rank'}
              </h3>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Rank Name *</label>
                <input
                  type="text"
                  required
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Level</label>
                <input
                  type="number"
                  value={formData.level}
                  onChange={(e) => setFormData({ ...formData, level: e.target.value })}
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows={3}
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none resize-none"
                />
              </div>
              <div className="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => { setShowModal(false); resetForm() }}
                  className="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary transition-all duration-200"
                >
                  {editingRank ? 'Update' : 'Create'} Rank
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}

export default Ranks
