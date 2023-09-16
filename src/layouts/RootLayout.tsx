import { Outlet } from "react-router-dom";
import Sidebar from "../components/Sidebar";

const RootLayout = () => {
  return (
    <>
      <section className="flex bg-slate-50 text-slate-500">
        <div className="hidden lg:block w-[20%] h-screen bg-white ">
          <Sidebar />
        </div>
        <div className="w-full h-screen overflow-y-scroll">
          <Outlet />
        </div>
      </section>
    </>
  );
};

export default RootLayout;
