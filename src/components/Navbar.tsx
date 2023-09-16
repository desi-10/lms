import { CiSearch } from "react-icons/ci";
import { SlEqualizer } from "react-icons/sl";
import { PiEnvelopeSimple, PiBellSimpleRinging } from "react-icons/pi";

const Navbar = () => {
  return (
    <nav className="border-b">
      <section className="flex justify-between items-center p-5 mx-auto">
        <div className="flex items-center space-x-5 bg-slate-200 p-2 rounded-full">
          <i>
            <CiSearch className="text-xl text-slate-500 ml-1" />
          </i>
          <input
            type="text"
            name=""
            id=""
            className="bg-transparent w-full outline-none text-sm p-1"
            placeholder="Search here..."
          />
          <i>
            <SlEqualizer className=" text-slate-500 mr-1" />
          </i>
        </div>

        <div className="flex items-center space-x-5">
          <i className="p-2 bg-slate-200 rounded-full cursor-pointer hover:bg-slate-300 transition-all duration-300">
            <PiEnvelopeSimple className="text-xl text-slate-500" />
          </i>
          <i className="p-2 bg-slate-200 rounded-full cursor-pointer hover:bg-slate-300 transition-all duration-300">
            <PiBellSimpleRinging className="text-xl text-slate-500 " />
          </i>
          <div className="w-10 h-10 rounded-full flex justify-center items-center border group cursor-pointer">
            <img
              src="/assets/blogging-clipart.svg"
              alt="user"
              className="w-full h-full group-hover:scale-105 transition-all duration-300"
            />
          </div>
        </div>
      </section>
    </nav>
  );
};

export default Navbar;
