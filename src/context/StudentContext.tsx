import React, { ReactNode, createContext, useContext, useReducer } from "react";
import {
  IDefaultState,
  Actions,
  studentReducer,
} from "../reducers/loginReducer";

const defaultStudentState: IDefaultState = {
  student: {
    index_number: "",
    password: "",
  },
  loading: false,
  error: false,
  errorMessage: "",
};

export const StudentContextAPI = createContext<{
  studentState: IDefaultState;
  studentDispatch: React.Dispatch<Actions>;
}>({
  studentState: defaultStudentState,
  studentDispatch: () => {},
});

const StudentContext = ({ children }: { children: ReactNode }) => {
  const [studentState, studentDispatch] = useReducer(
    studentReducer,
    defaultStudentState
  );

  return (
    <StudentContextAPI.Provider value={{ studentState, studentDispatch }}>
      {children}
    </StudentContextAPI.Provider>
  );
};

export const useStudent = () => {
  const context = useContext(StudentContextAPI);
  if (!context) {
    throw new Error("useInstructor must be used within a InstructorProvider");
  }
  return context;
};

export default StudentContext;
