import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { getOkr } from "../services/api";
import OkrForm from "../components/okr/OkrForm";

const OkrEdit = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [initialData, setInitialData] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchOkr = async () => {
      try {
        const response = await getOkr(id);
        setInitialData(response.data);
      } catch (err) {
        console.error("Error fetching OKR:", err);
        setError(err.response?.data?.message || "Failed to load OKR");
        // Navigate back after a short delay on error
        setTimeout(() => {
          navigate("/admin/okrs");
        }, 2000);
      } finally {
        setLoading(false);
      }
    };

    fetchOkr();
  }, [id, navigate]);

  const handleSuccess = () => {
    // Stay on edit page, just clear loading state
    // The success toast is already shown by OkrForm
  };

  const handleCancel = () => {
    navigate("/admin/okrs");
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-red-500">{error}</div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <OkrForm
        mode="edit"
        initialData={initialData}
        okrId={id}
        onSuccess={handleSuccess}
        onCancel={handleCancel}
      />
    </div>
  );
};

export default OkrEdit;
