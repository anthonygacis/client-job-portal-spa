import {createApp} from 'vue'
import "@tabler/core/dist/js/tabler.min.js";
import "@tabler/core/dist/css/tabler.min.css";
import "@tabler/core/dist/css/tabler-vendors.min.css";
import "@tabler/core/dist/css/demo.min.css";
import App from './App.vue'
import {createPinia} from "pinia";
import {router} from "@/js/router";
// import VueTablerIcons from "vue-tabler-icons"
import Toast from "vue-toastification"
import "vue-toastification/dist/index.css";
import {requestGet} from "@/js/helpers/requests";

const app = createApp(App)
app.use(createPinia())
app.use(router)
// app.use(VueTablerIcons)
app.use(Toast, {
    position: "top-right",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: false,
    showCloseButtonOnHover: true,
    hideProgressBar: true,
    closeButton: "button",
    // icon: "ti ti-360-view",
    rtl: false
})
app.directive("tooltip", {
    mounted(el, binding) {
        new bootstrap.Tooltip(el, {
            title: binding.value,
            placement: "top",
            trigger: "hover",
        });
    },
    beforeUnmount(el) {
        let tooltip = bootstrap.Tooltip.getInstance(el);
        tooltip?.dispose();
    },
});
// check before mount
requestGet('/api/check').catch((e) => {
    if (e.response.status == 401) {
        localStorage.removeItem('auth')
    }
}).finally(() => {
    app.mount('#app')
})
