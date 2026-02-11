import ObjectiveFormItem from "./ObjectiveFormItem";

const ObjectiveFormList = ({
  objectives,
  employees,
  onAdd,
  onUpdate,
  onRemove,
}) => {
  // Calculate total weight of objectives
  const totalWeight = objectives.reduce((sum, obj) => {
    const weight = parseFloat(obj.weight) || 0;
    return sum + weight;
  }, 0);

  const hasObjectives = objectives.some((obj) => obj.description.trim() !== "");
  const weightDiff = Math.abs(totalWeight - 100);
  const isWeightValid = weightDiff < 0.01;
  const weightStatus =
    totalWeight > 100 ? "over" : totalWeight < 100 ? "under" : "valid";

  const getWeightColor = () => {
    if (isWeightValid) return "text-green-600 bg-green-50 border-green-200";
    if (weightStatus === "over") return "text-red-600 bg-red-50 border-red-200";
    return "text-orange-600 bg-orange-50 border-orange-200";
  };

  const getWeightIcon = () => {
    if (isWeightValid) {
      return (
        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path
            fillRule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clipRule="evenodd"
          />
        </svg>
      );
    }
    return (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path
          fillRule="evenodd"
          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
          clipRule="evenodd"
        />
      </svg>
    );
  };

  return (
    <div className="space-y-4">
      {/* Header with Stats */}

      {/* Empty State */}
      {objectives.length === 0 ? (
        <div className="text-center py-12 bg-white rounded-2xl border-2 border-dashed border-gray-200">
          <div className="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg
              className="w-8 h-8 text-gray-400"
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
          </div>
          <h4 className="text-lg font-semibold text-gray-700 mb-2">
            No Objectives Yet
          </h4>
          <p className="text-gray-500 mb-4 max-w-sm mx-auto">
            Start by adding measurable objectives that contribute to this OKR.
            The total weight should equal 100%.
          </p>
        </div>
      ) : (
        <div className="space-y-4">
          {objectives.map((objective, index) => (
            <ObjectiveFormItem
              key={index}
              objective={objective}
              index={index}
              employees={employees}
              onUpdate={onUpdate}
              onRemove={onRemove}
            />
          ))}

          {/* Add Another Button */}
          <button
            type="button"
            onClick={onAdd}
            className="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-200 font-medium flex items-center justify-center gap-2"
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
                d="M12 4v16m8-8H4"
              />
            </svg>
            Add Another Objective
          </button>
        </div>
      )}
    </div>
  );
};

export default ObjectiveFormList;
