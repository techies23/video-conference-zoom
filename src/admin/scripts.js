import './styles/app.scss'

import {initUserSync} from './modules/userSync';
import {initSettingsForm} from './modules/settings';
import {initRecordingsTable} from "./modules/recordings";

document.addEventListener('DOMContentLoaded', () => {
    initUserSync();
    initSettingsForm();
    initRecordingsTable();
});