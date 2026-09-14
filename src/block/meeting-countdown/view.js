/**
 * Standalone Countdown & State Manager for vczapi/meeting-countdown.
 */
function initVczapiCountdownBlocks() {
  const blocks = document.querySelectorAll( '.vczapi-meeting-countdown[data-vczapi-utc-start]' );

  if ( ! blocks.length ) {
    return;
  }

  blocks.forEach( ( block ) => {
    const startIso = block.dataset.vczapiUtcStart;
    const durationMins = parseInt( block.dataset.vczapiDuration || '60', 10 );
    const startedText = block.dataset.vczapiStartedText;
    const endedText = block.dataset.vczapiEndedText;
    const showDays = block.dataset.vczapiShowDays === '1';
    const keepStartedAllDay = block.dataset.vczapiAllDayStarted === '1';

    const timerEl = block.querySelector( '.vczapi-countdown-timer' );
    const messageEl = block.querySelector( '.vczapi-countdown-status-message' );

    if ( ! startIso || ! timerEl || ! messageEl ) {
      return;
    }

    const startTime = new Date( startIso ).getTime();
    const durationMs = durationMins * 60 * 1000;
    const meetingEndTime = startTime + durationMs;

    function updateCountdown() {
      const now = new Date().getTime();

      // 1. Check if meeting has ended
      let isEnded = false;
      if ( keepStartedAllDay ) {
        const endOfDay = new Date( startTime ).setHours( 23, 59, 59, 999 );
        isEnded = now > endOfDay;
      } else {
        isEnded = now > meetingEndTime;
      }

      if ( isEnded ) {
        timerEl.style.display = 'none';
        messageEl.textContent = endedText;
        messageEl.style.display = 'block';
        return true; // Halt execution loop
      }

      // 2. Check if meeting is in progress
      if ( now >= startTime ) {
        timerEl.style.display = 'none';
        messageEl.textContent = startedText;
        messageEl.style.display = 'block';
        return false;
      }

      // 3. Active countdown
      const diff = startTime - now;

      const days = Math.floor( diff / ( 1000 * 60 * 60 * 24 ) );
      const hours = Math.floor( ( diff % ( 1000 * 60 * 60 * 24 ) ) / ( 1000 * 60 * 60 ) );
      const minutes = Math.floor( ( diff % ( 1000 * 60 * 60 ) ) / ( 1000 * 60 ) );
      const seconds = Math.floor( ( diff % ( 1000 * 60 ) ) / 1000 );

      if ( showDays ) {
        const daysEl = block.querySelector( '.vczapi-countdown-unit--days .vczapi-countdown-value' );
        if ( daysEl ) daysEl.textContent = String( days ).padStart( 2, '0' );
      }

      const hoursEl = block.querySelector( '.vczapi-countdown-unit--hours .vczapi-countdown-value' );
      const minsEl = block.querySelector( '.vczapi-countdown-unit--minutes .vczapi-countdown-value' );
      const secsEl = block.querySelector( '.vczapi-countdown-unit--seconds .vczapi-countdown-value' );

      if ( hoursEl ) hoursEl.textContent = String( hours ).padStart( 2, '0' );
      if ( minsEl ) minsEl.textContent = String( minutes ).padStart( 2, '0' );
      if ( secsEl ) secsEl.textContent = String( seconds ).padStart( 2, '0' );

      timerEl.style.display = 'flex';
      messageEl.style.display = 'none';
      return false;
    }

    const isFinished = updateCountdown();
    if ( ! isFinished ) {
      const interval = setInterval( () => {
        if ( updateCountdown() ) {
          clearInterval( interval );
        }
      }, 1000 );
    }
  } );
}

window.vczapiInitCountdowns = initVczapiCountdownBlocks;

if ( document.readyState === 'loading' ) {
  document.addEventListener( 'DOMContentLoaded', initVczapiCountdownBlocks );
} else {
  initVczapiCountdownBlocks();
}