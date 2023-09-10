import axios from "axios";

export const instance = axios.create({
  baseURL: "YOUR_API_BASE_URL",
  withCredentials: true, // Send cookies with requests
});

const useAxios = () => {};

export default useAxios;
