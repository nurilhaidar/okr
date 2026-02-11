import { useState, useEffect } from "react";
import {
  getOkrs,
  createOkr,
  updateOkr,
  deleteOkr,
  activateOkr,
  deactivateOkr,
  getAvailableOwners,
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
  getMe,
} from "../services/api";

const Okrs = () => {
  const [okrs, setOkrs] = useState([]);
  const [okrTypes, setOkrTypes] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [availableOwners, setAvailableOwners] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [showObjectivesModal, setShowObjectivesModal] = useState(false);
  const [showWeightAlert, setShowWeightAlert] = useState(false);
  const [weightAlertMessage, setWeightAlertMessage] = useState("");
  const [showCheckInModal, setShowCheckInModal] = useState(false);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [confirmModalConfig, setConfirmModalConfig] = useState({
    title: "",
    message: "",
    onConfirm: null,
  });
  const [selectedObjective, setSelectedObjective] = useState(null);
  const [checkIns, setCheckIns] = useState([]);
  const [approvalLogs, setApprovalLogs] = useState([]);
  const [currentUser, setCurrentUser] = useState(null);
  const [expandedOkrId, setExpandedOkrId] = useState(null);
  const [okrObjectives, setOkrObjectives] = useState({});
  const [checkInFormData, setCheckInFormData] = useState({
    date: new Date().toISOString().split("T")[0],
    current_value: "",
    comments: "",
    evidence_file: null,
  });
  const [evidenceFileName, setEvidenceFileName] = useState("");
  const [editingOkr, setEditingOkr] = useState(null);
  const [selectedOkr, setSelectedOkr] = useState(null);
  const [objectives, setObjectives] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [okrTypeFilter, setOkrTypeFilter] = useState("all");
  const [formData, setFormData] = useState({
    name: "",
    weight: "",
    okr_type_id: "",
    start_date: "",
    end_date: "",
    owner_type: "App\\Models\\Employee",
    owner_id: "",
    is_active: true,
    objectives: [],
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [okrsRes, okrTypesRes, employeesRes, ownersRes, userRes] =
        await Promise.all([
          getOkrs(),
          getOkrTypes(),
          getEmployees(),
          getAvailableOwners(),
          getMe(),
        ]);

      setOkrs(okrsRes.data);
      setOkrTypes(okrTypesRes.data);
      setEmployees(employeesRes.data);
      setAvailableOwners(ownersRes.data);
      setCurrentUser(userRes.data.employee);
      // Debug logging
      console.log("User data:", userRes.data);
      console.log("Current user (employee):", userRes.data.employee);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  // Helper function to convert percentage to decimal
  const percentToDecimal = (percent) => {
    const parsed = parseFloat(percent);
    return isNaN(parsed) ? 0 : parsed / 100;
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

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      // Filter out empty objectives
      const validObjectives = formData.objectives.filter(
        (obj) => obj.description.trim() !== "",
      );

      // Validate objectives
      for (const obj of validObjectives) {
        // Validate target value based on target type
        const targetValue = parseFloat(obj.target_value);
        if (obj.target_type === "binary") {
          if (targetValue !== 0 && targetValue !== 1) {
            setWeightAlertMessage(
              `Objective "${obj.description}" has binary target type, so target value must be 0 or 1. Current value: ${targetValue}.`,
            );
            setShowWeightAlert(true);
            return;
          }
        } else if (obj.target_type === "numeric") {
          if (targetValue < 0) {
            setWeightAlertMessage(
              `Objective "${obj.description}" has numeric target type, so target value must be a positive number. Current value: ${targetValue}.`,
            );
            setShowWeightAlert(true);
            return;
          }
        }
      }

      // Validate objective weights sum to 100%
      if (validObjectives.length > 0) {
        const totalWeightPercent = validObjectives.reduce(
          (sum, obj) => sum + (parseFloat(obj.weight) || 0),
          0,
        );
        const tolerance = 0.01; // Small tolerance for comparison

        if (Math.abs(totalWeightPercent - 100) > tolerance) {
          setWeightAlertMessage(
            `Objective weights must total 100%. Current total: ${totalWeightPercent}%. Please adjust the objective weights.`,
          );
          setShowWeightAlert(true);
          return;
        }
      }

      const submitData = {
        ...formData,
        weight: percentToDecimal(formData.weight), // Convert percentage to decimal
        objectives: validObjectives.map((obj) => ({
          ...obj,
          weight: percentToDecimal(obj.weight), // Convert percentage to decimal
        })),
      };

      if (editingOkr) {
        await updateOkr(editingOkr.id, submitData);
      } else {
        await createOkr(submitData);
      }
      await fetchData();
      setShowModal(false);
      resetForm();
    } catch (error) {
      console.error("Error saving OKR:", error);
    }
  };

  const handleEdit = (okr) => {
    setEditingOkr(okr);
    setFormData({
      name: okr.name,
      weight: decimalToPercent(okr.weight), // Convert decimal to percentage
      okr_type_id: okr.okr_type_id || "",
      start_date: okr.start_date?.split("T")[0] || "",
      end_date: okr.end_date?.split("T")[0] || "",
      owner_type: okr.owner_type,
      owner_id: okr.owner_id,
      is_active: okr.is_active,
      objectives: (okr.objectives || []).map((obj) => ({
        ...obj,
        weight: decimalToPercent(obj.weight), // Convert decimal to percentage
        deadline: obj.deadline?.split("T")[0] || "", // Format deadline for date input
      })),
    });
    setShowModal(true);
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

  const addObjectiveToForm = () => {
    setFormData({
      ...formData,
      objectives: [
        ...formData.objectives,
        {
          description: "",
          weight: "",
          target_type: "numeric",
          target_value: "",
          deadline: "",
          tracking_type: "weekly",
          tracker: "",
          approver: "",
        },
      ],
    });
  };

  const updateObjectiveInForm = (index, field, value) => {
    const newObjectives = [...formData.objectives];
    newObjectives[index][field] = value;
    setFormData({ ...formData, objectives: newObjectives });
  };

  const removeObjectiveFromForm = (index) => {
    const newObjectives = formData.objectives.filter((_, i) => i !== index);
    setFormData({ ...formData, objectives: newObjectives });
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

  const resetForm = () => {
    setFormData({
      name: "",
      weight: "",
      okr_type_id: "",
      start_date: "",
      end_date: "",
      owner_type: "App\\Models\\Employee",
      owner_id: "",
      is_active: true,
      objectives: [],
    });
    setEditingOkr(null);
  };

  const handleCheckIn = async (objective) => {
    setSelectedObjective(objective);
    setShowCheckInModal(true);
    try {
      const response = await getCheckInsByObjective(objective.id);
      const checkInsData = response.data || [];
      setCheckIns(checkInsData);

      // Debug logging
      console.log("Check-ins data:", checkInsData);
      console.log("Selected objective:", objective);

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
          // Don't set Content-Type when sending FormData - browser sets it automatically with boundary
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
      if (okrObjectives[selectedOkr?.id]) {
        const objResponse = await getObjectivesByOkr(selectedOkr.id);
        setOkrObjectives((prev) => ({
          ...prev,
          [selectedOkr.id]: objResponse.data.objectives || [],
        }));
      }
      // Reset form
      resetCheckInForm();
    } catch (error) {
      console.error("Error creating check-in:", error);
    }
  };

  const resetCheckInForm = () => {
    setCheckInFormData({
      date: new Date().toISOString().split("T")[0],
      current_value: "",
      comments: "",
      evidence_file: null,
    });
    setEvidenceFileName("");
  };

  const handleApproveCheckIn = (checkInId) => {
    showConfirm(
      "Approve Check-In",
      "Are you sure you want to approve this check-in?",
      async () => {
        try {
          await approveCheckIn(checkInId);
          // Refresh check-ins and approval logs
          await handleCheckIn(selectedObjective);
          // Refresh objectives list to update progress
          if (selectedOkr) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setObjectives(response.data.objectives || []);
          }
          // Refresh cached OKR objectives for progress display in main table
          if (okrObjectives[selectedOkr?.id]) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setOkrObjectives((prev) => ({
              ...prev,
              [selectedOkr.id]: response.data.objectives || [],
            }));
          }
          setShowConfirmModal(false);
        } catch (error) {
          console.error("Error approving check-in:", error);
        }
      },
    );
  };

  const handleRejectCheckIn = (checkInId) => {
    showConfirm(
      "Reject Check-In",
      "Are you sure you want to reject this check-in?",
      async () => {
        try {
          await rejectCheckIn(checkInId);
          // Refresh check-ins and approval logs
          await handleCheckIn(selectedObjective);
          // Refresh objectives list to update progress
          if (selectedOkr) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setObjectives(response.data.objectives || []);
          }
          // Refresh cached OKR objectives for progress display in main table
          if (okrObjectives[selectedOkr?.id]) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setOkrObjectives((prev) => ({
              ...prev,
              [selectedOkr.id]: response.data.objectives || [],
            }));
          }
          setShowConfirmModal(false);
        } catch (error) {
          console.error("Error rejecting check-in:", error);
        }
      },
    );
  };

  const handleDeleteCheckIn = async (checkInId) => {
    showConfirm(
      "Delete Check-In",
      "Are you sure you want to delete this check-in?",
      async () => {
        try {
          await deleteCheckIn(checkInId);
          // Refresh check-ins and approval logs
          await handleCheckIn(selectedObjective);
          // Refresh objectives list to update progress
          if (selectedOkr) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setObjectives(response.data.objectives || []);
          }
          // Refresh cached OKR objectives for progress display in main table
          if (okrObjectives[selectedOkr?.id]) {
            const response = await getObjectivesByOkr(selectedOkr.id);
            setOkrObjectives((prev) => ({
              ...prev,
              [selectedOkr.id]: response.data.objectives || [],
            }));
          }
          setShowConfirmModal(false);
        } catch (error) {
          console.error("Error deleting check-in:", error);
        }
      },
    );
  };

  const getStatusBadge = (status) => {
    const statusStyles = {
      pending: "bg-yellow-100 text-yellow-800",
      approved: "bg-green-100 text-green-800",
      rejected: "bg-red-100 text-red-800",
      draft: "bg-gray-100 text-gray-800",
    };
    return statusStyles[status] || statusStyles.draft;
  };

  const canApproveOrReject = (checkIn) => {
    if (!currentUser || !checkIn.current_status) return false;
    // Check if user is admin OR the designated approver for this objective
    const approverId =
      checkIn.objective?.approver || selectedObjective?.approver;
    const isAdmin = currentUser.role?.name === "Admin";
    const isDesignatedApprover = approverId === currentUser.id;
    const canApprove =
      (isAdmin || isDesignatedApprover) && checkIn.current_status === "pending";
    return canApprove;
  };

  const canDeleteCheckIn = (checkIn) => {
    if (!currentUser) return false;
    // Allow delete if user is admin (role === 'admin') or if they're the tracker
    const isAdmin = currentUser.role?.name === "Admin";
    const trackerId = checkIn.objective?.tracker;
    return isAdmin || trackerId === currentUser.id;
  };

  const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setCheckInFormData({ ...checkInFormData, evidence_file: file });
      setEvidenceFileName(file.name);
    }
  };

  const getOwnerName = (okr) => {
    if (!okr.owner) return "N/A";
    if (okr.owner_type.includes("Employee")) {
      return okr.owner.name || "N/A";
    } else {
      return okr.owner.name || okr.owner.custom_type || "N/A";
    }
  };

  const getOwnerTypeLabel = (ownerType) => {
    if (ownerType?.includes("Employee")) return "Employee";
    if (ownerType?.includes("OrgUnit")) return "Org Unit";
    return "Unknown";
  };

  const filteredOkrs = okrs.filter((okr) => {
    const matchesSearch =
      okr.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      getOwnerName(okr).toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus =
      statusFilter === "all" ||
      (statusFilter === "active" && okr.is_active) ||
      (statusFilter === "inactive" && !okr.is_active);

    const matchesOkrType =
      okrTypeFilter === "all" || okr.okr_type_id == okrTypeFilter;

    return matchesSearch && matchesStatus && matchesOkrType;
  });

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">OKR</h2>
          <p className="text-gray-600 mt-1">Manage OKR and Objective</p>
        </div>
        <button
          onClick={() => {
            setShowModal(true);
            resetForm();
          }}
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

      {/* Search */}
      <div className="mb-6">
        <input
          type="text"
          placeholder="Search OKRs..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
        />
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

        {/* Count */}
        <span className="text-sm text-gray-500">
          ({filteredOkrs.length} {filteredOkrs.length === 1 ? "OKR" : "OKRs"})
        </span>
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
                            onClick={() => handleEdit(okr)}
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
                                        className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                                          (objective.progress || 0) >= 100
                                            ? "bg-green-100 text-green-800"
                                            : (objective.progress || 0) >= 50
                                              ? "bg-blue-100 text-blue-800"
                                              : (objective.progress || 0) >= 25
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
                                        (objective.progress || 0) >= 100
                                          ? "bg-gradient-to-r from-green-500 to-green-400"
                                          : (objective.progress || 0) >= 50
                                            ? "bg-gradient-to-r from-blue-500 to-blue-400"
                                            : (objective.progress || 0) >= 25
                                              ? "bg-gradient-to-r from-yellow-500 to-yellow-400"
                                              : "bg-gradient-to-r from-red-500 to-red-400"
                                      }`}
                                      style={{
                                        width: `${Math.min(objective.progress || 0, 100)}%`,
                                      }}
                                    />
                                  </div>
                                </div>
                              ))
                            ) : (
                              <p className="text-sm text-gray-500">
                                Loading objectives...
                              </p>
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

      {/* Create/Edit OKR Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-gray-200 sticky top-0 bg-white">
              <h2 className="text-xl font-bold text-gray-900">
                {editingOkr ? "Edit OKR" : "Add New OKR"}
              </h2>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              {/* OKR Details */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    OKR Name *
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) =>
                      setFormData({ ...formData, name: e.target.value })
                    }
                    placeholder="e.g., Q1 2024 Sales Goals"
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Weight (%) *
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    required
                    value={formData.weight}
                    onChange={(e) =>
                      setFormData({ ...formData, weight: e.target.value })
                    }
                    placeholder="e.g., 50"
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    OKR Type *
                  </label>
                  <select
                    required
                    value={formData.okr_type_id}
                    onChange={(e) => {
                      const selectedType = okrTypes.find(
                        (t) => t.id === parseInt(e.target.value),
                      );
                      setFormData({
                        ...formData,
                        okr_type_id: e.target.value,
                        owner_type: selectedType?.is_employee
                          ? "App\\Models\\Employee"
                          : "App\\Models\\OrgUnit",
                        owner_id: "",
                      });
                    }}
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  >
                    <option value="">Select Type</option>
                    {okrTypes.length > 0 ? (
                      okrTypes.map((type) => (
                        <option key={type.id} value={type.id}>
                          {type.name}
                        </option>
                      ))
                    ) : (
                      <option disabled>Loading types...</option>
                    )}
                  </select>
                  {okrTypes.length === 0 && (
                    <p className="text-xs text-red-500 mt-1">
                      No OKR types available. Please create OKR types first.
                    </p>
                  )}
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Start Date *
                  </label>
                  <input
                    type="date"
                    required
                    value={formData.start_date}
                    onChange={(e) =>
                      setFormData({ ...formData, start_date: e.target.value })
                    }
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    End Date *
                  </label>
                  <input
                    type="date"
                    required
                    value={formData.end_date}
                    onChange={(e) =>
                      setFormData({ ...formData, end_date: e.target.value })
                    }
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    {formData.owner_type === "App\\Models\\Employee"
                      ? "Employee *"
                      : "Organization Unit *"}
                  </label>
                  <select
                    required
                    value={formData.owner_id}
                    onChange={(e) =>
                      setFormData({ ...formData, owner_id: e.target.value })
                    }
                    className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                  >
                    <option value="">
                      {formData.owner_type === "App\\Models\\Employee"
                        ? "Select Employee"
                        : "Select Organization Unit"}
                    </option>
                    {formData.owner_type === "App\\Models\\Employee"
                      ? availableOwners?.employees?.map((emp) => (
                          <option key={emp.id} value={emp.id}>
                            {emp.title || emp.name}
                          </option>
                        ))
                      : availableOwners?.org_units?.map((org) => (
                          <option key={org.id} value={org.id}>
                            {org.title || org.name}
                          </option>
                        ))}
                  </select>
                </div>
              </div>

              {/* Objectives Section */}
              <div className="border-t border-gray-200 pt-4">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-lg font-semibold text-gray-900">
                    Objectives
                  </h3>
                  <button
                    type="button"
                    onClick={addObjectiveToForm}
                    className="inline-flex items-center px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
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
                    Add Objective
                  </button>
                </div>

                {formData.objectives.length === 0 ? (
                  <div className="text-center py-8 bg-gray-50 rounded-xl">
                    <p className="text-gray-500">
                      No objectives added yet. Click "Add Objective" to create
                      one.
                    </p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {formData.objectives.map((objective, index) => (
                      <div
                        key={index}
                        className="bg-gray-50 rounded-xl p-4 relative"
                      >
                        <button
                          type="button"
                          onClick={() => removeObjectiveFromForm(index)}
                          className="absolute top-2 right-2 p-1 text-red-500 hover:bg-red-100 rounded"
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

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                          <div className="md:col-span-3">
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Description *
                            </label>
                            <input
                              type="text"
                              value={objective.description}
                              onChange={(e) =>
                                updateObjectiveInForm(
                                  index,
                                  "description",
                                  e.target.value,
                                )
                              }
                              placeholder="e.g., Increase sales revenue"
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            />
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Weight (%) *
                            </label>
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="100"
                              value={objective.weight}
                              onChange={(e) =>
                                updateObjectiveInForm(
                                  index,
                                  "weight",
                                  e.target.value,
                                )
                              }
                              placeholder="50"
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            />
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Target Type *
                            </label>
                            <select
                              value={objective.target_type}
                              onChange={(e) => {
                                const newType = e.target.value;
                                // When changing to binary, reset target value to 1 if not 0 or 1
                                if (
                                  newType === "binary" &&
                                  objective.target_value &&
                                  objective.target_value !== "0" &&
                                  objective.target_value !== "1"
                                ) {
                                  updateObjectiveInForm(
                                    index,
                                    "target_value",
                                    "1",
                                  );
                                }
                                updateObjectiveInForm(
                                  index,
                                  "target_type",
                                  newType,
                                );
                              }}
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            >
                              <option value="numeric">Numeric</option>
                              <option value="binary">Binary</option>
                            </select>
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Target Value *
                            </label>
                            {objective.target_type === "binary" ? (
                              <select
                                required
                                value={objective.target_value}
                                onChange={(e) =>
                                  updateObjectiveInForm(
                                    index,
                                    "target_value",
                                    e.target.value,
                                  )
                                }
                                className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                              >
                                <option value="">Select status</option>
                                <option value="0">0 - Not Achieved</option>
                                <option value="1">1 - Achieved</option>
                              </select>
                            ) : (
                              <input
                                type="number"
                                step="0.01"
                                min="0"
                                value={objective.target_value}
                                onChange={(e) =>
                                  updateObjectiveInForm(
                                    index,
                                    "target_value",
                                    e.target.value,
                                  )
                                }
                                placeholder="100"
                                className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                              />
                            )}
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Deadline *
                            </label>
                            <input
                              type="date"
                              value={objective.deadline}
                              onChange={(e) =>
                                updateObjectiveInForm(
                                  index,
                                  "deadline",
                                  e.target.value,
                                )
                              }
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            />
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Tracking Type *
                            </label>
                            <select
                              value={objective.tracking_type}
                              onChange={(e) =>
                                updateObjectiveInForm(
                                  index,
                                  "tracking_type",
                                  e.target.value,
                                )
                              }
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            >
                              <option value="daily">Daily</option>
                              <option value="weekly">Weekly</option>
                              <option value="monthly">Monthly</option>
                              <option value="quarterly">Quarterly</option>
                            </select>
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Tracker *
                            </label>
                            <select
                              value={objective.tracker}
                              onChange={(e) =>
                                updateObjectiveInForm(
                                  index,
                                  "tracker",
                                  e.target.value,
                                )
                              }
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            >
                              <option value="">Select Employee</option>
                              {employees.map((emp) => (
                                <option key={emp.id} value={emp.id}>
                                  {emp.name}
                                </option>
                              ))}
                            </select>
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-gray-700 mb-1">
                              Approver *
                            </label>
                            <select
                              value={objective.approver}
                              onChange={(e) =>
                                updateObjectiveInForm(
                                  index,
                                  "approver",
                                  e.target.value,
                                )
                              }
                              className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none text-sm"
                            >
                              <option value="">Select Employee</option>
                              {employees.map((emp) => (
                                <option key={emp.id} value={emp.id}>
                                  {emp.name}
                                </option>
                              ))}
                            </select>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="isActive"
                  checked={formData.is_active}
                  onChange={(e) =>
                    setFormData({ ...formData, is_active: e.target.checked })
                  }
                  className="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <label
                  htmlFor="isActive"
                  className="ml-2 text-sm font-medium text-gray-700"
                >
                  Active
                </label>
              </div>

              <div className="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowModal(false);
                    resetForm();
                  }}
                  className="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary transition-all duration-200"
                >
                  {editingOkr ? "Update" : "Create"} OKR
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Objectives Management Modal */}
      {showObjectivesModal && selectedOkr && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <div>
                <h2 className="text-xl text-gray-900">
                  <span className="font-bold">{selectedOkr.name}</span> -{" "}
                  {selectedOkr.owner.name}
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

            {/* Objectives List */}
            <div className="flex-1 overflow-y-auto p-6">
              {objectives.length === 0 ? (
                <div className="text-center py-12 text-gray-500">
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
                  <p className="text-lg font-medium">No objectives yet</p>
                  <p className="text-sm mt-1">
                    Create objectives when creating or editing an OKR
                  </p>
                </div>
              ) : (
                <div className="grid gap-4">
                  {objectives.map((objective) => (
                    <div
                      key={objective.id}
                      className="bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-primary transition-colors"
                    >
                      <div className="flex items-start justify-between mb-3">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-2">
                            <h4 className="font-semibold text-gray-900">
                              {objective.description}
                            </h4>
                            <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                              Weight: {decimalToPercent(objective.weight)}%
                            </span>
                          </div>
                          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                            <div>
                              <span className="font-medium">Target:</span>{" "}
                              {objective.target_value} ({objective.target_type})
                            </div>
                            <div>
                              <span className="font-medium">Deadline:</span>{" "}
                              {new Date(
                                objective.deadline,
                              ).toLocaleDateString()}
                            </div>
                            <div>
                              <span className="font-medium">Tracker:</span>{" "}
                              {objective.tracker_employee?.name || "N/A"}
                            </div>
                            <div>
                              <span className="font-medium">Approver:</span>{" "}
                              {objective.approver_employee?.name || "N/A"}
                            </div>
                            <div>
                              <span className="font-medium">Tracking:</span>{" "}
                              {objective.tracking_type}
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center gap-2 ml-4">
                          <button
                            onClick={() => handleCheckIn(objective)}
                            className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                            title="Check In"
                          >
                            <svg
                              className="w-3.5 h-3.5 mr-1"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                              />
                            </svg>
                            Check In
                          </button>
                          <button
                            onClick={() => handleDeleteObjective(objective.id)}
                            className="p-2 text-accent hover:bg-red-100 rounded-lg transition-colors"
                            title="Delete"
                          >
                            <svg
                              className="w-4 h-4"
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

                      {/* Progress Bar */}
                      <div className="mt-3 pt-3 border-t border-gray-100">
                        <div className="flex items-center justify-between mb-2">
                          <span className="text-sm font-medium text-gray-700">
                            Progress
                          </span>
                          <div className="flex items-center gap-2">
                            <span className="text-sm text-gray-600">
                              {objective.current_value || 0} /{" "}
                              {objective.target_value}
                            </span>
                            <span
                              className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                                (objective.progress || 0) >= 100
                                  ? "bg-green-100 text-green-800"
                                  : (objective.progress || 0) >= 50
                                    ? "bg-blue-100 text-blue-800"
                                    : (objective.progress || 0) >= 25
                                      ? "bg-yellow-100 text-yellow-800"
                                      : "bg-red-100 text-red-800"
                              }`}
                            >
                              {Math.round(objective.progress || 0)}%
                            </span>
                          </div>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                          <div
                            className={`h-full rounded-full transition-all duration-500 ease-out ${
                              (objective.progress || 0) >= 100
                                ? "bg-gradient-to-r from-green-500 to-green-400"
                                : (objective.progress || 0) >= 50
                                  ? "bg-gradient-to-r from-blue-500 to-blue-400"
                                  : (objective.progress || 0) >= 25
                                    ? "bg-gradient-to-r from-yellow-500 to-yellow-400"
                                    : "bg-gradient-to-r from-red-500 to-red-400"
                            }`}
                            style={{
                              width: `${Math.min(objective.progress || 0, 100)}%`,
                            }}
                          />
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

      {/* Weight Validation Alert Modal */}
      {showWeightAlert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900 flex items-center">
                <svg
                  className="w-6 h-6 text-orange-500 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
                Weight Validation Error
              </h2>
            </div>
            <div className="p-6">
              <p className="text-gray-700">{weightAlertMessage}</p>
            </div>
            <div className="p-6 border-t border-gray-200 flex justify-end">
              <button
                onClick={() => setShowWeightAlert(false)}
                className="px-6 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary transition-all duration-200"
              >
                OK
              </button>
            </div>
          </div>
        </div>
      )}

      {/* CheckIn Modal */}
      {showCheckInModal && selectedObjective && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <div>
                <h2 className="text-xl font-bold text-gray-900 flex items-center">
                  <svg
                    className="w-6 h-6 text-green-500 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  Check In
                </h2>
                <p className="text-sm text-gray-500 mt-1">
                  {selectedObjective.description}
                </p>
              </div>
              <button
                onClick={() => {
                  setShowCheckInModal(false);
                  setSelectedObjective(null);
                  resetCheckInForm();
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

            <div className="flex-1 overflow-y-auto">
              {/* CheckIn Form */}
              <div className="p-6 border-b border-gray-200 bg-gray-50">
                <h3 className="text-sm font-semibold text-gray-700 mb-3">
                  Add New Check-In
                </h3>
                <form onSubmit={handleSubmitCheckIn} className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
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
                        className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Current Value *
                      </label>
                      {selectedObjective.target_type === "binary" ? (
                        <select
                          required
                          value={checkInFormData.current_value}
                          onChange={(e) =>
                            setCheckInFormData({
                              ...checkInFormData,
                              current_value: e.target.value,
                            })
                          }
                          className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                        >
                          <option value="">Select status</option>
                          <option value="0">0 - Not Achieved</option>
                          <option value="1">1 - Achieved</option>
                        </select>
                      ) : (
                        <input
                          type="number"
                          step="0.01"
                          min="0"
                          required
                          value={checkInFormData.current_value}
                          onChange={(e) =>
                            setCheckInFormData({
                              ...checkInFormData,
                              current_value: e.target.value,
                            })
                          }
                          placeholder="Enter current value"
                          className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none"
                        />
                      )}
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
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
                      placeholder="Add comments about progress..."
                      rows="3"
                      className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none resize-none"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Evidence File
                    </label>
                    <div className="flex items-center gap-2">
                      <label className="flex-1">
                        <input
                          type="file"
                          onChange={handleFileChange}
                          className="hidden"
                          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                        />
                        <div className="w-full px-3 py-2 rounded-lg border-2 border-gray-200 cursor-pointer hover:border-primary transition-colors">
                          {evidenceFileName ? (
                            <span className="text-sm text-gray-700">
                              {evidenceFileName}
                            </span>
                          ) : (
                            <span className="text-gray-400 text-sm">
                              Choose file...
                            </span>
                          )}
                        </div>
                      </label>
                      {evidenceFileName && (
                        <button
                          type="button"
                          onClick={() => {
                            setCheckInFormData({
                              ...checkInFormData,
                              evidence_file: null,
                            });
                            setEvidenceFileName("");
                          }}
                          className="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                          title="Remove file"
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
                      )}
                    </div>
                    <p className="text-xs text-gray-500 mt-1">
                      Accepted: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX
                    </p>
                  </div>
                  <div className="flex justify-end">
                    <button
                      type="submit"
                      className="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium"
                    >
                      Add Check-In
                    </button>
                  </div>
                </form>
              </div>

              {/* CheckIns History */}
              <div className="p-6">
                <h3 className="text-sm font-semibold text-gray-700 mb-3">
                  Check-In History
                </h3>
                {checkIns.length === 0 ? (
                  <div className="text-center py-8 text-gray-500">
                    <svg
                      className="mx-auto h-10 w-10 text-gray-400 mb-3"
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
                    <p className="text-sm">
                      No check-ins yet. Add your first check-in above.
                    </p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {checkIns.map((checkIn) => (
                      <div
                        key={checkIn.id}
                        className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm"
                      >
                        {/* Main CheckIn Content */}
                        <div className="p-4">
                          <div className="flex items-start justify-between mb-3">
                            <div className="flex items-center gap-3 flex-1">
                              <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg
                                  className="w-5 h-5 text-green-600"
                                  fill="none"
                                  stroke="currentColor"
                                  viewBox="0 0 24 24"
                                >
                                  <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                  />
                                </svg>
                              </div>
                              <div className="flex-1">
                                <div className="flex items-center gap-2 flex-wrap">
                                  <p className="font-semibold text-gray-900">
                                    {checkIn.current_value} /{" "}
                                    {selectedObjective.target_value}
                                  </p>
                                  {checkIn.current_status && (
                                    <span
                                      className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${getStatusBadge(checkIn.current_status)}`}
                                    >
                                      {checkIn.current_status
                                        .charAt(0)
                                        .toUpperCase() +
                                        checkIn.current_status.slice(1)}
                                    </span>
                                  )}
                                  {checkIn.evidence_path && (
                                    <a
                                      href={checkIn.evidence_path}
                                      target="_blank"
                                      rel="noopener noreferrer"
                                      className="text-primary hover:underline text-xs flex items-center"
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
                                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                        />
                                      </svg>
                                      Evidence
                                    </a>
                                  )}
                                </div>
                                <p className="text-xs text-gray-500">
                                  {new Date(checkIn.date).toLocaleDateString()}
                                </p>
                              </div>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex items-center gap-2 flex-shrink-0">
                              {canApproveOrReject(checkIn) && (
                                <>
                                  <button
                                    onClick={() =>
                                      handleApproveCheckIn(checkIn.id)
                                    }
                                    className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                                    title="Approve"
                                  >
                                    <svg
                                      className="w-3.5 h-3.5 mr-1"
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
                                    Approve
                                  </button>
                                  <button
                                    onClick={() =>
                                      handleRejectCheckIn(checkIn.id)
                                    }
                                    className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                                    title="Reject"
                                  >
                                    <svg
                                      className="w-3.5 h-3.5 mr-1"
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
                                    Reject
                                  </button>
                                </>
                              )}
                              {canDeleteCheckIn(checkIn) && (
                                <button
                                  onClick={() =>
                                    handleDeleteCheckIn(checkIn.id)
                                  }
                                  className="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-red-100 hover:text-red-700 transition-colors"
                                  title="Delete"
                                >
                                  <svg
                                    className="w-3.5 h-3.5 mr-1"
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
                                  Delete
                                </button>
                              )}
                            </div>
                          </div>
                          {checkIn.comments && (
                            <p className="text-sm text-gray-600 mt-2">
                              {checkIn.comments}
                            </p>
                          )}
                        </div>

                        {/* Approval Logs */}
                        {approvalLogs[checkIn.id] &&
                          approvalLogs[checkIn.id].length > 0 && (
                            <div className="px-4 py-3 bg-gray-50 border-t border-gray-200">
                              <h4 className="text-xs font-semibold text-gray-700 mb-2">
                                Approval History
                              </h4>
                              <div className="space-y-2">
                                {approvalLogs[checkIn.id].map((log) => (
                                  <div
                                    key={log.id}
                                    className="flex items-center gap-2 text-xs"
                                  >
                                    <div
                                      className={`w-2 h-2 rounded-full flex-shrink-0 ${
                                        log.status === "approved"
                                          ? "bg-green-500"
                                          : log.status === "rejected"
                                            ? "bg-red-500"
                                            : "bg-yellow-500"
                                      }`}
                                    ></div>
                                    <span
                                      className={`font-medium ${
                                        log.status === "approved"
                                          ? "text-green-700"
                                          : log.status === "rejected"
                                            ? "text-red-700"
                                            : "text-yellow-700"
                                      }`}
                                    >
                                      {log.status.charAt(0).toUpperCase() +
                                        log.status.slice(1)}
                                    </span>
                                    <span className="text-gray-500">
                                      {new Date(
                                        log.created_at,
                                      ).toLocaleString()}
                                    </span>
                                  </div>
                                ))}
                              </div>
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
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div className="p-6">
              <div className="flex items-center mb-4">
                <svg
                  className="w-8 h-8 text-red-500 mr-3"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
                <h3 className="text-xl font-bold text-gray-900">
                  {confirmModalConfig.title}
                </h3>
              </div>
              <p className="text-gray-700 mb-6">{confirmModalConfig.message}</p>
              <div className="flex justify-end gap-3">
                <button
                  onClick={() => setShowConfirmModal(false)}
                  className="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  onClick={() => {
                    if (confirmModalConfig.onConfirm) {
                      confirmModalConfig.onConfirm();
                    }
                  }}
                  className="px-6 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors"
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

export default Okrs;
