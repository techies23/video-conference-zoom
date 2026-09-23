import './styles/app.scss';

import { initUserSync } from './modules/users/userSync';
import { initRecordingsTable } from './modules/recordings';
import { initConnect } from './modules/connect';
import {initUserList} from "./modules/users/users";
import { initMeetingSync } from './modules/sync';

document.addEventListener( 'DOMContentLoaded', () => {
	//Users
	initUserSync();
	initUserList();

	//Recordings
	initRecordingsTable();

	//Import
	initMeetingSync();

	//Settings
	initConnect();
} );
