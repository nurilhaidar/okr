import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import AdminDashboard from './components/AdminDashboard'
import Employees from './pages/Employees'
import Roles from './pages/Roles'
import OrgUnitTypes from './pages/OrgUnitTypes'
import OrgUnitRoles from './pages/OrgUnitRoles'
import OrgUnits from './pages/OrgUnits'
import Okrs from './pages/Okrs'
import OkrTypes from './pages/OkrTypes'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/login" replace />} />
        <Route path="/login" element={<Login />} />
        <Route path="/dashboard" element={<AdminDashboard />}>
          <Route index element={<Dashboard />} />
          <Route path="employees" element={<Employees />} />
          <Route path="roles" element={<Roles />} />
          <Route path="orgunit-types" element={<OrgUnitTypes />} />
          <Route path="orgunit-roles" element={<OrgUnitRoles />} />
          <Route path="orgunits" element={<OrgUnits />} />
          <Route path="okrs" element={<Okrs />} />
          <Route path="okr-types" element={<OkrTypes />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
