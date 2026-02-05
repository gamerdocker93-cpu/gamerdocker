import axios from "axios";

window.axios = axios;

// garante que axios chama a mesma origem do SPA (Railway)
window.axios.defaults.baseURL = "/";

// header padrão
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// CSRF correto (Laravel espera o valor do meta "csrf-token")
const csrf = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute("content");

if (csrf) {
  window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrf;
}

// se tiver token salvo, manda em TODAS as requisições (evita cair no /auth/redirect/google)
const token = localStorage.getItem("token");
if (token) {
  window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

/**
 * Echo/Pusher fica opcional (pode ligar depois).
 * Deixa desligado pra não quebrar nada em deploy.
 */
// import Echo from "laravel-echo";
// import Pusher from "pusher-js";
// window.Pusher = Pusher;
// window.Echo = new Echo({...});