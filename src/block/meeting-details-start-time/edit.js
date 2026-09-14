import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
  const { label, showLabel, timezoneDisplay, dateStyle } = attributes;

  const blockProps = useBlockProps( {
    className: 'vczapi-meeting-detail-item vczapi-meeting-detail-start-time',
  } );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Start Time Settings', 'video-conferencing-with-zoom-api' ) }>
          <ToggleControl
            label={ __( 'Show Label', 'video-conferencing-with-zoom-api' ) }
            checked={ showLabel }
            onChange={ ( val ) => setAttributes( { showLabel: val } ) }
          />
          { showLabel && (
            <TextControl
              label={ __( 'Label Text', 'video-conferencing-with-zoom-api' ) }
              value={ label }
              onChange={ ( val ) => setAttributes( { label: val } ) }
              placeholder={ __( 'Start Time:', 'video-conferencing-with-zoom-api' ) }
            />
          ) }
          <SelectControl
            label={ __( 'Timezone Mode', 'video-conferencing-with-zoom-api' ) }
            value={ timezoneDisplay }
            options={ [
              { label: __( 'Visitor Local Timezone (Default)', 'video-conferencing-with-zoom-api' ), value: 'user' },
              { label: __( 'Meeting Timezone', 'video-conferencing-with-zoom-api' ), value: 'meeting' },
            ] }
            onChange={ ( val ) => setAttributes( { timezoneDisplay: val } ) }
          />
          <SelectControl
            label={ __( 'Date & Time Style', 'video-conferencing-with-zoom-api' ) }
            value={ dateStyle }
            options={ [
              { label: __( 'Full (Friday, September 11, 2026 1:48 PM)', 'video-conferencing-with-zoom-api' ), value: 'full' },
              { label: __( 'Long (September 11, 2026 1:48 PM)', 'video-conferencing-with-zoom-api' ), value: 'long' },
              { label: __( 'Medium (Sep 11, 2026, 1:48 PM)', 'video-conferencing-with-zoom-api' ), value: 'medium' },
              { label: __( 'Short (9/11/2026, 1:48 PM)', 'video-conferencing-with-zoom-api' ), value: 'short' },
              { label: __( 'Date Only (Friday, September 11, 2026)', 'video-conferencing-with-zoom-api' ), value: 'date_only' },
              { label: __( 'Time Only (1:48 PM)', 'video-conferencing-with-zoom-api' ), value: 'time_only' },
            ] }
            onChange={ ( val ) => setAttributes( { dateStyle: val } ) }
          />
        </PanelBody>
      </InspectorControls>

      <div { ...blockProps }>
        { showLabel && (
          <RichText
            tagName="span"
            className="vczapi-meeting-detail-item__label"
            value={ label }
            onChange={ ( val ) => setAttributes( { label: val } ) }
            placeholder={ __( 'Start Time:', 'video-conferencing-with-zoom-api' ) }
            allowedFormats={ [] }
          />
        ) }
        <span className="vczapi-meeting-detail-item__value">
          Friday, September 11, 2026 1:48 PM
        </span>
      </div>
    </>
  );
}