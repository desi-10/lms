import { Link } from "react-router-dom";

const Home = () => {
  return (
    <section className="p-5 mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center">
      <div className="group w-[90%] lg:w-52 mx-auto lg:mx-0 border rounded-lg border-slate-300 cursor-pointer">
        <Link to="coursepage">
          <div className="">
            <img
              src="/assets/blogging-clipart.svg"
              className="group-hover:scale-105 transition-all duration-300"
              alt=""
            />
          </div>
          <h3 className=" text-black lg:w-52 truncate font-bold py-2 px-5 group-hover:text-blue-700">
            Database Management System <br />
            <span className="font-normal text-slate-500 text-sm">DBMS</span>
          </h3>
        </Link>
      </div>
    </section>
  );
};

export default Home;
