import { CiGrid41, CiStickyNote, CiChat2 } from "react-icons/ci";
import { GoVideo } from "react-icons/go";
import { Link } from "react-router-dom";

const Sidebar = () => {
  return (
    <aside className="border-r-2">
      <section className="w-[80%] mx-auto py-6 flex flex-col justify-between h-screen ">
        <div>
          <h2 className="mb-5 font-bold text-2xl text-blue-700">LMS</h2>

          <section className="space-y-5">
            <div className="">
              <Link
                to="."
                className="flex space-x-3 hover:text-blue-700 hover:font-bold"
              >
                <i>
                  <CiGrid41 className="text-xl" />
                </i>
                <p>Overview</p>
              </Link>
            </div>
            <div>
              <Link
                to="video"
                className="flex space-x-3 hover:text-blue-700 hover:font-bold"
              >
                <i>
                  <GoVideo className="text-xl" />
                </i>
                <p>Video</p>
              </Link>
            </div>
            <div>
              <Link
                to="notes"
                className="flex space-x-3 hover:text-blue-700 hover:font-bold"
              >
                <i>
                  <CiStickyNote className="text-xl" />
                </i>
                <p>Notes</p>
              </Link>
            </div>
            <div>
              <Link
                to="chat"
                className="flex space-x-3 hover:text-blue-700 hover:font-bold"
              >
                <i>
                  <CiChat2 className="text-xl" />
                </i>
                <p>Chat</p>
              </Link>
            </div>
          </section>
        </div>

        <button className="p-2 rounded-lg text-blue-700 border border-blue-700">
          Need help?
        </button>
      </section>
    </aside>
  );
};

export default Sidebar;
