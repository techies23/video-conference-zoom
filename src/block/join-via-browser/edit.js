import ServerSideRender from '@wordpress/server-side-render'
import { BlockControls, useBlockProps } from '@wordpress/block-editor'
import { debounce } from 'lodash'
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n'
import { useEffect, useState, useRef } from '@wordpress/element'

import {
  Placeholder,
  ToolbarButton,
  TextControl,
  ToolbarGroup,
  Button,
  RadioControl,
  Disabled,
  Spinner,
  SelectControl,
  ComboboxControl
} from '@wordpress/components'

export default function EditJoinViaBrowser (props) {
  const { className, attributes, setAttributes } = props
  const {
    host,
    selectedMeeting,
    disable_countdown,
    login_required,
    preview,
    shouldShow,
    passcode,
  } = attributes

  const isMounted = useRef()
  const [isEditing, setIsEditing] = useState(false)
  const [availableMeetings, setAvailableMeetings] = useState([])

  const [tempHost, setTempHost] = useState(host || null)
  const [tempShouldShow, setTempShouldShow] = useState(shouldShow?.value || shouldShow || 'meeting')
  const [tempSelectedMeeting, setTempSelectedMeeting] = useState(selectedMeeting || {})

  const [hostOptions, setHostOptions] = useState([])
  const [isLoadingHosts, setIsLoadingHosts] = useState(false)

  const [numberOfPages, setNumberOfPages] = useState(1)
  const [currentPage, setCurrentPage] = useState(1)

  const [isLoadingMeetings, setIsLoadingMeetings] = useState(false)

  // Debounced host search handler
  const handleHostFilter = debounce((input) => {
    setIsLoadingHosts(true)
    const searchParam = input ? encodeURIComponent(input) : ''

    fetch(ajaxurl + '?action=vczapi_get_zoom_hosts&host=' + searchParam)
    .then(response => response.json())
    .then(result => {
      if (isMounted.current && Array.isArray(result)) {
        const formatted = result.map(item => ({
          label: item.label || item.name || item.text,
          value: String(item.value || item.id)
        }))
        setHostOptions(formatted)
        setIsLoadingHosts(false)
      }
    })
    .catch(() => {
      if (isMounted.current) {
        setIsLoadingHosts(false)
      }
    })
  }, 300)

  const get_live_meetings = (host_id, shouldShowValue, additional_args = {}) => {
    if (!host_id || host_id === 'undefined') return []

    const showVal = typeof shouldShowValue === 'object' ? shouldShowValue.value : shouldShowValue
    let queryUrl = ajaxurl + '?action=vczapi_get_live_meetings&host_id=' +
      host_id + '&show=' + showVal

    if (additional_args.hasOwnProperty('page_number') &&
      additional_args.page_number !== 'undefined') {
      queryUrl += '&page_number=' + additional_args.page_number
    }

    setIsLoadingMeetings(true)
    fetch(queryUrl)
    .then(response => response.json())
    .then(result => {
      if (isMounted.current) {
        let returnedPages = parseFloat(result.total_records) / parseFloat(result.page_size)
        if (returnedPages > 1) {
          let pagination_count = Math.round(returnedPages)
          setNumberOfPages(pagination_count)
        } else {
          setNumberOfPages(1)
        }
        setAvailableMeetings(result.formatted_meetings || [])
        setIsLoadingMeetings(false)
      }
    })
    .catch(() => {
      if (isMounted.current) {
        setIsLoadingMeetings(false)
      }
    })
  }

  const PaginateLinks = ({ numberOfPages }) => {
    let pages = []
    if (numberOfPages > 1) {
      for (let i = 1; i <= numberOfPages; i++) {
        let className = (i === currentPage) ? 'selected' : ''
        pages.push(
          <Button
            key={i}
            className={className}
            onClick={() => {
              const currentHostVal = tempHost?.value || tempHost
              get_live_meetings(currentHostVal, tempShouldShow, {
                page_number: i,
              })
              setCurrentPage(i)
            }}>{i}</Button>)
      }
      return (
        <div className={'vczapi-blocks-pagination'}>
          {pages}
        </div>
      )
    }
    return ''
  }

  useEffect(() => {
    isMounted.current = true

    // 1. Fetch initial host options so the dropdown populates immediately when clicked
    setIsLoadingHosts(true)
    fetch(ajaxurl + '?action=vczapi_get_zoom_hosts&host=')
    .then(response => response.json())
    .then(result => {
      if (isMounted.current && Array.isArray(result)) {
        const formatted = result.map(item => ({
          label: item.label || item.name || item.text,
          value: String(item.value || item.id)
        }))
        setHostOptions(formatted)
        setIsLoadingHosts(false)
      }
    })
    .catch(() => {
      if (isMounted.current) {
        setIsLoadingHosts(false)
      }
    })

    // 2. Load meetings if host is already configured
    const initialHostVal = host?.value || host
    if (initialHostVal) {
      get_live_meetings(initialHostVal, tempShouldShow)
    }

    return () => {
      isMounted.current = false
    }
  }, [])

  if (preview) {
    return (
      <img src={vczapi_blocks.join_via_browser} alt={'Direct Meeting from Zoom'} />
    )
  }

  // Map meeting list items to valid string values for SelectControl
  const formattedMeetingOptions = availableMeetings.map(m => ({
    label: m.label,
    value: JSON.stringify(m)
  }))

  return (
    <div {...useBlockProps()}>
      <BlockControls>
        <ToolbarGroup>
          <ToolbarButton
            icon={(!isEditing) ? 'edit' : 'no'}
            title={(!isEditing) ? 'Edit' : 'Close'}
            subscript={'Edit'}
            onClick={() => {
              setIsEditing(prevIsEditing => !prevIsEditing)
            }}
          />
        </ToolbarGroup>
      </BlockControls>

      {(typeof selectedMeeting === 'undefined' || isEditing) &&
        <Placeholder>
          <div className="vczapi-label-header">
            <h2>{__('Zoom - Embed Join Via A Browser', 'video-conferencing-with-zoom-api')}</h2>
            <div><p>{__('Embed Join via a browser', 'video-conferencing-with-zoom-api')}</p></div>
          </div>
          <div className="vczapi-blocks-form">
            {
              (typeof selectedMeeting !== 'undefined' && selectedMeeting.hasOwnProperty('value'))
              && <div className={'vczapi-blocks-form--selected-meeting'}>
                <h4>Currently Selected Meeting: <strong>{selectedMeeting.label}</strong></h4>
              </div>
            }

            <div className="vczapi-blocks-form--group">
              <TextControl
                className={'text-input'}
                label={__(
                  'Passcode (Set password of your meeting to automatically let users join without needing them to enter password.)',
                  'video-conferencing-with-zoom-api')}
                value={passcode}
                onChange={(value) => {
                  setAttributes({ passcode: value })
                }}/>
            </div>

            <div className={'vczapi-blocks-form--group'}>
              <RadioControl
                className={'radio-inline'}
                label="Disable Countdown"
                selected={disable_countdown}
                options={[
                  { label: 'Yes', value: 'yes' },
                  { label: 'No', value: 'no' },
                ]}
                onChange={(option) => {
                  setAttributes({ disable_countdown: option })
                }}
              />
            </div>

            <div className={'vczapi-blocks-form--group'}>
              <RadioControl
                className={'radio-inline'}
                label="Login Required"
                selected={login_required}
                options={[
                  { label: 'Yes', value: 'yes' },
                  { label: 'No', value: 'no' },
                ]}
                onChange={(option) => {
                  setAttributes({ login_required: option })
                }}
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <SelectControl
                label={__('Would you like to show a Meeting or Webinar', 'video-conferencing-with-zoom-api')}
                value={tempShouldShow}
                options={[
                  { label: 'Meeting', value: 'meeting' },
                  { label: 'Webinar', value: 'webinar' },
                ]}
                onChange={(optionValue) => {
                  setTempShouldShow(optionValue)
                  const currentHostVal = tempHost?.value || tempHost
                  if (currentHostVal) {
                    setAvailableMeetings([])
                    get_live_meetings(currentHostVal, optionValue)
                  }
                }}
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <ComboboxControl
                label={__('Select A Host', 'video-conferencing-with-zoom-api')}
                help={__('Click to view available hosts or start typing to filter', 'video-conferencing-with-zoom-api')}
                value={tempHost?.value ? String(tempHost.value) : (typeof tempHost === 'string' ? tempHost : '')}
                options={hostOptions}
                onFilterValueChange={handleHostFilter}
                isLoading={isLoadingHosts}
                onChange={(selectedHostValue) => {
                  if (!selectedHostValue) return

                  const matchedObj = hostOptions.find(h => String(h.value) === String(selectedHostValue)) || {
                    value: selectedHostValue,
                    label: selectedHostValue
                  }

                  setAttributes({ host: matchedObj })
                  setTempHost(matchedObj)
                  get_live_meetings(selectedHostValue, tempShouldShow)
                }}
              />
            </div>

            {(isLoadingMeetings && (typeof availableMeetings === 'undefined' || availableMeetings.length === 0)) &&
              <div className="vczapi-blocks-form--group"><Spinner/></div>
            }

            {(typeof availableMeetings !== 'undefined' && availableMeetings.length > 0) &&
              <div className="vczapi-blocks-form--group">
                <SelectControl
                  label={
                    __('Select A Meeting : ', 'video-conferencing-with-zoom-api') +
                    (numberOfPages > 1 ? ' (use pagination below if necessary)' : '')
                  }
                  value={JSON.stringify(tempSelectedMeeting)}
                  options={[
                    { label: __('Select a meeting', 'video-conferencing-with-zoom-api'), value: '{}' },
                    ...formattedMeetingOptions
                  ]}
                  disabled={isLoadingMeetings}
                  onChange={(jsonString) => {
                    if (jsonString !== '{}') {
                      setTempSelectedMeeting(JSON.parse(jsonString))
                    }
                  }}
                />
                <PaginateLinks numberOfPages={numberOfPages}/>
              </div>
            }

            <div className="vczapi-blocks-form--group">
              <Button isPrimary onClick={() => {
                if (!tempSelectedMeeting.hasOwnProperty('value')) {
                  alert('Meeting Needs to be selected')
                  return false
                }
                setAttributes({ selectedMeeting: tempSelectedMeeting })
                setAttributes({ shouldShow: typeof tempShouldShow === 'object' ? tempShouldShow : { label: tempShouldShow, value: tempShouldShow } })
                setIsEditing(false)
              }}>{__('Save', 'video-conferencing-with-zoom-api')}</Button>
            </div>

          </div>
        </Placeholder>
      }

      {((typeof selectedMeeting !== 'undefined' && selectedMeeting.hasOwnProperty('value')) && !isEditing) &&
        <Disabled>
          <ServerSideRender
            block="vczapi/join-via-browser"
            attributes={
              {
                selectedMeeting: selectedMeeting,
                login_required: login_required,
                disable_countdown: disable_countdown,
                passcode: passcode,
                shouldShow: shouldShow,
              }
            }
          />
        </Disabled>
      }
    </div>
  )
}