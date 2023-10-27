import axios from "axios";
import { saveStudentToLocalStorage } from "./localStorage";
import { useStudent } from "../context/StudentContext";

const useAxios = () => {
  const { student, setStudent } = useStudent();

  const axiosInstance = axios.create({
    baseURL: "http://localhost:8080",
    headers: { Authorization: `Bearer ${student?.token}` },
  });

  axiosInstance.interceptors.request.use(async (req) => {
    const isExpire = false;

    if (!isExpire) return req;

    const { data } = await axios("http://localhost:8080/refresh-token", {
      withCredentials: true,
    });

    saveStudentToLocalStorage(data);
    setStudent(data);

    req.headers.Authorization = `Bearer ${data.accessToken}`;

    return req;
  });

  return axiosInstance;
};

export default useAxios;
