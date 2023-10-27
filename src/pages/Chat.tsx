import { CiSearch } from "react-icons/ci";

const Chat = () => {
  return (
    <section className="flex h-screen">
      <aside className="w-[300px] border-r h-[80%] px-5 overflow-y-scroll">
        <section className="flex justify-between items-center mb-5">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 rounded-full bg-white"></div>
            <p>chats</p>
          </div>

          <div className="flex items-center space-x-3">
            <i className="w-10 h-10 rounded-full bg-white"></i>
            <i className="w-10 h-10 rounded-full bg-white"></i>
          </div>
        </section>

        <div className="flex items-center space-x-1 bg-slate-200 p-2 rounded-full">
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
        </div>
      </aside>

      <main>
        <section className="w-full">
          <div className="bg-white w-full">sd</div>
        </section>
      </main>
    </section>
  );
};

export default Chat;
