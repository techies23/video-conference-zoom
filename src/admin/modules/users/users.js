import { initDataTable } from '../../components/datatable.js';

export const initUserList = () => {
	const dtEl = document.querySelector( '.vczapi-users-table' );
	const modalEl = document.getElementById( 'vczapi-link-user-modal' );

	if ( ! dtEl || ! modalEl ) {
		return;
	}

	const { ajaxurl, nonce } = window.vczapi_ajax || {};
	const apiEndpoint = ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php';

	// Cached DOM Elements
	const searchInput = document.getElementById( 'vczapi-modal-wp-user-search' );
	const resultsEl = document.getElementById( 'vczapi-modal-wp-user-results' );
	const zoomIdInput = document.getElementById( 'vczapi-modal-zoom-id' );
	const wpUserIdInput = document.getElementById( 'vczapi-modal-wp-user-id' );
	const feedbackEl = document.getElementById( 'vczapi-modal-feedback' );
	const selectionEl = document.getElementById( 'vczapi-modal-wp-user-selection' );

	// Internal State
	let resultItems = [];
	let activeIndex = -1;
	let isSaving = false;

	// Helper: Escape HTML string
	const escapeHtml = ( str ) =>
		String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );

	// Helper: Debounce function execution
	const debounce = ( fn, delay = 300 ) => {
		let timeoutId;
		return ( ...args ) => {
			clearTimeout( timeoutId );
			timeoutId = setTimeout( () => fn( ...args ), delay );
		};
	};

	// Display error or success feedback
	const showFeedback = ( message, type = 'success' ) => {
		if ( ! feedbackEl ) return;
		feedbackEl.style.display = 'block';
		feedbackEl.className = `vczapi-modal__feedback ${ type === 'error' ? 'is-error' : 'is-success' }`;
		feedbackEl.textContent = message;
	};

	const hideResults = () => {
		if ( resultsEl ) {
			resultsEl.hidden = true;
			resultsEl.innerHTML = '';
		}
		resultItems = [];
		activeIndex = -1;
	};

	const renderSelection = ( wpUserId ) => {
		if ( ! selectionEl ) return;

		selectionEl.textContent = '';
		if ( wpUserId ) {
			const label = document.createElement( 'span' );
			label.textContent = `Linked WordPress User ID: ${ wpUserId }`;

			const clearBtn = document.createElement( 'button' );
			clearBtn.type = 'button';
			clearBtn.className = 'button-link vczapi-combobox__clear';
			clearBtn.dataset.vczapiClear = 'wp-user';
			clearBtn.textContent = 'Remove link';

			selectionEl.append( label, ' ', clearBtn );
			selectionEl.hidden = false;
		} else {
			selectionEl.hidden = true;
		}
	};

	const resetModalState = () => {
		isSaving = false;
		hideResults();

		if ( feedbackEl ) {
			feedbackEl.style.display = 'none';
			feedbackEl.className = 'vczapi-modal__feedback';
			feedbackEl.textContent = '';
		}

		if ( searchInput ) {
			searchInput.value = '';
			searchInput.disabled = false;
		}

		if ( zoomIdInput ) zoomIdInput.value = '';
		if ( wpUserIdInput ) wpUserIdInput.value = '';

		renderSelection( '' );
	};

	const closeModal = () => {
		modalEl.classList.remove( 'is-active' );
		resetModalState();
	};

	// ------------------------------------------------------------------
	// Auto-Save API Request Logic
	// ------------------------------------------------------------------
	const saveLink = async ( targetWpUserId, actionType = 'save' ) => {
		const zoomUserId = zoomIdInput?.value.trim();
		if ( ! zoomUserId || isSaving ) return;

		isSaving = true;
		showFeedback( 'Saving changes...', 'success' );

		if ( searchInput ) searchInput.disabled = true;

		try {
			const body = new URLSearchParams( {
				action: 'vczapi_link_zoom_user',
				security: nonce,
				zoom_user_id: zoomUserId,
				wp_user_id: targetWpUserId || '',
				type: actionType,
			} );

			const response = await fetch( apiEndpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString(),
			} );

			const data = await response.json();

			if ( data?.success ) {
				const isRemoved = actionType === 'remove';
				const newWpId = isRemoved ? '' : targetWpUserId;

				if ( wpUserIdInput ) wpUserIdInput.value = newWpId;
				renderSelection( newWpId );
				showFeedback( isRemoved ? 'User unlinked successfully.' : 'User linked successfully.' );

				window.setTimeout( () => {
					closeModal();
					if ( dtInstance && typeof dtInstance.reload === 'function' ) {
						dtInstance.reload();
					}
				}, 500 );
			} else {
				showFeedback( data?.data?.message || 'Unable to update the user link.', 'error' );
				if ( searchInput && actionType === 'remove' ) searchInput.disabled = false;
			}
		} catch {
			showFeedback( 'Network error. Unable to update the link.', 'error' );
			if ( searchInput && actionType === 'remove' ) searchInput.disabled = false;
		} finally {
			isSaving = false;
		}
	};

	// ------------------------------------------------------------------
	// Combobox Search Logic
	// ------------------------------------------------------------------
	const renderResults = ( items ) => {
		resultItems = items;
		activeIndex = -1;

		if ( ! resultsEl ) return;

		if ( ! items.length ) {
			resultsEl.innerHTML = '<li class="is-empty">No matching users found.</li>';
		} else {
			resultsEl.innerHTML = items
				.map( ( user, i ) => `<li data-index="${ i }">${ escapeHtml( user.text ) }</li>` )
				.join( '' );
		}

		resultsEl.hidden = false;
	};

	const highlightActive = () => {
		if ( ! resultsEl ) return;
		Array.from( resultsEl.children ).forEach( ( li, i ) => {
			li.classList.toggle( 'is-selected', i === activeIndex );
		} );
	};

	const selectUserAndSave = ( user ) => {
		if ( searchInput ) {
			searchInput.value = '';
		}
		hideResults();
		saveLink( String( user.id ), 'save' );
	};

	const searchUsers = debounce( async () => {
		const q = searchInput?.value.trim();
		if ( ! q ) {
			hideResults();
			return;
		}

		try {
			const body = new URLSearchParams( {
				action: 'vczapi_search_wp_users',
				security: nonce,
				q,
			} );

			const response = await fetch( apiEndpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString(),
			} );

			const data = await response.json();
			renderResults( data?.success ? data.data.results || [] : [] );
		} catch {
			renderResults( [] );
		}
	}, 300 );

	if ( searchInput && resultsEl ) {
		searchInput.addEventListener( 'input', searchUsers );
		searchInput.addEventListener( 'focus', () => {
			if ( searchInput.value.trim() ) searchUsers();
		} );

		searchInput.addEventListener( 'keydown', ( e ) => {
			if ( resultsEl.hidden || ! resultItems.length ) return;

			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				activeIndex = ( activeIndex + 1 ) % resultItems.length;
				highlightActive();
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				activeIndex = ( activeIndex - 1 + resultItems.length ) % resultItems.length;
				highlightActive();
			} else if ( e.key === 'Enter' ) {
				e.preventDefault();
				const item = resultItems[ activeIndex >= 0 ? activeIndex : 0 ];
				if ( item ) selectUserAndSave( item );
			}
		} );

		resultsEl.addEventListener( 'click', ( e ) => {
			const li = e.target.closest( 'li[data-index]' );
			if ( ! li ) return;
			const item = resultItems[ parseInt( li.dataset.index, 10 ) ];
			if ( item ) selectUserAndSave( item );
		} );

		// Clear/Remove link handler
		document.addEventListener( 'click', ( e ) => {
			if ( ! e.target.closest( '[data-vczapi-clear="wp-user"]' ) ) return;

			// Fetch the currently assigned WP user ID before resetting
			const currentWpUserId = wpUserIdInput?.value.trim();
			saveLink( currentWpUserId, 'remove' );
		} );
	}

	// ------------------------------------------------------------------
	// Data Table Initialization & Action Delegation
	// ------------------------------------------------------------------
	const [ dtInstance ] = initDataTable( dtEl, {
		apiEndpoint,
		apiAction: 'vczapi_get_user_list',
		nonce,
		columns: [
			{ field: 'id', label: 'ID', sortable: false },
			{ field: 'first_name', label: 'First Name', sortable: true },
			{ field: 'last_name', label: 'Last Name', sortable: false },
			{ field: 'email', label: 'Email', sortable: true },
			{
				field: 'wp_user_id',
				label: 'WP User',
				sortable: false,
				render: ( value, row ) => {
					const label = value ? `Change User ID: ${ value }` : 'Link User';
					const zoomId = row.id || row.zoom_user_id || '';
					return `<a 
                       href="javascript:void(0);"
                       class="vczapi-users-table-link-user-btn" 
                       data-zoom-id="${ escapeHtml( zoomId ) }" 
                       data-wp-id="${ escapeHtml( value || '' ) }">
                       ${ escapeHtml( label ) }
                   </a>`;
				},
			},
			{
				field: 'status',
				label: 'Status',
				render: ( value ) => `<span class="badge ${ escapeHtml( value ) }">${ escapeHtml( value ) }</span>`,
			},
		],
		transformResults: ( response ) => ( {
			items: response.data?.users || [],
			total: response.data?.total_count || 0,
		} ),
	} );

	// Open Modal Event Handler
	dtEl.addEventListener( 'click', ( ev ) => {
		const btn = ev.target.closest( '.vczapi-users-table-link-user-btn' );
		if ( ! btn ) return;

		const zoomId = btn.dataset.zoomId || '';
		const wpUserId = btn.dataset.wpId || '';

		resetModalState();

		if ( zoomIdInput ) zoomIdInput.value = zoomId;
		if ( wpUserIdInput ) wpUserIdInput.value = wpUserId;

		if ( wpUserId && searchInput ) {
			searchInput.disabled = true;
			renderSelection( wpUserId );
		}

		modalEl.classList.add( 'is-active' );
		window.setTimeout( () => searchInput?.focus(), 50 );
	} );

	// ------------------------------------------------------------------
	// Modal Dismiss Controls
	// ------------------------------------------------------------------
	modalEl.querySelectorAll( '[data-vczapi-dismiss="modal"]' ).forEach( ( el ) => {
		el.addEventListener( 'click', closeModal );
	} );

	modalEl.addEventListener( 'click', ( e ) => {
		if ( e.target === modalEl ) closeModal();
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && modalEl.classList.contains( 'is-active' ) ) {
			closeModal();
		}
	} );
};