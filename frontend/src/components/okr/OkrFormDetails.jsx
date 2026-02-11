const OkrFormDetails = ({ formData, availableOwners, errors, onUpdate }) => {
  const fieldIcon = (type) => {
    const icons = {
      name: (
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
            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
          />
        </svg>
      ),
      weight: (
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
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
        </svg>
      ),
      type: (
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
            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
          />
        </svg>
      ),
      calendar: (
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
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      ),
      user: (
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
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
          />
        </svg>
      ),
      building: (
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
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
          />
        </svg>
      ),
    };
    return icons[type] || null;
  };

  const renderError = (error) => {
    if (!error) return null;
    return (
      <div className="flex items-center gap-1 mt-1.5 text-red-600 text-xs animate-fade-in">
        <svg
          className="w-3.5 h-3.5 flex-shrink-0"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fillRule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clipRule="evenodd"
          />
        </svg>
        <span>{error}</span>
      </div>
    );
  };

  return (
    <div className="space-y-5">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        {/* OKR Name */}
        <div className="group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {fieldIcon("name")}
            </span>
            OKR Name *
          </label>
          <div className="relative">
            <input
              type="text"
              required
              value={formData.name}
              onChange={(e) => onUpdate("name", e.target.value)}
              placeholder="e.g., Q1 2024 Sales Goals"
              className={`w-full px-4 py-3 pl-11 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none bg-white ${
                errors.name
                  ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                  : "border-gray-200 focus:border-primary"
              }`}
            />
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
              {fieldIcon("name")}
            </span>
          </div>
          {renderError(errors.name)}
        </div>

        {/* Weight */}
        <div className="group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {fieldIcon("weight")}
            </span>
            Weight *
          </label>
          <div className="relative">
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              required
              value={formData.weight}
              onChange={(e) => onUpdate("weight", e.target.value)}
              placeholder="100"
              className={`w-full px-4 py-3 pl-11 pr-10 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none bg-white ${
                errors.weight
                  ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                  : "border-gray-200 focus:border-primary"
              }`}
            />
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
              {fieldIcon("weight")}
            </span>
            <span className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">
              %
            </span>
          </div>
          {renderError(errors.weight)}
          {!errors.weight && (
            <p className="text-xs text-gray-500 mt-1.5">
              Enter a value between 0 and 100
            </p>
          )}
        </div>

        {/* OKR Type */}
        <div className="group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {fieldIcon("type")}
            </span>
            OKR Type *
          </label>
          <div className="relative">
            <select
              required
              value={formData.okr_type_id}
              onChange={(e) => onUpdate("okr_type_id", e.target.value)}
              className={`w-full px-4 py-3 pl-11 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none appearance-none bg-white cursor-pointer ${
                errors.okr_type_id
                  ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                  : "border-gray-200 focus:border-primary"
              }`}
            >
              <option value="">Select OKR Type</option>
              {availableOwners?.okr_types?.map((type) => (
                <option key={type.id} value={type.id}>
                  {type.name}
                </option>
              ))}
            </select>
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
              {fieldIcon("type")}
            </span>
            <svg
              className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </div>
          {renderError(errors.okr_type_id)}
        </div>

        {/* Owner Type */}
        <div className="group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {fieldIcon("user")}
            </span>
            Owner Type *
          </label>
          <div className="relative">
            <select
              required
              value={formData.owner_type}
              onChange={(e) => onUpdate("owner_type", e.target.value)}
              className="w-full px-4 py-3 pl-11 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none appearance-none bg-white cursor-pointer"
            >
              <option value="App\Models\Employee">Employee</option>
              <option value="App\Models\OrgUnit">Organization Unit</option>
            </select>
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
              {fieldIcon("user")}
            </span>
            <svg
              className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </div>
        </div>

        {/* Start Date */}
        <div className="group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {fieldIcon("calendar")}
            </span>
            Start Date *
          </label>
          <div className="relative">
            <input
              type="date"
              required
              value={formData.start_date}
              onChange={(e) => onUpdate("start_date", e.target.value)}
              className={`w-full px-4 py-3 pl-11 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none ${
                errors.start_date
                  ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                  : "border-gray-200 focus:border-primary"
              }`}
            />
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
              {fieldIcon("calendar")}
            </span>
          </div>
          {renderError(errors.start_date)}
        </div>

        {/* End Date */}
        <div className="group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {fieldIcon("calendar")}
            </span>
            End Date *
          </label>
          <div className="relative">
            <input
              type="date"
              required
              value={formData.end_date}
              onChange={(e) => onUpdate("end_date", e.target.value)}
              className={`w-full px-4 py-3 pl-11 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none ${
                errors.end_date
                  ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                  : "border-gray-200 focus:border-primary"
              }`}
            />
            <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
              {fieldIcon("calendar")}
            </span>
          </div>
          {renderError(errors.end_date)}
        </div>

        {/* Owner Selection */}
        <div className="md:col-span-2 group">
          <label className="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <span className="text-primary/60 group-hover:text-primary/80 transition-colors">
              {formData.owner_type === "App\\Models\\Employee"
                ? fieldIcon("user")
                : fieldIcon("building")}
            </span>
            {formData.owner_type === "App\\Models\\Employee"
              ? "Employee *"
              : "Organization Unit *"}
          </label>
          {formData.owner_type === "App\\Models\\Employee" ? (
            <div className="relative">
              <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none z-10">
                {fieldIcon("user")}
              </span>
              <div className="pl-11">
                <select
                  required
                  value={formData.owner_id}
                  onChange={(e) => onUpdate("owner_id", e.target.value)}
                  className={`w-full px-4 py-3 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none appearance-none bg-white cursor-pointer ${
                    errors.owner_id
                      ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                      : "border-gray-200 focus:border-primary"
                  }`}
                >
                  <option value="">Select employee</option>
                  {availableOwners?.employees?.map((emp) => (
                    <option key={emp.id} value={emp.id}>
                      {emp.title}
                    </option>
                  ))}
                </select>
                <svg
                  className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
          ) : (
            <div className="relative">
              <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none z-10">
                {fieldIcon("building")}
              </span>
              <div className="pl-11">
                <select
                  required
                  value={formData.owner_id}
                  onChange={(e) => onUpdate("owner_id", e.target.value)}
                  className={`w-full px-4 py-3 rounded-xl border-2 focus:ring-4 focus:ring-primary/20 transition-all duration-200 outline-none appearance-none bg-white cursor-pointer ${
                    errors.owner_id
                      ? "border-red-300 focus:border-red-500 focus:ring-red-500/20"
                      : "border-gray-200 focus:border-primary"
                  }`}
                >
                  <option value="">Select organization unit</option>
                  {availableOwners?.org_units?.map((org) => (
                    <option key={org.id} value={org.id}>
                      {org.title}
                    </option>
                  ))}
                </select>
                <svg
                  className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
          )}
          {renderError(errors.owner_id)}
        </div>
      </div>

      {/* Active Toggle */}
      <div className="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-gradient-to-br from-green-400 to-green-500 rounded-lg flex items-center justify-center text-white shadow-sm">
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
          </div>
          <div>
            <p className="text-sm font-semibold text-gray-700">Active Status</p>
            <p className="text-xs text-gray-500">
              Enable this OKR for tracking and reporting
            </p>
          </div>
        </div>
        <label className="relative inline-flex items-center cursor-pointer">
          <input
            type="checkbox"
            checked={formData.is_active}
            onChange={(e) => onUpdate("is_active", e.target.checked)}
            className="sr-only peer"
          />
          <div className="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-primary peer-checked:to-primary-dark shadow-inner"></div>
          <span className="ml-3 text-sm font-medium text-gray-700 min-w-[60px]">
            {formData.is_active ? (
              <span className="text-green-600 font-semibold">Active</span>
            ) : (
              <span className="text-gray-500">Inactive</span>
            )}
          </span>
        </label>
      </div>
    </div>
  );
};

export default OkrFormDetails;
