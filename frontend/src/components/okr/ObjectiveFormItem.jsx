const ObjectiveFormItem = ({ objective, index, employees, onUpdate, onRemove }) => {
  const fieldIcons = {
    description: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
      </svg>
    ),
    weight: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
    ),
    target: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
      </svg>
    ),
    calendar: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    ),
    tracking: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
    ),
    user: (
      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    ),
  };

  return (
    <div className="group bg-white rounded-2xl border-2 border-gray-200 hover:border-purple-200 hover:shadow-lg transition-all duration-200 overflow-hidden">
      {/* Card Header */}
      <div className="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-purple-100">
        <div className="flex items-center gap-2">
          <span className="w-7 h-7 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center text-white text-sm font-bold shadow-sm">
            {index + 1}
          </span>
          <span className="text-sm font-semibold text-gray-700">Objective</span>
        </div>
        <button
          type="button"
          onClick={() => onRemove(index)}
          className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all duration-200"
          title="Remove objective"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>

      {/* Card Body */}
      <div className="p-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {/* Description - Full Width */}
          <div className="md:col-span-3">
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.description}</span>
              Description *
            </label>
            <input
              type="text"
              value={objective.description}
              onChange={(e) => onUpdate(index, "description", e.target.value)}
              placeholder="e.g., Increase sales revenue by 20%"
              className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none text-sm"
            />
          </div>

          {/* Weight */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.weight}</span>
              Weight *
            </label>
            <div className="relative">
              <input
                type="number"
                step="0.01"
                min="0"
                max="100"
                value={objective.weight}
                onChange={(e) => onUpdate(index, "weight", e.target.value)}
                placeholder="50"
                className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none text-sm pr-10"
              />
              <span className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">%</span>
            </div>
          </div>

          {/* Target Type */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.target}</span>
              Target Type *
            </label>
            <div className="relative">
              <select
                value={objective.target_type}
                onChange={(e) => {
                  const newType = e.target.value;
                  if (
                    newType === "binary" &&
                    objective.target_value &&
                    objective.target_value !== "0" &&
                    objective.target_value !== "1"
                  ) {
                    onUpdate(index, "target_value", "1");
                  }
                  onUpdate(index, "target_type", newType);
                }}
                className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none appearance-none text-sm bg-white cursor-pointer"
              >
                <option value="numeric">Numeric</option>
                <option value="binary">Binary</option>
              </select>
              <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>

          {/* Target Value */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.target}</span>
              Target Value *
            </label>
            {objective.target_type === "binary" ? (
              <div className="relative">
                <select
                  required
                  value={objective.target_value}
                  onChange={(e) => onUpdate(index, "target_value", e.target.value)}
                  className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none appearance-none text-sm bg-white cursor-pointer"
                >
                  <option value="">Select status</option>
                  <option value="0">0 - Not Achieved</option>
                  <option value="1">1 - Achieved</option>
                </select>
                <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            ) : (
              <input
                type="number"
                step="0.01"
                min="0"
                value={objective.target_value}
                onChange={(e) => onUpdate(index, "target_value", e.target.value)}
                placeholder="100"
                className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none text-sm"
              />
            )}
          </div>

          {/* Deadline */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.calendar}</span>
              Deadline *
            </label>
            <input
              type="date"
              value={objective.deadline}
              onChange={(e) => onUpdate(index, "deadline", e.target.value)}
              className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none text-sm"
            />
          </div>

          {/* Tracking Type */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.tracking}</span>
              Tracking Type *
            </label>
            <div className="relative">
              <select
                value={objective.tracking_type}
                onChange={(e) => onUpdate(index, "tracking_type", e.target.value)}
                className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none appearance-none text-sm bg-white cursor-pointer"
              >
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
              </select>
              <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>

          {/* Tracker */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.user}</span>
              Tracker *
            </label>
            <div className="relative">
              <select
                required
                value={objective.tracker}
                onChange={(e) => onUpdate(index, "tracker", e.target.value)}
                className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none appearance-none text-sm bg-white cursor-pointer"
              >
                <option value="">Select employee</option>
                {employees.map((employee) => (
                  <option key={employee.id} value={employee.id}>
                    {employee.name}
                  </option>
                ))}
              </select>
              <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>

          {/* Approver */}
          <div>
            <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
              <span className="text-purple-500">{fieldIcons.user}</span>
              Approver *
            </label>
            <div className="relative">
              <select
                required
                value={objective.approver}
                onChange={(e) => onUpdate(index, "approver", e.target.value)}
                className="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/20 transition-all duration-200 outline-none appearance-none text-sm bg-white cursor-pointer"
              >
                <option value="">Select employee</option>
                {employees.map((employee) => (
                  <option key={employee.id} value={employee.id}>
                    {employee.name}
                  </option>
                ))}
              </select>
              <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ObjectiveFormItem;
