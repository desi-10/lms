import { IStudent } from "../context/AuthContext";

export const saveToLocalStorage = (user: IStudent) => {
  localStorage.setItem("user", JSON.stringify(user));
};

export const getFromLocalStorage = (): IStudent | null => {
  const storage = localStorage.getItem("user");
  if (storage) {
    return JSON.parse(storage);
  }
  return null;
};
