import './scss/app.scss'

import { initFlatpicker } from './components/flatpicker';
import initChoices from "./components/choices";

document.addEventListener('DOMContentLoaded', () => {
    initFlatpicker('.vczapi-datetimepicker');
    initChoices('.vczapi-choices');
})