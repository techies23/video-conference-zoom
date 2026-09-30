import { __ } from '@wordpress/i18n'
import { useEffect, useState, useMemo, useCallback } from '@wordpress/element'
import { BlockControls, useBlockProps } from '@wordpress/block-editor'
import ServerSideRender from '@wordpress/server-side-render'
import {
  Placeholder,
  ToolbarGroup,
  ToolbarButton,
  Button,
  TextControl,
  RadioControl,
  SelectControl,
  ComboboxControl,
  Spinner,
  Disabled,
  Notice,
} from '@wordpress/components'
import { debounce } from 'lodash'

export default function EditJoinViaBrowser( { attributes, setAttributes } ) {
  const {
    host,
    selectedMeeting,
    disable_countdown,
    login_required,
    preview,
    shouldShow,
    passcode,
  } = attributes

  const [ isEditing, setIsEditing ] = useState( false )
  const [ availableMeetings, setAvailableMeetings ] = useState( [] )
  const [ validationError, setValidationError ] = useState( '' )

  // Local draft states for editing before saving
  const [ tempHost, setTempHost ] = useState( host || null )
  const [ tempShouldShow, setTempShouldShow ] = useState(
    shouldShow?.value || shouldShow || 'meeting'
  )
  const [ tempSelectedMeeting, setTempSelectedMeeting ] = useState(
    selectedMeeting || {}
  )

  const [ hostOptions, setHostOptions ] = useState( [] )
  const [ isLoadingHosts, setIsLoadingHosts ] = useState( false )

  const [ numberOfPages, setNumberOfPages ] = useState( 1 )
  const [ currentPage, setCurrentPage ] = useState( 1 )
  const [ isLoadingMeetings, setIsLoadingMeetings ] = useState( false )

  // 1. Fetch Hosts via AJAX
  const fetchHosts = useCallback( ( searchInput = '' ) => {
    setIsLoadingHosts( true )
    const searchParam = searchInput ? encodeURIComponent( searchInput ) : ''
    const ajaxUrl = window.ajaxurl || 'admin-ajax.php'

    fetch( `${ ajaxUrl }?action=vczapi_get_zoom_hosts&host=${ searchParam }` )
    .then( ( response ) => response.json() )
    .then( ( result ) => {
      if ( Array.isArray( result ) ) {
        const formatted = result.map( ( item ) => ( {
          label: item.label || item.name || item.text || item.value,
          value: String( item.value || item.id ),
        } ) )
        setHostOptions( formatted )
      }
    } )
    .catch( () => {
      setHostOptions( [] )
    } )
    .finally( () => {
      setIsLoadingHosts( false )
    } )
  }, [] )

  // Debounce the host search query
  const debouncedFetchHosts = useMemo(
    () => debounce( fetchHosts, 300 ),
    [ fetchHosts ]
  )

  // 2. Fetch Live Meetings/Webinars
  const getLiveMeetings = useCallback(
    ( hostId, showVal, pageNumber = 1 ) => {
      if ( ! hostId ) return

      const cleanShowVal =
        typeof showVal === 'object' ? showVal.value : showVal
      const ajaxUrl = window.ajaxurl || 'admin-ajax.php'
      let queryUrl = `${ ajaxUrl }?action=vczapi_get_live_meetings&host_id=${ hostId }&show=${ cleanShowVal }`

      if ( pageNumber > 1 ) {
        queryUrl += `&page_number=${ pageNumber }`
      }

      setIsLoadingMeetings( true )
      fetch( queryUrl )
      .then( ( response ) => response.json() )
      .then( ( result ) => {
        const totalRecords = parseFloat( result?.total_records || 0 )
        const pageSize = parseFloat( result?.page_size || 1 )
        const returnedPages = totalRecords / pageSize

        setNumberOfPages( returnedPages > 1 ? Math.round( returnedPages ) : 1 )
        setAvailableMeetings( result?.formatted_meetings || [] )
      } )
      .catch( () => {
        setAvailableMeetings( [] )
      } )
      .finally( () => {
        setIsLoadingMeetings( false )
      } )
    },
    []
  )

  // Initial load handler
  useEffect( () => {
    fetchHosts()

    const initialHostVal = host?.value || host
    if ( initialHostVal ) {
      getLiveMeetings( initialHostVal, tempShouldShow )
    }

    return () => {
      debouncedFetchHosts.cancel()
    }
  }, [ fetchHosts, getLiveMeetings, debouncedFetchHosts ] )

  if ( preview ) {
    return (
      <img
        src={ window.vczapi_blocks?.join_via_browser }
        alt={ __( 'Direct Meeting from Zoom', 'video-conferencing-with-zoom-api' ) }
      />
    )
  }

  const blockProps = useBlockProps()

  // Pagination Helper Component
  const PaginateLinks = () => {
    if ( numberOfPages <= 1 ) return null

    const pages = []
    for ( let i = 1; i <= numberOfPages; i++ ) {
      pages.push(
        <Button
          key={ i }
          variant={ i === currentPage ? 'primary' : 'secondary' }
          isSmall
          onClick={ () => {
            const currentHostVal = tempHost?.value || tempHost
            getLiveMeetings( currentHostVal, tempShouldShow, i )
            setCurrentPage( i )
          } }
        >
          { i }
        </Button>
      )
    }

    return <div className="vczapi-blocks-pagination">{ pages }</div>
  }

  // Pre-format meeting options for SelectControl
  const formattedMeetingOptions = availableMeetings.map( ( m ) => ( {
    label: m.label,
    value: JSON.stringify( m ),
  } ) )

  const handleSave = () => {
    if ( ! tempSelectedMeeting?.value ) {
      setValidationError(
        __( 'Please select a meeting before saving.', 'video-conferencing-with-zoom-api' )
      )
      return
    }

    setValidationError( '' )
    setAttributes( {
      selectedMeeting: tempSelectedMeeting,
      host: tempHost,
      shouldShow:
        typeof tempShouldShow === 'object'
          ? tempShouldShow
          : { label: tempShouldShow, value: tempShouldShow },
    } )
    setIsEditing( false )
  }

  return (
    <div { ...blockProps }>
      <BlockControls>
        <ToolbarGroup>
          <ToolbarButton
            icon={ ! isEditing ? 'edit' : 'no' }
            title={
              ! isEditing
                ? __( 'Edit', 'video-conferencing-with-zoom-api' )
                : __( 'Close', 'video-conferencing-with-zoom-api' )
            }
            onClick={ () => setIsEditing( ( prev ) => ! prev ) }
          />
        </ToolbarGroup>
      </BlockControls>

      { ( ! selectedMeeting?.value || isEditing ) && (
        <Placeholder
          label={ __(
            'Zoom - Embed Join Via A Browser',
            'video-conferencing-with-zoom-api'
          ) }
          instructions={ __(
            'Embed Join via a browser',
            'video-conferencing-with-zoom-api'
          ) }
        >
          <div className="vczapi-blocks-form">
            { validationError && (
              <Notice status="error" isDismissible={ false }>
                { validationError }
              </Notice>
            ) }

            { selectedMeeting?.label && (
              <div className="vczapi-blocks-form--selected-meeting">
                <h4>
                  { __( 'Currently Selected Meeting:', 'video-conferencing-with-zoom-api' ) }{ ' ' }
                  <strong>{ selectedMeeting.label }</strong>
                </h4>
              </div>
            ) }

            <div className="vczapi-blocks-form--group">
              <TextControl
                label={ __(
                  'Passcode (Set password of your meeting to automatically let users join without needing them to enter password.)',
                  'video-conferencing-with-zoom-api'
                ) }
                value={ passcode || '' }
                onChange={ ( value ) => setAttributes( { passcode: value } ) }
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <RadioControl
                label={ __( 'Disable Countdown', 'video-conferencing-with-zoom-api' ) }
                selected={ disable_countdown || 'no' }
                options={ [
                  { label: __( 'Yes', 'video-conferencing-with-zoom-api' ), value: 'yes' },
                  { label: __( 'No', 'video-conferencing-with-zoom-api' ), value: 'no' },
                ] }
                onChange={ ( option ) =>
                  setAttributes( { disable_countdown: option } )
                }
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <RadioControl
                label={ __( 'Login Required', 'video-conferencing-with-zoom-api' ) }
                selected={ login_required || 'no' }
                options={ [
                  { label: __( 'Yes', 'video-conferencing-with-zoom-api' ), value: 'yes' },
                  { label: __( 'No', 'video-conferencing-with-zoom-api' ), value: 'no' },
                ] }
                onChange={ ( option ) =>
                  setAttributes( { login_required: option } )
                }
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <SelectControl
                label={ __(
                  'Would you like to show a Meeting or Webinar',
                  'video-conferencing-with-zoom-api'
                ) }
                value={ tempShouldShow }
                options={ [
                  { label: __( 'Meeting', 'video-conferencing-with-zoom-api' ), value: 'meeting' },
                  { label: __( 'Webinar', 'video-conferencing-with-zoom-api' ), value: 'webinar' },
                ] }
                onChange={ ( optionValue ) => {
                  setTempShouldShow( optionValue )
                  const currentHostVal = tempHost?.value || tempHost
                  if ( currentHostVal ) {
                    setAvailableMeetings( [] )
                    getLiveMeetings( currentHostVal, optionValue )
                  }
                } }
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <ComboboxControl
                label={ __( 'Select A Host', 'video-conferencing-with-zoom-api' ) }
                help={ __(
                  'Click to view available hosts or start typing to filter',
                  'video-conferencing-with-zoom-api'
                ) }
                value={
                  tempHost?.value
                    ? String( tempHost.value )
                    : typeof tempHost === 'string'
                      ? tempHost
                      : ''
                }
                options={ hostOptions }
                onFilterValueChange={ ( value ) => debouncedFetchHosts( value ) }
                isLoading={ isLoadingHosts }
                onChange={ ( selectedHostValue ) => {
                  if ( ! selectedHostValue ) return

                  const matchedObj = hostOptions.find(
                    ( h ) => String( h.value ) === String( selectedHostValue )
                  ) || {
                    value: selectedHostValue,
                    label: selectedHostValue,
                  }

                  setTempHost( matchedObj )
                  getLiveMeetings( selectedHostValue, tempShouldShow )
                } }
              />
            </div>

            { isLoadingMeetings && availableMeetings.length === 0 && (
              <div className="vczapi-blocks-form--group">
                <Spinner />
              </div>
            ) }

            { availableMeetings.length > 0 && (
              <div className="vczapi-blocks-form--group">
                <SelectControl
                  label={
                    __( 'Select A Meeting:', 'video-conferencing-with-zoom-api' ) +
                    ( numberOfPages > 1
                      ? __( ' (use pagination below if necessary)', 'video-conferencing-with-zoom-api' )
                      : '' )
                  }
                  value={ JSON.stringify( tempSelectedMeeting ) }
                  options={ [
                    {
                      label: __( 'Select a meeting', 'video-conferencing-with-zoom-api' ),
                      value: '{}',
                    },
                    ...formattedMeetingOptions,
                  ] }
                  disabled={ isLoadingMeetings }
                  onChange={ ( jsonString ) => {
                    if ( jsonString !== '{}' ) {
                      setTempSelectedMeeting( JSON.parse( jsonString ) )
                    }
                  } }
                />
                <PaginateLinks />
              </div>
            ) }

            <div className="vczapi-blocks-form--group">
              <Button variant="primary" onClick={ handleSave }>
                { __( 'Save', 'video-conferencing-with-zoom-api' ) }
              </Button>
            </div>
          </div>
        </Placeholder>
      ) }

      { selectedMeeting?.value && ! isEditing && (
        <Disabled>
          <ServerSideRender
            block="vczapi/join-via-browser"
            attributes={ {
              selectedMeeting,
              login_required,
              disable_countdown,
              passcode,
              shouldShow,
            } }
          />
        </Disabled>
      ) }
    </div>
  )
}