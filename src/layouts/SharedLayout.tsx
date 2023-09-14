import { Outlet } from "react-router-dom";
import Navbar from "../components/Navbar";

const SharedLayout = () => {
  return (
    <section>
      <Navbar />
      <Outlet />
    </section>
  );
};

export default SharedLayout;
