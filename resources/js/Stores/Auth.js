import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import router from "../Router";
import Echo from "laravel-echo";

export const useAuthStore = defineStore("auth", () => {
  const token = ref(localStorage.getItem("token") || "");
  const user = ref(() => {
    try {
      return JSON.parse(localStorage.getItem("user")) || null;
    } catch (e) {
      return null;
    }
  })();

  // ✅ IMPORTANTE: se tem token, começa autenticado
  const isAuth = ref(!!token.value);

  function setToken(tokenValue) {
    const v = tokenValue || "";
    localStorage.setItem("token", v);
    token.value = v;

    // mantém o estado coerente
    isAuth.value = !!v;
  }

  function getToken() {
    return token?.value || "";
  }

  function setUser(userValue) {
    if (userValue != null) {
      localStorage.setItem("user", JSON.stringify(userValue));
      user.value = userValue;
    }
  }

  function setIsAuth(auth) {
    isAuth.value = !!auth;
  }

  async function checkToken() {
    try {
      if (!token.value) {
        logout();
        return undefined;
      }

      const tokenAuth = "Bearer " + token.value;

      const { data } = await axios.get("/api/auth/verify", {
        headers: {
          Authorization: tokenAuth,
        },
      });

      // Se o verify retornar user, já salva e garante isAuth
      if (data) {
        setIsAuth(true);
        setUser(data);
      }

      return data;
    } catch (error) {
      const status = error?.response?.status;

      if (status === 401) {
        logout();
        router.push("/");
        return undefined;
      }

      console.log(error?.response || error);
      return undefined;
    }
  }

  function logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    token.value = "";
    user.value = null;
    isAuth.value = false;
  }

  function initializingEcho() {
    const csrfToken =
      document.head
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

    window.EchoPrivate = new Echo({
      broadcaster: "pusher",
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? "mt1",
      wsHost: import.meta.env.VITE_PUSHER_HOST
        ? import.meta.env.VITE_PUSHER_HOST
        : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
      wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
      forceTLS: false,
      enabledTransports: ["ws", "wss"],
      disabledTransports: ["sockjs", "xhr_polling", "xhr_streaming"],
      authEndpoint: `/api/broadcasting/auth`,
      auth: {
        headers: {
          "X-CSRF-TOKEN": csrfToken,
          Authorization: `Bearer ${token.value}`,
        },
      },
    });
  }

  return {
    token,
    user,
    setToken,
    setUser,
    getToken,
    checkToken,
    logout,
    setIsAuth,
    isAuth,
    initializingEcho,
  };
});