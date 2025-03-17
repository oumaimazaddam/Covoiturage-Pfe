import 'primeicons/primeicons.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import Aura from '@primeuix/themes/aura';
import axios from 'axios';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';

import '@/assets/styles.scss';
import 'primeicons/primeicons.css';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
const app = createApp(App);
app.config.globalProperties.$axios = axios;
axios.defaults.withCredentials = true;

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
app.mount('#app');
