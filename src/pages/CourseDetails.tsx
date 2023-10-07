import { IoIosArrowBack, IoIosArrowForward } from "react-icons/io";

const CourseDetails = () => {
  const goBack = () => {
    window.history.back();
  };

  const goForward = () => {
    window.history.forward();
  };
  return (
    <section className="">
      <div className="sticky top-0 left-0 z-10 bg-slate-50 p-5 flex justify-between items-center mb-5">
        <div className="flex items-center space-x-5">
          <div className="flex items-center">
            <i
              onClick={goBack}
              className="p-2 hover:bg-slate-200 rounded-full transition-all duration-300"
            >
              <IoIosArrowBack />
            </i>
            <i
              onClick={goForward}
              className="p-2 hover:bg-slate-200 rounded-full transition-all duration-300"
            >
              <IoIosArrowForward />
            </i>
          </div>
          <p className="hidden lg:block text-xs">
            Dashboard / Coruse-page /
            <span className="font-bold"> Course-details</span>
          </p>
        </div>
      </div>
      <div>
        <video src="" className="w-[700px] px-5 mb-5" controls></video>
      </div>

      <div className="px-5 mb-5">
        <h2 className="font-bold text-black text-2xl mb-3">Description</h2>

        <p className="w-[700px] text-sm">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Nisi, at
          fugiat accusantium repudiandae commodi libero numquam sunt?
          Repellendus corrupti fugiat nostrum odit qui eveniet, magnam molestias
          doloribus sint et. Sint! Lorem ipsum dolor sit amet consectetur
          adipisicing elit. Ab, fugiat excepturi? Dignissimos, nam et. Delectus
          sed aspernatur dolore quod ipsum earum velit. Deleniti placeat
          blanditiis optio, quas cupiditate nostrum facere! Lorem ipsum dolor
          sit amet consectetur adipisicing elit. Amet ea perspiciatis laborum
          possimus eum quas et temporibus aperiam ipsa consequatur,
          exercitationem reiciendis quo quis. Laudantium omnis recusandae
          voluptatum inventore quam? Lorem ipsum dolor sit amet consectetur
          adipisicing elit. Nemo aliquid dicta eaque harum consequuntur dolorum
          officiis numquam placeat? Dolorum molestiae eos repudiandae? Ratione
          blanditiis aut maxime molestias magni? Incidunt, aliquid.
        </p>
      </div>
    </section>
  );
};

export default CourseDetails;
