import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
  const { label, showLabel } = attributes;

  const blockProps = useBlockProps( {
    className: 'vczapi-meeting-detail-item vczapi-meeting-detail-duration',
  } );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Duration Settings', 'video-conferencing-with-zoom-api' ) }>
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
              placeholder={ __( 'Duration:', 'video-conferencing-with-zoom-api' ) }
            />
          ) }
        </PanelBody>
      </InspectorControls>

      <div { ...blockProps }>
        { showLabel && (
          <RichText
            tagName="span"
            className="vczapi-meeting-detail-item__label"
            value={ label }
            onChange={ ( val ) => setAttributes( { label: val } ) }
            placeholder={ __( 'Duration:', 'video-conferencing-with-zoom-api' ) }
            allowedFormats={ [] }
          />
        ) }
        <span className="vczapi-meeting-detail-item__value">
          { __( '40 minutes', 'video-conferencing-with-zoom-api' ) }
        </span>
      </div>
    </>
  );
}