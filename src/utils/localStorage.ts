export interface IUser {
  id: string;
  username: string;
  email: string;
  password: string;
  accessToken: string;
}

export const saveToLocalStorage = (user: IUser) => {
  localStorage.setItem("user", JSON.stringify(user));
};

export const getFromLocalStorage = () => {
  const storage = localStorage.getItem("user");
  if (storage) {
    return JSON.parse(storage);
  }
  return null;
};
