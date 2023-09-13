import axios from "axios";
import { useUser } from "../context/AuthContext";
import { saveToLocalStorage } from "./localStorage";

const useAxios = () => {
  const { user, setUser } = useUser();

  const axiosInstance = axios.create({
    baseURL: "http://localhost:8080",
    headers: { Authorization: `Bearer ${user?.accessToken}` },
  });

  axiosInstance.interceptors.request.use(async (req) => {
    const isExpire = false;

    if (!isExpire) return req;

    const { data } = await axios("http://localhost:8080/refresh-token", {
      withCredentials: true,
    });

    saveToLocalStorage(data);
    setUser(data);

    req.headers.Authorization = `Bearer ${data.accessToken}`;

    return req;
  });

  return axiosInstance;
};

export default useAxios;
