import  '@/service/echo';
import 'primeicons/primeicons.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createPinia } from 'pinia';
import Aura from '@primeuix/themes/aura';
import axios from 'axios';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import '@/assets/styles.scss';
import 'primeicons/primeicons.css';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import DataTable from 'primevue/datatable';

const app = createApp(App);
app.use(Toast, {
    position: 'top-right',
    timeout: 5000,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    transition: 'Vue-Toastification__bounce',
    maxToasts: 20,
    newestOnTop: true,
});

app.config.globalProperties.$axios = axios;
axios.defaults.withCredentials = true;
app.use(createPinia());

app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.app-dark'
        }
    }
});
app.use(ToastService);
app.use(ConfirmationService);
app.component('Dialog', Dialog);
app.component('Button', Button);
app.component('DataTable', DataTable);

app.mount('#app');
