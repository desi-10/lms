import { Navigate, Outlet } from "react-router-dom";
import { useUser } from "../context/AuthContext";

const RestrictedRoute = () => {
  const { user } = useUser();
  if (user) {
    return <Navigate to="/home" />;
  }
  return (
    <section>
      <Outlet />
    </section>
  );
};

export default RestrictedRoute;
