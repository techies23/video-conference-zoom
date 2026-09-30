import { formatInTimeZone } from 'date-fns-tz'
import { ar } from 'date-fns/locale/ar'
import { de } from 'date-fns/locale/de'
import { enUS } from 'date-fns/locale/en-US'
import { es } from 'date-fns/locale/es'
import { fr } from 'date-fns/locale/fr'
import { it } from 'date-fns/locale/it'
import { ja } from 'date-fns/locale/ja'
import { ko } from 'date-fns/locale/ko'
import { nl } from 'date-fns/locale/nl'
import { pl } from 'date-fns/locale/pl'
import { pt } from 'date-fns/locale/pt'
import { ru } from 'date-fns/locale/ru'
import { tr } from 'date-fns/locale/tr'
import { zhCN } from 'date-fns/locale/zh-CN'

/**
 * Meeting time helpers shared by the countdown timers.
 *
 * Zoom returns `start_time` as a UTC instant plus a separate IANA `timezone`
 * for the meeting. The previous implementation handed that pair to
 * `moment.tz(start_time, timezone)`, which reinterprets the naive wall-clock
 * portion as meeting-local time. These helpers keep that same interpretation so
 * existing installations render identically after the move to date-fns.
 */

/**
 * The browser's IANA timezone.
 *
 * date-fns-tz v3 dropped `guessTimeZone()`, so fall back to Intl when the
 * browser cannot report one.
 *
 * @return {string} IANA timezone identifier.
 */
export function guessTimeZone() {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'
    } catch (error) {
        return 'UTC'
    }
}

/**
 * Normalise the legacy spelling of a timezone.
 *
 * `Asia/Katmandu` is the pre-2015 name for the zone now called
 * `Asia/Kathmandu`. Both aliases still resolve, but the Intl database
 * canonicalises to the newer name, so return whichever the runtime accepts.
 *
 * @param {string} timeZone IANA timezone identifier.
 * @return {string} A timezone the runtime recognises.
 */
export function normalizeTimeZone(timeZone) {
    if (timeZone !== 'Asia/Katmandu') {
        return timeZone
    }

    try {
        new Intl.DateTimeFormat('en-US', { timeZone }).format()
        return 'Asia/Katmandu'
    } catch (error) {
        return 'Asia/Kathmandu'
    }
}

/**
 * Resolve a Zoom `start_time` to the instant it refers to.
 *
 * Zoom reports `start_time` as a UTC instant and returns the meeting timezone
 * separately, which is what Helpers\Date::dateConverter() assumes on the PHP
 * side: it parses the string and then converts for display. The countdown must
 * do the same and keep the instant untouched, otherwise every meeting would be
 * offset by its own timezone (4 hours for America/New_York, 5:45 for
 * Asia/Kathmandu).
 *
 * @param {string|Date} valueDate Zoom `start_time`.
 * @return {Date|null} The instant, or null when the input is unparseable.
 */
export function resolveMeetingInstant(valueDate) {
    if (valueDate instanceof Date) {
        return isNaN(valueDate.getTime()) ? null : valueDate
    }

    if (!valueDate) {
        return null
    }

    const instant = new Date(valueDate)

    return isNaN(instant.getTime()) ? null : instant
}

/**
 * Render an instant in the visitor's timezone.
 *
 * @param {Date}   instant       Instant to render.
 * @param {string} formatPattern date-fns pattern, eg `PPPPpp`.
 * @param {string} [locale]      BCP-47 locale, defaults to the document language.
 * @return {string} Formatted date, or an empty string when formatting fails.
 */
export function formatForUser(instant, formatPattern, locale) {
    if (!instant) {
        return ''
    }

    try {
        return formatInTimeZone(instant, guessTimeZone(), formatPattern || 'PPPPpp', {
            locale: resolveLocale(locale)
        })
    } catch (error) {
        return ''
    }
}

/**
 * Locales that ship a date-fns locale, keyed by base language.
 *
 * date-fns only bundles locales that are imported, and the plugin's translation
 * set is larger than the locales date-fns publishes, so unknown languages fall
 * back to the default rather than throwing.
 */
const LOCALES = {
    ar: ar,
    de: de,
    en: enUS,
    es: es,
    fr: fr,
    it: it,
    ja: ja,
    ko: ko,
    nl: nl,
    pl: pl,
    pt: pt,
    ru: ru,
    tr: tr,
    zh: zhCN
};

/**
 * Map a BCP-47 language tag onto a date-fns locale.
 *
 * @param {string} [locale] BCP-47 language tag.
 * @return {Object|undefined} date-fns locale object, or undefined for the default.
 */
function resolveLocale(locale) {
    if (!locale) {
        return undefined
    }

    return LOCALES[String(locale).toLowerCase().split('-')[0]] || undefined
}

/**
 * Seconds remaining until the meeting starts.
 *
 * @param {Date|null} instant Meeting instant.
 * @return {number} Whole seconds, negative once the meeting has started.
 */
export function secondsUntil(instant) {
    if (!instant) {
        return 0
    }

    return Math.floor((instant.getTime() - Date.now()) / 1000)
}
