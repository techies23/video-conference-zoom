/**
 * Helper to map Moment tokens and native built-in preset constants to Intl options
 */
export function parseMomentFormatToIntl(formatStr) {
  // Normalize layout input context
  if (!formatStr) {
    formatStr = 'LLLL';
  }

  // 1. Resolve Moment's Core Built-in Localized Format Constants
  switch (formatStr) {
    case 'LLLL': // Example: Wednesday, May 6, 2020 05:00 PM
      return {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
      };

    case 'llll': // Example: Wed, May 6, 2020 05:00 AM
      return {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
      };

    case 'lll': // Example: May 6, 2020 05:00 AM
      return {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
      };

    case 'L LT': // Example: 05/06/2020 03:00 PM
      return {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: 'numeric',
        minute: '2-digit'
      };

    case 'l LT': // Example: 5/6/2020 03:00 PM
      return {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
      };
  }

  // 2. Fallback: Parse Custom Layout String Tokens (When 'custom' radio option is chosen)
  const options = {};

  // Explicit Hour Cycle Precision Check (12-hour vs 24-hour markers)
  if (formatStr.includes('H')) {
    options.hour12 = false;
  } else if (formatStr.includes('h') || formatStr.includes('a') || formatStr.includes('A')) {
    options.hour12 = true;
  }

  // Day of Week
  if (formatStr.includes('dddd')) options.weekday = 'long';
  else if (formatStr.includes('ddd')) options.weekday = 'short';

  // Year
  if (formatStr.includes('YYYY')) options.year = 'numeric';
  else if (formatStr.includes('YY')) options.year = '2-digit';

  // Month
  if (formatStr.includes('MMMM')) options.month = 'long';
  else if (formatStr.includes('MMM')) options.month = 'short';
  else if (formatStr.includes('MM')) options.month = '2-digit';
  else if (formatStr.includes('M')) options.month = 'numeric';

  // Day of Month
  if (formatStr.includes('DD')) options.day = '2-digit';
  else if (formatStr.includes('D')) options.day = 'numeric';

  // Hours
  if (formatStr.includes('HH') || formatStr.includes('hh')) options.hour = '2-digit';
  else if (formatStr.includes('H') || formatStr.includes('h')) options.hour = 'numeric';

  // Minutes
  if (formatStr.includes('mm')) options.minute = '2-digit';
  else if (formatStr.includes('m')) options.minute = 'numeric';

  // Seconds
  if (formatStr.includes('ss')) options.second = '2-digit';
  else if (formatStr.includes('s')) options.second = 'numeric';

  return options;
}
