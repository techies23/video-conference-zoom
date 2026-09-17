/**
 * Native Gutenberg meeting fields panel for the zoom-meetings post type.
 *
 * Reads/writes `vczapi_meeting_fields` via core-data (`useEntityProp`), mirrors
 * the classic metabox schema, and provides real (server-aligned) validation:
 * invalid state locks post saving so the post cannot be published until the
 * meeting details are fixed.
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { sprintf, __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useRef } from '@wordpress/element';
import { useEntityProp } from '@wordpress/core-data';
import {
    CheckboxControl,
    Notice,
    SelectControl,
    TextareaControl,
    TextControl,
} from '@wordpress/components';

const NOTICE_PREFIX = 'vczapi-meeting-';
const MIN_DURATION = 1;
const MAX_DURATION = 1440;

const validateFields = (fields) => {
    const errors = {};

    if (!fields || Object.keys(fields).length === 0) {
        return errors;
    }

    if (!fields.user_id) {
        errors.user_id = __('Meeting Host is required to create a meeting.', 'video-conferencing-with-zoom-api');
    }

    if (![1, 2].includes(parseInt(fields.type, 10))) {
        errors.type = __('Please select a valid Meeting Type.', 'video-conferencing-with-zoom-api');
    }

    if (!fields.start_time || Number.isNaN(Date.parse(fields.start_time))) {
        errors.start_time = __('Start Date/Time is required.', 'video-conferencing-with-zoom-api');
    }

    if (!fields.timezone) {
        errors.timezone = __('Timezone is required.', 'video-conferencing-with-zoom-api');
    }

    const duration = parseInt(fields.duration, 10);
    if (Number.isNaN(duration) || duration < MIN_DURATION || duration > MAX_DURATION) {
        errors.duration = sprintf(
            /* translators: %1$d: min duration, %2$d: max duration */
            __('Meeting duration must be between %1$d and %2$d minutes.', 'video-conferencing-with-zoom-api'),
            MIN_DURATION,
            MAX_DURATION
        );
    }

    if (fields.password && fields.password.length > 10) {
        errors.password = sprintf(
            /* translators: %d: max length */
            __('Password must be a maximum of %d characters.', 'video-conferencing-with-zoom-api'),
            10
        );
    }

    return errors;
};

const MeetingFieldsPanel = () => {
    const { postType, postId, currentPost } = useSelect((select) => {
        const editor = select('core/editor');
        if (!editor) {
            return { postType: '', postId: 0, currentPost: {} };
        }

        return {
            postType: editor.getCurrentPostType(),
            postId: editor.getCurrentPostId(),
            currentPost: editor.getCurrentPost(),
        };
    }, []);

    const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');
    const { createNotice, removeNotice } = useDispatch('core/notices');

    // postId is required; prevent data fetching on unrelated screens.
    const effectivePostId = postId || 0;
    const [meta, setMeta] = useEntityProp('postType', 'zoom-meetings', 'meta', effectivePostId);

    const editorData = window.vczapi_editor || {};
    const fields = (meta && meta.vczapi_meeting_fields) || {};

    const meetingId = currentPost?.vczapi_meeting_id || editorData.meetingId || '';
    const zoomDetails = currentPost?.vczapi_meeting_zoom_details || editorData.zoomDetails || null;
    const hasMeetingId = !!meetingId;
    const isWebinar = parseInt(fields.type, 10) === 2;

    const setField = (key, value) => {
        const nextFields = { ...((meta && meta.vczapi_meeting_fields) || {}), [key]: value };
        setMeta({ ...(meta || {}), vczapi_meeting_fields: nextFields });
    };

    // ---------------------------------------------------------------------
    // Validation state
    // ---------------------------------------------------------------------
    const errors = useMemo(() => validateFields(fields), [meta]);
    const hasErrors = Object.keys(errors).length > 0;
    const prevErrorKeys = useRef([]);

    useEffect(() => {
        const next = Object.keys(errors);

        prevErrorKeys.current.forEach((key) => {
            if (!next.includes(key)) {
                removeNotice(`${NOTICE_PREFIX}${key}`);
            }
        });
        prevErrorKeys.current = next;
    }, [errors]);

    useEffect(() => {
        if (postType !== 'zoom-meetings') {
            return;
        }

        if (hasErrors) {
            lockPostSaving('vczapi_meeting_validation');
            Object.entries(errors).forEach(([key, message]) => {
                createNotice('error', message, { id: `${NOTICE_PREFIX}${key}`, isDismissible: true });
            });
        } else {
            unlockPostSaving('vczapi_meeting_validation');
        }
    }, [hasErrors, errors, postType]);

    if (postType !== 'zoom-meetings') {
        return null;
    }

    // ---------------------------------------------------------------------
    // Options
    // ---------------------------------------------------------------------
    const hostOptions = Object.entries(editorData.hosts || {}).map(([value, label]) => ({
        value,
        label: `${label} (${value})`,
    }));
    const timezoneOptions = Object.entries(editorData.timezones || {}).map(([value, label]) => ({
        value,
        label: `${label}`,
    }));
    const typeOptions = [
        { value: 1, label: __('Meeting', 'video-conferencing-with-zoom-api') },
        { value: 2, label: __('Webinar', 'video-conferencing-with-zoom-api') },
    ];
    const autoRecordingOptions = [
        { value: 'none', label: __('No Recordings', 'video-conferencing-with-zoom-api') },
        { value: 'local', label: __('Local', 'video-conferencing-with-zoom-api') },
        { value: 'cloud', label: __('Cloud', 'video-conferencing-with-zoom-api') },
    ];
    const jbhTimeOptions = [
        { value: 0, label: __('Allow participant to join anytime.', 'video-conferencing-with-zoom-api') },
        { value: 5, label: __('Allow participant to join 5 minutes before start.', 'video-conferencing-with-zoom-api') },
        { value: 10, label: __('Allow participant to join 10 minutes before start.', 'video-conferencing-with-zoom-api') },
        { value: 15, label: __('Allow participant to join 15 minutes before start.', 'video-conferencing-with-zoom-api') },
    ];

    const durationMinutes = (() => {
        const duration = parseInt(fields.duration, 10);
        return Number.isNaN(duration) ? 40 : duration;
    })();
    const durationHours = Math.floor(durationMinutes / 60);
    const durationMinsRemainder = durationMinutes % 60;

    const onDurationHourChange = (hour) => {
        setField('duration', parseInt(hour, 10) * 60 + durationMinsRemainder);
    };
    const onDurationMinuteChange = (minute) => {
        setField('duration', durationHours * 60 + parseInt(minute, 10));
    };

    const startTimeValue = fields.start_time ? String(fields.start_time).slice(0, 16) : '';

    const booleanField = (key, label, help) => (
        <CheckboxControl
            label={label}
            help={help}
            checked={!!fields[key]}
            onChange={(checked) => setField(key, checked ? '1' : null)}
        />
    );

    const hiddenOptions = isWebinar
        ? __('Only Zoom Meetings support these options.', 'video-conferencing-with-zoom-api')
        : null;

    // ---------------------------------------------------------------------
    // Render
    // ---------------------------------------------------------------------
    return (
        <PluginDocumentSettingPanel
            name="vczapi-meeting-fields-panel"
            title={__('Zoom Meeting Details', 'video-conferencing-with-zoom-api')}
            initialOpen={true}
        >
            {hasMeetingId && (
                <Notice status="success" isDismissible={false}>
                    {sprintf(
                        /* translators: %s: Zoom Meeting ID */
                        __('Zoom Meeting ID: %s', 'video-conferencing-with-zoom-api'),
                        meetingId
                    )}
                </Notice>
            )}

            {zoomDetails && zoomDetails.code !== undefined && (
                <Notice status="error" isDismissible={false}>
                    {sprintf(
                        /* translators: %s: Zoom API error message */
                        __('Zoom Error: %s', 'video-conferencing-with-zoom-api'),
                        zoomDetails.message || zoomDetails.code
                    )}
                </Notice>
            )}

            <h3>{__('General Settings', 'video-conferencing-with-zoom-api')}</h3>

            <SelectControl
                label={__('Meeting Host *', 'video-conferencing-with-zoom-api')}
                help={__('This is host ID for the meeting (Required).', 'video-conferencing-with-zoom-api')}
                value={fields.user_id || ''}
                options={hostOptions}
                disabled={hasMeetingId}
                onChange={(value) => setField('user_id', value)}
                __nextHasNoMarginBottom
            />

            <TextareaControl
                label={__('Agenda', 'video-conferencing-with-zoom-api')}
                value={fields.agenda || ''}
                onChange={(value) => setField('agenda', value)}
                rows={2}
            />

            <SelectControl
                label={__('Type *', 'video-conferencing-with-zoom-api')}
                help={__('Type of Event.', 'video-conferencing-with-zoom-api')}
                value={parseInt(fields.type, 10) || ''}
                options={typeOptions}
                disabled={hasMeetingId}
                onChange={(value) => setField('type', parseInt(value, 10))}
                __nextHasNoMarginBottom
            />

            <TextControl
                label={__('Start Date/Time *', 'video-conferencing-with-zoom-api')}
                help={__('Starting Date and Time of the Meeting (Required).', 'video-conferencing-with-zoom-api')}
                type="datetime-local"
                value={startTimeValue}
                onChange={(value) => setField('start_time', value)}
                __nextHasNoMarginBottom
            />

            <SelectControl
                label={__('Timezone *', 'video-conferencing-with-zoom-api')}
                value={fields.timezone || editorData.defaultTimezone || 'UTC'}
                options={timezoneOptions}
                onChange={(value) => setField('timezone', value)}
                __nextHasNoMarginBottom
            />

            <div style={{ display: 'flex', gap: '12px' }}>
                <SelectControl
                    label={__('Hours', 'video-conferencing-with-zoom-api')}
                    value={durationHours}
                    options={Array.from({ length: 25 }, (_, i) => ({ value: i, label: String(i) }))}
                    onChange={onDurationHourChange}
                    style={{ flex: 1 }}
                    __nextHasNoMarginBottom
                />
                <SelectControl
                    label={__('Minutes', 'video-conferencing-with-zoom-api')}
                    value={durationMinsRemainder}
                    options={[0, 5, 10, 15, 20, 30, 40, 45].map((m) => ({ value: m, label: String(m) }))}
                    onChange={onDurationMinuteChange}
                    style={{ flex: 1 }}
                    __nextHasNoMarginBottom
                />
            </div>

            <h3>{__('Security & Room Rules', 'video-conferencing-with-zoom-api')}</h3>

            <TextControl
                label={__('Password', 'video-conferencing-with-zoom-api')}
                help={__('Password to join the meeting. Max 10 characters. (Leave blank to auto generate).', 'video-conferencing-with-zoom-api')}
                value={fields.password || ''}
                onChange={(value) => setField('password', value)}
                maxLength={10}
                __nextHasNoMarginBottom
            />

            {booleanField(
                'disable_waiting_room',
                __('Disable Waiting Room', 'video-conferencing-with-zoom-api'),
                __('Anyone with the link can join without host authorization.', 'video-conferencing-with-zoom-api')
            )}
            {booleanField(
                'meeting_authentication',
                __('Meeting Authentication', 'video-conferencing-with-zoom-api'),
                __('Only logged-in users in Zoom App can join this Meeting.', 'video-conferencing-with-zoom-api')
            )}

            <h3>{__('Advanced Settings', 'video-conferencing-with-zoom-api')}</h3>

            {!isWebinar && booleanField(
                'join_before_host',
                __('Join Before Host', 'video-conferencing-with-zoom-api'),
                __('Allow users to join before host starts/joins.', 'video-conferencing-with-zoom-api')
            )}

            {!isWebinar && (
                <SelectControl
                    label={__('Join Before Host Time', 'video-conferencing-with-zoom-api')}
                    value={parseInt(fields.jbh_time, 10) || 0}
                    options={jbhTimeOptions}
                    onChange={(value) => setField('jbh_time', parseInt(value, 10))}
                    __nextHasNoMarginBottom
                />
            )}

            {booleanField(
                'host_video',
                __('Start Host Video', 'video-conferencing-with-zoom-api'),
                __('Start video when the host joins the meeting.', 'video-conferencing-with-zoom-api')
            )}

            {!isWebinar && booleanField(
                'participant_video',
                __('Start Participant Video', 'video-conferencing-with-zoom-api'),
                __('Start video when participants join meeting.', 'video-conferencing-with-zoom-api')
            )}

            {!isWebinar && booleanField(
                'mute_upon_entry',
                __('Mute Participants upon entry', 'video-conferencing-with-zoom-api'),
                __('Mutes participants when entering the meeting.', 'video-conferencing-with-zoom-api')
            )}

            <SelectControl
                label={__('Auto Recording', 'video-conferencing-with-zoom-api')}
                value={fields.auto_recording || 'none'}
                options={autoRecordingOptions}
                onChange={(value) => setField('auto_recording', value)}
                __nextHasNoMarginBottom
            />

            <SelectControl
                label={__('Alternative Hosts', 'video-conferencing-with-zoom-api')}
                help={__('Paid Zoom Account is required for alternative hosts.', 'video-conferencing-with-zoom-api')}
                value={fields.alternative_hosts || []}
                options={hostOptions}
                multiple
                onChange={(value) => setField('alternative_hosts', value)}
                __nextHasNoMarginBottom
            />

            {isWebinar && hiddenOptions && (
                <p style={{ color: '#8a8a8a', fontStyle: 'italic' }}>{hiddenOptions}</p>
            )}

            <h3>{__('Webinar Specific', 'video-conferencing-with-zoom-api')}</h3>

            {isWebinar && booleanField(
                'panelists_video',
                __('When Panelists Join', 'video-conferencing-with-zoom-api'),
                __('Start video when panelists join webinar.', 'video-conferencing-with-zoom-api')
            )}
            {isWebinar && booleanField(
                'practice_session',
                __('Practice Session', 'video-conferencing-with-zoom-api'),
                __('Enable Practice Session.', 'video-conferencing-with-zoom-api')
            )}
            {isWebinar && booleanField(
                'hd_video',
                __('HD Video', 'video-conferencing-with-zoom-api'),
                __('Defaults to HD video.', 'video-conferencing-with-zoom-api')
            )}
            {isWebinar && booleanField(
                'allow_multiple_devices',
                __('Allow Multiple Devices', 'video-conferencing-with-zoom-api'),
                __('Allow attendees to join from multiple devices.', 'video-conferencing-with-zoom-api')
            )}
        </PluginDocumentSettingPanel>
    );
};

registerPlugin('vczapi-meeting-fields-panel', {
    icon: 'video-alt2',
    render: MeetingFieldsPanel,
});