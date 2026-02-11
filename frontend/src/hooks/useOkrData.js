import { useState, useEffect } from "react";
import { getOkrTypes, getEmployees, getAvailableOwners } from "../services/api";

const useOkrData = () => {
  const [okrTypes, setOkrTypes] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [availableOwners, setAvailableOwners] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [okrTypesRes, employeesRes, ownersRes] = await Promise.all([
        getOkrTypes(),
        getEmployees(),
        getAvailableOwners(),
      ]);

      setOkrTypes(okrTypesRes.data);
      setEmployees(employeesRes.data);
      setAvailableOwners(ownersRes.data);
      setError(null);
    } catch (err) {
      console.error("Error fetching OKR data:", err);
      setError(err.message || "Failed to load required data");
    } finally {
      setLoading(false);
    }
  };

  const refetch = () => {
    fetchData();
  };

  return {
    okrTypes,
    employees,
    availableOwners,
    loading,
    error,
    refetch,
  };
};

export default useOkrData;
