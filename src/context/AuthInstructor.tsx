import React, { createContext, useContext, useState, ReactNode } from "react";

export interface IInstructor {
  id: number;
  lname: string;
  oname: string;
  username: string;
  user_role: string;
  token: string;
}

type InstructorContextType = {
  instructor: IInstructor | null;
  setInstructor: (intructor: IInstructor | null) => void;
};

const InstructorContext = createContext<InstructorContextType>({
  instructor: null,
  setInstructor: () => {},
});

type InstructorProviderProps = {
  children: ReactNode;
};

const InstructorProvider: React.FC<InstructorProviderProps> = ({
  children,
}) => {
  const [instructor, setInstructor] = useState<IInstructor | null>(null);

  return (
    <InstructorContext.Provider value={{ instructor, setInstructor }}>
      {children}
    </InstructorContext.Provider>
  );
};

export const useInstructor = () => {
  const context = useContext(InstructorContext);
  if (!context) {
    throw new Error("useInstructor must be used within a InstructorProvider");
  }
  return context;
};

export default InstructorProvider;
