import { Link } from "react-router-dom";

const OkrBreadcrumbs = ({ mode, okrName }) => {
  return (
    <nav className="flex items-center space-x-2 text-sm mb-6">
      <Link
        to="/admin/okrs"
        className="text-gray-500 hover:text-primary transition-colors"
      >
        OKRs
      </Link>
      <svg
        className="w-4 h-4 text-gray-400"
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
      <span className="text-gray-900 font-medium">
        {mode === "create"
          ? "Create New OKR"
          : okrName
          ? `Edit: ${okrName}`
          : "Edit OKR"}
      </span>
    </nav>
  );
};

export default OkrBreadcrumbs;
