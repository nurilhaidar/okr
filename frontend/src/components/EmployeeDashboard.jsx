import { useState, useEffect, createContext } from "react";
import { Outlet, useNavigate, Link, useLocation } from "react-router-dom";
import {
  logout,
  getMe,
  getObjectivesByTracker,
  getObjectivesByApprover,
  getCheckInsByTracker,
  getOkrsByEmployee,
} from "../services/api";

export const EmployeeContext = createContext(null);

const EmployeeDashboard = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [myObjectives, setMyObjectives] = useState([]);
  const [approverObjectives, setApproverObjectives] = useState([]);
  const [myCheckIns, setMyCheckIns] = useState([]);
  const [myOkrs, setMyOkrs] = useState([]);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [activeTab, setActiveTab] = useState('my-objectives');

  const menuItems = [
    {
      name: "Dashboard",
      path: "/dashboard",
      icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
    },
  ];

  const isActive = (path) =>
    location.pathname === path ||
    (path !== "/dashboard" && location.pathname.startsWith(path));

  useEffect(() => {
    const loadUser = async () => {
      try {
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
          const userData = JSON.parse(storedUser);
          setUser(userData);

          // Check if user is admin, redirect to admin dashboard
          if (userData.role?.toLowerCase() === "admin") {
            navigate("/admin");
            return;
          }
        }

        const response = await getMe();
        if (response.success) {
          setUser(response.data.user);
          localStorage.setItem("user", JSON.stringify(response.data.user));

          // Check again if user is admin
          if (response.data.user?.role?.toLowerCase() === "admin") {
            navigate("/admin");
            return;
          }
        }
      } catch (err) {
        console.error("Failed to fetch user data:", err);
        navigate("/login");
      } finally {
        setLoading(false);
      }
    };

    loadUser();
  }, [navigate]);

  useEffect(() => {
    const fetchEmployeeData = async () => {
      if (!user) return;

      try {
        // Fetch objectives (as tracker and approver), check-ins, and OKRs in parallel
        const [objectivesRes, approverObjectivesRes, checkInsRes, okrsRes] = await Promise.all([
          getObjectivesByTracker(user.id),
          getObjectivesByApprover(user.id),
          getCheckInsByTracker(user.id),
          getOkrsByEmployee(user.id),
        ]);

        if (objectivesRes.success) {
          setMyObjectives(objectivesRes.data || []);
        }

        if (approverObjectivesRes.success) {
          setApproverObjectives(approverObjectivesRes.data || []);
        }

        if (checkInsRes.success) {
          setMyCheckIns(checkInsRes.data || []);
        }

        if (okrsRes.success) {
          setMyOkrs(okrsRes.data || []);
        }
      } catch (err) {
        console.error("Failed to fetch employee data:", err);
      }
    };

    fetchEmployeeData();
  }, [user]);

  const handleLogout = async () => {
    try {
      await logout();
    } catch (err) {
      console.error("Logout error:", err);
    } finally {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      navigate("/login");
    }
  };

  const stats = {
    totalObjectives: myObjectives.length,
    inProgress: myObjectives.filter(
      (obj) => obj.progress < 100 && obj.progress >= 0,
    ).length,
    completed: myObjectives.filter((obj) => obj.progress >= 100).length,
    totalCheckIns: myCheckIns.length,
    pendingCheckIns: myCheckIns.filter(
      (checkin) => checkin.status === "pending",
    ).length,
    approvedCheckIns: myCheckIns.filter(
      (checkin) => checkin.status === "approved",
    ).length,
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex">
      {/* Sidebar */}
      <aside
        className={`fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out ${
          sidebarOpen ? "translate-x-0" : "-translate-x-full"
        } lg:translate-x-0`}
      >
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-between p-6 border-b-2 border-gray-100">
            <div className="flex items-center">
              <div className="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-500 rounded-xl flex items-center justify-center mr-3">
                <svg
                  className="w-6 h-6 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
              <div>
                <h1 className="text-lg font-bold text-gray-900">
                  Employee Portal
                </h1>
                <p className="text-xs text-gray-500">OKR Management</p>
              </div>
            </div>
            <button
              onClick={() => setSidebarOpen(false)}
              className="lg:hidden text-gray-500 hover:text-gray-700"
            >
              <svg
                className="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          {/* User Info */}
          <div className="p-4 border-b-2 border-gray-100">
            <div className="flex items-center">
              <div className="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                {user?.name?.charAt(0)?.toUpperCase() || "U"}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">
                  {user?.name}
                </p>
                <p className="text-xs text-gray-500 truncate">
                  {user?.position || "Employee"}
                </p>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 overflow-y-auto p-4">
            <ul className="space-y-2">
              {menuItems.map((item) => (
                <li key={item.path}>
                  <Link
                    to={item.path}
                    onClick={() => setSidebarOpen(false)}
                    className={`flex items-center px-4 py-3 rounded-xl transition-all duration-200 ${
                      isActive(item.path)
                        ? "bg-gradient-to-r from-green-500 to-blue-500 text-white shadow-md"
                        : "text-gray-700 hover:bg-gray-100"
                    }`}
                  >
                    <svg
                      className="w-5 h-5 mr-3"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d={item.icon}
                      />
                    </svg>
                    <span className="font-medium">{item.name}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </nav>

          {/* Logout Button */}
          <div className="p-4 border-t-2 border-gray-100">
            <button
              onClick={handleLogout}
              className="flex items-center w-full px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-colors"
            >
              <svg
                className="w-5 h-5 mr-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                />
              </svg>
              <span className="font-medium">Logout</span>
            </button>
          </div>
        </div>
      </aside>

      {/* Overlay for mobile */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-20 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        ></div>
      )}

      {/* Main Content */}
      <div className="flex-1 lg:pl-64">
        {/* Top Bar */}
        <header className="bg-white shadow-sm sticky top-0 z-10">
          <div className="flex items-center justify-between px-6 py-4">
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="lg:hidden text-gray-500 hover:text-gray-700"
            >
              <svg
                className="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d={
                    sidebarOpen
                      ? "M6 18L18 6M6 6l12 12"
                      : "M4 6h16M4 12h16M4 18h16"
                  }
                />
              </svg>
            </button>
            <h2 className="text-xl font-bold text-gray-900 ml-4 lg:ml-0">
              {menuItems.find((item) => isActive(item.path))?.name ||
                "Dashboard"}
            </h2>
          </div>
        </header>

        {/* Page Content */}
        <main>
          <EmployeeContext.Provider
            value={{ user, myObjectives, approverObjectives, myCheckIns, myOkrs, loading, activeTab, setActiveTab }}
          >
            <Outlet />
          </EmployeeContext.Provider>
        </main>
      </div>
    </div>
  );
};

export default EmployeeDashboard;
