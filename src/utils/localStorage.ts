import { IStudent } from "../context/StudentContext";
import { IInstructor } from "../context/AuthInstructor";

export const saveStudentToLocalStorage = (student: IStudent) => {
  localStorage.setItem("student", JSON.stringify(student));
};

export const getStudentFromLocalStorage = (): IStudent | null => {
  const storage = localStorage.getItem("student");
  if (storage) {
    return JSON.parse(storage);
  }
  return null;
};

export const saveInstructorToLocalStorage = (instructor: IInstructor) => {
  localStorage.setItem("instructor", JSON.stringify(instructor));
};

export const getInstructorFromLocalStorage = (): IInstructor | null => {
  const storage = localStorage.getItem("instructor");
  if (storage) {
    return JSON.parse(storage);
  }
  return null;
};
