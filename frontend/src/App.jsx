import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { ToastContainer } from 'react-toastify'
import 'react-toastify/dist/ReactToastify.css'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import AdminDashboard from './components/AdminDashboard'
import EmployeeDashboard from './components/EmployeeDashboard'
import EmployeeDashboardHome from './pages/EmployeeDashboardHome'
import Employees from './pages/Employees'
import Roles from './pages/Roles'
import OrgUnitTypes from './pages/OrgUnitTypes'
import OrgUnitRoles from './pages/OrgUnitRoles'
import OrgUnits from './pages/OrgUnits'
import OkrList from './pages/OkrList'
import OkrCreate from './pages/OkrCreate'
import OkrEdit from './pages/OkrEdit'
import OkrTypes from './pages/OkrTypes'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/login" replace />} />
        <Route path="/login" element={<Login />} />

        {/* Admin Routes - with /admin prefix */}
        <Route path="/admin" element={<AdminDashboard />}>
          <Route index element={<Dashboard />} />
          <Route path="employees" element={<Employees />} />
          <Route path="roles" element={<Roles />} />
          <Route path="orgunit-types" element={<OrgUnitTypes />} />
          <Route path="orgunit-roles" element={<OrgUnitRoles />} />
          <Route path="orgunits" element={<OrgUnits />} />
          <Route path="okrs" element={<OkrList />} />
          <Route path="okrs/create" element={<OkrCreate />} />
          <Route path="okrs/:id/edit" element={<OkrEdit />} />
          <Route path="okr-types" element={<OkrTypes />} />
        </Route>

        {/* Employee Routes - direct page names */}
        <Route path="/dashboard" element={<EmployeeDashboard />}>
          <Route index element={<EmployeeDashboardHome />} />
          {/* Future employee routes can be added here */}
        </Route>
      </Routes>
      <ToastContainer
        position="top-right"
        autoClose={3000}
        hideProgressBar={false}
        newestOnTop
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="colored"
      />
    </BrowserRouter>
  )
}

export default App
