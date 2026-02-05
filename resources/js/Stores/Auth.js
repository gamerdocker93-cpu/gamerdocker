import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import router from "../Router";
import Echo from "laravel-echo";

function safeJsonParse(value, fallback = null) {
    try {
        if (!value) return fallback;
        return JSON.parse(value);
    } catch (e) {
        return fallback;
    }
}

export const useAuthStore = defineStore("auth", () => {
    const token = ref(localStorage.getItem("token") || "");
    const user = ref(safeJsonParse(localStorage.getItem("user"), null));

    // ✅ nasce autenticado se tiver token
    const isAuth = ref(!!token.value);

    function applyAxiosAuthHeader(tokenValue) {
        try {
            if (tokenValue) {
                axios.defaults.headers.common["Authorization"] = `Bearer ${tokenValue}`;
            } else {
                delete axios.defaults.headers.common["Authorization"];
            }
        } catch (e) {}
    }

    // ✅ aplica no start
    applyAxiosAuthHeader(token.value);

    function setToken(tokenValue) {
        localStorage.setItem("token", tokenValue);
        token.value = tokenValue || "";
        isAuth.value = !!token.value;
        applyAxiosAuthHeader(token.value);
    }

    function getToken() {
        return token?.value;
    }

    function setUser(userValue) {
        if (userValue != null) {
            localStorage.setItem("user", JSON.stringify(userValue));
            user.value = userValue;
        }
    }

    function setIsAuth(auth) {
        isAuth.value = !!auth;
        if (!isAuth.value) {
            applyAxiosAuthHeader("");
        }
    }

    async function checkToken() {
        try {
            if (!token.value) {
                logout();
                return null;
            }

            const { data } = await axios.get("/api/auth/verify", {
                headers: { Authorization: `Bearer ${token.value}` },
            });

            // ✅ se validou, marca auth true
            isAuth.value = true;
            applyAxiosAuthHeader(token.value);

            return data;
        } catch (error) {
            if (error?.response?.status === 401) {
                logout();
                router.push({ name: "home" });
                return null;
            }
            console.log(error?.response || error);
            return null;
        }
    }

    function logout() {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        token.value = "";
        user.value = null;
        isAuth.value = false;
        applyAxiosAuthHeader("");
    }

    function initializingEcho() {
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
                    "X-CSRF-TOKEN": document.head.querySelector('meta[name="csrf-token"]'),
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