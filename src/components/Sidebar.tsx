const Sidebar = () => {
  return (
    <aside>
      <section className="w-[80%] mx-auto py-6 flex flex-col justify-between h-screen">
        <div>
          <p className="mb-5">Logo</p>

          <section className="space-y-3">
            <div className="flex space-x-3">
              <p>///</p>
              <p>Overview</p>
            </div>
            <div className="flex space-x-3">
              <p>///</p>
              <p>Video</p>
            </div>
            <div className="flex space-x-3">
              <p>///</p>
              <p>Notes</p>
            </div>
            <div className="flex space-x-3">
              <p>///</p>
              <p>Chat</p>
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
