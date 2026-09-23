import { initFlatpicker } from './components/flatpicker';
import initChoices from "./components/choices";
import 'datatables.net';

document.addEventListener('DOMContentLoaded', () => {
    initFlatpicker('.vczapi-datetimepicker');
    initChoices('.vczapi-choices');
})