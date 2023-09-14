import { Navigate, Outlet } from "react-router-dom";
import { useUser } from "../context/AuthContext";

const ProtectedRoute = () => {
  const { user } = useUser();
  console.log(user);
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
