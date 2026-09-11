import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes, context } ) {
  const { label, showLabel } = attributes;

  const blockProps = useBlockProps( {
    className: 'vczapi-meeting-detail-item vczapi-meeting-detail-topic',
  } );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Topic Settings', 'video-conferencing-with-zoom-api' ) }>
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
            />
          ) }
        </PanelBody>
      </InspectorControls>

      <div { ...blockProps }>
        { showLabel && (
          <span class="vczapi-meeting-detail-item__label">
						{ label }
					</span>
        ) }
        <span class="vczapi-meeting-detail-item__value">
					{ __( 'Meeting Topic Placeholder', 'video-conferencing-with-zoom-api' ) }
				</span>
      </div>
    </>
  );
}