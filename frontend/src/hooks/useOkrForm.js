import { useState } from "react";

const useOkrForm = (initialData = null) => {
  // Helper function to convert decimal to percentage
  const decimalToPercent = (decimal) => {
    const parsed = parseFloat(decimal);
    return isNaN(parsed) ? 0 : Math.round(parsed * 100);
  };

  const [formData, setFormData] = useState({
    name: initialData?.name || "",
    weight: initialData?.weight ? decimalToPercent(initialData.weight) : "",
    okr_type_id: initialData?.okr_type_id || "",
    start_date: initialData?.start_date?.split("T")[0] || "",
    end_date: initialData?.end_date?.split("T")[0] || "",
    owner_type: initialData?.owner_type || "App\\Models\\Employee",
    owner_id: initialData?.owner_id || "",
    is_active: initialData?.is_active ?? true,
    objectives: (initialData?.objectives || []).map((obj) => ({
      ...obj,
      weight: decimalToPercent(obj.weight),
      deadline: obj.deadline?.split("T")[0] || "",
    })),
  });

  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [showWeightAlert, setShowWeightAlert] = useState(false);
  const [weightAlertMessage, setWeightAlertMessage] = useState("");

  // Helper function to convert percentage to decimal
  const percentToDecimal = (percent) => {
    const parsed = parseFloat(percent);
    return isNaN(parsed) ? 0 : parsed / 100;
  };

  const updateField = (field, value) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
    // Clear error for this field when user updates it
    if (errors[field]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const addObjective = () => {
    setFormData((prev) => ({
      ...prev,
      objectives: [
        ...prev.objectives,
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
    }));
  };

  const updateObjective = (index, field, value) => {
    setFormData((prev) => {
      const newObjectives = [...prev.objectives];
      newObjectives[index][field] = value;
      return { ...prev, objectives: newObjectives };
    });
  };

  const removeObjective = (index) => {
    setFormData((prev) => ({
      ...prev,
      objectives: prev.objectives.filter((_, i) => i !== index),
    }));
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
    setErrors({});
    setShowWeightAlert(false);
    setWeightAlertMessage("");
  };

  const validateForm = () => {
    const newErrors = {};

    // Validate OKR basic fields
    if (!formData.name.trim()) {
      newErrors.name = "OKR name is required";
    }
    if (!formData.weight || formData.weight <= 0) {
      newErrors.weight = "OKR weight is required";
    }
    if (!formData.okr_type_id) {
      newErrors.okr_type_id = "OKR type is required";
    }
    if (!formData.start_date) {
      newErrors.start_date = "Start date is required";
    }
    if (!formData.end_date) {
      newErrors.end_date = "End date is required";
    }
    if (formData.start_date && formData.end_date && formData.end_date <= formData.start_date) {
      newErrors.end_date = "End date must be after start date";
    }
    if (!formData.owner_id) {
      newErrors.owner_id = "Owner is required";
    }

    // Filter out empty objectives
    const validObjectives = formData.objectives.filter(
      (obj) => obj.description.trim() !== "",
    );

    // Validate objectives if any exist
    for (const obj of validObjectives) {
      // Validate target value based on target type
      const targetValue = parseFloat(obj.target_value);
      if (obj.target_type === "binary") {
        if (targetValue !== 0 && targetValue !== 1) {
          setWeightAlertMessage(
            `Objective "${obj.description}" has binary target type, so target value must be 0 or 1. Current value: ${targetValue}.`,
          );
          setShowWeightAlert(true);
          return false;
        }
      } else if (obj.target_type === "numeric") {
        if (targetValue < 0) {
          setWeightAlertMessage(
            `Objective "${obj.description}" has numeric target type, so target value must be a positive number. Current value: ${targetValue}.`,
          );
          setShowWeightAlert(true);
          return false;
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
        return false;
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const prepareSubmitData = () => {
    // Filter out empty objectives
    const validObjectives = formData.objectives.filter(
      (obj) => obj.description.trim() !== "",
    );

    return {
      ...formData,
      weight: percentToDecimal(formData.weight),
      objectives: validObjectives.map((obj) => ({
        ...obj,
        weight: percentToDecimal(obj.weight),
      })),
    };
  };

  return {
    formData,
    setFormData,
    errors,
    setErrors,
    loading,
    setLoading,
    showWeightAlert,
    setShowWeightAlert,
    weightAlertMessage,
    setWeightAlertMessage,
    updateField,
    addObjective,
    updateObjective,
    removeObjective,
    resetForm,
    validateForm,
    prepareSubmitData,
  };
};

export default useOkrForm;
