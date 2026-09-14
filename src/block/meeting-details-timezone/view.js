/**
 * Dynamic client-side timezone resolution for vczapi/meeting-details-timezone.
 */
function initVczapiTimezoneBlocks() {
  const blocks = document.querySelectorAll( '.vczapi-meeting-detail-timezone[data-vczapi-tz-mode="user"]' );

  if ( ! blocks.length ) {
    return;
  }

  // Get visitor's browser timezone
  let userTimezone = '';
  try {
    userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  } catch ( e ) {
    userTimezone = '';
  }

  if ( ! userTimezone ) {
    return;
  }

  blocks.forEach( ( block ) => {
    const valueEl = block.querySelector( '.vczapi-meeting-detail-item__value' );
    if ( valueEl ) {
      valueEl.textContent = userTimezone;
    }
  } );
}

// Global initialization window hook for third-party AJAX calls
window.vczapiInitTimezones = initVczapiTimezoneBlocks;

if ( document.readyState === 'loading' ) {
  document.addEventListener( 'DOMContentLoaded', initVczapiTimezoneBlocks );
} else {
  initVczapiTimezoneBlocks();
}