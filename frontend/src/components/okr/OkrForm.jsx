import { toast } from "react-toastify";
import { createOkr, updateOkr } from "../../services/api";
import useOkrForm from "../../hooks/useOkrForm";
import useOkrData from "../../hooks/useOkrData";
import OkrBreadcrumbs from "./OkrBreadcrumbs";
import OkrFormDetails from "./OkrFormDetails";
import ObjectiveFormList from "./ObjectiveFormList";

const OkrForm = ({ mode, initialData, okrId, onSuccess, onCancel }) => {
  const {
    formData,
    errors,
    loading,
    updateField,
    addObjective,
    updateObjective,
    removeObjective,
    resetForm,
    validateForm,
    prepareSubmitData,
  } = useOkrForm(initialData);
  const {
    okrTypes,
    employees,
    availableOwners,
    loading: dataLoading,
  } = useOkrData();

  // Calculate total weight of objectives
  const totalWeight = formData.objectives.reduce((sum, obj) => {
    const weight = parseFloat(obj.weight) || 0;
    return sum + weight;
  }, 0);
  const weightDiff = Math.abs(totalWeight - 100);
  const isWeightValid = weightDiff < 0.01;
  const weightStatus = totalWeight > 100 ? 'over' : totalWeight < 100 ? 'under' : 'valid';

  const getWeightColor = () => {
    if (isWeightValid) return 'text-green-600 bg-green-50 border-green-200';
    if (weightStatus === 'over') return 'text-red-600 bg-red-50 border-red-200';
    return 'text-orange-600 bg-orange-50 border-orange-200';
  };

  const getWeightIcon = () => {
    if (isWeightValid) {
      return (
        <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
        </svg>
      );
    }
    return (
      <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
        <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
      </svg>
    );
  };

  // Merge okr_types into availableOwners for easier access in OkrFormDetails
  const enhancedAvailableOwners = availableOwners
    ? { ...availableOwners, okr_types: okrTypes }
    : { okr_types: okrTypes };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Validate form
    if (!validateForm()) {
      // Show toast for validation errors
      if (!isWeightValid) {
        toast.warning(
          `Total objective weight is ${totalWeight.toFixed(1)}%. It should equal 100%.`
        );
      } else {
        toast.error("Please fix the validation errors before submitting.");
      }
      // Scroll to first error
      const firstErrorField = Object.keys(errors)[0];
      if (firstErrorField) {
        const errorElement = document.querySelector(
          `[name="${firstErrorField}"]`,
        );
        if (errorElement) {
          errorElement.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      }
      return;
    }

    try {
      const submitData = prepareSubmitData();

      if (mode === "edit") {
        await updateOkr(okrId, submitData);
        toast.success("OKR updated successfully!");
      } else {
        await createOkr(submitData);
        toast.success("OKR created successfully!");
      }

      setTimeout(() => {
        onSuccess();
      }, 500);
    } catch (error) {
      console.error("Error saving OKR:", error);
      const errorMessage =
        error.response?.data?.message || error.message || "Failed to save OKR";
      toast.error(errorMessage);
    }
  };

  const handleCancel = () => {
    resetForm();
    onCancel();
  };

  if (dataLoading) {
    return (
      <div className="flex flex-col items-center justify-center py-20">
        <div className="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
        <p className="mt-4 text-gray-600">Loading form...</p>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto">
      <OkrBreadcrumbs mode={mode} okrName={formData.name} />

      {/* Page Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          {mode === "edit" ? "Edit OKR" : "Create New OKR"}
        </h1>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Top Action Bar */}

        {/* Step 1: OKR Details */}
        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-gradient-to-br from-primary to-primary-dark rounded-xl flex items-center justify-center text-white shadow-lg">
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
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                </div>
                <div>
                  <h2 className="text-xl font-bold text-gray-900">OKR Details</h2>
                  <p className="text-sm text-gray-600">Basic information about this OKR</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={handleCancel}
                  disabled={loading}
                  className="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="px-5 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl hover:from-primary-dark hover:to-primary hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-md text-sm"
                >
                  {loading ? (
                    <span className="flex items-center">
                      <svg
                        className="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          className="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          strokeWidth="4"
                        />
                        <path
                          className="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        />
                      </svg>
                      {mode === "edit" ? "Updating..." : "Creating..."}
                    </span>
                  ) : (
                    <span className="flex items-center">
                      <svg
                        className="w-4 h-4 mr-1.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        {mode === "edit" ? (
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 8l-4-4m0 0L8 12m0 0l4 4"
                          />
                        ) : (
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 4v16m8-8H4"
                          />
                        )}
                      </svg>
                      {mode === "edit" ? "Update OKR" : "Create OKR"}
                    </span>
                  )}
                </button>
              </div>
            </div>
          </div>
          <div className="p-6">
            <OkrFormDetails
              formData={formData}
              availableOwners={enhancedAvailableOwners}
              errors={errors}
              onUpdate={updateField}
            />
          </div>
        </div>

        {/* Step 2: Objectives */}
        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
            <div className="flex items-center justify-between gap-4">
              <div className="flex items-center gap-3 flex-1">
                <div className="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
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
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                    />
                  </svg>
                </div>
                <div>
                  <h2 className="text-xl font-bold text-gray-900">Objectives</h2>
                  <p className="text-sm text-gray-600">
                    {formData.objectives.length} {formData.objectives.length === 1 ? 'objective' : 'objectives'} added
                  </p>
                </div>
              </div>

              {/* Weight Progress Indicator */}
              {formData.objectives.length > 0 && (
                <div className={`flex items-center gap-2 px-3 py-1.5 rounded-lg border ${getWeightColor()}`}>
                  <div className="flex-shrink-0">
                    {getWeightIcon()}
                  </div>
                  <div className="text-sm">
                    <span className="font-bold">{totalWeight.toFixed(1)}%</span>
                    {!isWeightValid && (
                      <span className="ml-1 opacity-75">
                        ({weightStatus === 'over' ? `${(totalWeight - 100).toFixed(1)}% over` : `${(100 - totalWeight).toFixed(1)}% under`})
                      </span>
                    )}
                  </div>
                </div>
              )}

              <button
                type="button"
                onClick={addObjective}
                className="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 shadow-md hover:shadow-lg transition-all duration-200 font-medium text-sm flex-shrink-0"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                Add Objective
              </button>
            </div>
          </div>
          <div className="p-6">
            <ObjectiveFormList
              objectives={formData.objectives}
              employees={employees}
              onAdd={addObjective}
              onUpdate={updateObjective}
              onRemove={removeObjective}
            />
          </div>
        </div>
      </form>
    </div>
  );
};

export default OkrForm;
