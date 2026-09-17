import './styles/app.scss';

import { initUserSync } from './modules/userSync';
import { initRecordingsTable } from './modules/recordings';
import { initConnect } from './modules/connect';

document.addEventListener( 'DOMContentLoaded', () => {
	initUserSync();
	initRecordingsTable();
	initConnect();
} );
