// resources/js/Services/HttpApi.js
import axios from "axios";
import router from "../Router";
import { useAuthStore } from "@/Stores/Auth.js";

const csrfToken = document.head.querySelector('meta[name="csrf-token"]');

const http_axios = axios.create({
  // ✅ definitivo: mesma origem, sem depender de env, sem CORS, sem dor no futuro
  baseURL: "/api/",
  headers: {
    "X-Requested-With": "XMLHttpRequest",
    "Accept": "application/json",
    "Content-Type": "application/json",
    ...(csrfToken?.content ? { "X-CSRF-TOKEN": csrfToken.content } : {})
  },
  // se você usa sessão/cookies em algum endpoint, pode manter true
  // se não usa, pode deixar false. Vou deixar true pra máxima compatibilidade.
  withCredentials: true,
});

http_axios.interceptors.request.use((request) => {
  const userStore = useAuthStore();

  const token = userStore.getToken?.() || localStorage.getItem("token");
  if (token) {
    request.headers.Authorization = "Bearer " + token;
  }

  // reforça CSRF caso a meta exista
  if (csrfToken?.content) {
    request.headers["X-CSRF-TOKEN"] = csrfToken.content;
  }

  return request;
});

http_axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response && [401, 403].includes(error.response.status)) {
      // ✅ mais correto no Vue Router 4
      router.push({ name: "login" });
    }
    return Promise.reject(error);
  }
);

export default http_axios;