import axios from "axios";

const api = axios.create({
  baseURL: "http://192.168.1.6:8000/api", 
  withCredentials: true,
});

// every request: attach Authorization header if we have a token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  config.headers.Accept = "application/json";
  return config;
});

export default api;
