import './styles/app.scss'

import {initUserSync} from './modules/userSync';
import {initSettingsForm} from './modules/settings';

document.addEventListener('DOMContentLoaded', () => {
    initUserSync();
    initSettingsForm();
});