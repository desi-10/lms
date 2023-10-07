import { Navigate, Outlet } from "react-router-dom";
import { useStudent } from "../context/AuthContext";

const RestrictedRoute = () => {
  const { student } = useStudent();
  if (student) {
    return <Navigate to="/home" />;
  }
  return (
    <section>
      <Outlet />
    </section>
  );
};

export default RestrictedRoute;
