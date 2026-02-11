import { useNavigate } from "react-router-dom";
import OkrForm from "../components/okr/OkrForm";

const OkrCreate = () => {
  const navigate = useNavigate();

  const handleSuccess = () => {
    navigate("/admin/okrs");
  };

  const handleCancel = () => {
    navigate("/admin/okrs");
  };

  return (
    <div className="p-6">
      <OkrForm
        mode="create"
        onSuccess={handleSuccess}
        onCancel={handleCancel}
      />
    </div>
  );
};

export default OkrCreate;
