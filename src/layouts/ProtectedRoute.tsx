import { Navigate, Outlet } from "react-router-dom";
import { useStudent } from "../context/StudentContext";

const ProtectedRoute = () => {
  const { student } = useStudent();
  console.log(student);

  const a = false;

  if (a) {
    return <Navigate to="/" />;
  }
  return (
    <section>
      <Outlet />
    </section>
  );
};

export default ProtectedRoute;
