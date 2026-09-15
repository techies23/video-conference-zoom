/**
 * Native Intl formatting option presets.
 */
const VCZAPI_FORMAT_PRESETS = {
  full: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true },
  long: { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true },
  medium: { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true },
  short: { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true },
  date_only: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
  time_only: { hour: 'numeric', minute: '2-digit', hour12: true },
};

function initVczapiStartTimeBlocks() {
  const blocks = document.querySelectorAll( '.vczapi-meeting-detail-start-time[data-vczapi-utc]' );

  if ( ! blocks.length ) {
    return;
  }

  blocks.forEach( ( block ) => {
    const utcString = block.dataset.vczapiUtc;
    const meetingTz = block.dataset.vczapiMeetingTz || 'UTC';
    const mode = block.dataset.vczapiTzMode || 'user';
    const styleKey = block.dataset.vczapiStyle || 'full';
    const valueEl = block.querySelector( '.vczapi-meeting-detail-item__value' );

    if ( ! utcString || ! valueEl ) {
      return;
    }

    try {
      const dateObj = new Date( utcString );
      const targetTz = mode === 'user'
        ? Intl.DateTimeFormat().resolvedOptions().timeZone
        : meetingTz;

      const formatOptions = {
        ...( VCZAPI_FORMAT_PRESETS[ styleKey ] || VCZAPI_FORMAT_PRESETS.full ),
        timeZone: targetTz,
      };

      const formatter = new Intl.DateTimeFormat( undefined, formatOptions );
      valueEl.textContent = formatter.format( dateObj );
    } catch ( e ) {
      // Retain fallback HTML if date parsing fails
    }
  } );
}

window.vczapiInitStartTimes = initVczapiStartTimeBlocks;

if ( document.readyState === 'loading' ) {
  document.addEventListener( 'DOMContentLoaded', initVczapiStartTimeBlocks );
} else {
  initVczapiStartTimeBlocks();
}