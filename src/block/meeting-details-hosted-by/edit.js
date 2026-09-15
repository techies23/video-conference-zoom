import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes, context } ) {
  const { label, showLabel, hostType, customHostName } = attributes;
  const sourceType = context['vczapi/sourceType'] || 'current';

  const blockProps = useBlockProps( {
    className: 'vczapi-meeting-detail-item vczapi-meeting-detail-hosted-by',
  } );

  const isCustomSource = sourceType === 'custom';
  const effectiveHostType = isCustomSource ? 'custom' : hostType;

  const previewHost = effectiveHostType === 'custom'
    ? ( customHostName || __( 'Custom Host Name', 'video-conferencing-with-zoom-api' ) )
    : __( 'Post Author Name', 'video-conferencing-with-zoom-api' );

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Host Settings', 'video-conferencing-with-zoom-api' ) }>
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
              placeholder={ __( 'Hosted By:', 'video-conferencing-with-zoom-api' ) }
            />
          ) }

          { ! isCustomSource && (
            <SelectControl
              label={ __( 'Host Source', 'video-conferencing-with-zoom-api' ) }
              value={ hostType }
              options={ [
                { label: __( 'Post Author', 'video-conferencing-with-zoom-api' ), value: 'post_author' },
                { label: __( 'Custom', 'video-conferencing-with-zoom-api' ), value: 'custom' },
              ] }
              onChange={ ( val ) => setAttributes( { hostType: val } ) }
            />
          ) }

          { ( isCustomSource || hostType === 'custom' ) && (
            <TextControl
              label={ __( 'Custom Host Name', 'video-conferencing-with-zoom-api' ) }
              value={ customHostName }
              onChange={ ( val ) => setAttributes( { customHostName: val } ) }
              placeholder={ __( 'Enter host name', 'video-conferencing-with-zoom-api' ) }
              help={ isCustomSource ? __( 'Custom meeting source has no associated post author.', 'video-conferencing-with-zoom-api' ) : '' }
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
            placeholder={ __( 'Hosted By:', 'video-conferencing-with-zoom-api' ) }
            allowedFormats={ [] }
          />
        ) }
        <span className="vczapi-meeting-detail-item__value">
          { previewHost }
        </span>
      </div>
    </>
  );
}