import axios from "axios";

window.axios = axios;

// mantém same-origin
window.axios.defaults.baseURL = "/";
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// CSRF do meta (não quebra se não existir)
try {
  const csrf = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

  if (csrf) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrf;
  }
} catch (e) {
  // não deixa quebrar o app
}

// Authorization Bearer (não quebra se localStorage falhar)
try {
  const token = window?.localStorage?.getItem("token");
  if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  }
} catch (e) {
  // não deixa quebrar o app
}

/**
 * Echo/Pusher fica desligado por enquanto.
 */