import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
  const { label, showLabel, timezoneDisplay } = attributes;

  const blockProps = useBlockProps( {
    className: 'vczapi-meeting-detail-item vczapi-meeting-detail-timezone',
  } );

  const previewTz = timezoneDisplay === 'user'
    ? __( 'Browser Timezone (e.g. Asia/Kathmandu)', 'video-conferencing-with-zoom-api' )
    : __( 'Meeting Timezone (e.g. UTC)', 'video-conferencing-with-zoom-api' );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Timezone Settings', 'video-conferencing-with-zoom-api' ) }>
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
              placeholder={ __( 'Timezone:', 'video-conferencing-with-zoom-api' ) }
            />
          ) }
          <SelectControl
            label={ __( 'Timezone Display', 'video-conferencing-with-zoom-api' ) }
            value={ timezoneDisplay }
            options={ [
              { label: __( 'Visitor Local Timezone', 'video-conferencing-with-zoom-api' ), value: 'user' },
              { label: __( 'Meeting Timezone', 'video-conferencing-with-zoom-api' ), value: 'meeting' },
            ] }
            onChange={ ( val ) => setAttributes( { timezoneDisplay: val } ) }
            help={ __( 'Visitor Local Timezone automatically resolves using the user browser locale.', 'video-conferencing-with-zoom-api' ) }
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
            placeholder={ __( 'Timezone:', 'video-conferencing-with-zoom-api' ) }
            allowedFormats={ [] }
          />
        ) }
        <span className="vczapi-meeting-detail-item__value">
          { previewTz }
        </span>
      </div>
    </>
  );
}