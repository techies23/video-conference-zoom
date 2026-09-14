import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
  const {
    sourceType,
    selectedMeetingPostId,
    customMeetingId,
    layoutPreset,
    startedText,
    endedText,
    showDays,
    showSeconds,
    keepStartedAllDay
  } = attributes;

  const blockProps = useBlockProps( {
    className: `vczapi-meeting-countdown vczapi-meeting-countdown--${ layoutPreset }`,
  } );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Meeting Source', 'video-conferencing-with-zoom-api' ) }>
          <SelectControl
            label={ __( 'Source', 'video-conferencing-with-zoom-api' ) }
            value={ sourceType }
            options={ [
              { label: __( 'Current Post / Page', 'video-conferencing-with-zoom-api' ), value: 'current' },
              { label: __( 'Select Meeting Post', 'video-conferencing-with-zoom-api' ), value: 'post_type' },
              { label: __( 'Custom Meeting ID', 'video-conferencing-with-zoom-api' ), value: 'custom' },
            ] }
            onChange={ ( val ) => setAttributes( { sourceType: val } ) }
          />
          { sourceType === 'custom' && (
            <TextControl
              label={ __( 'Zoom Meeting ID', 'video-conferencing-with-zoom-api' ) }
              value={ customMeetingId }
              onChange={ ( val ) => setAttributes( { customMeetingId: val } ) }
              placeholder="e.g. 123456789"
            />
          ) }
        </PanelBody>

        <PanelBody title={ __( 'Countdown Settings', 'video-conferencing-with-zoom-api' ) }>
          <SelectControl
            label={ __( 'Design Preset', 'video-conferencing-with-zoom-api' ) }
            value={ layoutPreset }
            options={ [
              { label: __( 'Cards (Dark Tiles)', 'video-conferencing-with-zoom-api' ), value: 'cards' },
              { label: __( 'Minimal (Bordered Tiles)', 'video-conferencing-with-zoom-api' ), value: 'minimal' },
              { label: __( 'Minimal Flat (Dividers)', 'video-conferencing-with-zoom-api' ), value: 'minimal_flat' },
              { label: __( 'Pills (Rounded Badges)', 'video-conferencing-with-zoom-api' ), value: 'pills' },
            ] }
            onChange={ ( val ) => setAttributes( { layoutPreset: val } ) }
          />
          <ToggleControl
            label={ __( 'Show Days Unit', 'video-conferencing-with-zoom-api' ) }
            checked={ showDays }
            onChange={ ( val ) => setAttributes( { showDays: val } ) }
          />
          <ToggleControl
            label={ __( 'Show Seconds Unit', 'video-conferencing-with-zoom-api' ) }
            checked={ showSeconds }
            onChange={ ( val ) => setAttributes( { showSeconds: val } ) }
          />
        </PanelBody>

        <PanelBody title={ __( 'Status Messaging', 'video-conferencing-with-zoom-api' ) } initialOpen={ false }>
          <TextControl
            label={ __( 'Meeting Started Message', 'video-conferencing-with-zoom-api' ) }
            value={ startedText }
            onChange={ ( val ) => setAttributes( { startedText: val } ) }
          />
          <TextControl
            label={ __( 'Meeting Ended Message', 'video-conferencing-with-zoom-api' ) }
            value={ endedText }
            onChange={ ( val ) => setAttributes( { endedText: val } ) }
          />
          <ToggleControl
            label={ __( 'Keep "Started" Message Active All Day', 'video-conferencing-with-zoom-api' ) }
            help={ __( 'If enabled, "Meeting Started" stays visible until midnight instead of hiding after meeting duration.', 'video-conferencing-with-zoom-api' ) }
            checked={ keepStartedAllDay }
            onChange={ ( val ) => setAttributes( { keepStartedAllDay: val } ) }
          />
        </PanelBody>
      </InspectorControls>

      <div { ...blockProps }>
        <div className="vczapi-countdown-timer">
          { showDays && (
            <div className="vczapi-countdown-unit">
              <span className="vczapi-countdown-value">02</span>
              <span className="vczapi-countdown-label">{ __( 'DAYS', 'video-conferencing-with-zoom-api' ) }</span>
            </div>
          ) }
          <div className="vczapi-countdown-unit">
            <span className="vczapi-countdown-value">05</span>
            <span className="vczapi-countdown-label">{ __( 'HOURS', 'video-conferencing-with-zoom-api' ) }</span>
          </div>
          <div className="vczapi-countdown-unit">
            <span className="vczapi-countdown-value">42</span>
            <span className="vczapi-countdown-label">{ __( 'MINUTES', 'video-conferencing-with-zoom-api' ) }</span>
          </div>
          { showSeconds && (
            <div className="vczapi-countdown-unit">
              <span className="vczapi-countdown-value">18</span>
              <span className="vczapi-countdown-label">{ __( 'SECONDS', 'video-conferencing-with-zoom-api' ) }</span>
            </div>
          ) }
        </div>
      </div>
    </>
  );
}