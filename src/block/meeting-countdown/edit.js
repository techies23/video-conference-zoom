import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl, ColorPalette, FontSizePicker } from '@wordpress/components';
import MeetingSourceSelector from '../components/MeetingSourceSelector';

export default function Edit( { attributes, setAttributes } ) {
  const {
    sourceType,
    customMeetingId,
    selectedMeetingPostId,
    layoutPreset,
    unitBackgroundColor,
    labelFontSize,
    startedText,
    endedText,
    showDays,
    showSeconds,
    keepStartedAllDay
  } = attributes;

  const styleObj = {};
  if ( unitBackgroundColor ) {
    styleObj['--vczapi-unit-bg'] = unitBackgroundColor;
  }
  if ( labelFontSize ) {
    styleObj['--vczapi-label-size'] = labelFontSize;
  }

  const blockProps = useBlockProps( {
    className: `vczapi-meeting-countdown vczapi-meeting-countdown--${ layoutPreset }`,
    style: styleObj,
  } );

  const fontSizePresets = [
    { name: __( 'Small', 'video-conferencing-with-zoom-api' ), slug: 'small', size: '0.65rem' },
    { name: __( 'Medium', 'video-conferencing-with-zoom-api' ), slug: 'medium', size: '0.75rem' },
    { name: __( 'Large', 'video-conferencing-with-zoom-api' ), slug: 'large', size: '0.875rem' },
  ];

  return (
    <>
      { /* Main Settings Tab */ }
      <InspectorControls>
        <MeetingSourceSelector
          sourceType={ sourceType }
          meetingId={ customMeetingId }
          selectedMeetingPostId={ selectedMeetingPostId }
          onChangeSourceType={ ( val ) => setAttributes( { sourceType: val } ) }
          onChangeMeetingId={ ( val ) => setAttributes( { customMeetingId: val } ) }
          onChangeSelectedMeetingPostId={ ( val ) => setAttributes( { selectedMeetingPostId: val } ) }
        />

        <PanelBody title={ __( 'Countdown Settings', 'video-conferencing-with-zoom-api' ) }>
          <SelectControl
            label={ __( 'Design Preset', 'video-conferencing-with-zoom-api' ) }
            value={ layoutPreset }
            options={ [
              { label: __( 'Cards (Dark Tiles)', 'video-conferencing-with-zoom-api' ), value: 'cards' },
              { label: __( 'Minimal (Colon Separated)', 'video-conferencing-with-zoom-api' ), value: 'minimal' },
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

      { /* Styles Tab */ }
      <InspectorControls group="styles">
        <PanelBody title={ __( 'Countdown Styles', 'video-conferencing-with-zoom-api' ) }>
          <div>
            <p className="components-base-control__label">
              { __( 'Label Font Size', 'video-conferencing-with-zoom-api' ) }
            </p>
            <FontSizePicker
              fontSizes={ fontSizePresets }
              value={ labelFontSize }
              onChange={ ( newSize ) => setAttributes( { labelFontSize: newSize || '0.75rem' } ) }
              fallbackFontSize="0.75rem"
            />
          </div>

          { ( layoutPreset === 'cards' || layoutPreset === 'pills' ) && (
            <div style={ { marginTop: '16px' } }>
              <p className="components-base-control__label">
                { __( 'Tile / Pill Background Color', 'video-conferencing-with-zoom-api' ) }
              </p>
              <ColorPalette
                value={ unitBackgroundColor }
                onChange={ ( color ) => setAttributes( { unitBackgroundColor: color } ) }
              />
            </div>
          ) }
        </PanelBody>
      </InspectorControls>

      <div { ...blockProps }>
        <div className="vczapi-countdown-timer">
          { showDays && (
            <>
              <div className="vczapi-countdown-unit vczapi-countdown-unit--days">
                <span className="vczapi-countdown-value">01</span>
                <span className="vczapi-countdown-label">{ __( 'DAYS', 'video-conferencing-with-zoom-api' ) }</span>
              </div>
              { layoutPreset === 'minimal' && <span className="vczapi-countdown-separator">:</span> }
            </>
          ) }
          <div className="vczapi-countdown-unit vczapi-countdown-unit--hours">
            <span className="vczapi-countdown-value">09</span>
            <span className="vczapi-countdown-label">{ __( 'HOURS', 'video-conferencing-with-zoom-api' ) }</span>
          </div>
          { layoutPreset === 'minimal' && <span className="vczapi-countdown-separator">:</span> }
          <div className="vczapi-countdown-unit vczapi-countdown-unit--minutes">
            <span className="vczapi-countdown-value">34</span>
            <span className="vczapi-countdown-label">{ __( 'MINUTES', 'video-conferencing-with-zoom-api' ) }</span>
          </div>
          { showSeconds && (
            <>
              { layoutPreset === 'minimal' && <span className="vczapi-countdown-separator">:</span> }
              <div className="vczapi-countdown-unit vczapi-countdown-unit--seconds">
                <span className="vczapi-countdown-value">12</span>
                <span className="vczapi-countdown-label">{ __( 'SECONDS', 'video-conferencing-with-zoom-api' ) }</span>
              </div>
            </>
          ) }
        </div>
      </div>
    </>
  );
}