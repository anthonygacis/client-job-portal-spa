import {createApp} from 'vue'
import App from './Landing.vue'
import {createPinia} from "pinia";
import "vue-toastification/dist/index.css";
import "@/js-landing/assets/ionicons/css/ionicons.min.css"
import "bootstrap/dist/css/bootstrap.min.css"
import "@/js-landing/assets/css/stisla.css"
// import "@/js-landing/assets/js/jquery.min"
import "bootstrap/dist/js/bootstrap.min"
// import "@/js-landing/assets/js/jquery.easeScroll"
// import "@/js-landing/assets/sweetalert/dist/sweetalert.min"
// import "@/js-landing/assets/js/stisla"

const app = createApp(App)
app.use(createPinia())
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
app.mount('#app')
