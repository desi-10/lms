import { Navigate, Outlet } from "react-router-dom";
import { useStudent } from "../context/StudentContext";

const RestrictedRoute = () => {
  const { studentState } = useStudent();
  if (studentState) {
    return <Navigate to="/home" />;
  }
  return (
    <section>
      <Outlet />
    </section>
  );
};

export default RestrictedRoute;
