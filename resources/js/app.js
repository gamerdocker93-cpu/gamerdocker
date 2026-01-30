import './bootstrap';

import { createApp, markRaw } from 'vue';
import { createPinia } from 'pinia';
import { i18nVue } from 'laravel-vue-i18n';
import { vMaska } from "maska";

/**
 * LIBS
 */
import moment from 'moment';
import "vue-toastification/dist/index.css";
import '@/index.css';

import App from './App.vue';
import { useAuthStore } from "@/Stores/Auth.js";

/**
 * APP
 */
const app = createApp(App);

app.config.globalProperties.state = {
  platformName() {
    return 'VIPERPRO';
  },
  dateFormatServer(date) {
    const currentDate = new Date(date);
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
  },
  formatDate(dateTimeString) {
    const date = new Date(dateTimeString);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dayAfterTomorrow = new Date(today);
    dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);

    const options = { hour: '2-digit', minute: '2-digit' };

    if (date.toDateString() === today.toDateString()) {
      return `Hoje às ${date.toLocaleTimeString(document.documentElement.getAttribute("lang"), options)}`;
    } else if (date.toDateString() === tomorrow.toDateString()) {
      return `Amanhã às ${date.toLocaleTimeString(document.documentElement.getAttribute("lang"), options)}`;
    } else if (date.toDateString() === dayAfterTomorrow.toDateString()) {
      const dayOfWeek = new Intl.DateTimeFormat(document.documentElement.getAttribute("lang"), { weekday: 'long' }).format(date);
      return `Na ${dayOfWeek} às ${date.toLocaleTimeString(document.documentElement.getAttribute("lang"), options)}`;
    } else {
      return `${date.toLocaleDateString(document.documentElement.getAttribute("lang"))} às ${date.toLocaleTimeString(document.documentElement.getAttribute("lang"), options)}`;
    }
  },
  generateSlug(text) {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-') // espaços -> hífen
      .replace(/[^\w\-]+/g, '') // remove especiais
      .replace(/\-\-+/g, '-') // múltiplos hífens -> 1
      .replace(/^-+/, '') // remove hífen do início
      .replace(/-+$/, ''); // remove hífen do final
  },
  timeAgo(value) {
    return moment(value).fromNow();
  },
  currencyFormat(value, currency) {
    if (value === undefined || currency === undefined) {
      currency = 'USD';
    }

    const options = {
      style: 'currency',
      currency: currency
    };

    return value.toLocaleString(document.documentElement.getAttribute("lang"), options);
  },
  currencyUSD(value) {
    if (typeof value === 'number') {
      return new Intl.NumberFormat('en-US', {
        currency: 'USD',
        minimumFractionDigits: 2,
      }).format(value);
    } else {
      return new Intl.NumberFormat('en-US', {
        currency: 'USD',
        minimumFractionDigits: 2,
      }).format(parseFloat(value));
    }
  },
  limitCharacters(value, limite) {
    if (!value) return '';
    if (value.length <= limite) return value;
    return value.slice(0, limite) + '...';
  },
};

/**
 * Axios
 */
import axios from 'axios'
import VueAxios from 'vue-axios'

app.use(VueAxios, axios)
app.provide('axios', axios)

/**
 * Fullscreen
 */
import VueFullscreen from 'vue-fullscreen'
app.use(VueFullscreen)

/**
 * Toast
 */
import Toast from "vue-toastification";
const optionsToast = {}
app.use(Toast, optionsToast)

/**
 * Router
 */
import router from './Router';
app.use(router);

/**
 * Directive
 */
app.directive("maska", vMaska)

/**
 * ✅ PINIA (MOVIDO PRA CIMA)
 * Precisa estar ativo ANTES de usar qualquer Store.
 */
const pinia = createPinia()
pinia.use(({ store }) => { store.router = markRaw(router) })
app.use(pinia);

/**
 * i18n
 */
app.use(i18nVue, {
  resolve: async lang => {
    const langs = import.meta.glob('../../lang/*.json');
    return await langs[`../../lang/${lang}.json`]();
  }
});

/**
 * ✅ MOUNT (mantido)
 * Se existir #app, monta nele. Senão tenta #viperpro.
 */
const mountEl =
  document.getElementById('app') ||
  document.getElementById('viperpro');

if (!mountEl) {
  console.error('[Vue] Não achei #app nem #viperpro no HTML. Vue não vai montar.');
} else {
  app.mount(mountEl);
}

/**
 * Load settings (AGORA DEPOIS do Pinia estar ativo)
 */
import { useSettingStore } from "@/Stores/SettingStore.js";

(async () => {
  const setting = useSettingStore();
  try {
    const settingData = await setting.getSetting();
    setting.setSetting(settingData);
  } catch (e) {
    // opcional: console.error(e)
  }
})()

/**
 * Auth (AGORA DEPOIS do Pinia estar ativo)
 */
if (localStorage.getItem('token')) {
  (async () => {
    const auth = useAuthStore();
    try {
      auth.setIsAuth(true);
      const user = await auth.checkToken();
      if (user !== undefined) {
        auth.setUser(user);
      }
      // auth.initializingEcho();
    } catch (e) {
      auth.setIsAuth(false);
    }
  })()
}