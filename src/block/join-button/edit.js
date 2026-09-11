import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText,
  PanelColorSettings,
  ContrastChecker
} from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  ToggleControl,
  BoxControl,
  TabPanel
} from '@wordpress/components';
import MeetingSourceSelector from '../components/MeetingSourceSelector'


const ACTION_DEFAULTS = {
  app: __( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ),
  browser: __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ),
  start: __( 'Start Meeting', 'video-conferencing-with-zoom-api' ),
};

export default function Edit( { attributes, setAttributes } ) {
  const {
    actionType,
    sourceType,
    meetingId,
    selectedMeetingPostId,
    buttonText,
    openInNewTab,
    backgroundColor,
    textColor,
    bgHoverColor,
    textHoverColor,
    bgVisitedColor,
    textVisitedColor,
    padding,
    margin
  } = attributes;

  const handleActionTypeChange = ( newAction ) => {
    const isDefaultText = ! buttonText || Object.values( ACTION_DEFAULTS ).includes( buttonText );
    setAttributes( {
      actionType: newAction,
      buttonText: isDefaultText ? ACTION_DEFAULTS[ newAction ] : buttonText,
    } );
  };

  const blockProps = useBlockProps( {
    className: 'vczapi-button-block',
  } );

  const buttonStyles = {
    '--vczapi-btn-bg': backgroundColor || undefined,
    '--vczapi-btn-color': textColor || undefined,
    '--vczapi-btn-bg-hover': bgHoverColor || undefined,
    '--vczapi-btn-color-hover': textHoverColor || undefined,
    '--vczapi-btn-bg-visited': bgVisitedColor || undefined,
    '--vczapi-btn-color-visited': textVisitedColor || undefined,
    paddingTop: padding?.top,
    paddingRight: padding?.right,
    paddingBottom: padding?.bottom,
    paddingLeft: padding?.left,
    marginTop: margin?.top,
    marginRight: margin?.right,
    marginBottom: margin?.bottom,
    marginLeft: margin?.left,
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Action Settings', 'video-conferencing-with-zoom-api' ) }>
          <SelectControl
            label={ __( 'Button Action', 'video-conferencing-with-zoom-api' ) }
            value={ actionType }
            options={ [
              { label: __( 'Join via App', 'video-conferencing-with-zoom-api' ), value: 'app' },
              { label: __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ), value: 'browser' },
              { label: __( 'Start Meeting', 'video-conferencing-with-zoom-api' ), value: 'start' },
            ] }
            onChange={ handleActionTypeChange }
          />
          <ToggleControl
            label={ __( 'Open link in new tab', 'video-conferencing-with-zoom-api' ) }
            checked={ openInNewTab }
            onChange={ ( val ) => setAttributes( { openInNewTab: val } ) }
          />
        </PanelBody>

        { /* Reusable Meeting Source Selector */ }
        <MeetingSourceSelector
          sourceType={ sourceType }
          meetingId={ meetingId }
          selectedMeetingPostId={ selectedMeetingPostId }
          onChangeSourceType={ ( val ) => setAttributes( { sourceType: val } ) }
          onChangeMeetingId={ ( val ) => setAttributes( { meetingId: val } ) }
          onChangeSelectedMeetingPostId={ ( val ) => setAttributes( { selectedMeetingPostId: val } ) }
        />
      </InspectorControls>

      <InspectorControls group="styles">
        <PanelBody title={ __( 'Color Settings', 'video-conferencing-with-zoom-api' ) }>
          <TabPanel
            className="vczapi-color-tab-panel"
            activeClass="is-active"
            tabs={ [
              { name: 'default', title: __( 'Default', 'video-conferencing-with-zoom-api' ) },
              { name: 'hover', title: __( 'Hover', 'video-conferencing-with-zoom-api' ) },
              { name: 'visited', title: __( 'Visited', 'video-conferencing-with-zoom-api' ) },
            ] }
          >
            { ( tab ) => {
              if ( tab.name === 'hover' ) {
                return (
                  <PanelColorSettings
                    title={ __( 'Hover Colors', 'video-conferencing-with-zoom-api' ) }
                    initialOpen={ true }
                    colorSettings={ [
                      {
                        value: bgHoverColor,
                        onChange: ( val ) => setAttributes( { bgHoverColor: val || '' } ),
                        label: __( 'Background Color', 'video-conferencing-with-zoom-api' ),
                      },
                      {
                        value: textHoverColor,
                        onChange: ( val ) => setAttributes( { textHoverColor: val || '' } ),
                        label: __( 'Text Color', 'video-conferencing-with-zoom-api' ),
                      },
                    ] }
                  />
                );
              }

              if ( tab.name === 'visited' ) {
                return (
                  <PanelColorSettings
                    title={ __( 'Visited Colors', 'video-conferencing-with-zoom-api' ) }
                    initialOpen={ true }
                    colorSettings={ [
                      {
                        value: bgVisitedColor,
                        onChange: ( val ) => setAttributes( { bgVisitedColor: val || '' } ),
                        label: __( 'Background Color', 'video-conferencing-with-zoom-api' ),
                      },
                      {
                        value: textVisitedColor,
                        onChange: ( val ) => setAttributes( { textVisitedColor: val || '' } ),
                        label: __( 'Text Color', 'video-conferencing-with-zoom-api' ),
                      },
                    ] }
                  />
                );
              }

              return (
                <PanelColorSettings
                  title={ __( 'Default Colors', 'video-conferencing-with-zoom-api' ) }
                  initialOpen={ true }
                  colorSettings={ [
                    {
                      value: backgroundColor,
                      onChange: ( val ) => setAttributes( { backgroundColor: val || '' } ),
                      label: __( 'Background Color', 'video-conferencing-with-zoom-api' ),
                    },
                    {
                      value: textColor,
                      onChange: ( val ) => setAttributes( { textColor: val || '' } ),
                      label: __( 'Text Color', 'video-conferencing-with-zoom-api' ),
                    },
                  ] }
                >
                  <ContrastChecker
                    backgroundColor={ backgroundColor }
                    textColor={ textColor }
                  />
                </PanelColorSettings>
              );
            } }
          </TabPanel>
        </PanelBody>

        <PanelBody title={ __( 'Dimensions', 'video-conferencing-with-zoom-api' ) }>
          <BoxControl
            label={ __( 'Padding', 'video-conferencing-with-zoom-api' ) }
            values={ padding }
            onChange={ ( val ) => setAttributes( { padding: val } ) }
          />
          <BoxControl
            label={ __( 'Margin', 'video-conferencing-with-zoom-api' ) }
            values={ margin }
            onChange={ ( val ) => setAttributes( { margin: val } ) }
          />
        </PanelBody>
      </InspectorControls>

      <div { ...blockProps }>
        <RichText
          tagName="span"
          className="vczapi-btn"
          style={ buttonStyles }
          value={ buttonText }
          onChange={ ( newText ) => setAttributes( { buttonText: newText } ) }
          placeholder={ ACTION_DEFAULTS[ actionType ] }
          allowedFormats={ [ 'core/bold', 'core/italic' ] }
        />
      </div>
    </>
  );
}