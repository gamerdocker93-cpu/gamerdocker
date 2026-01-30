import axios from 'axios';
import router from '../Router';
import { useAuthStore } from "@/Stores/Auth.js";

const csrfToken = document.head.querySelector('meta[name="csrf-token"]');

const http_axios = axios.create({
    // ✅ definitivo em produção (mesma origem)
    baseURL: '/api/',
    headers: {
        ...(csrfToken?.content ? { 'X-CSRF-TOKEN': csrfToken.content } : {}),
        "Content-type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json",
    },
    withCredentials: true,
});

// request interceptor
http_axios.interceptors.request.use((request) => {
    const userStore = useAuthStore();

    const token = userStore.getToken?.() || localStorage.getItem('token');
    if (token) {
        request.headers.Authorization = 'Bearer ' + token;
    }

    // reforça CSRF se existir
    if (csrfToken?.content) {
        request.headers['X-CSRF-TOKEN'] = csrfToken.content;
    }

    return request;
});

// response interceptor
http_axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && [401, 403].includes(error.response.status)) {
            router.push({ name: 'login' });
        }
        return Promise.reject(error);
    }
);

export default http_axios;