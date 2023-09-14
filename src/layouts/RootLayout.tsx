import { Outlet } from "react-router-dom";
import Sidebar from "../components/Sidebar";

const RootLayout = () => {
  return (
    <>
      <section className="flex bg-slate-50 text-slate-500 ">
        <div className="w-[20%] h-screen bg-white">
          <Sidebar />
        </div>
        <div className="w-full">
          <Outlet />
        </div>
      </section>
    </>
  );
};

export default RootLayout;
