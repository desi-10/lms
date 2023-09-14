import { Outlet } from "react-router-dom";
import Navbar from "../components/Navbar";
import SNavbar from "../components/SNavbar";

const SharedLayout = () => {
  return (
    <section>
      <section className="hidden lg:block">
        <Navbar />
      </section>
      <section className="lg:hidden">
        <SNavbar />
      </section>
      <section>
        <Outlet />
      </section>
    </section>
  );
};

export default SharedLayout;
