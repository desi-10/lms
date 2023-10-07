import React, { createContext, useContext, useState, ReactNode } from "react";

export interface IStudent {
  id: number;
  index_number: string;
  lname: string;
  oname: string;
  username: string;
  user_role: string;
  token: string;
}

type StudentContextType = {
  student: IStudent | null;
  setStudent: (student: IStudent | null) => void;
};

const StudentContext = createContext<StudentContextType>({
  student: null,
  setStudent: () => {},
});

type StudentProviderProps = {
  children: ReactNode;
};

const StudentProvider: React.FC<StudentProviderProps> = ({ children }) => {
  const [student, setStudent] = useState<IStudent | null>(null);

  return (
    <StudentContext.Provider value={{ student, setStudent }}>
      {children}
    </StudentContext.Provider>
  );
};

export const useStudent = () => {
  const context = useContext(StudentContext);
  if (!context) {
    throw new Error("useStudent must be used within a StudentProvider");
  }
  return context;
};

export default StudentProvider;
