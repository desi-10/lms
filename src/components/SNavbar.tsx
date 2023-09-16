import { CiSearch } from "react-icons/ci";
import { PiBellSimpleRinging, PiEnvelopeSimple } from "react-icons/pi";

const SNavbar = () => {
  return (
    <nav className="border-b">
      <section className="flex justify-between items-center p-5 mx-auto">
        <div>
          <i>///</i>
        </div>
        <div className="flex items-center space-x-3">
          <i className="p-2 bg-slate-200 rounded-full">
            <CiSearch className="text-xl text-slate-500" />
          </i>
          <i className="p-2 bg-slate-200 rounded-full">
            <PiEnvelopeSimple className="text-xl text-slate-500" />
          </i>
          <i className="p-2 bg-slate-200 rounded-full">
            <PiBellSimpleRinging className="text-xl text-slate-500" />
          </i>
        </div>
      </section>
    </nav>
  );
};

export default SNavbar;
