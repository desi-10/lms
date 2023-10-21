import { ChangeEvent, FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";

import { saveStudentToLocalStorage } from "../utils/localStorage";
import { useStudent } from "../context/StudentContext";

const Login = () => {
  const { studentState, studentDispatch } = useStudent();
  const navigate = useNavigate();

  const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = event.target;

    if (name === "index_number") {
      studentDispatch({ type: "INDEX_NUMBER", payload: value });
    } else if (name === "password") {
      studentDispatch({ type: "PASSWORD", payload: value });
    }
  };

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    studentDispatch({ type: "LOADING", payload: "" });
    try {
      const { data } = await axios.post(
        "elms.shsdesk.com/src/api/user",
        studentState.student
      );
      console.log(data);
      saveStudentToLocalStorage(data);
      navigate("/dashboard");
    } catch (error) {
      studentDispatch({ type: "ERROR", payload: "error.message" });
      setTimeout(() => {
        studentDispatch({ type: "NO_ERROR", payload: "" });
      }, 2000);
      console.error(error);
    }
  };

  return (
    <main className="h-screen flex justify-center items-center bg-slate-50 text-slate-500">
      <div className="w-[90%] mx-auto  md:w-[500px] lg:w-[1000px]">
        <h2 className="hidden text-2xl lg:block my-4 text-center font-bold text-black">
          Learning Management System
        </h2>

        {studentState.error && (
          <p className="bg-red-500 text-center py-1 text-white text-sm rounded-lg">
            {studentState.errorMessage || "Error here"}
          </p>
        )}
        <section className="mx-auto grid grid-cols-1 lg:grid-cols-2 justify-center items-center">
          <div className="w-[200px] md:w-[300px] lg:w-full block mx-auto">
            <img src="/assets/blogging-clipart.svg" alt="" />
          </div>

          <form
            onSubmit={handleSubmit}
            action=""
            className="space-y-3 lg:space-y-5 lg:px-10"
          >
            <h2 className="lg:hidden my-4 lg:mb-2 text-center font-bold text-black">
              Learning Management System
            </h2>
            <div className="grid lg:space-y-1">
              <label htmlFor="" className="text-sm ">
                Index number
              </label>
              <input
                type="text"
                name="index_number"
                value={studentState.student.index_number}
                maxLength={10}
                className="p-2 border rounded-lg bg-slate-50 border-slate-300"
                onChange={handleChange}
              />
            </div>
            <div className="grid lg:space-y-1">
              <label htmlFor="" className="text-sm">
                Password
              </label>
              <input
                type="password"
                name="password"
                value={studentState.student.password}
                maxLength={20}
                className="p-2 border rounded-lg bg-slate-50 border-slate-300"
                onChange={handleChange}
              />
            </div>

            <button className="w-full p-2 rounded-lg bg-blue-700 text-white">
              {studentState.loading ? "Loading..." : "Login"}
            </button>

            <div className="flex justify-between items-center">
              <p className="text-sm underline text-blue-700 cursor-pointer">
                Forgetten Password
              </p>
              <p className="py-2 px-5 border border-slate-300 rounded-lg text-sm cursor-pointer">
                I need help
              </p>
            </div>
          </form>
        </section>
      </div>
    </main>
  );
};

export default Login;
