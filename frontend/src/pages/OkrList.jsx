import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import {
  getOkrs,
  deleteOkr,
  activateOkr,
  deactivateOkr,
  getOkrTypes,
  getEmployees,
  getObjectivesByOkr,
  deleteObjective,
  getCheckInsByObjective,
  createCheckIn,
  approveCheckIn,
  rejectCheckIn,
  deleteCheckIn,
  getCheckInApprovalLogs,
} from "../services/api";

const OkrList = () => {
  const navigate = useNavigate();
  const [okrs, setOkrs] = useState([]);
  const [okrTypes, setOkrTypes] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showObjectivesModal, setShowObjectivesModal] = useState(false);
  const [showCheckInModal, setShowCheckInModal] = useState(false);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmModalConfig, setConfirmModalConfig] = useState({
    title: "",
    message: "",
    onConfirm: null,
  });
  const [selectedObjective, setSelectedObjective] = useState(null);
  const [checkIns, setCheckIns] = useState([]);
  const [approvalLogs, setApprovalLogs] = useState({});
  const [expandedOkrId, setExpandedOkrId] = useState(null);
  const [okrObjectives, setOkrObjectives] = useState({});
  const [selectedOkr, setSelectedOkr] = useState(null);
  const [objectives, setObjectives] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [okrTypeFilter, setOkrTypeFilter] = useState("all");
  const [checkInFormData, setCheckInFormData] = useState({
    date: new Date().toISOString().split("T")[0],
    current_value: "",
    comments: "",
    evidence_file: null,
  });
  const [evidenceFileName, setEvidenceFileName] = useState("");

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [okrsRes, okrTypesRes, employeesRes] = await Promise.all([
        getOkrs(),
        getOkrTypes(),
        getEmployees(),
      ]);

      setOkrs(okrsRes.data);
      setOkrTypes(okrTypesRes.data);
      setEmployees(employeesRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  // Helper function to convert decimal to percentage
  const decimalToPercent = (decimal) => {
    const parsed = parseFloat(decimal);
    return isNaN(parsed) ? 0 : Math.round(parsed * 100);
  };

  // Helper function to show confirm modal
  const showConfirm = (title, message, onConfirm) => {
    setConfirmModalConfig({ title, message, onConfirm });
    setShowConfirmModal(true);
  };

  const handleDelete = async (id) => {
    showConfirm(
      "Delete OKR",
      "Are you sure you want to delete this OKR?",
      async () => {
        try {
          await deleteOkr(id);
          await fetchData();
          setShowConfirmModal(false);
        } catch (error) {
          console.error("Error deleting OKR:", error);
        }
      },
    );
  };

  const handleToggleActive = async (okr) => {
    try {
      if (okr.is_active) {
        await deactivateOkr(okr.id);
      } else {
        await activateOkr(okr.id);
      }
      await fetchData();
    } catch (error) {
      console.error("Error updating OKR status:", error);
    }
  };

  const handleManageObjectives = async (okr) => {
    setSelectedOkr(okr);
    setShowObjectivesModal(true);
    try {
      const response = await getObjectivesByOkr(okr.id);
      setObjectives(response.data.objectives || []);
    } catch (error) {
      console.error("Error fetching objectives:", error);
    }
  };

  const handleDeleteObjective = async (objectiveId) => {
    showConfirm(
      "Delete Objective",
      "Are you sure you want to delete this objective?",
      async () => {
        try {
          await deleteObjective(objectiveId);
          const response = await getObjectivesByOkr(selectedOkr.id);
          setObjectives(response.data.objectives || []);
          setShowConfirmModal(false);
        } catch (error) {
          console.error("Error deleting objective:", error);
        }
      },
    );
  };

  const toggleOkrExpansion = async (okrId) => {
    if (expandedOkrId === okrId) {
      setExpandedOkrId(null);
    } else {
      setExpandedOkrId(okrId);
      // Fetch objectives with progress if not already loaded
      if (!okrObjectives[okrId]) {
        try {
          const response = await getObjectivesByOkr(okrId);
          setOkrObjectives((prev) => ({
            ...prev,
            [okrId]: response.data.objectives || [],
          }));
        } catch (error) {
          console.error("Error fetching objectives:", error);
        }
      }
    }
  };

  const handleCheckIn = async (objective) => {
    setSelectedObjective(objective);
    setShowCheckInModal(true);
    try {
      const response = await getCheckInsByObjective(objective.id);
      const checkInsData = response.data || [];
      setCheckIns(checkInsData);

      // Fetch approval logs for each check-in
      const logsPromises = checkInsData.map((checkIn) =>
        getCheckInApprovalLogs(checkIn.id).catch(() => ({ data: [] })),
      );
      const logsResponses = await Promise.all(logsPromises);
      const logsMap = {};
      checkInsData.forEach((checkIn, index) => {
        logsMap[checkIn.id] = logsResponses[index].data || [];
      });
      setApprovalLogs(logsMap);
    } catch (error) {
      console.error("Error fetching check-ins:", error);
      setCheckIns([]);
      setApprovalLogs({});
    }
  };

  const handleSubmitCheckIn = async (e) => {
    e.preventDefault();
    try {
      const currentValue = parseFloat(checkInFormData.current_value || 0);

      // Create FormData for file upload
      const formData = new FormData();
      formData.append("objective_id", selectedObjective.id);
      formData.append("date", checkInFormData.date);
      formData.append("current_value", currentValue);
      formData.append("comments", checkInFormData.comments);

      if (checkInFormData.evidence_file) {
        formData.append("evidence_file", checkInFormData.evidence_file);
      }

      // Use FormData for multipart upload
      const token = localStorage.getItem("token");
      const response = await fetch("http://localhost:8000/api/check-ins", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
        },
        body: formData,
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || "Error creating check-in");
      }

      // Refresh check-ins list and approval logs
      await handleCheckIn(selectedObjective);
      // Refresh objectives list to update progress
      if (selectedOkr) {
        const response = await getObjectivesByOkr(selectedOkr.id);
        setObjectives(response.data.objectives || []);
      }
      // Refresh cached OKR objectives for progress display in main table
      if (expandedOkrId) {
        const response = await getObjectivesByOkr(expandedOkrId);
        setOkrObjectives((prev) => ({
          ...prev,
          [expandedOkrId]: response.data.objectives || [],
        }));
      }

      // Reset check-in form data
      setCheckInFormData({
        date: new Date().toISOString().split("T")[0],
        current_value: "",
        comments: "",
        evidence_file: null,
      });
      setEvidenceFileName("");
    } catch (error) {
      console.error("Error submitting check-in:", error);
      alert(error.message || "Failed to submit check-in");
    }
  };

  const handleApproveCheckIn = async (checkInId) => {
    try {
      await approveCheckIn(checkInId);
      await handleCheckIn(selectedObjective);
      if (selectedOkr) {
        const response = await getObjectivesByOkr(selectedOkr.id);
        setObjectives(response.data.objectives || []);
      }
    } catch (error) {
      console.error("Error approving check-in:", error);
    }
  };

  const handleRejectCheckIn = async (checkInId) => {
    try {
      await rejectCheckIn(checkInId);
      await handleCheckIn(selectedObjective);
      if (selectedOkr) {
        const response = await getObjectivesByOkr(selectedOkr.id);
        setObjectives(response.data.objectives || []);
      }
    } catch (error) {
      console.error("Error rejecting check-in:", error);
    }
  };

  const handleDeleteCheckIn = async (checkInId) => {
    showConfirm(
      "Delete Check-In",
      "Are you sure you want to delete this check-in?",
      async () => {
        try {
          await deleteCheckIn(checkInId);
          await handleCheckIn(selectedObjective);
          if (selectedOkr) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setObjectives(response.data.objectives || []);
          }
          setShowConfirmModal(false);
        } catch (error) {
          console.error("Error deleting check-in:", error);
        }
      },
    );
  };

  const getOwnerName = (okr) => {
    if (okr.owner) {
      if (typeof okr.owner === "object") {
        return okr.owner.name || okr.owner.custom_type || "Unknown";
      }
      return okr.owner;
    }
    return "Unknown";
  };

  const getOwnerTypeLabel = (ownerType) => {
    if (!ownerType) return "Unknown";
    if (ownerType.includes("Employee")) return "Employee";
    if (ownerType.includes("OrgUnit")) return "Org Unit";
    return "Unknown";
  };

  // Filter OKRs based on search term and filters
  const filteredOkrs = okrs.filter((okr) => {
    const matchesSearch =
      searchTerm === "" ||
      okr.name.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus =
      statusFilter === "all" ||
      (statusFilter === "active" && okr.is_active) ||
      (statusFilter === "inactive" && !okr.is_active);

    const matchesType =
      okrTypeFilter === "all" ||
      okr.okr_type_id.toString() === okrTypeFilter;

    return matchesSearch && matchesStatus && matchesType;
  });

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">OKRs</h1>
        <p className="text-gray-600">Manage and track Objectives and Key Results</p>
      </div>

      <div className="flex items-center justify-between mb-6">
        {/* Search */}
        <div className="relative flex-1 max-w-md">
          <svg
            className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <input
            type="text"
            placeholder="Search OKRs..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
          />
        </div>

        {/* Count */}
        <span className="text-sm text-gray-500">
          ({filteredOkrs.length} {filteredOkrs.length === 1 ? "OKR" : "OKRs"})
        </span>

        {/* Add OKR Button - Navigate to create page */}
        <button
          onClick={() => navigate("/admin/okrs/create")}
          className="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary transition-all duration-200 shadow-md hover:shadow-lg"
        >
          <svg
            className="w-5 h-5 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 5v14M5 12h14"
            />
          </svg>
          Add OKR
        </button>
      </div>

      {/* Status Filter and Type Filter */}
      <div className="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-6 mb-6">
        {/* Status Filter */}
        <div className="flex items-center space-x-2">
          <span className="text-sm font-medium text-gray-700">Status:</span>
          <div className="flex rounded-lg overflow-hidden border-2 border-gray-200">
            <button
              onClick={() => setStatusFilter("all")}
              className={`px-4 py-2 text-sm font-medium transition-colors ${
                statusFilter === "all"
                  ? "bg-primary text-white"
                  : "bg-white text-gray-700 hover:bg-gray-50"
              }`}
            >
              All
            </button>
            <button
              onClick={() => setStatusFilter("active")}
              className={`px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200 ${
                statusFilter === "active"
                  ? "bg-green-500 text-white"
                  : "bg-white text-gray-700 hover:bg-gray-50"
              }`}
            >
              Active
            </button>
            <button
              onClick={() => setStatusFilter("inactive")}
              className={`px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200 ${
                statusFilter === "inactive"
                  ? "bg-red-500 text-white"
                  : "bg-white text-gray-700 hover:bg-gray-50"
              }`}
            >
              Inactive
            </button>
          </div>
        </div>

        {/* OKR Type Filter */}
        <div className="flex items-center space-x-2">
          <span className="text-sm font-medium text-gray-700">Type:</span>
          <div className="flex rounded-lg overflow-hidden border-2 border-gray-200">
            <button
              onClick={() => setOkrTypeFilter("all")}
              className={`px-4 py-2 text-sm font-medium transition-colors ${
                okrTypeFilter === "all"
                  ? "bg-primary text-white"
                  : "bg-white text-gray-700 hover:bg-gray-50"
              }`}
            >
              All Types
            </button>
            {okrTypes.map((type) => (
              <button
                key={type.id}
                onClick={() => setOkrTypeFilter(type.id.toString())}
                className={`px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200 ${
                  okrTypeFilter === type.id.toString()
                    ? "bg-primary text-white"
                    : "bg-white text-gray-700 hover:bg-gray-50"
                }`}
              >
                {type.name}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* OKRs Table */}
      <div className="bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden mb-6">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gradient-to-r from-blue-50 to-red-50">
              <tr>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  OKR Name
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Type
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Owner
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Period
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  OKR Progress
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Objectives
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {filteredOkrs.length === 0 ? (
                <tr>
                  <td
                    colSpan="8"
                    className="px-6 py-12 text-center text-gray-500"
                  >
                    <svg
                      className="mx-auto h-12 w-12 text-gray-400 mb-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                      />
                    </svg>
                    <p className="text-lg font-medium">No OKRs found</p>
                    <p className="text-sm mt-1">
                      Create a new OKR to get started
                    </p>
                  </td>
                </tr>
              ) : (
                filteredOkrs.map((okr) => (
                  <>
                    <tr
                      key={okr.id}
                      className="hover:bg-blue-50/50 transition-colors"
                    >
                      <td className="px-6 py-4">
                        <div className="flex items-center">
                          <button
                            onClick={() => toggleOkrExpansion(okr.id)}
                            className="mr-2 p-1 hover:bg-gray-100 rounded transition-colors"
                            title={
                              expandedOkrId === okr.id ? "Collapse" : "Expand"
                            }
                          >
                            <svg
                              className={`w-4 h-4 text-gray-500 transition-transform duration-200 ${expandedOkrId === okr.id ? "rotate-90" : ""}`}
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 5l7 7-7 7"
                              />
                            </svg>
                          </button>
                          <div>
                            <div className="text-sm font-medium text-gray-900">
                              {okr.name}
                            </div>
                            <div className="text-xs text-gray-500">
                              Weight: {decimalToPercent(okr.weight)}%
                            </div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                          {okr.okr_type?.name || "N/A"}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <div className="text-sm">
                          <div className="font-medium text-gray-900">
                            {getOwnerName(okr)}
                          </div>
                          <div className="text-xs text-gray-500">
                            {getOwnerTypeLabel(okr.owner_type)}
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <div className="text-sm text-gray-700">
                          <div>
                            {okr.start_date
                              ? new Date(okr.start_date).toLocaleDateString()
                              : "N/A"}
                          </div>
                          <div className="text-xs text-gray-500">
                            to{" "}
                            {okr.end_date
                              ? new Date(okr.end_date).toLocaleDateString()
                              : "N/A"}
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <div className="w-32">
                          <div className="flex items-center justify-between mb-1">
                            <span className="text-xs text-gray-600">
                              Progress
                            </span>
                            <span
                              className={`text-xs font-semibold ${
                                (okr.progress || 0) >= 100
                                  ? "text-green-700"
                                  : (okr.progress || 0) >= 50
                                    ? "text-blue-700"
                                    : (okr.progress || 0) >= 25
                                      ? "text-yellow-700"
                                      : "text-red-700"
                              }`}
                            >
                              {Math.round(okr.progress || 0)}%
                            </span>
                          </div>
                          <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div
                              className={`h-full rounded-full transition-all duration-500 ease-out ${
                                (okr.progress || 0) >= 100
                                  ? "bg-gradient-to-r from-green-500 to-green-400"
                                  : (okr.progress || 0) >= 50
                                    ? "bg-gradient-to-r from-blue-500 to-blue-400"
                                    : (okr.progress || 0) >= 25
                                      ? "bg-gradient-to-r from-yellow-500 to-yellow-400"
                                      : "bg-gradient-to-r from-red-500 to-red-400"
                              }`}
                              style={{
                                width: `${Math.min(okr.progress || 0, 100)}%`,
                              }}
                            />
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <button
                          onClick={() => handleManageObjectives(okr)}
                          className="text-sm text-primary hover:underline"
                        >
                          {okr.objectives?.length || 0} Objectives
                        </button>
                      </td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
                            okr.is_active
                              ? "bg-green-100 text-green-800"
                              : "bg-red-100 text-red-800"
                          }`}
                        >
                          {okr.is_active ? "Active" : "Inactive"}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => handleManageObjectives(okr)}
                            className="p-2 text-primary hover:bg-blue-100 rounded-lg transition-colors"
                            title="Manage Objectives"
                          >
                            <svg
                              className="w-5 h-5"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                              />
                            </svg>
                          </button>
                          <button
                            onClick={() => handleToggleActive(okr)}
                            className={`p-2 ${okr.is_active ? "text-orange-500 hover:bg-orange-100" : "text-green-500 hover:bg-green-100"} rounded-lg transition-colors`}
                            title={okr.is_active ? "Deactivate" : "Activate"}
                          >
                            {okr.is_active ? (
                              <svg
                                className="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                              >
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  strokeWidth={2}
                                  d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                              </svg>
                            ) : (
                              <svg
                                className="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                              >
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  strokeWidth={2}
                                  d="M5 13l4 4L19 7"
                                />
                              </svg>
                            )}
                          </button>
                          <button
                            onClick={() => navigate(`/admin/okrs/${okr.id}/edit`)}
                            className="p-2 text-primary hover:bg-blue-100 rounded-lg transition-colors"
                            title="Edit"
                          >
                            <svg
                              className="w-5 h-5"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                              />
                            </svg>
                          </button>
                          <button
                            onClick={() => handleDelete(okr.id)}
                            className="p-2 text-accent hover:bg-red-100 rounded-lg transition-colors"
                            title="Delete"
                          >
                            <svg
                              className="w-5 h-5"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                              />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                    {/* Expanded Row - Objectives Progress */}
                    {expandedOkrId === okr.id && (
                      <tr key={`${okr.id}-expanded`} className="bg-blue-50/30">
                        <td colSpan="8" className="px-6 py-4">
                          <div className="space-y-3">
                            <div className="flex items-center justify-between mb-3">
                              <h4 className="text-sm font-semibold text-gray-700">
                                Objectives Progress
                              </h4>
                              <div className="flex items-center gap-2">
                                <span className="text-xs text-gray-600">
                                  OKR Overall Progress:
                                </span>
                                <span
                                  className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold ${
                                    (okr.progress || 0) >= 100
                                      ? "bg-green-100 text-green-800"
                                      : (okr.progress || 0) >= 50
                                        ? "bg-blue-100 text-blue-800"
                                        : (okr.progress || 0) >= 25
                                          ? "bg-yellow-100 text-yellow-800"
                                          : "bg-red-100 text-red-800"
                                  }`}
                                >
                                  {Math.round(okr.progress || 0)}%
                                </span>
                              </div>
                            </div>
                            {okrObjectives[okr.id]?.length > 0 ? (
                              okrObjectives[okr.id].map((objective) => (
                                <div
                                  key={objective.id}
                                  className="bg-white rounded-lg p-3 border border-gray-200"
                                >
                                  <div className="flex items-start justify-between mb-2">
                                    <div className="flex-1">
                                      <div className="flex items-center gap-2 mb-1">
                                        <span className="font-medium text-sm text-gray-900">
                                          {objective.description}
                                        </span>
                                        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                          {decimalToPercent(objective.weight)}%
                                        </span>
                                      </div>
                                      <div className="text-xs text-gray-500">
                                        Deadline:{" "}
                                        {new Date(
                                          objective.deadline,
                                        ).toLocaleDateString()}{" "}
                                        • Tracker:{" "}
                                        {objective.tracker_employee?.name ||
                                          "N/A"}{" "}
                                        • Approver:{" "}
                                        {objective.approver_employee?.name ||
                                          "N/A"}
                                      </div>
                                    </div>
                                    <div className="flex items-center gap-2 ml-4">
                                      <span className="text-sm text-gray-600">
                                        {objective.current_value || 0} /{" "}
                                        {objective.target_value}
                                      </span>
                                      <span
                                        className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold ${
                                          objective.progress >= 100
                                            ? "bg-green-100 text-green-800"
                                            : objective.progress >= 50
                                              ? "bg-blue-100 text-blue-800"
                                              : objective.progress >= 25
                                                ? "bg-yellow-100 text-yellow-800"
                                                : "bg-red-100 text-red-800"
                                        }`}
                                      >
                                        {Math.round(objective.progress || 0)}%
                                      </span>
                                    </div>
                                  </div>
                                  <div className="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div
                                      className={`h-full rounded-full transition-all duration-500 ease-out ${
                                        objective.progress >= 100
                                          ? "bg-gradient-to-r from-green-500 to-green-400"
                                          : objective.progress >= 50
                                            ? "bg-gradient-to-r from-blue-500 to-blue-400"
                                            : objective.progress >= 25
                                              ? "bg-gradient-to-r from-yellow-500 to-yellow-400"
                                              : "bg-gradient-to-r from-red-500 to-red-400"
                                      }`}
                                      style={{
                                        width: `${Math.min(objective.progress || 0, 100)}%`,
                                      }}
                                    />
                                  </div>
                                  <div className="mt-2 flex items-center justify-between">
                                    <div className="text-xs text-gray-500">
                                      {objective.check_ins_count || 0} Check-ins
                                    </div>
                                    <button
                                      onClick={() => handleCheckIn(objective)}
                                      className="inline-flex items-center px-2 py-1 text-xs font-medium bg-primary text-white rounded hover:bg-primary-dark transition-colors"
                                    >
                                      <svg
                                        className="w-3 h-3 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                      >
                                        <path
                                          strokeLinecap="round"
                                          strokeLinejoin="round"
                                          strokeWidth={2}
                                          d="M12 4v16m8-8H4"
                                        />
                                      </svg>
                                      Add Check-in
                                    </button>
                                  </div>
                                </div>
                              ))
                            ) : (
                              <div className="text-center py-8 text-gray-500">
                                No objectives for this OKR
                              </div>
                            )}
                          </div>
                        </td>
                      </tr>
                    )}
                  </>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Objectives Management Modal */}
      {showObjectivesModal && selectedOkr && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <div>
                <h2 className="text-xl text-gray-900">
                  <span className="font-bold">{selectedOkr.name}</span> -{" "}
                  {getOwnerName(selectedOkr)}
                </h2>
                <p className="text-sm text-gray-500">
                  {new Date(selectedOkr.start_date).toLocaleDateString(
                    "en-GB",
                    {
                      day: "numeric",
                      month: "short",
                      year: "numeric",
                    },
                  )}{" "}
                  -{" "}
                  {new Date(selectedOkr.end_date).toLocaleDateString("en-GB", {
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                  })}
                </p>
              </div>
              <button
                onClick={() => {
                  setShowObjectivesModal(false);
                  setSelectedOkr(null);
                }}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg
                  className="w-5 h-5"
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
            <div className="flex-1 overflow-y-auto p-6">
              {objectives.length === 0 ? (
                <div className="text-center py-12">
                  <svg
                    className="mx-auto h-12 w-12 text-gray-400 mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                  </svg>
                  <p className="text-gray-500">No objectives found</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {objectives.map((objective) => (
                    <div
                      key={objective.id}
                      className="bg-gray-50 rounded-xl p-4 border border-gray-200"
                    >
                      <div className="flex items-start justify-between">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-2">
                            <h3 className="text-lg font-semibold text-gray-900">
                              {objective.description}
                            </h3>
                            <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                              Weight: {decimalToPercent(objective.weight)}%
                            </span>
                            <span
                              className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                                objective.progress >= 100
                                  ? "bg-green-100 text-green-800"
                                  : objective.progress >= 50
                                    ? "bg-blue-100 text-blue-800"
                                    : objective.progress >= 25
                                      ? "bg-yellow-100 text-yellow-800"
                                      : "bg-red-100 text-red-800"
                              }`}
                            >
                              {Math.round(objective.progress || 0)}%
                            </span>
                          </div>
                          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                              <span className="text-gray-600">Target Type:</span>{" "}
                              <span className="font-medium">
                                {objective.target_type}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Target:</span>{" "}
                              <span className="font-medium">
                                {objective.target_value}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Current:</span>{" "}
                              <span className="font-medium">
                                {objective.current_value || 0}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Deadline:</span>{" "}
                              <span className="font-medium">
                                {new Date(
                                  objective.deadline,
                                ).toLocaleDateString()}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Tracker:</span>{" "}
                              <span className="font-medium">
                                {objective.tracker_employee?.name || "N/A"}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Approver:</span>{" "}
                              <span className="font-medium">
                                {objective.approver_employee?.name || "N/A"}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Tracking:</span>{" "}
                              <span className="font-medium capitalize">
                                {objective.tracking_type}
                              </span>
                            </div>
                            <div>
                              <span className="text-gray-600">Check-ins:</span>{" "}
                              <span className="font-medium">
                                {objective.check_ins_count || 0}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center gap-2 ml-4">
                          <button
                            onClick={() => handleCheckIn(objective)}
                            className="inline-flex items-center px-3 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
                          >
                            <svg
                              className="w-4 h-4 mr-1"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M12 4v16m8-8H4"
                              />
                            </svg>
                            Check-in
                          </button>
                          <button
                            onClick={() => handleDeleteObjective(objective.id)}
                            className="p-2 text-accent hover:bg-red-100 rounded-lg transition-colors"
                            title="Delete Objective"
                          >
                            <svg
                              className="w-5 h-5"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                              />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Check-in Modal */}
      {showCheckInModal && selectedObjective && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <h2 className="text-xl font-bold text-gray-900">
                Check-in for: {selectedObjective.description}
              </h2>
              <button
                onClick={() => {
                  setShowCheckInModal(false);
                  setSelectedObjective(null);
                  setCheckInFormData({
                    date: new Date().toISOString().split("T")[0],
                    current_value: "",
                    comments: "",
                    evidence_file: null,
                  });
                  setEvidenceFileName("");
                }}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg
                  className="w-5 h-5"
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

            <div className="flex-1 overflow-y-auto p-6">
              {/* Check-in Form */}
              <form onSubmit={handleSubmitCheckIn} className="space-y-4 mb-6">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Date *
                  </label>
                  <input
                    type="date"
                    required
                    value={checkInFormData.date}
                    onChange={(e) =>
                      setCheckInFormData({
                        ...checkInFormData,
                        date: e.target.value,
                      })
                    }
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Current Value *
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    required
                    value={checkInFormData.current_value}
                    onChange={(e) =>
                      setCheckInFormData({
                        ...checkInFormData,
                        current_value: e.target.value,
                      })
                    }
                    placeholder={`Enter current value (target: ${selectedObjective.target_value})`}
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Comments
                  </label>
                  <textarea
                    value={checkInFormData.comments}
                    onChange={(e) =>
                      setCheckInFormData({
                        ...checkInFormData,
                        comments: e.target.value,
                      })
                    }
                    placeholder="Add comments about this check-in..."
                    rows={3}
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none resize-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Evidence File (Optional)
                  </label>
                  <input
                    type="file"
                    onChange={(e) => {
                      const file = e.target.files[0];
                      if (file) {
                        setCheckInFormData({
                          ...checkInFormData,
                          evidence_file: file,
                        });
                        setEvidenceFileName(file.name);
                      }
                    }}
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700"
                  />
                  {evidenceFileName && (
                    <p className="mt-2 text-sm text-gray-600">
                      Selected: {evidenceFileName}
                    </p>
                  )}
                </div>
                <div className="flex justify-end gap-3">
                  <button
                    type="submit"
                    className="px-6 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary transition-all duration-200"
                  >
                    Submit Check-in
                  </button>
                </div>
              </form>

              {/* Check-in History */}
              <div className="border-t border-gray-200 pt-4">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  Check-in History
                </h3>
                {checkIns.length === 0 ? (
                  <div className="text-center py-8 text-gray-500">
                    No check-ins recorded yet
                  </div>
                ) : (
                  <div className="space-y-3">
                    {checkIns.map((checkIn) => (
                      <div
                        key={checkIn.id}
                        className="bg-gray-50 rounded-lg p-4 border border-gray-200"
                      >
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-2">
                              <span className="text-sm font-medium text-gray-900">
                                {new Date(checkIn.date).toLocaleDateString()}
                              </span>
                              <span
                                className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                                  checkIn.status === "approved"
                                    ? "bg-green-100 text-green-800"
                                    : checkIn.status === "rejected"
                                      ? "bg-red-100 text-red-800"
                                      : "bg-yellow-100 text-yellow-800"
                                }`}
                              >
                                {checkIn.status}
                              </span>
                            </div>
                            <div className="text-sm text-gray-700">
                              <span className="font-medium">Value: </span>
                              {checkIn.current_value}
                            </div>
                            {checkIn.comments && (
                              <div className="text-sm text-gray-600 mt-1">
                                <span className="font-medium">Comments: </span>
                                {checkIn.comments}
                              </div>
                            )}
                            {checkIn.evidence_file && (
                              <div className="text-sm text-gray-600 mt-1">
                                <span className="font-medium">Evidence: </span>
                                <a
                                  href={`http://localhost:8000/storage/${checkIn.evidence_file}`}
                                  target="_blank"
                                  rel="noopener noreferrer"
                                  className="text-primary hover:underline"
                                >
                                  View File
                                </a>
                              </div>
                            )}
                          </div>
                          <div className="flex items-center gap-2 ml-4">
                            {checkIn.status === "pending" && (
                              <>
                                <button
                                  onClick={() => handleApproveCheckIn(checkIn.id)}
                                  className="p-2 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
                                  title="Approve"
                                >
                                  <svg
                                    className="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                  >
                                    <path
                                      strokeLinecap="round"
                                      strokeLinejoin="round"
                                      strokeWidth={2}
                                      d="M5 13l4 4L19 7"
                                    />
                                  </svg>
                                </button>
                                <button
                                  onClick={() => handleRejectCheckIn(checkIn.id)}
                                  className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                  title="Reject"
                                >
                                  <svg
                                    className="w-5 h-5"
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
                              </>
                            )}
                            <button
                              onClick={() => handleDeleteCheckIn(checkIn.id)}
                              className="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors"
                              title="Delete"
                            >
                              <svg
                                className="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                              >
                                <path
                                  strokeLinecap="round"
                                  strokeLinejoin="round"
                                  strokeWidth={2}
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                              </svg>
                            </button>
                          </div>
                        </div>
                        {approvalLogs[checkIn.id]?.length > 0 && (
                          <div className="mt-3 pt-3 border-t border-gray-300">
                            <p className="text-xs font-semibold text-gray-600 mb-2">
                              Approval History:
                            </p>
                            {approvalLogs[checkIn.id].map((log) => (
                              <div
                                key={log.id}
                                className="text-xs text-gray-600 flex items-center gap-2"
                              >
                                <span>{log.employee?.name || "Unknown"}</span>
                                <span>-</span>
                                <span className="capitalize">{log.action}</span>
                                <span>-</span>
                                <span>
                                  {new Date(log.created_at).toLocaleString()}
                                </span>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Confirm Modal */}
      {showConfirmModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-2">
                {confirmModalConfig.title}
              </h3>
              <p className="text-gray-600 mb-6">
                {confirmModalConfig.message}
              </p>
              <div className="flex justify-end gap-3">
                <button
                  onClick={() => setShowConfirmModal(false)}
                  className="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  onClick={() => {
                    if (confirmModalConfig.onConfirm) {
                      confirmModalConfig.onConfirm();
                    }
                  }}
                  className="px-4 py-2 bg-accent text-white rounded-xl hover:bg-red-600 transition-colors"
                >
                  Confirm
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default OkrList;
