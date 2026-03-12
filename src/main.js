/* eslint-disable camelcase */
__webpack_public_path__ = window.cnwData?.distUrl || '';
/* eslint-enable camelcase */

import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index.js';
import './styles/main.css';

const app = createApp(App);
app.use(router);
app.mount('#cnw-social-bridge-app');
